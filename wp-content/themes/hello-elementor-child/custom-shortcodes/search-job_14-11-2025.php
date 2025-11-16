<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit;
}

function my_search_job()
{
  ob_start();

  // Sorting Logic
  $order = 'DESC';
  if (!empty($_POST['job_sorting']) && $_POST['job_sorting'] === 'Oldest Post') {
    $order = 'ASC';
  }

  $tax_query = []; // Initialize

  // Handle location from GET and treat it as taxonomy
  if (!empty($_GET['location'])) {
    $locations = explode(',', sanitize_text_field($_GET['location']));
    $locations = array_map('trim', $locations);

    $tax_query[] = [
      'taxonomy' => 'job_location_category', // your location taxonomy
      'field' => 'slug',                  // filtering by slug
      'terms' => $locations,              // can be multiple
      'operator' => 'IN'
    ];
  }

  // Handle other taxonomies if needed
  $other_taxonomies = ['job_listing_type', 'job_listing_category'];

  foreach ($other_taxonomies as $taxonomy) {
    if (!empty($_GET[$taxonomy])) {
      $terms = is_array($_GET[$taxonomy]) ? $_GET[$taxonomy] : explode(',', $_GET[$taxonomy]);
      $terms = array_map('trim', $terms);

      $tax_query[] = [
        'taxonomy' => $taxonomy,
        'field' => 'slug',
        'terms' => $terms,
        'operator' => 'IN',
      ];
    }
  }

  // --- NEW: Exclude expired jobs ---
  $today = current_time('Y-m-d');
  $meta_query = [
    'relation' => 'OR',
    [
      'key' => '__job_expires',
      'value' => $today,
      'compare' => '>=',
      'type' => 'DATE',
    ],
    [
      'key' => '__job_expires',
      'compare' => 'NOT EXISTS', // Include jobs with no expiry date set
    ],
  ];

  // Fetch all jobs
  $jobs = new WP_Query([
    'post_type' => 'job_listing',
    'post_status' => 'publish',
    's' => !empty($_GET['key']) ? sanitize_text_field($_GET['key']) : '',
    'posts_per_page' => -1,
    'tax_query' => !empty($tax_query) ? $tax_query : [],
    'orderby' => 'date',
    'meta_query' => $meta_query, // <-- include only non-expired jobs
    'order' => !empty($order) ? $order : 'DESC',
  ]);

?>
  <link rel="stylesheet" type="text/css"
    href="<?php echo get_stylesheet_directory_uri(); ?>/custom-shortcodes/css/search-job.css">
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

   <style>
    .bookmark-btn {
  position: relative;
  width: 40px;
  height: 40px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: none;
  background-color: transparent !important; /* Force full transparency */
  cursor: pointer;
  outline: none;
  transition: background-color 0s;
  -webkit-tap-highlight-color: transparent; /* Removes mobile tap flash */
}

.bookmark-btn:focus,
.bookmark-btn:active {
  background-color: transparent !important;
  box-shadow: none !important;
}

.bookmark-btn.bookmark-loading .bookmark-icon {
  opacity: 0;
  visibility: hidden;
}

/* Spinner */
.bookmark-btn::after {
  content: "";
  position: absolute;
  inset: 0;
  margin: auto;
  width: 20px;
  height: 20px;
  border: 2px solid transparent;
  border-top-color: #005f83;
  border-right-color: #005f83;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  opacity: 0;
  visibility: hidden;
  background: transparent !important;
}

.bookmark-btn.bookmark-loading::after {
  opacity: 1;
  visibility: visible;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

  </style>
  <script>
    function copyJobLink(event) {
      event.preventDefault();
      const input = document.getElementById("job-link");
      input.select();
      input.setSelectionRange(0, 99999);
      navigator.clipboard.writeText(input.value).then(() => {
        const message = document.getElementById("copy-message");
        message.style.display = "inline-block";
        setTimeout(() => {
          message.style.display = "none";
        }, 2000);
      });
    }

    function toggleJobLinkPopup() {
      const popup = document.getElementById("job-link-popup");
      popup.classList.toggle("show");
    }
  </script>
  <?php
  $selected_locations = [];
  if (!empty($_GET['location'])) {
    $selected_locations = array_map('sanitize_text_field', explode(',', $_GET['location']));
  }
  ?>

  <div class="container">
    <div class="tabs">
      <button class="tab active">All Jobs</button>
      <?php if (!is_user_logged_in()) { ?>
        <button class="tab saved-jobs-btn" data-action="show-saved-jobs">Saved Jobs</button>
      <?php } else { ?>
        <button class="tab saved-jobs-btn" data-action="show-saved-jobs"
          onclick="window.location.href='<?php echo esc_url(site_url('/saved-jobs')); ?>'">Saved Jobs</button>
      <?php } ?>
    </div>

    <div class="search-container">
      <div class="search-input-wrapper">
        <input type="text" placeholder="Search with keyword," value="<?php echo $_REQUEST['key']; ?>" name="search_input"
          class="search-input">
      </div>
      <div class="filters-row">
        <div class="filter-select">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
            <path
              d="M20 6h-2V4c0-1.11-.89-2-2-2H8c-1.11 0-2 .89-2 2v2H4c-1.11 0-2 .89-2 2v11c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zM8 4h8v2H8V4zm12 15H4V8h2v1c0 .55.45 1 1 1s1-.45 1-1V8h8v1c0 .55.45 1 1 1s1-.45 1-1V8h2v11z" />
          </svg>
          <div class="multiselect-wrapper">
            <div class="multiselect-trigger" data-target="job_listing_type">
              <span class="selection-text">All Job Types</span>
            </div>
            <div class="multiselect-dropdown">
              <div class="multiselect-search">
                <input type="text" placeholder="Search job types..." class="multiselect-search-input">
              </div>
              <div class="multiselect-options">
                <?php
                $job_types = get_terms([
                  'taxonomy' => 'job_listing_type',
                  'hide_empty' => false,
                ]);

                if (!empty($job_types) && !is_wp_error($job_types)) {
                  foreach ($job_types as $type) {
                    echo '<div class="multiselect-option">';
                    echo '<input type="checkbox" id="job_type_' . esc_attr($type->slug) . '" value="' . esc_attr($type->slug) . '">';
                    echo '<label for="job_type_' . esc_attr($type->slug) . '">' . esc_html($type->name) . '</label>';
                    echo '</div>';
                  }
                }
                ?>
              </div>
              <div class="multiselect-actions">
                <button class="multiselect-btn reset">Reset</button>
                <button class="multiselect-btn apply">Apply</button>
              </div>
            </div>
          </div>
        </div>

        <div class="filter-select">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
            <path
              d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
          </svg>
          <div class="multiselect-wrapper">
            <div class="multiselect-trigger" data-target="job_location_category">
              <span class="selection-text">All Locations</span>
            </div>
            <div class="multiselect-dropdown">
              <div class="multiselect-search">
                <input type="text" placeholder="Search locations..." class="multiselect-search-input">
              </div>
              <div class="multiselect-options">
                <?php
                $terms = get_terms([
                  'taxonomy' => 'job_location_category',
                  'hide_empty' => false,
                ]);

                if (!empty($terms) && !is_wp_error($terms)) {
                  foreach ($terms as $term) {
                    echo '<div class="multiselect-option">';
                    $checked = in_array($term->slug, $selected_locations) ? 'checked' : '';
                    echo '<input type="checkbox" id="location_' . esc_attr($term->slug) . '" value="' . esc_attr($term->slug) . '" ' . $checked . '>';
                    echo '<label for="location_' . esc_attr($term->slug) . '">' . esc_html($term->name) . '</label>';
                    echo '</div>';
                  }
                }
                ?>
              </div>
              <div class="multiselect-actions">
                <button class="multiselect-btn reset">Reset</button>
                <button class="multiselect-btn apply">Apply</button>
              </div>
            </div>
          </div>
        </div>

        <div class="filter-select">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
            <path
              d="M11.99 18.54l-7.37-5.73L3 14.07l9 7 9-7-1.63-1.27-7.38 5.74zM12 16l7.36-5.73L21 9l-9-7-9 7 1.63 1.27L12 16z" />
          </svg>
          <div class="multiselect-wrapper">
            <div class="multiselect-trigger" data-target="job_listing_category">
              <span class="selection-text">All Categories</span>
            </div>
            <div class="multiselect-dropdown">
              <div class="multiselect-search">
                <input type="text" placeholder="Search categories..." class="multiselect-search-input">
              </div>
              <div class="multiselect-options">
                <?php
                $job_categories = get_terms([
                  'taxonomy' => 'job_listing_category',
                  'hide_empty' => false,
                ]);

                if (!empty($job_categories) && !is_wp_error($job_categories)) {
                  foreach ($job_categories as $category) {
                    echo '<div class="multiselect-option">';
                    echo '<input type="checkbox" id="category_' . esc_attr($category->slug) . '" value="' . esc_attr($category->slug) . '">';
                    echo '<label for="category_' . esc_attr($category->slug) . '">' . esc_html($category->name) . '</label>';
                    echo '</div>';
                  }
                }
                ?>
              </div>
              <div class="multiselect-actions">
                <button class="multiselect-btn reset">Reset</button>
                <button class="multiselect-btn apply">Apply</button>
              </div>
            </div>
          </div>
        </div>

        <div class="filter-select">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
            <path
              d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10z" />
          </svg>
          <?php
          global $wpdb;

          $company_names = $wpdb->get_col("
        SELECT DISTINCT meta_value 
        FROM $wpdb->postmeta pm
        INNER JOIN $wpdb->posts p ON p.ID = pm.post_id
        WHERE pm.meta_key = '_company_name'
        AND p.post_type = 'job_listing'
        AND p.post_status = 'publish'
        ORDER BY meta_value ASC
        ");
          ?>
          <div class="multiselect-wrapper">
            <div class="multiselect-trigger" data-target="company_name">
              <span class="selection-text">All Companies</span>
            </div>
            <div class="multiselect-dropdown">
              <div class="multiselect-search">
                <input type="text" placeholder="Search companies..." class="multiselect-search-input">
              </div>
              <div class="multiselect-options">
                <?php
                if (!empty($company_names)) {
                  foreach ($company_names as $company) {
                    if (empty($company)) {
                      continue; // Skip empty company names
                    }
                    $company_id = sanitize_title($company);
                    echo '<div class="multiselect-option">';
                    echo '<input type="checkbox" id="company_' . esc_attr($company_id) . '" value="' . esc_attr($company) . '">';
                    echo '<label for="company_' . esc_attr($company_id) . '">' . esc_html($company) . '</label>';
                    echo '</div>';
                  }
                }
                ?>
              </div>
              <div class="multiselect-actions">
                <button class="multiselect-btn reset">Reset</button>
                <button class="multiselect-btn apply">Apply</button>
              </div>
            </div>
          </div>
        </div>

        <button class="btn search-btn">Find Job</button>
        <button class="btn reset-btn">Reset All</button>
      </div>
    </div>

    <div class="main-content">
      <div class="job-list">
        <div class="list-header">
          <span class="job-count"><?php echo $jobs->found_posts; ?> Jobs</span>
          <div class="sort-wrapper">
            <span style="margin-right:12px;">Sort by</span>
            <div class="sort-select-wrapper">
              <div class="multiselect-wrapper" id="custom-sort-container">
                <div class="multiselect-trigger">
                  <span class="selection-text">Newest Post</span>
                </div>
                <div class="multiselect-dropdown">
                  <div class="multiselect-options">
                    <div class="multiselect-option sort-option" data-value="Newest Post">
                      <label>Newest Post</label>
                    </div>
                    <div class="multiselect-option sort-option" data-value="Oldest Post">
                      <label>Oldest Post</label>
                    </div>
                  </div>
                </div>
                <input type="hidden" id="job_sorting_value" value="Newest Post">
              </div>
            </div>
          </div>
        </div>
        <div class="job-cards">
          <?php
          if ($jobs->have_posts()):
            $count = 0;
            while ($jobs->have_posts()):
              $jobs->the_post();
              $company_logo = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail') ?: get_stylesheet_directory_uri() . '/images/noimage.jpg';
              $company_name = get_post_meta(get_the_ID(), '_company_name', true);
              $terms = get_the_terms(get_the_ID(), 'job_location_category');
              $terms = get_the_terms(get_the_ID(), 'job_location_category');
              $location = !empty($terms) && !is_wp_error($terms)
                ? esc_html(implode(', ', wp_list_pluck($terms, 'name')))
                : 'Not specified';
              // $location = get_post_meta(get_the_ID(), '_job_location', true);
              $posted = human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago';


              $company_data = get_company_info_by_post(get_the_ID());
          ?>
              <div class="job-card <?php echo $count === 0 ? 'selected' : ''; ?>" data-id="<?php echo get_the_ID(); ?>"
                id="current_job_id_<?php echo get_the_ID(); ?>">
                <img src="<?php echo $company_data['logo']; ?>" alt="<?php echo esc_attr($company_name); ?>"
                  class="company-logo">
                <div class="job-info">
                  <h3><?php the_title(); ?></h3>
                  <p class="company-name"><?php echo esc_html($company_name); ?></p>
                  <div class="job-meta">
                    <div class="location">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                        <path
                          d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                      </svg>
                      <?php echo esc_html($location); ?>
                    </div>
                    <span class="posted-time"><?php echo esc_html($posted); ?></span>
                  </div>
                </div>
              </div>
          <?php
              $count++;
            endwhile;
            wp_reset_postdata();
          else:
            echo '<p style="text-align: center; padding-top: 40px;">No jobs found.</p>';
          endif;
          ?>
        </div>
        <div class="subscribe-box">
          <div class="subscribe-content">
            <h4>Subscribe for Latest Job Updates</h4>
            <button class="btn get-alerts-btn">Get Alerts</button>
          </div>
        </div>
      </div>

      <div class="job-details" id="job-details">
        <div class="loader" style="display:none;text-align:center;padding:40px;">Loading...</div>
        <?php
        // Show first job details by default
        if ($jobs->have_posts()):
          $jobs->rewind_posts();
          $jobs->the_post();
          echo my_get_single_job_html(get_the_ID());
          wp_reset_postdata();
        else:
          echo '<div id="no-jobs-message" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px 20px; color: #555; font-family: Arial, sans-serif; text-align: center;">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="9" y1="9" x2="15" y2="15"></line>
              <line x1="15" y1="9" x2="9" y2="15"></line>
            </svg>
            <h3 style="margin: 15px 0 5px; font-size: 20px; color:#333;">No Jobs Found</h3>
            <p style="font-size: 14px; max-width: 250px; color:#777;">
              We could not find any jobs matching your filters. Try changing your search criteria.
            </p>
          </div>';
        endif;
        ?>
      </div>
    </div>

    <!-- Job Alerts Inline Popup -->
    <div id="job-alerts-modal" class="job-alerts-modal" aria-hidden="true">
      <div class="jam-overlay" aria-hidden="true"></div>
      <div class="jam-dialog" role="dialog" aria-modal="true" aria-labelledby="jam-title">
        <button type="button" class="jam-close" aria-label="Close">×</button>
        <h2 id="jam-title" class="jam-title">Get Notified – Jobs That Match You</h2>
        <div class="jam-content">
          <?php echo do_shortcode('[job_alert_form]'); ?>
        </div>
      </div>
    </div>
  </div>

  <script>
    jQuery(document).ready(function($) {

      if (window.myMultiselectLoaded) return; // prevent multiple init
      window.myMultiselectLoaded = true;

      // Multi-select functionality
      let selectedFilters = {
        job_listing_type: [],
        job_location_category: [],
        job_listing_category: [],
        company_name: []
      };

      // Toggle dropdown
      $('.multiselect-trigger').on('click', function(e) {
        e.stopPropagation();
        const dropdown = $(this).siblings('.multiselect-dropdown');

        // Close other dropdowns
        $('.multiselect-dropdown').not(dropdown).removeClass('show');
        $('.multiselect-trigger').not(this).removeClass('active');

        // Toggle current dropdown
        dropdown.toggleClass('show');
        $(this).toggleClass('active');
      });

      // Close dropdowns when clicking outside
      $(document).on('click', function() {
        $('.multiselect-dropdown').removeClass('show');
        $('.multiselect-trigger').removeClass('active');
      });

      // Prevent dropdown from closing when clicking inside
      $('.multiselect-dropdown').on('click', function(e) {
        e.stopPropagation();
      });

      // Search within dropdown
      $('.multiselect-search-input').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        const options = $(this).closest('.multiselect-dropdown').find('.multiselect-option');

        options.each(function() {
          const text = $(this).find('label').text().toLowerCase();
          $(this).toggle(text.includes(searchTerm));
        });
      });

      // Handle checkbox changes
      $('.multiselect-option input[type="checkbox"]').on('change', function() {
        const filterType = $(this).closest('.multiselect-wrapper').find('.multiselect-trigger').data('target');
        const value = $(this).val();

        if ($(this).is(':checked')) {
          if (!selectedFilters[filterType].includes(value)) {
            selectedFilters[filterType].push(value);
          }
        } else {
          selectedFilters[filterType] = selectedFilters[filterType].filter(item => item !== value);
        }

        updateTriggerText(filterType);
      });

      // Reset button in dropdown
      $('.multiselect-btn.reset').on('click', function() {
        const dropdown = $(this).closest('.multiselect-dropdown');
        const filterType = dropdown.siblings('.multiselect-trigger').data('target');

        // Clear checkboxes
        dropdown.find('input[type="checkbox"]').prop('checked', false);

        // Clear search input in this dropdown
        dropdown.find('.multiselect-search-input').val('');

        // Show all options (in case some were hidden by search)
        dropdown.find('.multiselect-option').show();

        // Clear selected filters for this type
        selectedFilters[filterType] = [];

        // Update trigger text and styling
        updateTriggerText(filterType);
      });

      // Apply button in dropdown
      $('.multiselect-btn.apply').on('click', function() {
        const dropdown = $(this).closest('.multiselect-dropdown');
        dropdown.removeClass('show');
        dropdown.siblings('.multiselect-trigger').removeClass('active');
      });

      // Update trigger text based on selections
      function updateTriggerText(filterType) {
        const trigger = $(`.multiselect-trigger[data-target="${filterType}"]`);
        const selections = selectedFilters[filterType];
        const textElement = trigger.find('.selection-text');
        const countElement = trigger.find('.selection-count');

        // Remove existing count badge
        countElement.remove();

        if (selections.length === 0) {
          let defaultText = 'All';
          switch (filterType) {
            case 'job_listing_type':
              defaultText = 'All Job Types';
              break;
            case 'job_location_category':
              defaultText = 'All Locations';
              break;
            case 'job_listing_category':
              defaultText = 'All Categories';
              break;
            case 'company_name':
              defaultText = 'All Companies';
              break;
          }
          textElement.text(defaultText);
          trigger.removeClass('active');
          trigger.closest('.filter-select').removeClass('active');

          // Reset to default styling
          trigger.css({
            'background-color': 'white',
            'border-color': '#e2e8f0',
            'color': '#334155'
          });
        } else {
          if (selections.length === 1) {
            // Show single selection name
            const checkbox = $(`.multiselect-trigger[data-target="${filterType}"]`)
              .siblings('.multiselect-dropdown')
              .find(`input[value="${selections[0]}"]`);
            const label = checkbox.siblings('label').text();
            textElement.text(label);
          } else {
            // Show count for multiple selections
            textElement.text(`${selections.length} selected`);
          }

          // Add count badge
          trigger.append(`<span class="selection-count">${selections.length}</span>`);
          trigger.addClass('active');
          trigger.closest('.filter-select').addClass('active');

          // Force the active styling
          trigger.css({
            'background-color': '#00688f',
            'border-color': '#00688f',
            'color': 'white'
          });
        }
      }

      // Bookmark Button Functionality
      $(document).on("click", ".bookmark-btn", function(e) {
        e.preventDefault();
        const button = $(this);
        const jobId = button.data("job-id");
        if (jobId == '') {
          if (typeof elementorProFrontend !== "undefined" && elementorProFrontend.modules.popup) {
            elementorProFrontend.modules.popup.showPopup({
              id: 2326
            });
          } else {
            console.error("Elementor Popup module not found");
          }
          return false;
        }
        button.addClass("bookmark-loading");
        $.ajax({
          url: "<?php echo admin_url('admin-ajax.php'); ?>",
          type: "POST",
          dataType: "json",
          data: {
            action: "toggle_save_job",
            job_id: jobId
          },
          success: function(response) {
            button.removeClass("bookmark-loading");
            if (response.success) {
              if (response.data.saved) {
                button.addClass("saved");
                button.find("svg").attr("fill", "currentColor");
              } else {
                button.removeClass("saved");
                button.find("svg").attr("fill", "none");
              }
            } else {
              alert(response.data.message);
            }
          },
          error: function() {
            button.removeClass("bookmark-loading");
            alert("Something went wrong. Please try again.");
          }
        });
      });

      // Saved Jobs Button Functionality
      $(document).on("click", ".saved-jobs-btn", function(e) {
        e.preventDefault();
        const button = $(this);
        if (!<?php echo is_user_logged_in() ? 'true' : 'false'; ?>) {
          if (typeof elementorProFrontend !== "undefined" && elementorProFrontend.modules.popup) {
            elementorProFrontend.modules.popup.showPopup({
              id: 2326
            });
          } else {
            console.error("Elementor Popup module not found");
          }
          return false;
        }
        // For logged-in users, the onclick attribute handles the redirect
      });

      // Job card click handler
      $('.job-cards').on('click', '.job-card', function() {
        var jobId = $(this).data('id');

        $('.job-card').removeClass('selected');
        $(this).addClass('selected');

        $('#job-details').html('<div class="loader-wrapper"><div class="spinner"></div></div>');

        $.ajax({
          url: "<?php echo admin_url('admin-ajax.php'); ?>",
          type: "POST",
          data: {
            action: "get_job_details",
            job_id: jobId,
          },
          success: function(response) {
            $('#job-details').html(response);
          },
          error: function() {
            $('#job-details').html('<p style="text-align:center;">Error loading job details.</p>');
          }
        });
      });

      // --- Custom Sort Dropdown Option Click ---
      $('#custom-sort-container .multiselect-option.sort-option').on('click', function() {
        const value = $(this).data('value');
        const text = $(this).find('label').text();
        const container = $(this).closest('#custom-sort-container');

        // Update the visible text and the hidden input's value
        container.find('.selection-text').text(text);
        container.find('#job_sorting_value').val(value);

        // Close the dropdown
        container.find('.multiselect-dropdown').removeClass('show');
        container.find('.multiselect-trigger').removeClass('active');

        // Trigger the search
        $('.search-btn').trigger('click');
      });


      // Search button handler
      $('.search-btn').on('click', function() {
        let filters = {
          job_listing_type: selectedFilters.job_listing_type,
          job_location_category: selectedFilters.job_location_category,
          job_listing_category: selectedFilters.job_listing_category,
          company_name: selectedFilters.company_name,
          keyword: $('input[name="search_input"]').val(),
          job_sorting: $('#job_sorting_value').val(), // Get value from the hidden input
          action: 'filter_jobs'
        };

        $('.job-cards').html('<div style="text-align:center;padding:20px;">Loading...</div>');
        $('#job-details').html('');

        $.post("<?php echo admin_url('admin-ajax.php'); ?>", filters, function(response) {
          $('.job-cards').html(response);
          $('.job-count').html($('.job-cards .job-card').length + ' Jobs');

          let firstCard = $('.job-cards .job-card').first();
          if (firstCard.length) {
            firstCard.trigger('click');
          } else {
            $('#job-details').html(`<div style="
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    padding: 40px 20px;
                    color: #555;
                    font-family: Arial, sans-serif;
                    text-align: center;
                ">
                    <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                    </svg>
                    <h3 style="margin: 15px 0 5px; font-size: 20px; color:#333;">No Jobs Found</h3>
                    <p style="font-size: 14px; max-width: 250px; color:#777;">
                        We could not find any jobs matching your filters. Try changing your search criteria.
                    </p>
                </div>`);
          }
        });
      });

      // Reset All button
      $('.reset-btn').on('click', function(e) {
        e.preventDefault();

        // Clear all multi-select filters
        Object.keys(selectedFilters).forEach(filterType => {
          selectedFilters[filterType] = [];
          const trigger = $(`.multiselect-trigger[data-target="${filterType}"]`);
          const dropdown = trigger.siblings('.multiselect-dropdown');
          dropdown.find('input[type="checkbox"]').prop('checked', false);
          dropdown.find('.multiselect-search-input').val('');
          dropdown.find('.multiselect-option').show();
          updateTriggerText(filterType);
        });

        // Clear main search input
        $('input[name="search_input"]').val('');

        // Reset sort dropdown to default
        $('#custom-sort-container .selection-text').text('Newest Post');
        $('#job_sorting_value').val('Newest Post');

        // Close any open dropdowns
        $('.multiselect-dropdown').removeClass('show');
        $('.multiselect-trigger').removeClass('active');

        // Trigger search to refresh results
        $('.search-btn').trigger('click');
      });

      <?php if (!empty($_REQUEST['selected_job'])) { ?>
        $('#current_job_id_<?php echo intval($_REQUEST['selected_job']); ?>').trigger('click');
      <?php } ?>

      selectedFilters.job_location_category = <?php echo json_encode($selected_locations); ?>;
      selectedFilters.job_location_category.forEach(function(slug) {
        $(".multiselect-trigger[data-target='job_location_category']")
          .siblings('.multiselect-dropdown')
          .find('input[type="checkbox"][value="' + slug + '"]')
          .prop('checked', true);
      });
      updateTriggerText('job_location_category');

      // --- Job Alerts Inline Popup (no Elementor) ---
      function openJobAlertsModal() {
        const $popup = $('#job-alerts-modal');
        const $dialog = $popup.find('.jam-dialog');

        $popup.addClass('open').attr('aria-hidden', 'false');
        $('body').addClass('no-scroll job-alerts-open');

        // Re-init dropdown widgets when visible
        prepareModalWidgets($dialog);

        // Focus first input (optional UX)
        setTimeout(() => {
          const $first = $dialog.find('input, select, textarea, button, a').filter(':visible').first();
          if ($first.length) $first.trigger('focus');
        }, 50);
      }

      function closeJobAlertsModal() {
        $('#job-alerts-modal').removeClass('open').attr('aria-hidden', 'true');
        $('body').removeClass('no-scroll job-alerts-open');
      }

      function prepareModalWidgets($dialog) {
        // Re-initialize Select2 dropdowns
        if ($.fn.select2) {
          $dialog.find('select').each(function() {
            const $sel = $(this);

            try {
              if ($sel.data('select2')) {
                $sel.select2('destroy');
              }
            } catch (e) {}

            $sel.select2({
              dropdownParent: $dialog,
              width: '100%'
            });
          });
        }

      }

      // Open on "Get Alerts" button click
      $(document).on('click', '.get-alerts-btn', function(e) {
        e.preventDefault();
        openJobAlertsModal();
      });

      // Close on overlay click, X button, or ESC key
      $(document).on('click', '#job-alerts-modal .jam-overlay, #job-alerts-modal .jam-close', function() {
        closeJobAlertsModal();
      });
      $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#job-alerts-modal').hasClass('open')) {
          closeJobAlertsModal();
        }
      });
    });
  </script>

