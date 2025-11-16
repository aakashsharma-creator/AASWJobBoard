<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

function locum_dashboard_shortcode()
{
    global $wpdb;
    // Get the current user
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $user_email = $current_user->user_email;

    $profile_views = (int)get_user_meta($user_id, 'locum_views_', true);


    // Get saved jobs count from user meta
    $saved_jobs = is_user_logged_in() ? get_user_meta($user_id, 'saved_jobs', true) : [];
    $saved_jobs_count = is_array($saved_jobs) ? count($saved_jobs) : 0;
    $table = $wpdb->prefix . 'frmt_form_entry_meta';

    // First get entry_id by email
    $entry_id = $wpdb->get_var($wpdb->prepare(
        "SELECT entry_id FROM $table WHERE meta_key = %s AND meta_value = %s",
        'email-1',
        $user_email
    ));

    $user_meta_data = $wpdb->get_results($wpdb->prepare(
        "SELECT meta_key, meta_value FROM $table WHERE entry_id = %d",
        $entry_id
    ));

    $profile_score = 80; // base score

    // Convert result to associative array: meta_key => meta_value
    $profile_data = [];
    foreach ($user_meta_data as $field) {
        $profile_data[$field->meta_key] = $field->meta_value;
    }

    // ✅ Profile Picture (upload-2)
    if (!empty($profile_data['upload-2'])) {
        $profile_score += 5;
    }

    // ✅ CV Upload (upload-1)
    if (!empty($profile_data['upload-1'])) {
        $profile_score += 5;
    }

    // ✅ LinkedIn (url-1)
    if (!empty($profile_data['url-1'])) {
        $profile_score += 5;
    }

    // ✅ About You (textarea-1)
    if (!empty($profile_data['textarea-1'])) {
        $profile_score += 5;
    }

    if ($profile_score < 85) {
        $chart_image = '80.png';
    } elseif ($profile_score < 90) {
        $chart_image = '85.png';
    } elseif ($profile_score < 95) {
        $chart_image = '90.png';
    } elseif ($profile_score < 100) {
        $chart_image = '95.png';
    } else {
        $chart_image = '100.png';
    }



    $sector = isset($profile_data['select-6']) ? $profile_data['select-6'] : '';

    if (!empty($sector)) {
        if (strpos($sector, ',') !== false) {
            $sector_array = array_map('trim', explode(',', $sector));
        } else {
            $sector_array = [trim($sector)];
        }
    } else {
        $sector_array = []; // fallback
    }

    // ✅ WP_Query: Jobs by user’s sector
    $args = [
        'post_type' => 'job_listing',
        'post_status' => 'publish',
        'posts_per_page' => 5,
    ];

    if (!empty($sector_array)) {
        $args['tax_query'] = [
            [
                'taxonomy' => 'job_listing_category',
                'field' => 'slug',
                'terms' => $sector_array,
                'operator' => 'IN',
            ],
        ];
    }

    $job_query = new WP_Query($args);

    $total_jobs = $job_query->found_posts;
?>
    <link rel="stylesheet" type="text/css"
        href="<?php echo get_stylesheet_directory_uri(); ?>/custom-shortcodes/css/locum-dashboard.css">




    <div class="dashboard-container">
        <!-- Stats Section -->
        <div class="stats-section">
            <div class="stat-card">
                <div class="stat-icon-wrapper blue">
                    <img class="stat-icon"
                        src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/icon-1.png'); ?>"
                        alt="Active Jobs Icon" />
                </div>
                <div class="stat-text">
                    <div class="stat-label">Recommended Jobs</div>
                    <div class="stat-number blue-text"><?php echo $total_jobs ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon-wrapper red">
                    <img class="stat-icon"
                        src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/icon-2.png'); ?>"
                        alt="User Views Icon" />
                </div>
                <div class="stat-text">
                    <div class="stat-label">Profile Views</div>
                    <div class="stat-number red-text"><?php echo $profile_views; ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon-wrapper blue">
                    <img class="stat-icon"
                        src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/Vector_fill.png'); ?>"
                        alt="Plan Icon" />
                </div>
                <div class="stat-text">
                    <div class="stat-label">Saved Jobs</div>
                    <div class="stat-number blue-text"><?php echo esc_html($saved_jobs_count); ?></div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content-dashboard">
            <div class="left-section">
                <div class="latest-jobs-card">
                    <div class="card-header">
                        <h3>Latest Jobs</h3>
                        <a href="/search-job" class="view-all-link">View All</a>
                    </div>
                    <div class="job-list">
                        <?php
                        if ($job_query->have_posts()):
                            while ($job_query->have_posts()):
                                $job_query->the_post();

                                // ✅ Get company info using same function as your working card
                                $company_data = function_exists('get_company_info_by_post') ? get_company_info_by_post(get_the_ID()) : [];

                                $company_logo = !empty($company_data['logo'])
                                    ? $company_data['logo']
                                    : get_stylesheet_directory_uri() . '/images/icon2/company.png';

                                $company_name = !empty($company_data['name'])
                                    ? $company_data['name']
                                    : (get_post_meta(get_the_ID(), '_company_name', true) ?: 'Unknown Company');

                                // ✅ Job type
                                $job_type_obj = function_exists('get_the_job_type') ? get_the_job_type() : '';
                                $job_type = !empty($job_type_obj) ? $job_type_obj->name : 'Full Time';

                                // ✅ Experience level (from wp_postmeta)
                                $experience = get_post_meta(get_the_ID(), '_experience_level', true);
                                if (empty($experience)) {
                                    $experience = 'Not specified';
                                }

                                $experience_class = strtolower(str_replace(' ', '-', $experience));
                        ?>

                                <div class="job-item">
                                    <div class="job-item-left">
                                        <img src="<?php echo esc_url($company_logo); ?>" alt="<?php echo esc_attr($company_name); ?>"
                                            class="job-logo">

                                        <div class="job-details">
                                            <h4 class="job-title">
                                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                            </h4>

                                            <p class="company-name"><?php echo esc_html($company_name); ?></p>

                                            <div class="job-tags">
                                                <?php if ($job_type): ?>
                                                    <span class="job-tag full-time"><?php echo esc_html($job_type); ?></span>
                                                <?php endif; ?>

                                                <?php if ($experience): ?>
                                                    <span class="job-tag <?php echo esc_attr($experience_class); ?>">
                                                        <?php echo esc_html($experience); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="job-item-right">
                                        <span class="job-posted-time">
                                            <?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago'; ?>
                                        </span>
                                    </div>
                                </div>

                        <?php
                            endwhile;
                            wp_reset_postdata();
                        else:
                            echo '<p>No job listings found for your selected sector.</p>';
                        endif;
                        ?>
                    </div>
                </div>
            </div>

            <div class="right-section">
                <div class="profile-completion-card">
                    <div class="profile-content">
                        <h3>Complete Your Profile</h3>
                        <p>Get noticed by employers and increase your chances of getting booked. Fill in your details so
                            clinics can easily review your qualifications.</p>
                    </div>
                    <div class="profile-chart">
                        <div class="chart-wrapper" style="width: 135px;">
                            <img class="chart-image"
                                src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/chart-icon/' . $chart_image); ?>"
                                alt="Profile Chart" />
                            <div class="percentage-text"><?php echo $profile_score ?>%</div>
                        </div>
                        <div class="completed-text">COMPLETED</div>
                    </div>
                </div>

                <div class="quick-actions-card">
                    <h3>Quick Actions</h3>
                    <a href="<?php echo esc_url(get_permalink(get_page_by_path('search-job'))); ?>">
                        <button class="action-btn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                            </svg>
                            Search for Jobs
                        </button>
                    </a>

                    <a href="<?php echo esc_url(get_permalink(get_page_by_path('locum-profile'))); ?>">
                        <button class="action-btn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14"></path>
                                <path d="m12 5 7 7-7 7"></path>
                            </svg>
                            Manage Profile
                        </button>
                    </a>
                </div>

                <div class="support-card">
                    <div class="support-header">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                            <path d="M12 17h.01"></path>
                        </svg>
                        <h3>Need Help?</h3>
                    </div>
                    <p>Our support team is here to help you make the most of your hiring experience.</p>
                    <button class="contact-support-btn" onclick="location.href='<?php echo get_permalink(260); ?>'"
                        style="cursor:pointer">Contact Support</button>
                </div>
            </div>
        </div>
    </div>

<?php
}

add_shortcode('locum_dashboard', 'locum_dashboard_shortcode');
?>