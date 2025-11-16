<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit;
}

function home_search_widget()
{
  ob_start();

?>

  <style>
    .filter-select,
    .multiselect-wrapper,
    .search-bar {
      overflow: visible !important;
    }

    .filters-row {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
      align-items: center;
    }

    /* Multi-Select Dropdown Styles */
    .filter-select {
      position: relative;
      min-width: 160px;
      /* Reduced for smaller screens */
      flex: 1;
    }

    .multiselect-wrapper {
      position: relative;
      width: 100%;
    }

    .multiselect-trigger {
      height: 48px !important;
      /* Slightly smaller for mobile */
      padding: 0 12px !important;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-radius: 8px !important;
      font-size: 14px;
      /* Adjusted for better scaling */
      background: white;
      border: 1px solid #e5e7eb;
      cursor: pointer;
    }

    .multiselect-trigger:focus {
      outline: none;
      border-color: #2563eb;
    }

    .multiselect-trigger.active {
      background: #00688f !important;
      border-color: #00688f !important;
      color: white !important;
    }

    .multiselect-trigger .selection-text {
      flex: 1;
      text-align: left;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      color: #808184;
      padding-left:24px;
    }

    .multiselect-trigger .selection-count {
      background: rgba(255, 255, 255, 0.2);
      color: white;
      padding: 2px 6px;
      border-radius: 10px;
      font-size: 11px;
      font-weight: 600;
      margin-left: 6px;
      margin-right: 5px;
    }

    .filter-select svg {
      position: absolute;
      left: 10px;
      top: 50%;
      transform: translateY(-50%);
      width: 14px;
      height: 14px;
      color: #64748b;
      pointer-events: none;
      z-index: 1;
    }

    .filter-select.active svg {
      color: white !important;
    }

    .multiselect-trigger::after {
      content: '';
      width: 6px;
      height: 6px;
      border: solid #64748b;
      border-width: 0 2px 2px 0;
      transform: rotate(45deg);
      transition: transform 0.3s ease;
    }

    .multiselect-trigger.active::after {
      border-color: white !important;
      transform: rotate(-135deg);
    }

    .multiselect-dropdown {
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      background: white;
      border: 1px solid #e2e8f0;
      border-top: none;
      border-radius: 0 0 6px 6px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      z-index: 1000;
      display: none;
      max-height: 50vh;
      /* Use viewport height for better scaling */
      overflow-y: auto;
    }

    .multiselect-dropdown.show {
      display: block;
    }

    .multiselect-search {
      padding: 8px;
      border-bottom: 1px solid #e2e8f0;
    }

    .multiselect-search input {
      width: 100%;
      padding: 6px 10px;
      border: 1px solid #e2e8f0;
      border-radius: 4px;
      font-size: 13px;
    }

    .multiselect-options {
      max-height: 40vh;
      /* Adjusted for smaller screens */
      overflow-y: auto;
    }

    .multiselect-option {
      display: flex;
      align-items: center;
      padding: 8px 10px;
      cursor: pointer;
      transition: background-color 0.2s ease;
    }

    .multiselect-option:hover {
      background-color: #f8fafc;
    }

    .multiselect-option input[type="checkbox"] {
      margin-right: 8px;
      width: 14px;
      height: 14px;
    }

    .multiselect-option label {
      flex: 1;
      cursor: pointer;
      font-size: 13px;
      color: #334155;
    }

    .multiselect-actions {
      padding: 8px;
      border-top: 1px solid #e2e8f0;
      display: flex;
      gap: 6px;
    }

    .multiselect-btn {
      flex: 1;
      padding: 6px 10px;
      border: none;
      border-radius: 4px;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .multiselect-btn.reset {
      background: #f1f5f9;
      color: #64748b;
    }

    .multiselect-btn.reset:hover {
      background: #e2e8f0;
    }

    .multiselect-btn.apply {
      background: #00688f;
      color: white;
    }

    .multiselect-btn.apply:hover {
      background: #005a7a;
    }

    .search-bar {
      display: flex !important;
      align-items: center !important;
      gap: 8px !important;
      /* Reduced gap for smaller screens */
      background: transparent !important;
      border-radius: 0 !important;
      box-shadow: none !important;
      padding: 0 !important;
      border: none !important;
      transition: box-shadow 0.2s ease !important;
      width: 100%;
      /* Ensure full width */
      max-width: 1200px;
      /* Prevent excessive stretching on large screens */
      margin: 0 auto;
      /* Center the search bar */
    }

    .search-input-group {
      position: relative !important;
      flex: 1;
      /* Allow inputs to grow */
      min-width: 0;
      /* Prevent overflow */
    }

    .search-input-group:first-child,
    .search-input-group:last-of-type {
      width: 100% !important;
      /* Full width by default */
      max-width: 300px;
      /* Limit max width for larger screens */
    }

    .input-wrapper {
      position: relative !important;
      display: flex !important;
      align-items: center !important;
      background: white !important;
      border-radius: 8px !important;
      border: 1px solid #e5e7eb !important;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
    }

    .input-icon {
      position: absolute !important;
      left: 12px !important;
      z-index: 2 !important;
      color: #9ca3af !important;
      width: 18px !important;
      height: 18px !important;
    }

    .search-input {
      width: 100% !important;
      height: 48px !important;
      /* Slightly smaller for mobile */
      padding: 12px 8px !important;
      padding-left: 40px !important;
      border: none !important;
      background: transparent !important;
      font-size: 14px !important;
      color: #374151 !important;
      outline: none !important;
      border-radius: 8px !important;
      transition: all 0.2s ease !important;
    }

    .search-input::placeholder {
      color: #9ca3af !important;
      font-weight: 400 !important;
    }

    .search-input:focus {
      background-color: #f9fafb !important;
    }

    .search-button {
      background: #00688f !important;
      color: white !important;
      border: 1.5px solid #ffffff !important;
      padding: 12px 16px !important;
      height: 48px !important;
      width: auto !important;
      /* Allow button to size naturally */
      min-width: 100px;
      /* Ensure button isn't too small */
      border-radius: 8px !important;
      font-size: 14px !important;
      font-weight: 600 !important;
      cursor: pointer !important;
      transition: all 0.2s ease !important;
      white-space: nowrap !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
    }

    .search-button:hover {
      background: #005a7a !important;
      border-color: #005a7a !important;
      transform: translateY(-1px) !important;
      box-shadow: 0 4px 12px rgba(0, 104, 143, 0.3) !important;
    }

    .search-button:active {
      transform: translateY(0) !important;
    }

    .input-wrapper:focus-within {
      outline: 2px solid #00688f !important;
      outline-offset: 2px !important;
    }

    .search-button:focus {
      outline: 2px solid #00688f !important;
      outline-offset: 2px !important;
    }

    /* Tablet and smaller screens */
    @media (max-width: 1024px) {
      .search-bar {
        flex-wrap: wrap !important;
        gap: 10px !important;
        padding: 12px !important;
      }

      .search-input-group,
      .search-input-group:first-child,
      .search-input-group:last-of-type {
        width: 100% !important;
        max-width: none !important;
      }

      .filter-select {
        min-width: 100% !important;
      }

      .search-button {
        width: 100% !important;
        min-width: auto !important;
      }
    }

    /* Mobile screens */
    @media (max-width: 768px) {
      .search-bar {
        flex-direction: column !important;
        gap: 8px !important;
        padding: 10px !important;
      }

      .search-input {
        height: 44px !important;
        font-size: 13px !important;
        padding-left: 36px !important;
      }

      .search-button {
        height: 44px !important;
        font-size: 13px !important;
        padding: 10px 14px !important;
      }

      .multiselect-trigger {
        height: 44px !important;
        font-size: 13px !important;
        padding: 0 10px !important;
      }

      .filter-select svg {
        width: 12px;
        height: 12px;
        left: 8px;
      }

      .multiselect-dropdown {
        max-height: 40vh !important;
      }

      .multiselect-options {
        max-height: 30vh !important;
      }
    }
      /* Extra small screens */
      @media (max-width: 480px) {
        body {
          padding: 16px !important;
        }

        .search-bar {
          padding: 8px !important;
          min-width: 361px !important;
        }

        .search-input {
          height: 40px !important;
          font-size: 12px !important;
          padding-left: 34px !important;
        }

        .search-button {
          height: 40px !important;
          font-size: 12px !important;
          padding: 8px 12px !important;
        }

        .multiselect-trigger {
          height: 40px !important;
          font-size: 12px !important;
          padding: 0 8px !important;
        }

        .multiselect-search input {
          font-size: 12px !important;
          padding: 6px 8px !important;
        }

        .multiselect-option {
          padding: 6px 8px !important;
        }

        .multiselect-option label {
          font-size: 12px !important;
        }

        .multiselect-btn {
          font-size: 11px !important;
          padding: 5px 8px !important;
        }
      }






      /* .input-wrapper:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12) !important;
} */

      /* .search-bar:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12) !important;
} */
  </style>
  </head>

  <!-- FORM START -->
  <form class="search-bar" method="get" action="https://aaswjobstaging.wpenginepowered.com/list-job-custom/">

    <div class="search-input-group">
      <div class="input-wrapper">
        <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"></circle>
          <path d="m21 21-4.35-4.35"></path>
        </svg>
        <!-- Name must match query param in URL -->
        <input type="text" name="key" placeholder="Job title, keywords" class="search-input">
      </div>
    </div>

    <!-- Location Multi-Select -->
    <div class="filter-select">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
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
              'taxonomy'   => 'job_location_category',
              'hide_empty' => false,
            ]);

            if (!empty($terms) && !is_wp_error($terms)) {
              foreach ($terms as $term) {
                echo '<div class="multiselect-option">';
                echo '<input type="checkbox" id="location_' . esc_attr($term->slug) . '" value="' . esc_attr($term->slug) . '">';
                echo '<label for="location_' . esc_attr($term->slug) . '">' . esc_html($term->name) . '</label>';
                echo '</div>';
              }
            }
            ?>
          </div>
          <div class="multiselect-actions">
            <button type="button" class="multiselect-btn reset">Reset</button>
            <button type="button" class="multiselect-btn apply">Apply</button>
          </div>
        </div>
      </div>
    </div>

    <button type="submit" class="search-button">
      Search
    </button>
  </form>
  <!-- FORM END -->

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

      //alert('a');
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

            case 'job_location_category':
              defaultText = 'All Locations';
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

      // On form submit, collect selected locations
      $('.search-bar').on('submit', function(e) {
        const form = $(this);

        // Remove any previous hidden input
        form.find('input[name="location"]').remove();

        // Get only the checkboxes inside the location multiselect
        const locations = [];
        $('.multiselect-wrapper .multiselect-trigger[data-target="job_location_category"]')
          .siblings('.multiselect-dropdown')
          .find('input[type="checkbox"]:checked')
          .each(function() {
            locations.push($(this).val());
          });

        if (locations.length > 0) {
          const locationValue = locations.join(',');
          form.append('<input type="hidden" name="location" value="' + locationValue + '">');
        }
      });

    });
  </script>

<?php
  return ob_get_clean();
}
add_shortcode('home_search_widget', 'home_search_widget');

?>