<?php
  return ob_get_clean();
}
add_shortcode('search_job', 'my_search_job');

// Helper function to generate job details HTML (unchanged)
function my_get_single_job_html($post_id)
{
  ob_start();
  $company_logo = get_the_post_thumbnail_url($post_id, 'medium') ?: get_stylesheet_directory_uri() . '/images/noimage.jpg';
  $company_name = get_post_meta($post_id, '_company_name', true);
  $location = get_post_meta($post_id, '_job_location', true);
  $salary = get_post_meta($post_id, '_job_salary', true);
  $job_salary_currency = get_post_meta($post_id, '_job_salary_currency', true);
  $job_salary_unit = get_post_meta($post_id, '_job_salary_unit', true);

  $job_type_terms = wp_get_post_terms($post_id, 'job_listing_type');
  $job_types = !empty($job_type_terms) ? wp_list_pluck($job_type_terms, 'name') : array();

  $company_data = get_company_info_by_post($post_id);


  // Get job attachments
  $attachments = get_post_meta($post_id, '_job_attachments', true);
  $attachment_urls = !empty($attachments) ? (is_array($attachments) ? $attachments : array($attachments)) : array();

?>

  <div class="job-details-header">
    <img src="<?php echo $company_data['logo']; ?>" alt="<?php echo esc_attr($company_name); ?>"
      class="company-logo-large">
    <div class="job-title-section">
      <h2><?php echo get_the_title($post_id); ?></h2>
      <p class="company-name"><?php echo esc_html($company_name); ?></p>
      <div class="location-info">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
          <path
            d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
        </svg>
        <?php echo esc_html($location); ?>
      </div>
    </div>
    <?php
    $user_id = get_current_user_id();
    $saved_jobs = is_user_logged_in() ? get_user_meta($user_id, 'saved_jobs', true) : [];
    $is_saved = is_array($saved_jobs) && in_array($post_id, $saved_jobs);
    ?>
    <?php if (!is_user_logged_in()) { ?>

      <button class="bookmark-btn" data-job-id="">
        <svg class="bookmark-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
          stroke-width="2" xmlns="http://www.w3.org/2000/svg">
          <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
        </svg>
      </button>

    <?php } else { ?>

      <button class="bookmark-btn <?php echo $is_saved ? 'saved' : ''; ?>" data-job-id="<?php echo $post_id; ?>">
        <svg class="bookmark-icon" width="20" height="20" viewBox="0 0 24 24"
          fill="<?php echo $is_saved ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2"
          xmlns="http://www.w3.org/2000/svg">
          <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
        </svg>
      </button>
    <?php } ?>

  </div>
  <div class="job-tags">
    <?php foreach ($job_types as $type): ?>
      <span class="tag tag-blue"><?php echo esc_html($type); ?></span>
    <?php endforeach; ?>

    <?php if (!empty($salary)): ?>
      <?php
      // Format salary display
      $formatted_salary = '';
      if (strpos($salary, 'above-') !== false) {
        // Convert "above-100,000" → "Above 100,000"
        $formatted_salary = 'Above ' . str_replace('above-', '', $salary);
      } else {
        // Keep range as it is (e.g., "10,000-20,000")
        $formatted_salary = $salary;
      }
      ?>
      <span class="salary">
        <?php echo esc_html($formatted_salary); ?>
        <?php if (!empty($job_salary_currency)) echo ' ' . esc_html($job_salary_currency); ?>
        <?php if (!empty($job_salary_unit)) echo ' / ' . ucfirst(strtolower($job_salary_unit)); ?>
      </span>
    <?php endif; ?>
  </div>

  <div class="job-content">
    <?php echo (apply_filters('the_content', get_post_field('post_content', $post_id))); ?>

  </div>
  <div class="job-content">
    <h4 style="font-weight:700;">How to Apply</h4>
    <?php //echo (get_field('how_to_apply', $post_id)); 
    ?>
    <?php echo apply_filters('the_content', get_field('how_to_apply', $post_id)); ?>

  </div>

  <div class="job-link-container">
    <?php $job_link = get_field('job_link', $post_id); ?>
    <button class="job-link-button" onclick="toggleJobLinkPopup()">Job Link</button>
    <div class="job-link-popup" id="job-link-popup">
      <input type="text" id="job-link" class="job-link-input" value="<?php echo esc_attr($job_link); ?>" readonly>
      <a href="#" class="copy-link" onclick="copyJobLink(event)">Copy Link</a>
      <span id="copy-message" class="copy-message">Link Copied to Clipboard!</span>
    </div>
  </div>

  <?php

  // Decode JSON if needed
  if (!empty($attachment_urls)) {
    $attachment_urls = json_decode($attachment_urls[0], true);
  }

  /*echo '<pre>';
    print_r($attachment_urls);
  echo '</pre>';*/

  if (!empty($attachment_urls)):

    // Single file
    if (count($attachment_urls) === 1):
      $file_url = $attachment_urls[0]; ?>
      <a href="<?php echo esc_url($file_url); ?>" class="download-button" download>
        <svg aria-hidden="true" class="fa-download-icon" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
          <path
            d="M216 0h80c13.3 0 24 10.7 24 24v168h87.7c17.8 0 26.7 21.5 14.1 34.1L269.7 378.3c-7.5 7.5-19.8 7.5-27.3 0L90.1 226.1c-12.6-12.6-3.7-34.1 14.1-34.1H192V24c0-13.3 10.7-24 24-24zm296 376v112c0 13.3-10.7 24-24 24H24c-13.3 0-24-10.7-24-24V376c0-13.3 10.7-24 24-24h146.7l49 49c20.1 20.1 52.5 20.1 72.6 0l49-49H488c13.3 0 24 10.7 24 24zm-124 88c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20z">
          </path>
        </svg>
        Download Attachment
      </a>
    <?php
    else:
      // Multiple files  link to AJAX ZIP download
      $zip_url = add_query_arg(
        ['action' => 'download_job_attachments', 'post_id' => $post_id],
        admin_url('admin-ajax.php')
      ); ?>
      <a href="<?php echo esc_url($zip_url); ?>" class="download-button">
        <svg aria-hidden="true" class="fa-download-icon" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
          <path
            d="M216 0h80c13.3 0 24 10.7 24 24v168h87.7c17.8 0 26.7 21.5 14.1 34.1L269.7 378.3c-7.5 7.5-19.8 7.5-27.3 0L90.1 226.1c-12.6-12.6-3.7-34.1 14.1-34.1H192V24c0-13.3 10.7-24 24-24zm296 376v112c0 13.3-10.7 24-24 24H24c-13.3 0-24-10.7-24-24V376c0-13.3 10.7-24 24-24h146.7l49 49c20.1 20.1 52.5 20.1 72.6 0l49-49H488c13.3 0 24 10.7 24 24zm-124 88c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20z">
          </path>
        </svg>
        Download Attachments (<?php echo count($attachment_urls); ?>)
      </a>
  <?php
    endif;
  endif;
  ?>

<?php
  return ob_get_clean();
}

