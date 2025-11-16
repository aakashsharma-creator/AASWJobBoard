<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit;
}

function my_custom_list_job_shortcode()
{
  ob_start();

  $current_user = wp_get_current_user();
  // Query job listings

  $preview_jobs = new WP_Query(array(
    'post_type' => 'job_listing',
    'post_status' => array('preview'),
    'author' => $current_user->ID,
    'posts_per_page' => -1,
  ));



  if ($preview_jobs->have_posts()) {
    while ($preview_jobs->have_posts()) {
      $preview_jobs->the_post();
      $preview_id = get_the_ID();

      // Update job to publish
      wp_update_post(array(
        'ID' => $preview_id,
        'post_status' => 'publish',
      ));
    }
    wp_reset_postdata();
  }



  $args = array(
    'post_type' => 'job_listing',
    'post_status' => array('publish', 'draft'),
    'author' => $current_user->ID,
    'posts_per_page' => -1, // Fetch all jobs
  );

  $jobs = new WP_Query($args);
  $delete_nonce = wp_create_nonce('delete_job_nonce');
  $duplicate_nonce = wp_create_nonce('duplicate_job_nonce');

  // Get the "Post a Job" page ID from WP Job Manager settings
  $post_a_job_page_id = get_option('job_manager_submit_job_form_page_id');

  // Get the URL of that page
  $post_a_job_page_url = $post_a_job_page_id ? get_permalink($post_a_job_page_id) : '';
?>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css" />
  <!-- AlertifyJS JS -->
  <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

  <link rel="stylesheet" type="text/css"
    href="<?php echo get_stylesheet_directory_uri(); ?>/custom-shortcodes/css/list_job.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
  <style>
    #job-date-range {
      transition: all 0.2s ease;
      user-select: none;
    }

    #job-date-range:hover {
      background: #f0f0f0 !important;
      border-color: #999 !important;
    }
  </style>
  <div class="job-listing-container">

    <!-- Header -->

    <!-- Table -->
    <div class="page">
      <!-- Top header -->
      <div class="page-header page-header-joblist">
        <div>
          <h2>Job Listing</h2>
          <p id="job-subtitle" class="job-subtitle"></p>

          <?php
          global $wpdb;
          $current_user_id = get_current_user_id();

          $table_name = $wpdb->prefix . 'wcpl_user_packages'; // wp_wcpl_user_packages

          $result = $wpdb->get_row(
            $wpdb->prepare(
              "SELECT package_count, package_duration, order_id, package_limit
			FROM $table_name 
			WHERE user_id = %d AND `package_type`='job_listing'
			order by `id` DESC LIMIT 1 ",
              $current_user_id
            )
          );

          $package_count_now = $result->package_count;
          $package_limit = $result->package_limit;
          $package_count = 0;

          if ($package_limit > $package_count_now) {
            $package_count = 1;
          }
          //echo '<br>';
          $package_duration = $result->package_duration;
          //echo '<br>';
          $order_id = $result->order_id;

          // Fetch order (post) date from wp_posts
          $order_post = $wpdb->get_row(
            $wpdb->prepare(
              "SELECT post_date FROM {$wpdb->prefix}posts WHERE ID = %d",
              $order_id
            )
          );

          $order_date = $order_post->post_date;

          // Convert to timestamp and calculate expiry
          $order_timestamp = strtotime($order_date);
          $expiry_timestamp = strtotime("+$package_duration days", $order_timestamp);
          $current_timestamp = current_time('timestamp'); // WP timezone safe

          $package_expiry_date = date('Y-m-d', $expiry_timestamp);

          // Check expiration
          if ($current_timestamp > $expiry_timestamp) {
            $status = 'Expired';
          } else {
            $status = 'Active';
          }

          ?>

        </div>
        <div>
          <span id="job-date-range" class="date-range"
            style="cursor:pointer; display:inline-block; padding:6px 10px; border:1px solid #ccc; border-radius:4px; background:#fff;">
          </span>
          <a href="<?php echo esc_url($post_a_job_page_url); ?>">
            <button class="btn-primary">+ Post a New Job</button>
          </a>
        </div>
      </div>

      <!-- Job list card -->
      <div class="card">
        <div class="card-header">
          <h3>Job List</h3>
          <input type="hidden" id="package_status" value="<?php echo $status; ?>" />
          <input type="hidden" id="package_count" value="<?php echo $package_count; ?>" />
          <input type="hidden" id="package_expiry_date" value="<?php echo $package_expiry_date; ?>" />
        </div>
        <div class="table-responsive">
          <table class="list-job">
            <thead>
              <tr>
                <th>Job Title</th>
                <th>Status</th>
                <th>Date Posted</th>
                <th>Expiry Date</th>
                <!-- <th>Job Type</th> -->
                <th>Views</th>
                <!--<th>Category</th>-->
                <th align="center" style="text-align:center">Actions</th>
              </tr>
            </thead>

            <tbody>
              <?php if ($jobs->have_posts()): ?>
                <?php while ($jobs->have_posts()):
                  $jobs->the_post();
                  $post_id = get_the_ID();

                  // Get job data from WP Job Manager meta fields
                  $status = get_post_status($post_id); // publish, draft, expired
                  $job_type_terms = wp_get_post_terms($post_id, 'job_listing_type');
                  $job_types = !empty($job_type_terms) ? wp_list_pluck($job_type_terms, 'name') : array();
                  $job_type_display = !empty($job_types) ? implode(', ', $job_types) : 'N/A';

                  $date_posted = get_the_date('M-d-Y', $post_id);
                  $expiry_date = get_post_meta($post_id, '__job_expires', true);
                  $expiry_date = $expiry_date ? date('M-d-Y', strtotime($expiry_date)) : '-';
                  $expiry_date_for_check = $expiry_date ? date('Y-m-d', strtotime($expiry_date)) : '-';
                  // echo '<br>';
                  $now = date('Y-m-d');

                  $views = get_post_meta($post_id, '_job_views', true) ?: 0;

                  $categories = wp_get_post_terms($post_id, 'job_listing_category');
                  $category_display = !empty($categories) ? implode(', ', wp_list_pluck($categories, 'name')) : '';
                ?>
                  <tr>
                    <td><?php the_title(); ?></td>
                    <td>
                      <?php if ($expiry_date_for_check !== '-' && strtotime($expiry_date_for_check) < strtotime($now)) { ?>

                        <span class="status expired">
                          <?php echo ucfirst('expired'); ?>
                        </span>

                      <?php } else { ?>
                        <span class="status <?php echo esc_attr($status); ?>">
                          <?php echo ucfirst($status); ?>
                        </span>
                      <?php } ?>
                    </td>
                    <td><?php echo esc_html($date_posted); ?></td>
                    <td><?php echo esc_html($expiry_date); ?></td>
                    <!-- <td>
                      <?php foreach ($job_types as $type): ?>
                        <span
                          class="tag <?php echo strtolower(str_replace(' ', '', $type)); ?>"><?php echo esc_html($type); ?></span>
                      <?php endforeach; ?>
                    </td> -->
                    <td><?php echo esc_html($views); ?></td>
                    <!--<td><?php echo esc_html($category_display); ?></td>-->
                    <td style="text-align: center;">
                      <div class="action-menu">
                        <button class="action-toggle">...</button>
                        <div class="action-dropdown">
                          <button class="action-edit"
                            onclick="window.location.href='<?php echo esc_url(home_url('/edit-job/?job_id=' . $post_id)); ?>'">
                            <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/tabler_edit.png'); ?>"
                              alt="Edit" class="action-icon">
                            <span style="font-size:14px;">Edit</span>
                          </button>
                          <button class="action-view"
                            onclick="window.open('<?php echo get_permalink($post_id); ?>', '_blank')">
                            <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/lucide_view.png" alt="View"
                              class="action-icon">
                            <span style="font-size:14px;"> View Job</span>
                          </button>
                          <button class="action-duplicate" data-id="<?php echo esc_attr($post_id); ?>">
                            <i class="dashicons dashicons-admin-page" style="font-size:24px;"></i> Duplicate
                          </button>
                          <button class="action-renew" data-id="<?php echo esc_attr($post_id); ?>" data-expiry_date="<?php echo esc_attr(get_post_meta($post_id, '__job_expires', true)); ?>">
                            <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/octicon_issue-reopened-16.png"
                              alt="Renew" class="action-icon">
                            <span style="font-size:14px;"> Renew</span>
                          </button>
                          <button class="action-delete" data-id="<?php echo esc_attr($post_id); ?>">
                            <img
                              src="<?php echo get_stylesheet_directory_uri(); ?>/images/material-symbols_delete-outline.png"
                              alt="Delete" class="action-icon">
                            <span style="font-size:14px;"> Delete</span>
                          </button>
                        </div>
                    </td>
                  </tr>
                <?php endwhile;
                wp_reset_postdata(); ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" style="text-align:center;">No job listings found.</td>
                </tr>
              <?php endif; ?>
            </tbody>

          </table>
        </div>
      </div>
    </div>

  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function() {

      // === Loader ===
      function showLoader() {
        let loader = document.createElement('div');
        loader.id = 'ajax-loader';
        loader.style.position = 'fixed';
        loader.style.top = '0';
        loader.style.left = '0';
        loader.style.width = '100%';
        loader.style.height = '100%';
        loader.style.background = 'rgba(255,255,255,0.6)';
        loader.style.zIndex = '9999';
        loader.style.display = 'flex';
        loader.style.alignItems = 'center';
        loader.style.justifyContent = 'center';
        loader.innerHTML = '<div class="spinner"></div>';
        document.body.appendChild(loader);
      }

      function hideLoader() {
        let loader = document.getElementById('ajax-loader');
        if (loader) loader.remove();
      }

      // === Action Dropdown Initialization ===
      function initActionDropdowns() {
        document.querySelectorAll(".action-toggle").forEach(button => {
          button.onclick = function(e) {
            e.stopPropagation();

            let originalDropdown = this.nextElementSibling;
            let existing = document.querySelector(".floating-dropdown");
            if (existing) existing.remove();

            let dropdown = originalDropdown.cloneNode(true);
            dropdown.classList.add("floating-dropdown");
            dropdown.style.position = "fixed";
            dropdown.style.zIndex = "9999";
            dropdown.style.display = "block";
            dropdown.style.visibility = "hidden";

            document.body.appendChild(dropdown);
            const rect = this.getBoundingClientRect();
            const dropdownWidth = 160;
            const viewportHeight = window.innerHeight;
            const dropdownHeight = dropdown.offsetHeight;

            let top = rect.bottom;
            let left = rect.left - dropdownWidth;
            if (rect.bottom + dropdownHeight > viewportHeight - 10) {
              top = rect.top - dropdownHeight;
              dropdown.classList.add("drop-up");
            }
            if (left < 0) left = rect.left;

            dropdown.style.top = top + "px";
            dropdown.style.left = left + "px";
            dropdown.style.visibility = "visible";

            // === Delete Action ===
            dropdown.querySelectorAll(".action-delete").forEach(deleteBtn => {
              deleteBtn.onclick = function(ev) {
                ev.preventDefault();
                let postId = this.dataset.id;

                alertify.confirm(
                  "",
                  `<div style="text-align:center; padding:10px;">
                <h3 style='color:#18191C;font-weight:700;font-size:33px;'>Delete this job listing?</h3>
                <p style='color:#5E6670;font-weight:400;font-size:15px;'>Are you sure you want to delete this job?</p>
              </div>`,
                  function() {
                    showLoader();
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: "POST",
                        headers: {
                          "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: new URLSearchParams({
                          action: "delete_job_listing",
                          post_id: postId,
                          _ajax_nonce: '<?php echo $delete_nonce; ?>'
                        })
                      })
                      .then(res => res.json())
                      .then(data => {
                        hideLoader();
                        if (data.success) {
                          alertify.success("Job deleted successfully!");
                          // Reload jobs dynamically after delete
                          document.getElementById("job-date-range").click();
                        } else {
                          alertify.error(data.message || "Failed to delete job.");
                        }
                      });
                  },
                  function() {}
                );
              };
            });

            // === Duplicate Action ===
            dropdown.querySelectorAll(".action-duplicate").forEach(dupBtn => {
              dupBtn.onclick = function(ev) {
                ev.preventDefault();

                // Get package info from hidden fields
                const packageStatus = document.getElementById("package_status").value.trim();
                const packageCount = parseInt(document.getElementById("package_count").value.trim()) || 0;
                const postId = this.dataset.id;

                // Validation check
                if (packageStatus.toLowerCase() === "expired" || packageCount <= 0) {
                  alertify.alert(
                    "",
                    `<div style="text-align:center; padding:10px;">
          <h3 style='color:#E74C3C;font-weight:700;font-size:28px;'>Action Denied</h3>
          <p style='color:#5E6670;font-weight:400;font-size:15px;'>
            Your package is either expired or has no remaining listings.
          </p>
        </div>`
                  );
                  return false;
                }

                // If package is valid, proceed with duplication
                alertify.confirm(
                  "",
                  `<div style="text-align:center; padding:10px;">
        <h3 style='color:#18191C;font-weight:700;font-size:33px;'>Make a duplicate of this job?</h3>
        <p style='color:#5E6670;font-weight:400;font-size:15px;'>Do you want to duplicate this job listing?</p>
      </div>`,
                  function() {
                    showLoader();
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: "POST",
                        headers: {
                          "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: new URLSearchParams({
                          action: "duplicate_job_listing",
                          post_id: postId,
                          _ajax_nonce: '<?php echo $duplicate_nonce; ?>'
                        })
                      })
                      .then(res => res.json())
                      .then(data => {
                        hideLoader();
                        if (data.success) {
                          alertify.success("Job duplicated successfully!");
                          //document.getElementById("job-date-range").click();
                          window.location.reload();

                        } else {
                          alertify.error(data.message || "Failed to duplicate job.");
                        }
                      });
                  },
                  function() {}
                );
              };
            });


            document.querySelectorAll(".action-renew").forEach(renewBtn => {
              renewBtn.onclick = function(ev) {
                ev.preventDefault();

                const packageStatus = document.getElementById("package_status").value.trim();
                const packageCount = parseInt(document.getElementById("package_count").value.trim()) || 0;
                const postId = this.dataset.id;
                const expiryDate = this.dataset.expiry_date; // from post meta __job_expires
                const now = new Date();
                const expiry = new Date(expiryDate);

                // 1️⃣ Check if job is already expired
                if (expiry > now) {
                  alertify.alert(
                    "",
                    `<div style="text-align:center; padding:10px;">
          <h3 style='color:#E67E22;font-weight:700;font-size:28px;'>Cannot Renew</h3>
          <p style='color:#5E6670;font-weight:400;font-size:15px;'>This job has not expired yet.</p>
        </div>`
                  );
                  return;
                }

                // 2️⃣ Check package validity before renewal
                if (packageStatus.toLowerCase() === "expired" || packageCount <= 0) {
                  alertify.alert(
                    "",
                    `<div style="text-align:center; padding:10px;">
          <h3 style='color:#E74C3C;font-weight:700;font-size:28px;'>Renewal Not Allowed</h3>
          <p style='color:#5E6670;font-weight:400;font-size:15px;'>
            Your package is either expired or has no remaining credits.
          </p>
        </div>`
                  );
                  return;
                }

                // 3️⃣ Show renewal confirmation popup
                alertify.confirm(
                  "",
                  `<div style="text-align:center; padding:10px;">
        <h3 style='color:#18191C;font-weight:700;font-size:30px;'>Renew this job?</h3>
        <p style='color:#5E6670;font-weight:400;font-size:15px;'>Do you want to extend the expiry date for this job?</p>
      </div>`,
                  function() {
                    // ✅ Step 4: Ask for new expiry date
                    alertify.prompt(
                      "",
                      `<div style="text-align:center; padding:10px;">
      <h3 style='color:#18191C;font-weight:700;font-size:28px;'>Select New Expiry Date</h3>
      <p style='color:#5E6670;font-weight:400;font-size:15px;'>Please choose a new expiry date for this job listing.</p>
      <input type="date" id="newExpiryDate" style="padding:8px;font-size:15px;width:80%;margin-top:10px;" />
  </div>`,
                      "",
                      function(evt, value) {
                        const newExpiry = document.getElementById("newExpiryDate").value;
                        if (!newExpiry) {
                          alertify.error("Please select a valid expiry date.");
                          return false;
                        }

                        showLoader();

                        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                            method: "POST",
                            headers: {
                              "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: new URLSearchParams({
                              action: "renew_job_listing",
                              post_id: postId,
                              new_expiry: newExpiry,
                              _ajax_nonce: '<?php echo $duplicate_nonce; ?>'
                            })
                          })
                          .then(res => res.json())
                          .then(data => {
                            hideLoader();
                            if (data.success) {
                              alertify.success("Job renewed successfully!");
                              setTimeout(() => location.reload(), 1000);
                            } else {
                              alertify.error(data.message || "Failed to renew job.");
                            }
                          });
                      },
                      function() {
                        alertify.message("Renewal cancelled.");
                      }
                    );

                    // Remove the default text box generated by Alertify
                    document.querySelector('.ajs-input')?.remove();

                    // ✅ Set max date dynamically from hidden input
                    const packageExpiry = document.getElementById("package_expiry_date")?.value;
                    if (packageExpiry) {
                      const dateInput = document.getElementById("newExpiryDate");
                      if (dateInput) {
                        dateInput.setAttribute("max", packageExpiry);
                      }
                    }


                    // Remove the default text box generated by Alertify
                    document.querySelector('.ajs-input')?.remove();
                  },
                  function() {}
                );
              };
            });

            // === Close Dropdown on Outside Click ===
            document.addEventListener("click", function removeDropdown(event) {
              if (!dropdown.contains(event.target) && !event.target.classList.contains("action-toggle")) {
                dropdown.remove();
                document.removeEventListener("click", removeDropdown);
              }
            });
          };
        });
      }

      // Initialize actions initially
      initActionDropdowns();

      // Re-run after AJAX load (called later)
      window.refreshActionMenus = initActionDropdowns;
    });
  </script>


  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const displayEl = document.getElementById("job-date-range");
      const tableBody = document.querySelector(".list-job tbody");
      const subtitleEl = document.getElementById("job-subtitle");

      if (!displayEl || !tableBody) return;

      const today = new Date();
      const sixMonthsAgo = new Date();
      sixMonthsAgo.setMonth(today.getMonth() - 6);

      // Hidden input for Flatpickr
      const hiddenInput = document.createElement("input");
      hiddenInput.type = "text";
      hiddenInput.style.position = "fixed";
      hiddenInput.style.left = "-9999px";
      document.body.appendChild(hiddenInput);

      // Initialize Flatpickr
      const fp = flatpickr(hiddenInput, {
        mode: "range",
        dateFormat: "M Y",
        defaultDate: [sixMonthsAgo, today],
        plugins: [
          new monthSelectPlugin({
            shorthand: true,
            dateFormat: "M Y",
            altFormat: "F Y",
            theme: "light"
          })
        ],
        onChange: function(selectedDates) {
          if (selectedDates.length === 2) {
            const start = fp.formatDate(selectedDates[0], "M Y");
            const end = fp.formatDate(selectedDates[1], "M Y");
            displayEl.textContent = `${start} - ${end}`;
            fetchJobs(start, end);
          }
        }
      });

      // Set initial display
      const startText = fp.formatDate(sixMonthsAgo, "M Y");
      const endText = fp.formatDate(today, "M Y");
      displayEl.textContent = `${startText} - ${endText}`;

      // Open calendar below span
      displayEl.addEventListener("click", function(e) {
        e.stopPropagation();
        fp.open();

        setTimeout(() => {
          const calendar = fp.calendarContainer;
          if (!calendar) return;
          const rect = displayEl.getBoundingClientRect();
          const scrollTop = window.scrollY;
          calendar.style.position = "absolute";
          calendar.style.top = rect.bottom + scrollTop + 6 + "px";
          calendar.style.left = rect.left + "px";
          calendar.style.zIndex = "99999";
        }, 10);
      });

      // Close when clicking outside
      document.addEventListener("click", function(e) {
        if (!displayEl.contains(e.target) && !fp.calendarContainer?.contains(e.target)) {
          fp.close();
        }
      });

      // Fetch jobs via AJAX
      function fetchJobs(start, end) {
        tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Loading...</td></tr>';

        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            body: new URLSearchParams({
              action: "filter_jobs_by_date",
              start: start,
              end: end
            })
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              tableBody.innerHTML = data.data.html;
              subtitleEl.textContent = `Showing jobs from ${start} to ${end}`;
              window.refreshActionMenus(); // <-- important
            } else {
              tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Error loading jobs.</td></tr>';
            }
          })
          .catch(err => {
            console.error(err);
            tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Request failed.</td></tr>';
          });
      }

      // Load initial data (6 months default)
      fetchJobs(startText, endText);
    });
  </script>

  <?php
  return ob_get_clean();
}

