<?php

// 1. Enqueue scripts and styles only when the shortcode is present
function interactive_job_map_enqueue_assets() {
    // This check ensures assets are only loaded on pages/posts containing the shortcode
    if ( is_singular() && has_shortcode( get_queried_object()->post_content, 'interactive_job_map' ) ) {

        // Enqueue Stylesheets
        wp_enqueue_style('google-fonts-nunito', 'https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;500;600;700&display=swap', array(), null);
        wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet/dist/leaflet.css', array(), '1.9.4');

        // Enqueue Scripts
        wp_enqueue_script('tailwindcss', 'https://cdn.tailwindcss.com', array(), '3.4.1', false);
        wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet/dist/leaflet.js', array(), '1.9.4', true);

        // === START: SCOPED CUSTOM CSS TO PREVENT CONFLICTS ===
        $custom_css = "
            #gemini-interactive-map-container {
                font-family: 'Nunito Sans', sans-serif;
            }
            #gemini-interactive-map-container .state-count-marker {
                background-clip: padding-box;
                border-radius: 50%;
                background-color: #c03b55; /* AASW Red */
                color: white;
                font-weight: bold;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 16px;
                border: 2px solid white;
                box-shadow: 0 0 5px rgba(0,0,0,0.4);
                cursor: pointer;
                z-index: 1000; /* Ensures circle is on top */
            }
            #gemini-interactive-map-container .state-count-marker:hover {
                background-color: #00688F; /* AASW Blue on hover */
            }
            #gemini-interactive-map-container .leaflet-popup-content-wrapper {
                border-radius: 8px;
            }
            #gemini-interactive-map-container #map-list::-webkit-scrollbar { display: none; }
            #gemini-interactive-map-container #map-list { -ms-overflow-style: none; scrollbar-width: none; }
            
            #gemini-interactive-map-container .state-label {
                font-size: 10px;
                font-weight: normal;
                color: #555;
                text-shadow: -1px -1px 0 #FFF, 1px -1px 0 #FFF, -1px 1px 0 #FFF, 1px 1px 0 #FFF;
                background: transparent;
                border: none;
                white-space: nowrap;
                z-index: 1; /* Ensures label is behind circle */
            }
            /* Custom Dropdown Styles */
            .custom-select-button.selected-state {
                background-color: #00688F;
                color: white;
                border-color: #00688F;
            }
            .custom-select-button.all-locations {
                 background-color: white;
                color: #00688F;
                border: 1.5px solid #00688F;
            }
             .custom-select-button.all-locations .arrow-down {
                color: #00688F;
            }
        ";
        // === END: SCOPED CUSTOM CSS ===
        wp_add_inline_style('leaflet-css', $custom_css);
    }
}
add_action('wp_enqueue_scripts', 'interactive_job_map_enqueue_assets');


// Helper function to extract the primary State from location string
function gemini_get_primary_state_from_string($job_location_string) {
    if (empty($job_location_string)) return null;
    $states = [
        'Queensland'        => ['QLD', 'Queensland'],
        'New South Wales'   => ['NSW', 'New South Wales'],
        'Victoria'          => ['VIC', 'Victoria'],
        'Western Australia' => ['WA', 'Western Australia', 'wa-perth'],
        'South Australia'   => ['SA', 'South Australia'],
        'Tasmania'          => ['TAS', 'Tasmania'],
        'Northern Territory'=> ['NT', 'Northern Territory'],
        'Australian Capital Territory' => ['ACT', 'Australian Capital Territory']
    ];
    foreach ($states as $state_name => $abbreviations) {
        foreach ($abbreviations as $abbr) {
            if (preg_match('/\b' . preg_quote($abbr, '/') . '\b/i', $job_location_string)) {
                return $state_name;
            }
        }
    }
    return null;
}