// AJAX callback (unchanged)
add_action('wp_ajax_get_job_details', 'my_ajax_get_job_details');
add_action('wp_ajax_nopriv_get_job_details', 'my_ajax_get_job_details');

function my_ajax_get_job_details()
{
  if (!isset($_POST['job_id'])) {
    wp_send_json_error('Missing job_id');
  }
  $job_id = intval($_POST['job_id']);
  echo my_get_single_job_html($job_id);
  wp_die();
}

// Bookmark functionality (unchanged)
add_action('wp_ajax_toggle_save_job', 'toggle_save_job');
add_action('wp_ajax_nopriv_toggle_save_job', 'toggle_save_job');

function toggle_save_job()
{
  if (!is_user_logged_in()) {
    wp_send_json_error(['message' => 'Please log in to save jobs.']);
  }

  $user_id = get_current_user_id();
  $job_id = intval($_POST['job_id']);

  if (!$job_id || get_post_type($job_id) !== 'job_listing') {
    wp_send_json_error(['message' => 'Invalid job ID']);
  }

  $saved_jobs = get_user_meta($user_id, 'saved_jobs', true);
  if (!is_array($saved_jobs)) {
    $saved_jobs = [];
  }

  if (in_array($job_id, $saved_jobs)) {
    $saved_jobs = array_diff($saved_jobs, [$job_id]);
    update_user_meta($user_id, 'saved_jobs', $saved_jobs);
    wp_send_json_success(['saved' => false, 'message' => 'Job removed from saved list']);
  } else {
    $saved_jobs[] = $job_id;
    $saved_jobs = array_unique($saved_jobs);
    update_user_meta($user_id, 'saved_jobs', $saved_jobs);
    wp_send_json_success(['saved' => true, 'message' => 'Job saved successfully']);
  }
}