add_shortcode('list_job_custom', 'my_custom_list_job_shortcode');

// AJAX Delete handler
add_action('wp_ajax_delete_job_listing', 'my_delete_job_listing');
function my_delete_job_listing()
{
  check_ajax_referer('delete_job_nonce');

  $post_id = intval($_POST['post_id'] ?? 0);
  $post = get_post($post_id);

  // Validate post
  if (!$post || $post->post_type !== 'job_listing') {
    wp_send_json_error(array('message' => 'Invalid job ID.'));
  }

  $current_user_id = get_current_user_id();

  // Allow only the post owner or admins to delete
  if ($post->post_author != $current_user_id && !current_user_can('delete_others_job_listings')) {
    wp_send_json_error(array('message' => 'You are not allowed to delete this job.'));
  }

  // Delete permanently
  $deleted = wp_delete_post($post_id, true);

  if ($deleted) {
    wp_send_json_success(array('message' => 'Job deleted successfully.'));
  } else {
    wp_send_json_error(array('message' => 'Could not delete job. Try again.'));
  }
}
// AJAX Duplicate handler
add_action('wp_ajax_duplicate_job_listing', 'my_duplicate_job_listing');
function my_duplicate_job_listing()
{
  check_ajax_referer('duplicate_job_nonce');

  global $wpdb;

  $post_id = intval($_POST['post_id'] ?? 0);
  $post = get_post($post_id);
  $current_user_id = get_current_user_id();
  if (!$post || $post->post_type !== 'job_listing') {
    wp_send_json_error(['message' => 'Invalid job ID.']);
  }

  // ✅ Ensure the user owns the job
  if ($post->post_author != get_current_user_id()) {
    wp_send_json_error(['message' => 'You can only duplicate your own jobs.']);
  }


  // ✅ Create duplicated post
  $new_post = [
    'post_title' => $post->post_title . ' (Copy)',
    'post_content' => $post->post_content,
    'post_status' => 'draft', // always start as draft
    'post_type' => 'job_listing',
    'post_author' => get_current_user_id(),
  ];

  $new_post_id = wp_insert_post($new_post);

  if (is_wp_error($new_post_id) || !$new_post_id) {
    wp_send_json_error(['message' => 'Could not duplicate job.']);
  }

  // ✅ Copy all meta fields
  $meta = get_post_meta($post_id);
  foreach ($meta as $key => $values) {
    foreach ($values as $value) {
      update_post_meta($new_post_id, $key, maybe_unserialize($value));
    }
  }

  // ✅ Copy all taxonomies (like job categories, tags, etc.)
  $taxonomies = get_object_taxonomies('job_listing');
  foreach ($taxonomies as $taxonomy) {
    $terms = wp_get_object_terms($post_id, $taxonomy, ['fields' => 'ids']);
    wp_set_object_terms($new_post_id, $terms, $taxonomy);
  }

  // ✅ Increment package_count for this user
  $table_name = $wpdb->prefix . 'wcpl_user_packages';
  $updated = $wpdb->query(
    $wpdb->prepare(
      "UPDATE $table_name 
             SET package_count = package_count + 1 
             WHERE user_id = %d 
             AND package_type = 'job_listing' order by `id` DESC
             LIMIT 1",
      $current_user_id
    )
  );

  // ✅ Return success with new ID
  wp_send_json_success([
    'message' => 'Job duplicated successfully!',
    'new_post_id' => $new_post_id,
    'edit_url' => get_edit_post_link($new_post_id),
  ]);
}