// The function that generates the HTML and JS for the map
function interactive_job_map_shortcode_function() {

    // 1. Define static data & fetch dynamic taxonomy terms
    $state_coords = [
        'Queensland'        => ['center' => [-20.9176, 142.7028], 'zoom' => 5],
        'New South Wales'   => ['center' => [-31.2532, 146.9211], 'zoom' => 5],
        'Victoria'          => ['center' => [-37.4713, 144.7852], 'zoom' => 6],
        'Western Australia' => ['center' => [-27.6728, 121.6283], 'zoom' => 4],
        'South Australia'   => ['center' => [-30.0002, 136.2092], 'zoom' => 5],
        'Tasmania'          => ['center' => [-41.4545, 145.9707], 'zoom' => 6],
        'Northern Territory'=> ['center' => [-19.4914, 132.5509], 'zoom' => 5],
        'Australian Capital Territory' => ['center' => [-35.4735, 149.0124], 'zoom' => 7]
    ];
    $all_states_terms = get_terms(['taxonomy' => 'job_location_category', 'hide_empty' => false]);
    $js_city_data = ['all' => ['center' => [-25.2744, 133.7751], 'zoom' => 5, 'name' => 'All Locations']];

    if (!is_wp_error($all_states_terms) && !empty($all_states_terms)) {
        foreach ($all_states_terms as $term) {
            $coords = isset($state_coords[$term->name]) ? $state_coords[$term->name] : ['center' => [-25.2744, 133.7751], 'zoom' => 4];
            $js_city_data[$term->slug] = [
                'center' => $coords['center'],
                'zoom'   => $coords['zoom'],
                'name'   => $term->name
            ];
        }
    }

    // 2. Fetch Job Data & Aggregate Counts
    $jobs_data = [];
    $state_job_counts = [];
    $placeholder_logo = 'https://i.ibb.co/L8g28t5/rfds-logo-placeholder.png';
    $job_listings = new WP_Query(['post_type' => 'job_listing', 'post_status' => 'publish', 'posts_per_page' => -1]);

    if ($job_listings->have_posts()) {
        while ($job_listings->have_posts()) {
            $job_listings->the_post();
            global $post;
            $job_id = get_the_ID();
            $full_location_string = get_post_meta($job_id, '_job_location', true);
            $location_terms = wp_get_post_terms($job_id, 'job_location_category');
            
            $job_states = [];
            if (!is_wp_error($location_terms) && !empty($location_terms)) {
                $job_states = wp_list_pluck($location_terms, 'name');
            }
            
            $location_display_text = !empty($job_states) ? implode(', ', $job_states) : $full_location_string;

            if (!empty($job_states)) {
                foreach ($job_states as $state_name) {
                    if (!isset($state_job_counts[$state_name])) {
                        $state_job_counts[$state_name] = 0;
                    }
                    $state_job_counts[$state_name]++;
                }
            }

            $company_logo_url = get_the_company_logo($post, 'thumbnail');
            
            $jobs_data[] = [
                'id'            => $job_id,
                'title'         => get_the_title(),
                'location'      => $location_display_text,
                'company'       => get_the_company_name(),
                'states'        => $job_states,
                'logoUrl'       => !empty($company_logo_url) ? $company_logo_url : $placeholder_logo,
                'permalink'     => get_permalink($job_id)
            ];
        }
        wp_reset_postdata();
    }

    ob_start();
    ?>
    <div id="gemini-interactive-map-container">
        <style>
            #gemini-interactive-map-container #map-list-container::after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 0;
                width: 100%;
                height: 120px;
                background: linear-gradient(to top, rgb(255 255 255), rgba(255, 255, 255, 0));
                pointer-events: none;
                border-bottom-left-radius: 1rem;
                border-bottom-right-radius: 1rem;
            }
        </style>
        
        <div class="w-full mx-auto px-[21px] py-4 md:py-6 text-gray-800">
            <header class="mb-4 flex md:hidden justify-between items-center">
                 <div class="flex items-center bg-gray-100 rounded-full p-1 text-sm font-semibold shadow-inner">
                    <button id="mobile-list-btn" class="px-5 py-2 rounded-full focus:outline-none transition-colors flex items-center gap-2 border-0">
                         <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.5 6C6.5 6.39782 6.34196 6.77936 6.06066 7.06066C5.77936 7.34196 5.39782 7.5 5 7.5C4.60218 7.5 4.22064 7.34196 3.93934 7.06066C3.65804 6.77936 3.5 6.39782 3.5 6C3.5 5.60218 3.65804 5.22064 3.93934 4.93934C4.22064 4.65804 4.60218 4.5 5 4.5C5.39782 4.5 5.77936 4.65804 6.06066 4.93934C6.34196 5.22064 6.5 5.60218 6.5 6ZM6.5 10C6.5 10.3978 6.34196 10.7794 6.06066 11.0607C5.77936 11.342 5.39782 11.5 5 11.5C4.60218 11.5 4.22064 11.342 3.93934 11.0607C3.65804 10.7794 3.5 10.3978 3.5 10C3.5 9.60218 3.65804 9.22064 3.93934 8.93934C4.22064 8.65804 4.60218 8.5 5 8.5C5.39782 8.5 5.77936 8.65804 6.06066 8.93934C6.34196 9.22064 6.5 9.60218 6.5 10ZM6.5 14C6.5 14.3978 6.34196 14.7794 6.06066 15.0607C5.77936 15.342 5.39782 15.5 5 15.5C4.60218 15.5 4.22064 15.342 3.93934 15.0607C3.65804 14.7794 3.5 14.3978 3.5 14C3.5 13.6022 3.65804 13.2206 3.93934 12.9393C4.22064 12.658 4.60218 12.5 5 12.5C5.39782 12.5 5.77936 12.658 6.06066 12.9393C6.34196 13.2206 6.5 13.6022 6.5 14Z" fill="currentColor"/><path fill-rule="evenodd" clip-rule="evenodd" d="M7.5 6.5C7.5 6.36739 7.55268 6.24021 7.64645 6.14645C7.74021 6.05268 7.86739 6 8 6H16C16.1326 6 16.2598 6.05268 16.3536 6.14645C16.4473 6.24021 16.5 6.36739 16.5 6.5C16.5 6.63261 16.4473 6.75979 16.3536 6.85355C16.2598 6.94732 16.1326 7 16 7H8C7.86739 7 7.74021 6.94732 7.64645 6.85355C7.55268 6.75979 7.5 6.63261 7.5 6.5ZM7.5 10.5C7.5 10.3674 7.55268 10.2402 7.64645 10.1464C7.74021 10.0527 7.86739 10 8 10H16C16.1326 10 16.2598 10.0527 16.3536 10.1464C16.4473 10.2402 16.5 10.3674 16.5 10.5C16.5 10.6326 16.4473 10.7598 16.3536 10.8536C16.2598 10.9473 16.1326 11 16 11H8C7.86739 11 7.74021 10.9473 7.64645 10.8536C7.55268 10.7598 7.5 10.6326 7.5 10.5ZM7.5 14.5C7.5 14.3674 7.55268 14.2402 7.64645 14.1464C7.74021 14.0527 7.86739 14 8 14H16C16.1326 14 16.2598 14.0527 16.3536 14.1464C16.4473 14.2402 16.5 14.3674 16.5 14.5C16.5 14.6326 16.4473 14.7598 16.3536 14.8536C16.2598 14.9473 16.1326 15 16 15H8C7.86739 15 7.74021 14.9473 7.64645 14.8536C7.55268 14.7598 7.5 14.6326 7.5 14.5Z" fill="currentColor"/></svg>
                        List
                    </button>
                    <button id="mobile-map-btn" class="px-5 py-2 rounded-full bg-[#A32441] text-white focus:outline-none transition-colors flex items-center gap-2 border-0">
                       <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.5 5.5325V17.0833M12.5 2.5V14.1667M2.5 7.25833C2.5 6.03 2.5 5.41667 2.8275 5.05833C2.94341 4.93086 3.08441 4.82872 3.24167 4.75833C3.685 4.56167 4.2675 4.75583 5.4325 5.14417C6.32167 5.44083 6.76583 5.58917 7.21583 5.57333C7.38091 5.56791 7.54505 5.54613 7.70583 5.50833C8.14333 5.40417 8.53333 5.14417 9.31333 4.625L10.465 3.85667C11.465 3.19 11.9642 2.85667 12.5375 2.78083C13.1108 2.70333 13.6808 2.89333 14.82 3.27333L15.7908 3.59667C16.6158 3.87167 17.0283 4.00917 17.2642 4.33667C17.5 4.66417 17.5 5.1 17.5 5.96833V12.7425C17.5 13.97 17.5 14.5842 17.1725 14.9425C17.0564 15.0694 16.9154 15.1709 16.7583 15.2408C16.315 15.4383 15.7325 15.2442 14.5675 14.8558C13.6783 14.5592 13.2342 14.4108 12.7842 14.4267C12.6191 14.4321 12.455 14.4539 12.2942 14.4917C11.8567 14.5958 11.4667 14.8558 10.6867 15.375L9.535 16.1433C8.535 16.81 8.03583 17.1433 7.4625 17.2192C6.88917 17.2967 6.31917 17.1067 5.18 16.7267L4.20917 16.4033C3.38417 16.1283 2.97167 15.9908 2.73583 15.6633C2.5 15.3358 2.5 14.9 2.5 14.0317V7.25833Z" stroke="currentColor" stroke-width="1.5"/></svg>
                        Map
                    </button>
                </div>
                <div>
                    <button id="mobile-filter-btn" class="p-2 border-0 text-gray-600 focus:outline-none focus:ring-0 active:bg-transparent focus:bg-transparent">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M21.25 11.9999H8.895M4.534 11.9999H2.75M4.534 11.9999C4.534 11.4217 4.76368 10.8672 5.17251 10.4584C5.58134 10.0496 6.13583 9.81989 6.714 9.81989C7.29217 9.81989 7.84666 10.0496 8.25549 10.4584C8.66432 10.8672 8.894 11.4217 8.894 11.9999C8.894 12.5781 8.66432 13.1326 8.25549 13.5414C7.84666 13.9502 7.29217 14.1799 6.714 14.1799C6.13583 14.1799 5.58134 13.9502 5.17251 13.5414C4.76368 13.1326 4.534 12.5781 4.534 11.9999ZM21.25 18.6069H15.502M15.502 18.6069C15.502 19.1852 15.2718 19.7403 14.8628 20.1492C14.4539 20.5582 13.8993 20.7879 13.321 20.7879C12.7428 20.7879 12.1883 20.5572 11.7795 20.1484C11.3707 19.7396 11.141 19.1851 11.141 18.6069M15.502 18.6069C15.502 18.0286 15.2718 17.4745 14.8628 17.0655C14.4539 16.6566 13.8993 16.4269 13.321 16.4269C12.7428 16.4269 12.1883 16.6566 11.7795 17.0654C11.3707 17.4742 11.141 18.0287 11.141 18.6069M11.141 18.6069H2.75M21.25 5.39289H18.145M13.784 5.39289H2.75M13.784 5.39289C13.784 4.81472 14.0137 4.26023 14.4225 3.8514C14.8313 3.44257 15.3858 3.21289 15.964 3.21289C16.2503 3.21289 16.5338 3.26928 16.7983 3.37883C17.0627 3.48839 17.3031 3.64897 17.5055 3.8514C17.7079 4.05383 17.8685 4.29415 17.9781 4.55864C18.0876 4.82313 18.144 5.10661 18.144 5.39289C18.144 5.67917 18.0876 5.96265 17.9781 6.22714C17.8685 6.49163 17.7079 6.73195 17.5055 6.93438C17.3031 7.13681 17.0627 7.29739 16.7983 7.40695C16.5338 7.5165 16.2503 7.57289 15.964 7.57289C15.3858 7.57289 14.8313 7.34321 14.4225 6.93438C14.0137 6.52555 13.784 5.97106 13.784 5.39289Z" stroke="#00688F" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"/></svg>
                    </button>
                </div>
            </header>
            
            <header class="mb-6 hidden md:flex flex-wrap gap-4 items-center">
                <div id="desktop-custom-select" class="relative">
                    <select id="city-select" class="hidden">
                        <option value="all">All Location</option>
                        <?php if (!is_wp_error($all_states_terms) && !empty($all_states_terms)) : ?>
                            <?php foreach ($all_states_terms as $term) : ?>
                                <?php if (in_array($term->name, ['International', 'Hybrid/Remote'])) { continue; } ?>
                                <option value="<?php echo esc_attr($term->slug); ?>"><?php echo esc_html($term->name); ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <button class="custom-select-button all-locations font-semibold rounded-full py-2 pl-5 pr-10 cursor-pointer shadow-sm flex items-center justify-between w-48 transition-colors duration-200">
                        <span class="truncate">All Location</span>
                        <svg class="arrow-down fill-current h-4 w-4 text-white absolute right-3 transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                    </button>
                    <div class="custom-select-panel hidden absolute z-[1100] mt-1 w-48 bg-white rounded-lg shadow-lg overflow-hidden">
                        <div data-value="all" class="custom-select-option cursor-pointer px-4 py-2 hover:bg-[#00688F] hover:text-white">All Location</div>
                         <?php if (!is_wp_error($all_states_terms) && !empty($all_states_terms)) : ?>
                            <?php foreach ($all_states_terms as $term) : ?>
                                <?php if (in_array($term->name, ['International', 'Hybrid/Remote'])) { continue; } ?>
                                <div data-value="<?php echo esc_attr($term->slug); ?>" class="custom-select-option cursor-pointer px-4 py-2 hover:bg-[#00688F] hover:text-white"><?php echo esc_html($term->name); ?></div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </header>
            
            <main class="relative">
                <div class="grid grid-cols-1 md:grid-cols-[3fr_2fr] md:gap-[18px] lg:grid-cols-[1fr_auto] lg:gap-[55px]">
                    <div id="map-view-wrapper" class="flex flex-col items-center gap-4 md:block">
                        <div id="map-container" class="bg-white rounded-3xl shadow-lg p-1 w-full max-w-[335px] h-[320px] md:max-w-none md:w-full md:h-[420px] lg:h-[604px] border-4 border-gray-50">
                            <div id="map" class="h-full w-full rounded-3xl"></div>
                        </div>
                        <a href="https://aaswjobstaging.wpenginepowered.com/list-job-custom/" class="view-all-link md:hidden flex items-center justify-center w-4/5 max-w-[200px] h-[50px] bg-[#00688F] text-white font-bold rounded-lg hover:bg-[#005775] transition-colors gap-4 px-6 shadow-lg">
                            <span>View All</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </a>
                    </div>

                    <div id="map-list-container" class="hidden md:flex relative w-full h-[512px] mx-auto md:w-full lg:w-[395px] md:h-[420px] lg:h-[604px] bg-white rounded-2xl shadow-lg overflow-hidden flex-col" style="border: 0.5px solid #808184;">
                        <div id="map-list-header" class="bg-[#00688F] text-white p-4 flex justify-between items-center flex-shrink-0">
                            <h3 id="map-list-title" class="font-bold text-lg md:text-[18px] lg:text-[22px]">All Locations</h3>
                            <span id="job-count" class="bg-[#007a9f] text-white text-xs font-semibold rounded-full px-3 py-1"></span>
                        </div>
                        <div id="map-list" class="overflow-y-auto flex-grow p-4 pb-16 flex flex-col gap-4">
                        </div>
                        <a href="https://aaswjobstaging.wpenginepowered.com/list-job-custom/" class="view-all-link absolute z-10 bottom-4 left-1/2 -translate-x-1/2 w-[163px] h-[40px] bg-[#00688F] text-white font-bold rounded-lg hover:bg-[#005775] hover:text-white transition-colors flex items-center justify-between px-4 shadow-lg focus:outline-none focus:ring-0">
                            <span>View All</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </a>
                    </div>
                </div>
            </main>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const jobs = <?php echo wp_json_encode($jobs_data); ?>;
            const stateJobCounts = <?php echo wp_json_encode($state_job_counts); ?>;
            const cityData = <?php echo wp_json_encode($js_city_data); ?>;
            
            const map = L.map('map', { zoomControl: false, scrollWheelZoom: false, dragging: false, touchZoom: false, doubleClickZoom: false, boxZoom: false, keyboard: false, attributionControl: false });
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { subdomains: 'abcd', maxZoom: 19 }).addTo(map);
            
            const stateCountMarkers = L.layerGroup().addTo(map);
            const citySelect = document.getElementById('city-select');
            const jobList = document.getElementById('map-list');
            const jobListTitle = document.getElementById('map-list-title');
            const jobCount = document.getElementById('job-count');
            const desktopDropdownButton = document.querySelector('#desktop-custom-select .custom-select-button');
            const desktopDropdownText = desktopDropdownButton.querySelector('span');
            
            const viewAllButtons = document.querySelectorAll('.view-all-link');
            const baseJobListUrl = 'https://aaswjobstaging.wpenginepowered.com/list-job-custom/';

            function updateViewAllLinks() {
                const selectedLocation = citySelect.value;
                let newUrl = `${baseJobListUrl}?key=`; // Default to empty key
                if (selectedLocation && selectedLocation !== 'all') {
                    // *** CHANGE: Correctly format the URL as requested ***
                    newUrl = `${baseJobListUrl}?key=&location=${selectedLocation}`;
                }
                viewAllButtons.forEach(button => { button.href = newUrl; });
            }
            
            const mobileListBtn = document.getElementById('mobile-list-btn');
            const mobileMapBtn = document.getElementById('mobile-map-btn');
            const mapViewWrapper = document.getElementById('map-view-wrapper');
            const mapListContainer = document.getElementById('map-list-container');
            function showListView() { mapViewWrapper.classList.add('hidden'); mapListContainer.classList.remove('hidden'); mapListContainer.classList.add('flex'); mobileListBtn.classList.add('bg-[#A32441]', 'text-white'); mobileMapBtn.classList.remove('bg-[#A32441]', 'text-white'); }
            function showMapView() { mapViewWrapper.classList.remove('hidden'); mapListContainer.classList.add('hidden'); mobileMapBtn.classList.add('bg-[#A32441]', 'text-white'); mobileListBtn.classList.remove('bg-[#A32441]', 'text-white'); setTimeout(() => map.invalidateSize(), 10); }
            mobileListBtn.addEventListener('click', showListView);
            mobileMapBtn.addEventListener('click', showMapView);

            function updateDropdownUI(value, text) {
                desktopDropdownText.textContent = text;
                if (value === 'all') {
                    desktopDropdownButton.classList.remove('selected-state');
                    desktopDropdownButton.classList.add('all-locations');
                } else {
                    desktopDropdownButton.classList.remove('all-locations');
                    desktopDropdownButton.classList.add('selected-state');
                }
            }
            
            function renderJobList(jobsToDisplay) {
                jobList.innerHTML = ''; 
                if (jobsToDisplay.length === 0) {
                    jobList.innerHTML = '<p class="text-gray-500 text-center p-4">No jobs found in this area.</p>';
                    jobCount.textContent = '0 jobs';
                    return;
                }
                jobsToDisplay.forEach(job => {
                    const redirectUrl = `${baseJobListUrl}?job_id=${job.id}`;
                    const truncatedTitle = job.title.length > 60 ? job.title.substring(0, 60) + '...' : job.title;
                    const truncatedLocation = job.location.length > 25 ? job.location.substring(0, 25) + '...' : job.location;
                    const jobCard = `<div class="map-card w-full h-[100px] flex items-center gap-3 p-3 rounded-[10px] shadow-[0_0.5px_2px_rgba(0,0,0,0.25)]" data-id="${job.id}" style="cursor: pointer;"><div class="flex-shrink-0 w-16 h-16 border border-gray-200 bg-white rounded-md flex items-center justify-center p-1"><img src="${job.logoUrl}" alt="${job.company} logo" class="object-contain h-full w-full"></div><div class="flex-grow min-w-0"><h4 class="font-semibold text-[14px] md:text-[15px] text-gray-800 leading-tight">${truncatedTitle}</h4><p class="font-semibold text-[10px] md:text-[11px] text-gray-600 mt-1">${job.company}</p><div class="flex flex-wrap justify-between items-end gap-x-2 mt-2"><p class="flex items-start gap-1.5 text-[10px] md:text-[11px] font-normal text-[#00688F]"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor" class="flex-shrink-0 mt-0.5"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" /></svg><span>${truncatedLocation}</span></p><a href="${redirectUrl}" class="text-[12px] md:text-[12px] font-normal text-[#00688F] underline hover:no-underline flex-shrink-0">Read More</a></div></div></div>`;
                    jobList.innerHTML += jobCard;
                });
                jobCount.textContent = `${jobsToDisplay.length} jobs`;
                document.querySelectorAll('.map-card').forEach(card => {
                    card.addEventListener('click', (event) => {
                        const jobId = card.dataset.id;
                        if (event.target.tagName.toLowerCase() === 'a') {
                            event.preventDefault(); 
                        }
                        window.location.href = `${baseJobListUrl}?job_id=${jobId}`;
                    });
                });
            }

            function renderStateCountsOnMap() {
                stateCountMarkers.clearLayers();
                for (const stateName in stateJobCounts) {
                    if (stateName === 'International' || stateName === 'Hybrid/Remote') { continue; }
                    const count = stateJobCounts[stateName];
                    if (count > 0) {
                        const stateSlug = Object.keys(cityData).find(key => cityData[key].name === stateName);
                        if (stateSlug && cityData[stateSlug]) {
                            const stateInfo = cityData[stateSlug];
                            const size = count < 100 ? 35 : 45;
                            const countIcon = L.divIcon({
                                html: `<span>${count}</span>`,
                                className: 'state-count-marker',
                                iconSize: [size, size]
                            });
                            const marker = L.marker(stateInfo.center, { icon: countIcon, stateSlug: stateSlug });
                            marker.on('click', (e) => {
                                const clickedStateSlug = e.target.options.stateSlug;
                                citySelect.value = clickedStateSlug;
                                citySelect.dispatchEvent(new Event('change', { bubbles: true }));
                                if (window.innerWidth < 768) {
                                    showListView();
                                }
                            });
                            stateCountMarkers.addLayer(marker);
                        }
                    }
                }
            }

            citySelect.addEventListener('change', (e) => {
                const selectedValue = e.target.value;
                const cityInfo = cityData[selectedValue];
                if (cityInfo) {
                    jobListTitle.textContent = cityInfo.name;
                    const filteredJobs = (selectedValue === 'all') 
                        ? jobs 
                        : jobs.filter(job => job.states.includes(cityInfo.name));
                    renderJobList(filteredJobs);
                    updateDropdownUI(selectedValue, cityInfo.name);
                    updateViewAllLinks();
                }
            });
            
            function initializeApp() {
                citySelect.value = 'all';
                jobListTitle.textContent = cityData['all'].name;
                renderJobList(jobs);
                renderStateCountsOnMap();
                updateViewAllLinks();
                const australiaBounds = [[-43.7, 113.0], [-10.0, 153.6]];
                map.fitBounds(australiaBounds);
                const stateLabels = [ { name: 'WESTERN AUSTRALIA', lat: -25.5, lng: 122.5 }, { name: 'NORTHERN TERRITORY', lat: -19.5, lng: 133.5 }, { name: 'SOUTH AUSTRALIA', lat: -30.0, lng: 135.5 }, { name: 'QUEENSLAND', lat: -22.0, lng: 145.0 }, { name: 'NEW SOUTH WALES', lat: -33.0, lng: 147.0 }, { name: 'VICTORIA', lat: -36.8, lng: 144.5 }, { name: 'TASMANIA', lat: -42.2, lng: 146.7 } ];
                stateLabels.forEach(state => {
                    const labelIcon = L.divIcon({ className: 'state-label', html: `<span>${state.name}</span>`, iconSize: [150, 20], iconAnchor: [75, 10] });
                    L.marker([state.lat, state.lng], { icon: labelIcon, interactive: false }).addTo(map);
                });
                setTimeout(() => { map.invalidateSize(); map.fitBounds(australiaBounds); }, 250); 
                if (window.innerWidth < 768) { showMapView(); }
            }
            initializeApp();
        });
        </script>
    </div>
    
    <div id="mobile-city-popup" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[9999] flex items-center justify-center">
      <div class="bg-white rounded-2xl p-5 w-11/12 max-w-sm shadow-2xl">
        <h2 class="text-lg font-semibold mb-3 text-center text-[#00688F]">Select a Location</h2>
        <select id="mobile-city-select" class="hidden">
            <option value="all">All Location</option>
            <?php if (!is_wp_error($all_states_terms) && !empty($all_states_terms)) : ?>
                <?php foreach ($all_states_terms as $term) : ?>
                    <?php if (in_array($term->name, ['International', 'Hybrid/Remote'])) { continue; } ?>
                    <option value="<?php echo esc_attr($term->slug); ?>"><?php echo esc_html($term->name); ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
        <div id="mobile-custom-select" class="relative w-full">
             <button class="custom-select-button all-locations font-semibold rounded-full py-2 pl-5 pr-10 cursor-pointer shadow-sm flex items-center justify-between w-full transition-colors duration-200">
                <span class="truncate">All Location</span>
                <svg class="arrow-down fill-current h-4 w-4 text-white absolute right-3 transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
            </button>
            <div class="custom-select-panel hidden absolute z-[1100] mt-1 w-full bg-white rounded-lg shadow-lg overflow-hidden">
                <div data-value="all" class="custom-select-option cursor-pointer px-4 py-2 hover:bg-[#00688F] hover:text-white">All Location</div>
                <?php if (!is_wp_error($all_states_terms) && !empty($all_states_terms)) : ?>
                    <?php foreach ($all_states_terms as $term) : ?>
                        <?php if (in_array($term->name, ['International', 'Hybrid/Remote'])) { continue; } ?>
                        <div data-value="<?php echo esc_attr($term->slug); ?>" class="custom-select-option cursor-pointer px-4 py-2 hover:bg-[#00688F] hover:text-white"><?php echo esc_html($term->name); ?></div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <button id="close-popup" class="mt-4 w-full bg-gray-200 text-gray-700 rounded-full py-2 font-semibold hover:bg-gray-300">Close</button>
      </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        function setupCustomSelect(containerId) {
            const container = document.getElementById(containerId);
            if (!container) return;
            const button = container.querySelector('.custom-select-button');
            const panel = container.querySelector('.custom-select-panel');
            const options = container.querySelectorAll('.custom-select-option');
            const hiddenSelectId = (containerId === 'desktop-custom-select') ? 'city-select' : 'mobile-city-select';
            const hiddenSelect = document.getElementById(hiddenSelectId);
            const buttonText = button.querySelector('span');
            const arrow = button.querySelector('.arrow-down');
            button.addEventListener('click', (e) => {
                e.stopPropagation();
                panel.classList.toggle('hidden');
                arrow.classList.toggle('rotate-180');
            });
            options.forEach(option => {
                option.addEventListener('click', () => {
                    const value = option.getAttribute('data-value');
                    buttonText.textContent = option.textContent;
                    hiddenSelect.value = value;
                    if (value === 'all') {
                        button.classList.remove('selected-state');
                        button.classList.add('all-locations');
                    } else {
                        button.classList.remove('all-locations');
                        button.classList.add('selected-state');
                    }
                    hiddenSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    panel.classList.add('hidden');
                    arrow.classList.remove('rotate-180');
                });
            });
        }
        setupCustomSelect('desktop-custom-select');
        setupCustomSelect('mobile-custom-select');

        window.addEventListener('click', () => {
            document.querySelectorAll('.custom-select-panel').forEach(p => p.classList.add('hidden'));
            document.querySelectorAll('.arrow-down').forEach(a => a.classList.remove('rotate-180'));
        });
        
        const openBtn = document.getElementById('mobile-filter-btn');
        const popup = document.getElementById('mobile-city-popup');
        const closeBtn = document.getElementById('close-popup');
        const mobileHiddenSelect = document.getElementById('mobile-city-select');
        const desktopHiddenSelect = document.getElementById('city-select');

        if (openBtn && popup) {
            openBtn.addEventListener('click', () => popup.classList.remove('hidden'));
            closeBtn.addEventListener('click', () => popup.classList.add('hidden'));

            mobileHiddenSelect.addEventListener('change', () => {
                desktopHiddenSelect.value = mobileHiddenSelect.value;
                desktopHiddenSelect.dispatchEvent(new Event('change', { bubbles: true }));
                popup.classList.add('hidden');
            });
        }
    });
    </script>
    <?php
    return ob_get_clean();
}

add_shortcode('interactive_job_map', 'interactive_job_map_shortcode_function');