// Modified AJAX filter function to handle arrays
add_action('wp_ajax_filter_jobs', 'my_ajax_filter_jobs');
add_action('wp_ajax_nopriv_filter_jobs', 'my_ajax_filter_jobs');

function my_ajax_filter_jobs()
{
  $meta_query = [];
  $tax_query = [];

  // Handle company names (can be array)
  if (!empty($_POST['company_name'])) {
    $company_names = is_array($_POST['company_name']) ? $_POST['company_name'] : [$_POST['company_name']];
    if (count($company_names) > 1) {
      $company_meta_query = ['relation' => 'OR'];
      foreach ($company_names as $company) {
        $company_meta_query[] = [
          'key' => '_company_name',
          'value' => sanitize_text_field($company),
        ];
      }
      $meta_query[] = $company_meta_query;
    } else {
      $meta_query[] = [
        'key' => '_company_name',
        'value' => sanitize_text_field($company_names[0]),
      ];
    }
  }

  // Add meta_query for non-expired jobs
  $today = current_time('Y-m-d');
  $meta_query[] = [
    'relation' => 'OR',
    [
      'key' => '__job_expires',
      'value' => $today,
      'compare' => '>=',
      'type' => 'DATE',
    ],
    [
      'key' => '__job_expires',
      'compare' => 'NOT EXISTS', // Include jobs with no expiry date set
    ],
  ];

  // Handle taxonomies (can be arrays)
  $taxonomies = ['job_listing_type', 'job_location_category', 'job_listing_category'];

  foreach ($taxonomies as $taxonomy) {
    if (!empty($_POST[$taxonomy])) {
      $terms = is_array($_POST[$taxonomy]) ? $_POST[$taxonomy] : [$_POST[$taxonomy]];
      $terms = array_map('sanitize_text_field', $terms);

      $tax_query[] = [
        'taxonomy' => $taxonomy,
        'field' => 'slug',
        'terms' => $terms,
        'operator' => 'IN'
      ];
    }
  }

  // Sorting Logic
  $order = 'DESC';
  if (!empty($_POST['job_sorting']) && $_POST['job_sorting'] === 'Oldest Post') {
    $order = 'ASC';
  }

  $args = [
    'post_type' => 'job_listing',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    's' => sanitize_text_field($_POST['keyword']),
    'meta_query' => $meta_query,
    'tax_query' => $tax_query,
    'orderby' => 'date',
    'order' => $order,
  ];

  $jobs = new WP_Query($args);

  if ($jobs->have_posts()) {
    while ($jobs->have_posts()) {
      $jobs->the_post();
      $company_logo = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail') ?: get_stylesheet_directory_uri() . '/images/noimage.jpg';
      // $company_logo = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail') ?: 'https://via.placeholder.com/50';
      $company_name = get_post_meta(get_the_ID(), '_company_name', true);
      $location = get_post_meta(get_the_ID(), '_job_location', true);
      $posted = human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago';

      echo '<div class="job-card" data-id="' . get_the_ID() . '">';
      echo '<img src="' . esc_url($company_logo) . '" alt="'. esc_attr($company_name) .'"class="company-logo">';
      echo '<div class="job-info">';
      echo '<h3>' . esc_html(get_the_title()) . '</h3>';
      echo '<p class="company-name">' . esc_html($company_name) . '</p>';
      echo '<div class="job-meta">';
      echo '<div class="location">' . esc_html($location) . '</div>';
      echo '<span class="posted-time">' . esc_html($posted) . '</span>';
      echo '</div>';
      echo '</div>';
      echo '</div>';
    }
  } else {
    echo '<p style="text-align: center; padding-top: 40px;">No jobs found.</p>';
  }

  wp_reset_postdata();
  wp_die();
}
?>