add_action('wp_ajax_renew_job_listing', 'my_renew_job_listing');
function my_renew_job_listing()
{
  check_ajax_referer('duplicate_job_nonce');
  global $wpdb;

  $post_id = intval($_POST['post_id'] ?? 0);
  $new_expiry = sanitize_text_field($_POST['new_expiry'] ?? '');

  $post = get_post($post_id);
  if (!$post || $post->post_type !== 'job_listing') {
    wp_send_json_error(['message' => 'Invalid job ID.']);
  }

  $current_user_id = get_current_user_id();
  if ($post->post_author != $current_user_id) {
    wp_send_json_error(['message' => 'You can only renew your own jobs.']);
  }

  // ✅ Validate package again on backend
  $table_name = $wpdb->prefix . 'wcpl_user_packages';
  $package = $wpdb->get_row(
    $wpdb->prepare(
      "SELECT package_count, package_duration, order_id 
             FROM $table_name 
             WHERE user_id = %d AND package_type = 'job_listing' 
             LIMIT 1",
      $current_user_id
    )
  );

  if (!$package) {
    wp_send_json_error(['message' => 'No active package found.']);
  }

  // ✅ Check expiration of package
  $order_post = $wpdb->get_row(
    $wpdb->prepare("SELECT post_date FROM {$wpdb->prefix}posts WHERE ID = %d", $package->order_id)
  );
  $order_timestamp = strtotime($order_post->post_date ?? '');
  $expiry_timestamp = strtotime("+$package->package_duration days", $order_timestamp);
  $current_timestamp = current_time('timestamp');
  if ($current_timestamp > $expiry_timestamp) {
    wp_send_json_error(['message' => 'Your package has expired.']);
  }

  if ($package->package_count <= 0) {
    wp_send_json_error(['message' => 'No remaining job credits in your package.']);
  }

  // ✅ Validate expiry date input
  if (empty($new_expiry) || !strtotime($new_expiry)) {
    wp_send_json_error(['message' => 'Invalid expiry date provided.']);
  }

  // ✅ Update expiry date
  update_post_meta($post_id, '__job_expires', $new_expiry);


  // ✅ Set post status to "publish"
  wp_update_post([
    'ID'          => $post_id,
    'post_status' => 'publish',
  ]);

  // ✅ Decrease package count
  $wpdb->query(
    $wpdb->prepare(
      "UPDATE $table_name 
             SET package_count = package_count + 1 
             WHERE user_id = %d AND package_type = 'job_listing' 
             LIMIT 1",
      $current_user_id
    )
  );

  wp_send_json_success([
    'message' => 'Job renewed successfully!',
    'new_expiry' => $new_expiry,
  ]);
}

