<?php
/*
Plugin Name: Rooms for Hire Shortcode
Description: A custom shortcode to display "Rooms for Hire" posts with a specific design.
Version: 2.7
Author: Gemini
*/

// Enqueue Font Awesome for icons. The design uses icons for mail and phone.
function enqueue_rooms_styles() {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css');
    wp_enqueue_style('nunito-sans', 'https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;500;600;700&display=swap');
}
add_action('wp_enqueue_scripts', 'enqueue_rooms_styles');


// The main shortcode function
function all_rooms_for_hire_shortcode() {

    $post_type_slug = 'room-for-hire';
    $location_field = 'state'; 
    $dimensions_field = 'area'; 
    $listed_by_field = 'listed_by';
    $start_date_field = 'start_date'; 
    $contact_email_field = 'contact_email'; 
    $contact_phone_field = 'contact_phone'; 
    $location_icon_url = 'https://aaswjobstaging.wpenginepowered.com/wp-content/uploads/2025/10/location-icon.png';
    $dimensions_icon_url = 'https://aaswjobstaging.wpenginepowered.com/wp-content/uploads/2025/10/area-icon.png';
    // --- END PNG ICONS ---

    // --- State Slug to Name Mapping ---
    $state_map = [
        'NSW' => 'New South Wales',
        'QLD' => 'Queensland',
        'VIC' => 'Victoria',
        'SA'  => 'South Australia',
        'WA'  => 'Western Australia',
        'TAS' => 'Tasmania',
        'NT'  => 'Northern Territory',
        'ACT' => 'Australian Capital Territory',
    ];
    asort($state_map); // Sort alphabetically
    // --- End Mapping ---

    ob_start(); // Start output buffering to capture the HTML

    // --- Sorting Logic ---
    $sort_by = isset($_GET['sortby']) ? sanitize_text_field($_GET['sortby']) : 'newest';
    $orderby_args = array(
        'orderby' => 'date', 
        'order'   => 'DESC' 
    );
    if ($sort_by === 'oldest') {
        $orderby_args['order'] = 'ASC';
    }
    // --- End Sorting Logic ---

    // --- State Filtering Logic ---
    $filter_state = isset($_GET['filter_state']) ? sanitize_text_field($_GET['filter_state']) : '';
    
    $meta_query_args = array(); 
    
    if (!empty($filter_state)) {
        $meta_query_args[] = array(
            'key'     => $location_field,
            'value'   => $filter_state,
            'compare' => '=',
        );
    }
    // --- END: State Filtering Logic ---


    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    $args = array(
        'post_type' => $post_type_slug,
        'posts_per_page' => 5,
        'paged' => $paged,
        'post_status' => 'publish',
        'orderby' => $orderby_args['orderby'],
        'order'   => $orderby_args['order'],
        'meta_query' => $meta_query_args
    );
    $rooms_query = new WP_Query($args);
    ?>

    <style>
        .rooms-for-hire-container {
            font-family: 'Nunito Sans', sans-serif;
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            color: #333;
            /* --- FIX: Was 180%, which caused cropping/horizontal scroll. Set to 100%. --- */
            width: 100%; 
            box-sizing: border-box; 
        }

        /* --- MODIFIED: Set to 'nowrap' and 'baseline' --- */
        .rooms-header {
            display: flex;

            align-items: baseline;
            margin-bottom: 30px;
            flex-wrap: nowrap; /* Prevents wrapping on desktop */
            gap: 20px;
        }

        .rooms-header .header-text h2 {
            font-size: 24px;
            margin: 0;
            color: #111;
            font-weight: 600;
        }
        
        .rooms-header .header-text p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 14px;
        }

        /* --- MODIFIED: Removed flex-shrink --- */
        .room-filters-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-left: auto; /* <-- ADD THIS LINE */
        }

        .sort-by-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sort-by-wrapper label {
             font-size: 14px;
             color: #555;
             flex-shrink: 0;
        }
        
        /* --- MODIFIED: Width changed to 240px --- */
        .custom-sort-dropdown {
            position: relative;
            width: 240px;
            height: 35px;
            font-size: 14px;
            color: #333;
        }

        .custom-sort-dropdown .sort-selected {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            height: 100%;
            padding: 0 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fff;
            cursor: pointer;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }
        
        .custom-sort-dropdown .sort-selected.open,
        .custom-sort-dropdown .sort-selected:hover {
            border-color: #00688F;
            color: #00688F;
        }

        .custom-sort-dropdown .sort-selected .arrow {
            transition: transform 0.3s ease;
            color: #00688F;
        }

        .custom-sort-dropdown .sort-selected.open .arrow {
            transform: rotate(180deg);
        }

        .custom-sort-dropdown .sort-options {
            position: absolute;
            top: 110%;
            left: 0;
            right: 0;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            z-index: 100;
            overflow: hidden;
            display: none; 
            max-height: 300px;
            overflow-y: auto;
        }
        
        .custom-sort-dropdown .sort-options.open {
            display: block;
        }

        .custom-sort-dropdown .sort-option {
            padding: 10px 15px;
            cursor: pointer;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .custom-sort-dropdown .sort-option:hover {
            background-color: #00688fff;
            color: #fff;
        }
        
        .custom-sort-dropdown .sort-option.selected {
            background-color: #fff;
            font-weight: 600;
            color: #333;
        }

        .room-card {
            display: flex;
            background-color: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            margin-bottom: 25px; 
            overflow: hidden;
            transition: box-shadow 0.3s ease;
            flex-direction: row; 
            height: 290px; 
            box-sizing: border-box;
        }
        
        .room-card:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }

        .room-card .room-image {
            width: 280px;
            flex-shrink: 0;
            background-color: #f0f0f0;
        }
        
        .room-card .room-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .room-card .room-content-wrapper {
            padding: 20px 25px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            min-width: 0;
        }
        
        .room-content-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px; 
        }

        .room-content-top h3 {
            margin: 0;
            font-size: 22px; 
            font-weight: 600;
            color: #222;
            padding-right: 15px;
        }
        
        .room-details {
            display: flex;
            flex-direction: column;
            gap: 10px; 
            font-size: 14px;
            color: #555;
            margin-bottom: 15px;
        }
        
        .room-details span {
            display: flex;
            align-items: center;
            gap: 8px; 
        }
        
        .room-details .detail-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px; 
            height: 32px;
            border-radius: 6px; 
            flex-shrink: 0;
            overflow: hidden;
        }

        .room-details .detail-icon img {
            width: 100%; 
            height: 100%; 
            object-fit: contain; 
        }
        
        .room-description {
            font-size: 14px;
            line-height: 1.6;
            color: #444;
            flex-grow: 1; 
            margin-bottom: 20px; 
            overflow: hidden; 
        }

        .room-description p {
            margin: 0; 
            overflow: hidden; 
            text-overflow: ellipsis; 
            display: -webkit-box;
            -webkit-line-clamp: 2; 
            -webkit-box-orient: vertical;
        }
        
        .room-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .listed-by {
            font-size: 13px;
            color: #666;
        }
        
        .listed-by strong {
            color: #333;
        }
        
        .room-actions {
            display: flex;
            gap: 10px;
        }
        
        .room-actions .btn {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .room-actions .btn-mail {
            background-color: #fff;
            border: 1px solid #A32441; 
            color: #A32441; 
        }
        
        .room-actions .btn-mail:hover {
            background-color: #A32441;
            color: #fff;
        }
        
        .room-actions .btn-call {
            background-color: #A32441;
            color: #fff;
            border: 1px solid #A32441; 
        }
        
        .room-actions .btn-call:hover {
            background-color: #00688f;
            border-color: #00688f;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 40px;
            gap: 8px;
        }
        
        .pagination .page-numbers {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            transition: all 0.2s ease;
        }
        
        .pagination .page-numbers:hover {
            background-color: #f5f5ff;
        }
        
        .pagination .page-numbers.current {
            background-color: #336699;
            color: #fff;
            border-color: #336699;
        }
        
        /* --- FIX: Widened breakpoint to 992px to better cover tablets --- */
        @media (max-width: 992px) {
            .rooms-header {
                flex-direction: column;
                align-items: flex-start;
                flex-wrap: wrap; /* Re-enable wrapping */
            }
             .custom-sort-dropdown {
                width: 100%;
            }
            .room-filters-wrapper {
                width: 100%;
                margin-left: 0; /* Reset margin */
            }
            .sort-by-wrapper {
                width: 100%;
            }
            
            .room-card {
                flex-direction: column; 
                height: auto; 
            }
            
            .room-card .room-image {
                width: 100%; 
                height: 200px;
            }
        }

        /* --- ADDED: Rules for small mobile phones --- */
        @media (max-width: 480px) {
            .rooms-for-hire-container {
                padding: 10px;
                margin: 20px auto;
            }
            .room-content-wrapper {
                padding: 15px;
            }
            /* Stack the footer content on small screens */
            .room-footer {
                flex-direction: column;
                align-items: flex-start; /* Align text to the left */
            }
            /* Make action buttons full-width and stacked */
            .room-actions {
                flex-direction: column;
                width: 100%;
            }
            .room-actions .btn {
                width: 100%;
                box-sizing: border-box; 
                justify-content: center; /* Center icon/text */
            }
        }

    </style>
    
    <div class="rooms-for-hire-container">
        <div class="rooms-header">
            <div class="header-text">
                <h2>All Rooms</h2>
                <p>Showing <?php echo $rooms_query->found_posts; ?> results</p>
            </div>

            <div class="room-filters-wrapper">

                <?php
                $filter_state_text = 'All States'; 
                if (!empty($filter_state)) {
                    if (isset($state_map[strtolower($filter_state)])) {
                        $filter_state_text = $state_map[strtolower($filter_state)];
                    } else {
                        $filter_state_text = esc_html($filter_state);
                    }
                }
                ?>
                <div class="sort-by-wrapper">
                    <label for="state-filter-toggle">Filter by</label>
                    <div class="custom-sort-dropdown" id="custom-state-dropdown" data-param="filter_state">
                        <div class="sort-selected" id="state-filter-toggle" tabindex="0">
                            <span class_name="sort-by-text"><?php echo esc_html($filter_state_text); ?></span>
                            <i class="fas fa-chevron-down arrow"></i>
                        </div>
                        <div class="sort-options">
                            <div class="sort-option <?php echo empty($filter_state) ? 'selected' : ''; ?>" data-value="">All States</div>
                            
                            <?php foreach ($state_map as $state_slug => $state_name): ?>
                                <div class="sort-option <?php echo ($filter_state === $state_slug) ? 'selected' : ''; ?>" data-value="<?php echo esc_attr($state_slug); ?>">
                                    <?php echo esc_html($state_name); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php
                    $sort_by_text = ($sort_by === 'oldest') ? 'Oldest' : 'Newest';
                ?>
                <div class="sort-by-wrapper">
                    <label for="sort-by-toggle">Sort by</label>
                    <div class="custom-sort-dropdown" id="custom-sort-dropdown" data-param="sortby">
                        <div class="sort-selected" id="sort-by-toggle" tabindex="0">
                            <span class="sort-by-text"><?php echo esc_html($sort_by_text); ?></span>
                            <i class="fas fa-chevron-down arrow"></i>
                        </div>
                        <div class="sort-options">
                            <div class="sort-option <?php echo ($sort_by === 'newest') ? 'selected' : ''; ?>" data-value="newest">Newest</div>
                            <div class="sort-option <?php echo ($sort_by === 'oldest') ? 'selected' : ''; ?>" data-value="oldest">Oldest</div>
                        </div>
                    </div>
                </div>
                </div>
        </div>

        <?php if ($rooms_query->have_posts()) : ?>
            <?php while ($rooms_query->have_posts()) : $rooms_query->the_post(); ?>
                <?php
                $location_value = get_post_meta(get_the_ID(), $location_field, true);
                
                $location_key = strtolower($location_value);
                $location = isset($state_map[$location_key]) ? $state_map[$location_key] : $location_value;

                $dimensions = get_post_meta(get_the_ID(), $dimensions_field, true);
                $listed_by = get_post_meta(get_the_ID(), $listed_by_field, true);
                $start_date = get_post_meta(get_the_ID(), $start_date_field, true);
                $email = get_post_meta(get_the_ID(), $contact_email_field, true);
                $phone = get_post_meta(get_the_ID(), $contact_phone_field, true);
                ?>
                <div class="room-card">
                    <div class="room-image">
                        <?php if (has_post_thumbnail()) : ?>
                            <img src="<?php the_post_thumbnail_url('large'); ?>" alt="<?php the_title_attribute(); ?>">
                        <?php else: ?>
                            <img src="https://placehold.co/280x253/f0f0f0/ccc?text=No+Image" alt="No image available">
                        <?php endif; ?>
                    </div>
                    <div class="room-content-wrapper">
                        <div class="room-content-top">
                            <h3><a href="<?php the_permalink(); ?>" style="text-decoration: none; color: inherit;"><?php the_title(); ?></a></h3>
                            </div>
                        <div class="room-details">
                            <?php if ($location && $location_icon_url): ?>
                                <span>
                                    <span class="detail-icon"><img src="<?php echo esc_url($location_icon_url); ?>" alt="Location"></span>
                                    <?php echo esc_html($location); ?>
                                </span>
                            <?php endif; ?>
                             <?php if ($dimensions && $dimensions_icon_url): ?>
                                <span>
                                    <span class="detail-icon"><img src="<?php echo esc_url($dimensions_icon_url); ?>" alt="Dimensions"></span>
                                    <?php echo esc_html($dimensions); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="room-description">
                            <?php 
                            $excerpt = get_the_excerpt( get_the_ID() );
                            $trimmed_content = wp_trim_words( $excerpt, 30, '...' );
                            echo '<p>' . esc_html($trimmed_content) . '</p>';
                            ?>
                        </div>
                        <div class="room-footer">
                            <div class="listed-by">
                                <?php if ($listed_by): ?>
                                    Listed by: <strong><?php echo esc_html($listed_by); ?></strong>
                                <?php endif; ?>
                                <?php if ($start_date): ?>
                                     &nbsp;|&nbsp; Start Date: <strong><?php echo esc_html(date("d M Y", strtotime($start_date))); ?></strong>
                                <?php endif; ?>
                            </div>
                            <div class="room-actions">
                                 <?php if ($email): ?>
                                    <a href="mailto:<?php echo esc_attr($email); ?>" class="btn btn-mail"><i class="fa-regular fa-envelope"></i> Send Mail</a>
                                <?php endif; ?>
                                <?php if ($phone): ?>
                                    <a href="tel:<?php echo esc_attr($phone); ?>" class="btn btn-call"><i class="fa-solid fa-phone"></i> <?php echo esc_html($phone); ?></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>

            <div class="pagination">
                <?php
                echo paginate_links(array(
                    'total' => $rooms_query->max_num_pages,
                    'current' => $paged,
                    'prev_text' => __('&laquo;'),
                    'next_text' => __('&raquo;'),
                    'add_args' => array( 
                        'sortby' => $sort_by,
                        'filter_state' => $filter_state,
                    ), 
                ));
                ?>
            </div>

            <?php wp_reset_postdata(); ?>
        <?php else : ?>
            <p>No rooms found.</p>
        <?php endif; ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var dropdowns = document.querySelectorAll('.custom-sort-dropdown');
        if (!dropdowns.length) return;

        dropdowns.forEach(function(dropdown) {
            var selected = dropdown.querySelector('.sort-selected');
            var optionsContainer = dropdown.querySelector('.sort-options');
            var options = dropdown.querySelectorAll('.sort-option');
            var urlParam = dropdown.getAttribute('data-param');

            if (!selected || !optionsContainer || !options.length || !urlParam) {
                return;
            }

            function toggleDropdown(e) {
                e.stopPropagation(); 
                dropdowns.forEach(function(otherDropdown) {
                    if (otherDropdown !== dropdown) {
                        otherDropdown.querySelector('.sort-selected').classList.remove('open');
                        otherDropdown.querySelector('.sort-options').classList.remove('open');
                    }
                });
                
                selected.classList.toggle('open');
                optionsContainer.classList.toggle('open');
            }

            selected.addEventListener('click', toggleDropdown);
            
            selected.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    toggleDropdown(e);
                }
            });

            options.forEach(function(option) {
                option.addEventListener('click', function() {
                    var selectedValue = this.getAttribute('data-value');
                    var currentUrl = new URL(window.location.href);

                    if (selectedValue) {
                        currentUrl.searchParams.set(urlParam, selectedValue);
                    } else {
                        currentUrl.searchParams.delete(urlParam);
                    }
                    
                    currentUrl.searchParams.delete('paged');
                    window.location.href = currentUrl.href;
                });
                
                option.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        this.click();
                    }
                });
            });
        });

        document.addEventListener('click', function(e) {
            dropdowns.forEach(function(dropdown) {
                if (!dropdown.contains(e.target)) {
                    dropdown.querySelector('.sort-selected').classList.remove('open');
                    dropdown.querySelector('.sort-options').classList.remove('open');
                }
            });
        });
    });
    </script>
    <?php
    return ob_get_clean(); // End buffering and return the HTML
}
add_shortcode('all_rooms_for_hire', 'all_rooms_for_hire_shortcode');
?>