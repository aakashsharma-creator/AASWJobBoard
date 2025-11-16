<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit;
}

function my_search_locum()
{
  ob_start(); ?>
  <style>
    @import url("https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css");
    @import url("https://fonts.googleapis.com/css?family=Nunito+Sans:400,500,700,600|Raleway:600|Poppins:600|Inter:var(--body-medium-400-font-weight)");
  </style>

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

  <link rel="stylesheet" type="text/css"
    href="<?php echo get_stylesheet_directory_uri(); ?>/custom-shortcodes/css/search-locum.css">

  <div class="container">
    <!-- Tabs -->
    <div class="tabs">
      <button class="tab active">Search Locum</button>
      <a href="<?php echo esc_url(site_url('/saved-locum')); ?>" class="tab">Saved Locum</a>
    </div>
	
	
			<div><button id="filterToggleBtn" class="filter-btn" style="width: 100%; background: #00688f; border: 1px solid #00688f; color: #fff;">
	<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M3 4h18v2H3V4zm2 5h14v2H5V9zm4 5h6v2H9v-2z"></path>
            </svg>
	Filters</button></div>
	
	
    <!-- Search for Locum -->
    <div class="search-for-locum" id="filtersContainer" style="display:none;">
      <div class="main-content">
        <div class="search-container">
          <div class="search-header">
            <div class="search-input">
              <input type="text" name="search_input" placeholder="Search with keyword..." />
            </div>
            <div class="filters-row">
              <!-- Job Type (Availability) -->
              <div class="filter-select">
                <div class="multiselect-wrapper">
                  <div class="multiselect-trigger" data-target="job_listing_type">
                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/Frame_recolored.png" alt="" />
                    <span class="selection-text">Availabilities</span>
                  </div>
                  <div class="multiselect-dropdown">
                    <div class="multiselect-search">
                      <input type="text" placeholder="Search Availabilities..." class="multiselect-search-input">
                    </div>
                    <div class="multiselect-options">
                      <div class="multiselect-option">
                        <input type="checkbox" id="job_type_full_time" value="full_time">
                        <label for="job_type_full_time">Full Time</label>
                      </div>
                      <div class="multiselect-option">
                        <input type="checkbox" id="job_type_part_time" value="part_time">
                        <label for="job_type_part_time">Part Time</label>
                      </div>
                      <div class="multiselect-option">
                        <input type="checkbox" id="job_type_contract" value="contract">
                        <label for="job_type_contract">Contract</label>
                      </div>
                    </div>
                    <div class="multiselect-actions">
                      <button class="multiselect-btn reset">Reset</button>
                      <button class="multiselect-btn apply">Apply</button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Location -->
              <div class="filter-select">
                <div class="multiselect-wrapper">
                  <div class="multiselect-trigger" data-target="job_location_category">
                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/fi_map-pin.png" alt="" />
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
                          echo '<input type="checkbox" id="location_' . esc_attr($term->slug) . '" value="' . esc_attr($term->name) . '">';
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

              <!-- Category (Sector) -->
              <div class="filter-select">
                <div class="multiselect-wrapper">
                  <div class="multiselect-trigger" data-target="sector">
                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/fi_layers.png" alt="" />
                    <span class="selection-text">Sectors</span>
                  </div>
                  <div class="multiselect-dropdown">
                    <div class="multiselect-search">
                      <input type="text" placeholder="Search sectors..." class="multiselect-search-input">
                    </div>
                    <div class="multiselect-options">
                      <!-- Hardcoded sectors  -->
                      <?php
                      $all_sectors = [
                        'Academia',
                        'Accountant',
                        'Administration',
                        'Aged Care',
                        'Alcohol, tobacco and other drug',
                        'Assessments',
                        'Audits / accreditations',
                        'Business Development',
                        'Case Management',
                        'Child',
                        'Child protection',
                        'Community & Development',
                        'Consultancy',
                        'Corrections',
                        'Counselling / Therapy',
                        'Culturally and Linguistically Diverse',
                        'Cyber Security',
                        'Defence',
                        'Digital Content',
                        'Disability',
                        'Eating disorders',
                        'Education',
                        'Emergency Care',
                        'Employee Assistance Provider (EAP)',
                        'Ethical / Legal / Regulatory Compliance',
                        'Event management',
                        'Families/Carers',
                        'Family violence',
                        'Health',
                        'Hospital',
                        'Housing',
                        'Income Support',
                        'Infants',
                        'Management / Leadership',
                        'Marketing / Communications',
                        'Mental Health',
                        'Palliative Care / End of Life',
                        'Policy / Advocacy',
                        'Project Management',
                        'Research',
                        'Sexual assault',
                        'Social Worker',
                        'Supervision',
                        'Training',
                        'Trauma',
                        'Veterans',
                        'Women/Children',
                        'Youth'
                      ];
                      foreach ($all_sectors as $sector) {
                        $id = 'sector_' . sanitize_title($sector);
                        echo '<div class="multiselect-option">';
                        echo '<input type="checkbox" id="' . esc_attr($id) . '" value="' . esc_attr($sector) . '">';
                        echo '<label for="' . esc_attr($id) . '">' . esc_html($sector) . '</label>';
                        echo '</div>';
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

              <button class="search-btn">Search</button>
              <button class="reset-btn">Reset All</button>
            </div>
          </div>

          <div class="content-area">
            <div class="locum-list">
              <div class="list-header">
                <h3 class="locum-count">0 Locum</h3>
              </div>
              <div class="locum-cards">
                <div style="text-align:center;padding:20px;">Please search to see results</div>
              </div>
            </div>
            <!--<div class="detail-panel" id="locum-details">
              <div style="text-align:center;padding:40px;color:#777;">Select a locum to view details</div>
            </div>-->
            <div class="alerts-section">
              <div class="alerts-content">
                <span>Subscribe for Latest Locum Updates</span>
                <button class="get-alerts-btn">Get Alerts</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
	
		<div id="job-details" class="job-details popup" style="display:none;">
	<div class="popup-content">
	<button id="closeJobPopup" class="close-btn">×</button>
	<div id="jobDetailPopupContent"></div>
	</div>
	</div>
	
	

    <!-- Job Alerts Inline Modal -->
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
<style>
  /* Background overlay */
.popup {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

/* Modal box */
.popup-content {
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    width: 100%;
    max-width: 700px;
    max-height: 90%;
	min-height:400px;
    overflow-y: auto;
    position: relative;
}

/* Close button */
.close-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 25px;
    background: none;
    border: none;
    cursor: pointer;
}


</style>
  <script>
    jQuery(document).ready(function($) {
      if (window.myLocumSearchLoaded) return;
      window.myLocumSearchLoaded = true;

      // Initialize selected filters from URL
      let selectedFilters = {
        job_listing_type: <?php echo json_encode(!empty($_GET['job_listing_type']) ? array_map('sanitize_text_field', explode(',', $_GET['job_listing_type'])) : []); ?>,
        job_location_category: <?php echo json_encode(!empty($_GET['location']) ? array_map('sanitize_text_field', explode(',', $_GET['location'])) : []); ?>,
        sector: <?php echo json_encode(!empty($_GET['job_listing_category']) ? array_map('sanitize_text_field', explode(',', $_GET['job_listing_category'])) : []); ?>,
        company_name: <?php echo json_encode(!empty($_GET['company_name']) ? array_map('sanitize_text_field', explode(',', $_GET['company_name'])) : []); ?>
      };

      // Pre-select checkboxes
      ['job_listing_type', 'job_location_category', 'sector', 'company_name'].forEach(function(filterType) {
        selectedFilters[filterType].forEach(function(slug) {
          $(`.multiselect-trigger[data-target="${filterType}"]`)
            .siblings('.multiselect-dropdown')
            .find(`input[type="checkbox"][value="${slug}"]`)
            .prop('checked', true);
        });
        updateTriggerText(filterType);
      });

      // Toggle dropdown
      $(document).on('click', '.multiselect-trigger', function(e) {
        e.stopPropagation();
        const dropdown = $(this).siblings('.multiselect-dropdown');
        $('.multiselect-dropdown').not(dropdown).removeClass('show');
        $('.multiselect-trigger').not(this).removeClass('open');
        dropdown.toggleClass('show');
        $(this).toggleClass('open');
        if (dropdown.hasClass('show')) {
          dropdown.find('.multiselect-search-input').val('').trigger('input');
        }
      });

      $(document).on('click', function() {
        $('.multiselect-dropdown').removeClass('show');
        $('.multiselect-trigger').removeClass('open');
      });

      $(document).on('click', '.multiselect-dropdown', function(e) {
        e.stopPropagation();
      });

      // Search in dropdown
      $(document).on('input', '.multiselect-search-input', function() {
        const searchTerm = $(this).val().toLowerCase();
        const options = $(this).closest('.multiselect-dropdown').find('.multiselect-option');
        options.each(function() {
          const text = $(this).find('label').text().toLowerCase();
          $(this).toggle(text.includes(searchTerm));
        });
      });

      // Checkbox change
      $(document).on('change', '.multiselect-option input[type="checkbox"]', function() {
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

      // Reset in dropdown
      $(document).on('click', '.multiselect-btn.reset', function() {
        const dropdown = $(this).closest('.multiselect-dropdown');
        const filterType = dropdown.siblings('.multiselect-trigger').data('target');
        dropdown.find('input[type="checkbox"]').prop('checked', false);
        dropdown.find('.multiselect-search-input').val('');
        dropdown.find('.multiselect-option').show();
        selectedFilters[filterType] = [];
        updateTriggerText(filterType);
      });

      // Apply
      $(document).on('click', '.multiselect-btn.apply', function() {
        const dropdown = $(this).closest('.multiselect-dropdown');
        const filterType = dropdown.siblings('.multiselect-trigger').data('target');
        dropdown.removeClass('show');
        dropdown.siblings('.multiselect-trigger').removeClass('open');
        updateTriggerText(filterType);
      });

      // Update trigger text
      function updateTriggerText(filterType) {
        const trigger = $(`.multiselect-trigger[data-target="${filterType}"]`);
        const selections = selectedFilters[filterType];
        const textElement = trigger.find('.selection-text');
        const countElement = trigger.find('.selection-count');
        countElement.remove();

        if (selections.length === 0) {
          let defaultText = 'All';
          switch (filterType) {
            case 'job_listing_type':
              defaultText = 'Availabilities';
              break;
            case 'job_location_category':
              defaultText = 'All Locations';
              break;
            case 'sector':
              defaultText = 'Sectors';
              break;
          }
          textElement.text(defaultText);
          trigger.removeClass('selected').closest('.filter-select').removeClass('selected');
          trigger.css({
            'background-color': 'white',
            'border-color': '#e2e8f0',
            'color': '#334155'
          });
          trigger.find('img').css('filter', 'none');
        } else {
          if (selections.length === 1) {
            const label = $(`.multiselect-trigger[data-target="${filterType}"]`)
              .siblings('.multiselect-dropdown')
              .find(`input[value="${selections[0]}"]`)
              .siblings('label').text();
            textElement.text(label);
          } else {
            textElement.text(`${selections.length} selected`);
          }
          trigger.append(`<span class="selection-count">${selections.length}</span>`);
          trigger.addClass('selected').closest('.filter-select').addClass('selected');
          trigger.css({
            'background-color': '#00688f',
            'border-color': '#00688f',
            'color': 'white'
          });
          trigger.find('img').css('filter', 'brightness(0) invert(1)');
        }
      }

      // Perform search
      function performSearch() {
        const filters = {
          job_listing_type: selectedFilters.job_listing_type,
          job_location_category: selectedFilters.job_location_category,
          sector: selectedFilters.sector,
          keyword: $('input[name="search_input"]').val(),
          action: 'filter_locums'
        };

        $('.locum-cards').html('<div style="text-align:center;padding:20px;">Loading...</div>');
        $('#locum-details').html('<div style="text-align:center;padding:40px;color:#777;">Select a locum to view details</div>');

        $.ajax({
          url: "<?php echo admin_url('admin-ajax.php'); ?>",
          type: 'POST',
          data: filters,
          success: function(res) {
            $('.locum-cards').html(res);
            const count = $('.locum-cards .locum-card').length;
            $('.locum-count').text(`${count} Locum${count !== 1 ? 's' : ''}`);
            $('.locum-cards .locum-card').first().trigger('click');
          },
          error: function() {
            $('.locum-cards').html('<div style="text-align:center;padding:20px;">Error loading locums.</div>');
          }
        });
      }

      $(document).on('click', '.search-btn', performSearch);
      $(document).on('click', '.reset-btn', function(e) {
        e.preventDefault();
        Object.keys(selectedFilters).forEach(type => {
          selectedFilters[type] = [];
          const trigger = $(`.multiselect-trigger[data-target="${type}"]`);
          const dropdown = trigger.siblings('.multiselect-dropdown');
          dropdown.find('input[type="checkbox"]').prop('checked', false);
          dropdown.find('.multiselect-search-input').val('');
          dropdown.find('.multiselect-option').show();
          updateTriggerText(type);
        });
        $('input[name="search_input"]').val('');
        performSearch();
      });

      // Card click
      $(document).on('click', '.locum-card', function() {
        const locumId = $(this).data('id');
        $('.locum-card').removeClass('active');
        $(this).addClass('active');
        $('#locum-details').html('<div class="loader-container"><div class="loader"></div></div>');

        $.ajax({
          url: "<?php echo admin_url('admin-ajax.php'); ?>",
          type: 'POST',
          data: {
            action: 'get_locum_details',
            locum_id: locumId
          },
          success: function(res) {
            $('#locum-details').html(res);
          },
          error: function() {
            $('#locum-details').html('<div style="text-align:center;padding:20px;">Error loading details.</div>');
          }
        });
      });

      performSearch();
    });
  </script>

  <script>
    jQuery(document).on("click", ".bookmark-btn", function(e) {
      e.preventDefault();
      const button = jQuery(this);
      const icon = button.find(".bookmark-icon");
      const locumId = button.data("locum-id");

      <?php if (!is_user_logged_in()): ?>
        if (typeof elementorProFrontend !== "undefined") {
          elementorProFrontend.modules.popup.showPopup({
            id: 2326
          });
        } else {
          alert("Please log in to save bookmarks.");
        }
        return;
      <?php endif; ?>

      button.addClass("bookmark-loading");

      jQuery.ajax({
        url: "<?php echo admin_url('admin-ajax.php'); ?>",
        type: "POST",
        data: {
          action: "toggle_locum_bookmark",
          entry_id: locumId
        },
        success: function(response) {
          button.removeClass("bookmark-loading");
          if (response.success) {
            if (response.data.saved) {
              button.addClass("saved");
              icon.attr("fill", "currentColor");
            } else {
              button.removeClass("saved");
              icon.attr("fill", "none");
            }
          } else {
            alert(response.data.message);
          }
        },
        error: function() {
          button.removeClass("bookmark-loading");
          alert("Error! Try again.");
        },
        complete: function () {
    // ✅ Loader always removed (success or error)
    button.removeClass("bookmark-loading");
  },
      });
    });
  </script>

  <script>
  jQuery(function($) {
    function openJobAlertsModal() {
      const $popup = $('#job-alerts-modal');
      const $dialog = $popup.find('.jam-dialog');

      $popup.addClass('open').attr('aria-hidden', 'false');
      $('body').addClass('no-scroll job-alerts-open');

      // Re-init dropdown widgets so they render correctly inside the modal
      prepareModalWidgets($dialog);

      // Focus first input
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
      // Select2 dropdowns
      if ($.fn.select2) {
        $dialog.find('select').each(function() {
          const $sel = $(this);
          const wasSelect2 = !!$sel.data('select2') || $sel.hasClass('select2') || $sel.hasClass('select2-hidden-accessible');

          if ($sel.data('select2')) {
            try { $sel.select2('destroy'); } catch (e) {}
          }
          if (wasSelect2) {
            $sel.select2({
              dropdownParent: $dialog,
              width: '100%'
            });
          }
        });
      }
    }

    // Open on "Get Alerts" button click
    $(document).on('click', '.get-alerts-btn', function(e) {
      e.preventDefault();
      openJobAlertsModal();
    });

    // Close on overlay or X
    $(document).on('click', '#job-alerts-modal .jam-overlay, #job-alerts-modal .jam-close', function() {
      closeJobAlertsModal();
    });

    // Close on ESC
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
add_shortcode('search_locum', 'my_search_locum');

// AJAX: Filter Locums
add_action('wp_ajax_filter_locums', 'my_filter_locums');
add_action('wp_ajax_nopriv_filter_locums', 'my_filter_locums');

function my_filter_locums()
{
  global $wpdb;

  $keyword = isset($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : '';
  $job_types = isset($_POST['job_listing_type']) ? array_map('sanitize_text_field', (array) $_POST['job_listing_type']) : [];
  $locations = isset($_POST['job_location_category']) ? array_map('sanitize_text_field', (array) $_POST['job_location_category']) : [];
  $categories = isset($_POST['sector']) ? array_map('sanitize_text_field', (array) $_POST['sector']) : [];

  $job_type_map = ['full_time' => 'Full Time', 'part_time' => 'Part Time', 'contract' => 'Contract'];
  $mapped_job_types = array_map(fn($t) => $job_type_map[$t] ?? $t, $job_types);

  $location_map = [];
  $terms = get_terms(['taxonomy' => 'job_location_category', 'hide_empty' => false]);
  if (!empty($terms) && !is_wp_error($terms)) {
    foreach ($terms as $term) $location_map[$term->slug] = $term->name;
  }
  $mapped_locations = array_map(fn($l) => $location_map[$l] ?? $l, $locations);

  $args = [
    'role' => 'candidate',
    'orderby' => 'user_registered',
    'order' => 'DESC',
    'number' => 50,
    'search' => !empty($keyword) ? '*' . $keyword . '*' : '',
    'search_columns' => ['user_login', 'user_nicename', 'display_name', 'user_email'],
  ];

  $users = get_users($args);
  if (empty($users)) {
    echo '<div style="padding:20px;text-align:center;">No locums found matching your search criteria.</div>';
    wp_die();
  }

  $output = '';

  foreach ($users as $user) {
    $email = $user->user_email;

    $entry_id = $wpdb->get_var($wpdb->prepare(
      "SELECT entry_id FROM {$wpdb->prefix}frmt_form_entry_meta WHERE meta_key = 'email-1' AND meta_value = %s ORDER BY entry_id DESC LIMIT 1",
      $email
    ));

    if (!$entry_id) continue;

    $meta_rows = $wpdb->get_results($wpdb->prepare(
      "SELECT meta_key, meta_value FROM {$wpdb->prefix}frmt_form_entry_meta WHERE entry_id = %d",
      $entry_id
    ), OBJECT);

    $meta_data = [];
    foreach ($meta_rows as $row) {
      $meta_data[$row->meta_key] = maybe_unserialize($row->meta_value);
    }

    $name = !empty($meta_data['name-2']) && !empty($meta_data['name-3'])
      ? esc_html($meta_data['name-2'] . ' ' . $meta_data['name-3'])
      : esc_html($user->display_name);

    // Sector: format for display (comma separated, 2-line limit via CSS)
    $sector_raw = !empty($meta_data['select-6']) ? $meta_data['select-6'] : '';
    $sectors = is_array($sector_raw) ? $sector_raw : explode(',', $sector_raw);
    $sectors = array_map('trim', $sectors);
    $sectors_display = $sectors ? esc_html(implode(', ', $sectors)) : 'Sectors not provided';

    $address = !empty($meta_data['address-1']) && is_array($meta_data['address-1']) ? $meta_data['address-1'] : [];
    $location = !empty($address['city']) ? esc_html($address['city']) : (!empty($meta_data['select-4']) ? esc_html($meta_data['select-4']) : 'Location not provided');
    $availability = !empty($meta_data['select-1']) ? esc_html($meta_data['select-1']) : 'Availability not provided';
    $avatar = !empty($meta_data['upload-2']) && is_array($meta_data['upload-2']) && !empty($meta_data['upload-2']['file']['file_url'])
      ? esc_url($meta_data['upload-2']['file']['file_url'])
      : get_avatar_url($user->ID);

    // Filters
    $skip = false;

    if (!empty($keyword)) {
      $keyword = strtolower(trim($keyword));
      $match = strpos(strtolower($name), $keyword) !== false ||
        strpos(strtolower($sectors_display), $keyword) !== false ||
        strpos(strtolower($meta_data['textarea-1'] ?? ''), $keyword) !== false;
      if (!$match) $skip = true;
    }

    if (!empty($mapped_job_types) && !in_array($availability, $mapped_job_types)) $skip = true;

    if (!empty($mapped_locations)) {
      $location_value = $meta_data['select-4'] ?? '';
      $match = false;
      foreach ($mapped_locations as $loc) {
        if ($location_value === $loc) {
          $match = true;
          break;
        }
      }
      if (!$match) $skip = true;
    }

    if (!empty($categories)) {
      $sector_match = false;
      foreach ($categories as $cat) {
        if (in_array($cat, $sectors)) {
          $sector_match = true;
          break;
        }
      }
      if (!$sector_match) $skip = true;
    }

    if ($skip) continue;

    // Output card
    $output .= '<div class="locum-card" data-id="' . esc_attr($entry_id) . '">';
    $output .= '  <div class="card-content">';
    $output .= '    <div class="profile-section">';
    $output .= '      <img src="' . $avatar . '" alt="' . $name . '" class="avatar" />';
    $output .= '      <div class="profile-info">';
    $output .= '        <h4>' . $name . '</h4>';
    $output .= '        <p class="sector-text">' . $sectors_display . '</p>';
    $output .= '      </div>';
    $output .= '    </div>';
    $output .= '    <div class="card-meta">';
    $output .= '      <div class="meta-item">';
    $output .= '        <div class="meta-content"><img src="' . esc_url(get_stylesheet_directory_uri() . '/images/duo-icons_location.png') . '" alt="Location" /></div>';
    $output .= '        <span>' . esc_html($meta_data['select-4'] ?? '') . '</span>';
    $output .= '      </div>';
    $output .= '      <div class="meta-item" style="justify-content: end;">';
    $output .= '        <div class="meta-content"><img src="' . esc_url(get_stylesheet_directory_uri() . '/images/uim_calender.png') . '" alt="Availability" /></div>';
    $output .= '        <span>' . $availability . '</span>';
    $output .= '      </div>';
    $output .= '    </div>';
    $output .= '  </div>';
    $output .= '</div>';
  }

  echo $output ?: '<div style="padding:20px;text-align:center;">No locums found matching your search criteria.</div>';
  wp_die();
}

// AJAX: Toggle Bookmark
add_action('wp_ajax_toggle_locum_bookmark', 'toggle_locum_bookmark');
add_action('wp_ajax_nopriv_toggle_locum_bookmark', 'toggle_locum_bookmark');

function toggle_locum_bookmark()
{
  if (!is_user_logged_in()) {
    wp_send_json_error(['message' => 'Login required']);
  }

  $user_id = get_current_user_id();
  $entry_id = intval($_POST['entry_id'] ?? 0);
  if (!$entry_id) wp_send_json_error(['message' => 'Invalid ID']);

  $saved = get_user_meta($user_id, 'saved_locums', true);
  $saved = is_array($saved) ? $saved : ($saved ? explode(',', $saved) : []);
  $saved = array_map('intval', $saved);
  $was_saved = in_array($entry_id, $saved, true);

  if ($was_saved) {
    $saved = array_diff($saved, [$entry_id]);
  } else {
    $saved[] = $entry_id;
  }

  update_user_meta($user_id, 'saved_locums', array_values($saved));
  wp_send_json_success(['saved' => !$was_saved]);
}

// AJAX: Get Locum Details
add_action('wp_ajax_get_locum_details', 'my_get_locum_details');
add_action('wp_ajax_nopriv_get_locum_details', 'my_get_locum_details');

function my_get_locum_details()
{
  global $wpdb;

  $entry_id = intval($_POST['locum_id'] ?? 0);
  if (!$entry_id) {
    echo '<div style="padding:20px;text-align:center;">Invalid locum ID.</div>';
    wp_die();
  }

  $meta_rows = $wpdb->get_results($wpdb->prepare(
    "SELECT meta_key, meta_value FROM {$wpdb->prefix}frmt_form_entry_meta WHERE entry_id = %d",
    $entry_id
  ), OBJECT);

  if (empty($meta_rows)) {
    echo '<div style="padding:20px;text-align:center;">Locum not found.</div>';
    wp_die();
  }

  $meta_data = [];
  foreach ($meta_rows as $row) {
    $meta_data[$row->meta_key] = maybe_unserialize($row->meta_value);
  }

  $email = !empty($meta_data['email-1']) ? sanitize_email($meta_data['email-1']) : '';
  $user = $email ? get_user_by('email', $email) : false;

  $name = !empty($meta_data['name-2']) && !empty($meta_data['name-3'])
    ? esc_html($meta_data['name-2'] . ' ' . $meta_data['name-3'])
    : ($user ? esc_html($user->display_name) : 'Unknown');

  // Sector: dynamic, 2-line limit via CSS
  $sector_raw = !empty($meta_data['select-6']) ? $meta_data['select-6'] : '';
  $sectors = is_array($sector_raw) ? $sector_raw : explode(',', $sector_raw);
  $sectors = array_map('trim', $sectors);
  $sector_display = $sectors ? esc_html(implode(', ', $sectors)) : 'Not specified';

  $skills = !empty($meta_data['text-5']) ? esc_html($meta_data['text-5']) : 'Specializations not provided';
  $about = !empty($meta_data['textarea-1']) ? wp_strip_all_tags($meta_data['textarea-1']) : 'No description provided';
  $availability = !empty($meta_data['select-1']) ? esc_html($meta_data['select-1']) : 'Not specified';
  $address = !empty($meta_data['address-1']) && is_array($meta_data['address-1']) ? $meta_data['address-1'] : [];
  $location = !empty($address['city']) ? esc_html($address['city']) . ' - ' . esc_html($meta_data['select-4'] ?? 'Not specified') : esc_html($meta_data['select-4'] ?? 'Not specified');
  $accreditation = !empty($meta_data['text-4']) ? esc_html($meta_data['text-4']) : 'Not specified';
  $qualification = !empty($meta_data['select-3']) ? esc_html($meta_data['select-3']) : 'Not specified';
  $experience = !empty($meta_data['text-3']) ? esc_html($meta_data['text-3']) . ' Years' : 'Not specified';
  $linkedin = !empty($meta_data['url-1']) ? esc_url($meta_data['url-1']) : '';
  $mobileNumber = !empty($meta_data['phone-1']) ? esc_html($meta_data['phone-1']) : '#';
  $avatar = !empty($meta_data['upload-2']) && is_array($meta_data['upload-2']) && !empty($meta_data['upload-2']['file']['file_url'])
    ? esc_url($meta_data['upload-2']['file']['file_url'])
    : ($user ? get_avatar_url($user->ID) : esc_url(get_stylesheet_directory_uri() . '/images/Avatar.png'));
  $cv_url = !empty($meta_data['upload-1']) && is_array($meta_data['upload-1']) && !empty($meta_data['upload-1']['file']['file_url'][0])
    ? esc_url($meta_data['upload-1']['file']['file_url'][0]) : '';

  $saved_locums = get_user_meta(get_current_user_id(), 'saved_locums', true);
  $is_saved = is_array($saved_locums) && in_array($entry_id, $saved_locums);
  $status = !empty($meta_data['status']) ? esc_html($meta_data['status']) : 'N/A';

  // if ($user && $user->ID) {
  $meta_key = 'locum_views_';
  $current_views = (int) get_user_meta($user->ID, $meta_key, true);
  $new_views = $current_views + 1;
  update_user_meta($user->ID, $meta_key, $new_views);
  //  }


?>

  <div class="profile-header">
    <div class="profile-main">
      <img src="<?php echo $avatar; ?>" alt="<?php echo $name; ?>" class="profile-avatar" />
      <div class="profile-details">
        <h2><?php echo $name; ?></h2>
        <p class="sector-text"><?php echo $sector_display; ?></p>
      </div>
    </div>
    <div class="profile-actions">
      <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $status)); ?>"><?php echo $status; ?></span>
      <button class="bookmark-btn <?php echo $is_saved ? 'saved' : ''; ?>" data-locum-id="<?php echo esc_attr($entry_id); ?>">
        <svg class="bookmark-icon" width="22" height="22" viewBox="0 0 24 24" fill="<?php echo $is_saved ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2" xmlns="http://www.w3.org/2000/svg">
          <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
        </svg>
      </button>
    </div>
  </div>

  <div class="action-buttons">
    <span class="compare-link">+ Add to Compare</span>
    <div class="button-group">
      <a href="mailto:<?php echo $email; ?>" class="btn-outline">
        <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/Envelope.png'); ?>" alt="Email" /> Send Mail
      </a>
      <a href="tel:<?php echo $mobileNumber; ?>" class="btn-primary" style="text-decoration:none; color:#fff;">
        <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/proicons_call.png'); ?>" alt="Call" />
        <?php echo $mobileNumber; ?>
      </a>
    </div>
  </div>

  <div class="profile-content">
    <div class="about-section">
      <h3>About Me</h3>
      <p><?php echo $about; ?></p>
    </div>

    <div class="info-grid">
      <div class="info-item">
        <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/uil_calender.png'); ?>" alt="Availability" />
        <div>
          <div class="info-label">AVAILABILITY</div>
          <div class="info-value"><?php echo $availability; ?></div>
        </div>
      </div>
      <div class="info-item">
        <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/MapTrifold.png'); ?>" alt="Location" />
        <div>
          <div class="info-label">LOCATION</div>
          <div class="info-value"><?php echo $location; ?></div>
        </div>
      </div>
      <div class="info-item">
        <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/ClipboardText.png'); ?>" alt="Accreditation" />
        <div>
          <div class="info-label">ACCREDITATION</div>
          <div class="info-value"><?php echo $accreditation; ?></div>
        </div>
      </div>
      <div class="info-item">
        <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/UserCircle.png'); ?>" alt="Sector" />
        <div>
          <div class="info-label">SECTOR</div>
          <div class="info-value"><?php echo $sector_display; ?></div>
        </div>
      </div>
      <div class="info-item">
        <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/Stack.png'); ?>" alt="Experience" />
        <div>
          <div class="info-label">EXPERIENCE</div>
          <div class="info-value"><?php echo $experience; ?></div>
        </div>
      </div>
      <div class="info-item">
        <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/GraduationCap.png'); ?>" alt="Qualification" />
        <div>
          <div class="info-label">QUALIFICATION</div>
          <div class="info-value"><?php echo $qualification; ?></div>
        </div>
      </div>
    </div>
  </div>

  <p class="additional-info"><?php //echo $about; 
                              ?></p>

  <div class="footer-actions"><span>Reach out on LinkedIn</span></div>
  <div class="footer-buttons">
    <?php if ($linkedin): ?>
      <a href="<?php echo $linkedin; ?>" target="_blank">
        <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/Social Media.png'); ?>" alt="LinkedIn" />
      </a>
    <?php else: ?>
      <a href="javascript:void(0);"><img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/Social Media.png'); ?>" alt="LinkedIn" /></a>
    <?php endif; ?>

    <?php if ($cv_url): ?>
      <a href="<?php echo $cv_url; ?>" class="download-btn" download>
        <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/DownloadSimple.png'); ?>" alt="Download" />
        Download CV
      </a>
    <?php endif; ?>
  </div>

<?php
  wp_die();
}