// =====================
// AJAX: Filter Jobs by Date Range
// =====================
add_action('wp_ajax_filter_jobs_by_date', 'my_filter_jobs_by_date');
function my_filter_jobs_by_date()
{
  $current_user = wp_get_current_user();
  $start = sanitize_text_field($_POST['start'] ?? '');
  $end = sanitize_text_field($_POST['end'] ?? '');

  $delete_nonce = wp_create_nonce('delete_job_nonce');
  $duplicate_nonce = wp_create_nonce('duplicate_job_nonce');

  // Convert "M Y" → proper date range
  $start_date = DateTime::createFromFormat('M Y', $start);
  $end_date = DateTime::createFromFormat('M Y', $end);

  if (!$start_date || !$end_date) {
    wp_send_json_error(['message' => 'Invalid date range.']);
  }

  $start_date->modify('first day of this month');
  $end_date->modify('last day of this month');

  $args = [
    'post_type' => 'job_listing',
    'post_status' => ['publish', 'draft'],
    'author' => $current_user->ID,
    'posts_per_page' => -1,
    'date_query' => [
      [
        'after' => $start_date->format('Y-m-d'),
        'before' => $end_date->format('Y-m-d'),
        'inclusive' => true,
      ]
    ],
  ];

  $query = new WP_Query($args);

  ob_start();
  if ($query->have_posts()):
    while ($query->have_posts()):
      $query->the_post();
      $post_id = get_the_ID();
      $status = get_post_status($post_id);
      $date_posted = get_the_date('M-d-Y', $post_id);
      $expiry_date = get_post_meta($post_id, '__job_expires', true);
      $expiry_date = $expiry_date ? date('M-d-Y', strtotime($expiry_date)) : '-';
      $views = get_post_meta($post_id, '_job_views', true) ?: 0;

      $expiry_date_for_check = $expiry_date ? date('Y-m-d', strtotime($expiry_date)) : '-';
      $now = date('Y-m-d');
  ?>

      <tr>
        <td><?php the_title(); ?></td>
        <td>
          <?php
          if ($expiry_date_for_check !== '-' && strtotime($expiry_date_for_check) < strtotime($now)) {
          ?>
            <span class="status expired">
              <?php echo ucfirst('expired'); ?>
            </span>
          <?php
          } else {
          ?>
            <span class="status <?php echo esc_attr($status); ?>">
              <?php echo ucfirst($status); ?>
            </span>
          <?php
          }
          ?>
        </td>
        <td><?php echo esc_html($date_posted); ?></td>
        <td><?php echo esc_html($expiry_date); ?></td>
        <td><?php echo esc_html($views); ?></td>
        <td style="text-align:center;">
          <div class="action-menu">
            <button class="action-toggle">...</button>
            <div class="action-dropdown">
              <button class="action-edit"
                onclick="window.location.href='<?php echo esc_url(home_url('/edit-job/?job_id=' . $post_id)); ?>'">
                <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/tabler_edit.png'); ?>" alt="Edit"
                  class="action-icon">
                <span style="font-size:14px;">Edit</span>
              </button>
              <button class="action-view" onclick="window.open('<?php echo get_permalink($post_id); ?>', '_blank')">
                <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/lucide_view.png" alt="View"
                  class="action-icon">
                <span style="font-size:14px;"> View Job</span>
              </button>
              <button class="action-duplicate" data-id="<?php echo esc_attr($post_id); ?>">
                <i class="dashicons dashicons-admin-page" style="font-size:24px;"></i> Duplicate
              </button>
              <button class="action-renew" data-id="<?php echo esc_attr($post_id); ?>" data-expiry_date="<?php echo esc_attr(get_post_meta($post_id, '__job_expires', true)); ?>">
                <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/octicon_issue-reopened-16.png" alt="Renew"
                  class="action-icon">
                <span style="font-size:14px;"> Renew</span>
              </button>
              <button class="action-delete" data-id="<?php echo esc_attr($post_id); ?>">
                <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/material-symbols_delete-outline.png"
                  alt="Delete" class="action-icon">
                <span style="font-size:14px;"> Delete</span>
              </button>
            </div>
          </div>
        </td>
      </tr>

<?php
    endwhile;
  else:
    echo '<tr><td colspan="6" style="text-align:center;">No jobs found in this date range.</td></tr>';
  endif;

  wp_reset_postdata();

  $html = ob_get_clean();
  wp_send_json_success(['html' => $html]);
}
