<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

function my_recent_jobs_shortcode()
{
    ob_start(); ?>

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <style>
        .job-section {
            background-color: #ffffff;
            padding-right: 30px;
            border-radius: 0;
        }

        .job-section .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 30px;
        }

        .job-section .header-left {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .job-section .header-left .explore-text {
            font-size: 14px;
            color: #00688F;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin: 0;
        }

        .job-section .header-left h1 {
            margin: 0;
            color: #333;
            font-size: 48px;
            font-weight: 400;
            line-height: 1.2;
        }

        .view-all {
            background-color: #00688F;
            color: white;
            padding: 16px 32px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .view-all:hover {
            background-color: #005a7a;
            transform: translateY(-2px);
        }

        .view-all svg {
            width: 20px;
            height: 20px;
        }

        /* Swiper container */
        .job-cards-container.swiper {
            padding-bottom: 110px;
            overflow-x: hidden;
            overflow-y: hidden;
            width: 100%;
        }

        .swiper-wrapper {
            align-items: stretch;
        }

        .swiper-slide {
            display: flex;
            justify-content: center;
            height: auto;
        }

        .job-card {
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            padding: 32px 24px;
            gap: 16px;
            width: auto;
            max-width: none;
            height: 420px;
            background: #f0f9ff;
            border-bottom: 4px solid #00688F;
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.08);
            border-radius: 12px;
            position: relative;
            transition: all 0.3s ease;
            cursor: pointer; /* Indicate clickable */
        }

        .job-card:hover {
            transform: translateY(-4px);
            box-shadow: 0px 8px 30px rgba(0, 0, 0, 0.12);
        }

        .swiper-slide:nth-child(even) .job-card {
            background: #eed3d9;
            border-bottom: 4px solid #A32441;
        }

        .swiper-slide:nth-child(odd) .job-card {
            background: #e5f0f4;
            border-bottom: 4px solid #00688F;
        }

        .job-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            width: 100%;
            margin-bottom: 8px;
        }

        .job-header-left {
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex: 1;
        }

        .rf-logo {
            width: 48px;
            height: 48px;
            object-fit: contain;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 4px;
            background: white;
            border: 1px solid #00688F;
        }

        .job-title {
            font-size: 20px;
            margin: 0;
            font-weight: 600;
            color: #1f2937;
            line-height: 1.3;
        }

        .company-name {
            font-size: 14px;
            color: #6b7280;
            margin: 0;
            font-weight: 500;
        }

        .bookmark-btn {
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 8px;
            border-radius: 6px;
            transition: all 0.3s ease;
            flex-shrink: 0;
            z-index: 10; /* Ensure bookmark button is clickable */
        }

        .bookmark-btn:hover {
            background: #f1f5f9;
            color: #00688F;
            transform: scale(1.1);
        }

        .bookmark-btn.saved {
            color: #00688F;
        }

        .bookmark-btn.loading svg {
            opacity: 0.3;
        }

        .bookmark-btn.loading::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 24px;
            height: 24px;
            margin: -12px 0 0 -12px;
            border: 2px solid #0066FF;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .job-tags {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin: 8px 0;
        }

        .tag {
            display: inline-block;
            padding: 6px 16px;
            font-size: 14px;
            border-radius: 8px;
            font-weight: 500;
            border: 1px solid transparent;
        }

        .fulltime {
            background-color: #ffffff;
            color: #107296;
            border-color: #bfdbfe;
        }

        .experienced {
            background-color: #ffffff;
            color: #107296;
            border-color: #d8b4fe;
        }

        .intermediate {
            background-color: #ffffff;
            color: #107296;
            border-color: #a7f3d0;
        }

        .job-description {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.5;
            margin: 8px 0 16px 0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin: 0px;
        }

        .job-info {
            width: 100%;
        }

        .info-item {
            display: flex;
            align-items: center;
            font-size: 13px;
            margin-bottom: 8px;
            gap: 8px;
            color: #6b7280;
        }

        .info-item:last-child {
            margin-bottom: 0;
        }

        .info-item svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }

        /* Custom Swiper Navigation */
        .swiper-button-next,
        .swiper-button-prev {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            background: white;
            border: 2px solid #e5e7eb;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .swiper-button-next:hover,
        .swiper-button-prev:hover {
            border-color: #00688F;
            background: #00688F;
        }

        .swiper-button-next:after,
        .swiper-button-prev:after {
            font-size: 16px;
            font-weight: 600;
            color: #6b7280;
        }

        .swiper-button-next:hover:after,
        .swiper-button-prev:hover:after {
            color: white;
        }

        .swiper-button-next {
            right: 32px;
            top: auto;
            bottom: 20px;
            margin-top: 0;
            border: 1px solid #207b9d;
        }

        .swiper-button-prev {
            left: 87%;
            top: auto;
            bottom: 20px;
            margin-top: 0;
            border: 1px solid #207b9d;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .swiper-button-next {
                right: 10px;
            }

            .swiper-button-prev {
                left: 10px;
            }
        }

        @media (max-width: 768px) {
            .job-section {
                padding: 30px 20px;
            }

            .job-section .header {
                flex-direction: column;
                gap: 20px;
                align-items: flex-start;
            }

            .job-section .header-left h1 {
                font-size: 36px;
            }

            .view-all {
                align-self: stretch;
                justify-content: center;
            }

            .job-card {
                min-height: 380px;
                padding: 24px 20px;
            }

            .swiper-button-next,
            .swiper-button-prev {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .job-section .header-left h1 {
                font-size: 28px;
            }

            .job-card {
                min-height: 360px;
                padding: 20px 16px;
                margin-top: 20px;
            }

            .job-title {
                font-size: 18px;
            }

            .swiper-slide {
                width: 95% !important;
            }

            .job-card {
                padding: 16px;
            }

            .job-title {
                font-size: 15px;
            }

            .rf-logo {
                width: 36px;
                height: 36px;
            }
        }
    </style>

    <div class="job-section">
        <div class="header">
            <!-- <div class="header-left">
                <p class="explore-text">EXPLORE JOB</p>
                <h1>Recent Jobs</h1>
            </div> -->
            <!-- <button class="view-all">
                View All Jobs
                <svg viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </button> -->
        </div>

        <!-- Swiper Container -->
        <div class="job-cards-container swiper">
            <div class="swiper-wrapper">
                <?php
                // Fetch jobs
                $jobs = new WP_Query([
                    'post_type' => 'job_listing',
                    'post_status' => 'publish',
                    's' => sanitize_text_field($_REQUEST['key'] ?? ''),
                    'posts_per_page' => 20,
                    'orderby' => 'date',
                    'order' => 'DESC',
                ]);

                if ($jobs->have_posts()):
                    while ($jobs->have_posts()):
                        $jobs->the_post();
                        $post_id = get_the_ID();
                        $company_logo = get_the_post_thumbnail_url($post_id, 'thumbnail') ?: get_stylesheet_directory_uri() . '/images/noimage.jpg';
                        $company_name = get_post_meta($post_id, '_company_name', true);
                        $location = get_post_meta($post_id, '_job_location', true);
                        $salary = get_post_meta($post_id, '_job_salary', true);
                        $job_salary_currency = get_post_meta($post_id, '_job_salary_currency', true);
                        $job_salary_unit = get_post_meta($post_id, '_job_salary_unit', true);
                        $job_type_terms = wp_get_post_terms($post_id, 'job_listing_type');
                        $job_types = !empty($job_type_terms) ? wp_list_pluck($job_type_terms, 'name') : array();
                        $job_description = get_the_excerpt() ?: wp_trim_words(get_the_content(), 15, '...');
                        $user_id = get_current_user_id();
                        $saved_jobs = is_user_logged_in() ? get_user_meta($user_id, 'saved_jobs', true) : [];
                        $is_saved = is_array($saved_jobs) && in_array($post_id, $saved_jobs);
                        
                        $custom_permalink = esc_url(add_query_arg('selected_job', $post_id, 'https://aaswjobstaging.wpenginepowered.com/list-job-custom/'));
						
						$company_data = get_company_info_by_post($post_id);
                ?>
                        <div class="swiper-slide">
                            <div class="job-card" data-job-id="<?php echo esc_attr($post_id); ?>" data-permalink="<?php echo $custom_permalink; ?>">
                                <div class="job-header">
                                    <div class="job-header-left">
                                        <h3 class="job-title">
                                            <?php
                                            $title = get_the_title();
                                            if (strlen($title) > 40) {
                                                echo esc_html(substr($title, 0, 40)) . '...';
                                            } else {
                                                echo esc_html($title);
                                            }
                                            ?>
                                        </h3>
                                        <div style="display:flex;align-items:center;gap:10px;">
                                            <img src="<?php echo $company_data['logo']; ?>" alt="Company Logo" class="rf-logo"
                                                style="width:40px;height:auto; border:1px solid #00688F;"/>
                                            <div class="company-name"><?php echo esc_html($company_name); ?></div>
                                        </div>
                                    </div>
                                    <?php if (!is_user_logged_in()) { ?>
                                        <button class="bookmark-btn" data-job-id="">
                                            <svg class="bookmark-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#00688F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                                            </svg>
                                        </button>
                                    <?php } else { ?>
                                        <button class="bookmark-btn <?php echo $is_saved ? 'saved' : ''; ?>" data-job-id="<?php echo esc_attr($post_id); ?>">
                                            <svg class="bookmark-icon" width="24" height="24" viewBox="0 0 24 24" fill="<?php echo $is_saved ? 'currentColor' : 'none'; ?>" stroke="#00688F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                                            </svg>
                                        </button>
                                    <?php } ?>
                                </div>

                                <div class="job-tags">
                                    <?php
                                    $tag_classes = ['fulltime', 'experienced', 'intermediate'];
                                    foreach ($job_types as $index => $type):
                                        $class = $tag_classes[$index % count($tag_classes)];
                                    ?>
                                        <span class="tag <?php echo esc_attr($class); ?>"><?php echo esc_html($type); ?></span>
                                    <?php endforeach; ?>
                                </div>

                                <div class="job-description">
                                    <?php echo esc_html($job_description); ?>
                                </div>

                                <div class="job-info">
                                    <div class="info-item">
                                        <svg viewBox="0 0 16 16" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M11.536 3.464a5 5 0 010 7.072L8 14.07l-3.536-3.535a5 5 0 117.072-7.072v.001zm-4.243 1.757a2.5 2.5 0 103.536 3.536 2.5 2.5 0 00-3.536-3.536z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <?php echo esc_html($location); ?>
                                    </div>
                                    <?php if ($salary): ?>
                                        <div class="info-item">
                                            <svg viewBox="0 0 16 16" fill="currentColor">
                                                <path
                                                    d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718H4zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73l.348.086z" />
                                            </svg>
                                            <?php echo esc_html($salary); ?> <?php echo esc_html($job_salary_currency); ?> /
                                            <?php echo esc_html(ucfirst(strtolower($job_salary_unit))); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="info-item">
                                        <svg viewBox="0 0 16 16" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M8 15A7 7 0 108 1a7 7 0 000 14zm0 1A8 8 0 108 0a8 8 0 000 16z"
                                                clip-rule="evenodd" />
                                            <path fill-rule="evenodd"
                                                d="M7.5 3a.5.5 0 01.5.5v5.21l3.248 1.856a.5.5 0 01-.496.868l-3.5-2A.5.5 0 01 7 9V3.5a.5.5 0 01.5-.5z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <?php echo esc_html(human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                <?php
                    endwhile;
                    wp_reset_postdata();
                else:
                    echo '<div class="swiper-slide"><p style="text-align: center;">No jobs found.</p></div>';
                endif;
                ?>
            </div>

            <!-- Navigation -->
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </div>

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            new Swiper(".job-cards-container", {
                slidesPerView: 3.5,
                spaceBetween: 24,
                loop: true,
                autoplay: {
                    delay: 30000,
                    disableOnInteraction: false,
                },
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
                breakpoints: {
                    1200: {
                        slidesPerView: 3.5,
                        spaceBetween: 24
                    },
                    768: {
                        slidesPerView: 2,
                        spaceBetween: 20
                    },
                    480: {
                        slidesPerView: 1,
                        spaceBetween: 16
                    }
                }
            });

            // Bookmark and job card click functionality
            jQuery(document).ready(function($) {
                // Bookmark button click
                $(document).on("click", ".bookmark-btn", function(e) {
                    e.preventDefault();
                    e.stopPropagation(); // Prevent card click from triggering
                    const button = $(this);
                    const jobId = button.data("job-id");

                    if (jobId == '') {
                        if (typeof elementorProFrontend !== "undefined" && elementorProFrontend.modules.popup) {
                            elementorProFrontend.modules.popup.showPopup({ id: 2326 });
                        } else {
                            console.error("Elementor Popup module not found");
                        }
                        return false;
                    }

                    button.addClass("loading");
                    $.ajax({
                        url: "<?php echo admin_url('admin-ajax.php'); ?>",
                        type: "POST",
                        dataType: "json",
                        data: {
                            action: "toggle_save_job",
                            job_id: jobId
                        },
                        success: function(response) {
                            button.removeClass("loading");
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
                            button.removeClass("loading");
                            alert("Something went wrong. Please try again.");
                        }
                    });
                });

                // Job card click
                $(document).on("click", ".job-card", function(e) {
                    e.preventDefault();
                    const card = $(this);
                    const permalink = card.data("permalink");

                    // Redirect to job details page (log/non-log users)
                    window.location.href = permalink;
                });
            });
        });
    </script>

<?php
    return ob_get_clean();
}
add_shortcode('recent_jobs', 'my_recent_jobs_shortcode');
?>

