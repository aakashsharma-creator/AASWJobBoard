<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Redirect only if user is NOT logged in AND not already on home page


function employer_dashboard_shortcode()
{
    ob_start();
    global $wpdb;

    // Get the "Post a Job" page ID from WP Job Manager settings
    $post_a_job_page_id = get_option('job_manager_submit_job_form_page_id');

    // Get the URL of that page
    $post_a_job_page_url = $post_a_job_page_id ? get_permalink($post_a_job_page_id) : '';

    $current_user = wp_get_current_user();

    // Count active jobs
    $recent_jobs = new WP_Query([
        'post_type' => 'job_listing',    // WP Job Manager's CPT
        'author' => $current_user->ID,
        'post_status' => 'publish',
        // 'posts_per_page' => -1
    ]);
    $active_jobs_count = $recent_jobs->found_posts;

    $user_views = (int) get_user_meta($current_user->ID, 'user_views', true);

    //get company Logo
    global $wpdb;

    $company_email = $current_user->user_email;

    $query = $wpdb->prepare("
		SELECT upload.meta_value 
		FROM {$wpdb->prefix}frmt_form_entry_meta AS email
		INNER JOIN {$wpdb->prefix}frmt_form_entry_meta AS upload
			ON email.entry_id = upload.entry_id
		WHERE email.meta_key = %s
		  AND email.meta_value = %s
		  AND upload.meta_key = %s
	", 'email-1', $company_email, 'upload-1');

    $result = $wpdb->get_var($query);

  
    if ($result) {
        //echo '<pre>';
        $data = maybe_unserialize($result);
        $logourl = $data['file']['file_url'];
        //echo '</pre>';
    } else {
        $logourl = esc_url(get_stylesheet_directory_uri() . '/images/default-company.png');
    }


    $table = $wpdb->prefix . 'frmt_form_entry_meta';

    // First get entry_id by email
    $entry_id = $wpdb->get_var($wpdb->prepare(
        "SELECT entry_id FROM $table WHERE meta_key = %s AND meta_value = %s",
        'email-1',
        $company_email
    ));

    $user_meta_data = [];
    if ($entry_id) {
        // Step 2: Fetch all form data for that entry_id
        $user_meta_data = $wpdb->get_results($wpdb->prepare(
            "SELECT meta_key, meta_value FROM $table WHERE entry_id = %d",
            $entry_id
        ));
    }

    // ✅ Debug output to check everything
    $profile_data = [];
    foreach ($user_meta_data as $item) {
        $profile_data[$item->meta_key] = $item->meta_value;
    }

    // ✅ Calculate Profile Completeness
    $profile_score = 85; // Base score

    // Check Company Logo (upload-1)
    if (!empty($profile_data['upload-1'])) {
        $logo = maybe_unserialize($profile_data['upload-1']);
        if (!empty($logo['file']['file_url'])) {
            $profile_score += 5;
        }
    }

    // Check Company Description (textarea-1)
    if (!empty($profile_data['textarea-1'])) {
        $profile_score += 5;
    }

    // Check Company Website (url-1)
    if (!empty($profile_data['url-1'])) {
        $profile_score += 5;
    }

    if ($profile_score < 90) {
        $chart_image = '85.png';
    } elseif ($profile_score < 95) {
        $chart_image = '90.png';
    } elseif ($profile_score < 100) {
        $chart_image = '95.png';
    } else {
        $chart_image = '100.png';
    }
?>
    <link rel="stylesheet" type="text/css"
        href="<?php echo get_stylesheet_directory_uri(); ?>/custom-shortcodes/css/employer-dashboard.css">
    <?php
    // Get current logged-in user ID
    $current_user_id = get_current_user_id();

    // Get all job posts by this user
    $args = array(
        'post_type' => 'job_listing',
        'author' => $current_user_id,
        'posts_per_page' => -1, // get all jobs
        'post_status' => array('publish', 'pending', 'expired'),
    );

    $jobs = get_posts($args);
    $total_views = 0;

    if (!empty($jobs)) {
        $job_ids = wp_list_pluck($jobs, 'ID'); // array of job IDs
        $table = $wpdb->prefix . 'job_views_log';

        // Sum all view_counts from log table for these jobs
        $placeholders = implode(',', array_fill(0, count($job_ids), '%d'));
        $query = $wpdb->prepare(
            "SELECT SUM(view_count) as total_views FROM $table WHERE job_id IN ($placeholders)",
            ...$job_ids
        );

        $result = $wpdb->get_var($query);
        $total_views = $result ? intval($result) : 0;
    }
    ?>

    <?php
    global $wpdb;
    $table = $wpdb->prefix . 'job_views_log';

    $current_user_id = get_current_user_id();
    // Get all published jobs
    $jobs = get_posts([
        'post_type' => 'job_listing',
        'author' => $current_user_id,
        'post_status' => 'publish',
        'posts_per_page' => -1
    ]);

    $job_data = [];

    foreach ($jobs as $job) {
        $job_id = $job->ID;
        $title = get_the_title($job_id);

        // --- Week: last 7 days
        $week_arr = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $total = $wpdb->get_var($wpdb->prepare(
                "SELECT SUM(view_count) FROM {$table} WHERE job_id=%d AND view_date=%s",
                $job_id,
                $date
            ));
            $week_arr[] = intval($total);
        }

        // --- Month: last 4 weeks (week starts from Sunday)
        $month_arr = [];
        for ($w = 3; $w >= 0; $w--) {
            $start = date('Y-m-d', strtotime("last sunday -$w week"));
            $end = date('Y-m-d', strtotime("$start +6 days"));
            $total = $wpdb->get_var($wpdb->prepare(
                "SELECT SUM(view_count) FROM {$table} WHERE job_id=%d AND view_date BETWEEN %s AND %s",
                $job_id,
                $start,
                $end
            ));
            $month_arr[] = intval($total);
        }

        // --- Year: last 12 months
        $year_arr = [];
        for ($m = 11; $m >= 0; $m--) {
            $start = date('Y-m-01', strtotime("-$m months"));
            $end = date('Y-m-t', strtotime($start));
            $total = $wpdb->get_var($wpdb->prepare(
                "SELECT SUM(view_count) FROM {$table} WHERE job_id=%d AND view_date BETWEEN %s AND %s",
                $job_id,
                $start,
                $end
            ));
            $year_arr[] = intval($total);
        }

        $job_data[] = [
            'id' => $job_id,
            'title' => $title,
            'week' => $week_arr,
            'month' => $month_arr,
            'year' => $year_arr
        ];
    }

    // --- Labels
    $week_labels = [];
    for ($i = 6; $i >= 0; $i--)
        $week_labels[] = date('D', strtotime("-$i days"));

    $month_labels = [];
    for ($i = 3; $i >= 0; $i--)
        $month_labels[] = "Week " . (4 - $i);

    $year_labels = [];
    for ($i = 11; $i >= 0; $i--)
        $year_labels[] = date('M', strtotime("-$i month"));
    ?>

    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>



    <div class="dashboard-container">
        <div class="stats-section">
            <div class="stat-card">
                <img class="stat-icon" src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/icon-1.png'); ?>"
                    alt="Active Jobs Icon" />
                <div class="stat-text">
                    <div class="stat-label" style="color: #00688F;">Active Jobs</div>
                    <div class="stat-number" style="color: #000000;"><?php echo esc_html($active_jobs_count); ?></div>
                </div>
            </div>

            <div class="stat-card">
                <img class="stat-icon" src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/icon-2.png'); ?>"
                    alt="User Views Icon" />
                <div class="stat-text">
                    <div class="stat-label" style="color: #A32441;">User Views</div>
                    <div class="stat-number" style="color: #000000;"><?php echo esc_html($total_views); ?></div>
                </div>
            </div>

            <div class="stat-card plan-card">
                <img class="stat-icon" src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/icon-3.png'); ?>"
                    alt="Plan Icon" />
                <div class="stat-text">
                    <?php
                    $packages = wc_paid_listings_get_user_packages($current_user_id);

                    if (empty($packages)) {
                    ?>
                        <div class="plan-title">No Plan</div>
                        <div class="plan-subtitle">Subscribe to Post a Job</div>
                    <?php
                    } else {
                        $i = 0;
                        foreach ($packages as $package) {

                            //$remaining = $package->package_limit > 0 ? ( $package->package_limit - $package->count ) : 'Unlimited';

                            // Get the WooCommerce order related to this package
                            $order = wc_get_order($package->order_id);

                            if ($order && ! empty($package->package_duration) && $package->package_duration > 0) {
                                $order_date = $order->get_date_created(); // WC_DateTime object
                                $expiry_date = clone $order_date;
                                $expiry_date->modify('+' . $package->package_duration . ' days');

                                $today = new DateTime();
                                $interval = $today->diff($expiry_date);

                                if ($expiry_date < $today) {
                                    $expires_in = 'Expired';
                                } else {
                                    $expires_in = 'Expires in ' . $interval->days . ' day' . ($interval->days > 1 ? 's' : '');
                                }
                            } else {
                                $expires_in = 'No Expiry';
                            }

                            if ($i == 0) {
                                $totalLists = $package->package_limit > 0 ? $package->package_limit : 'Unlimited';
                                echo '<div class="job-package">';
                                echo '<div class="plan-title">' . esc_html(get_the_title($package->product_id)) . '</div>';
                                //echo '<p><strong>Job Listed:</strong> ' .$package->package_count. '/'.$totalLists.'</p>';
                                //echo '<p><strong>Used:</strong> ' . $package->package_count . '</p>';
                                //echo '<p><strong>Remaining:</strong> ' . $remaining . '</p>';
                                echo '<div class="plan-subtitle">' . $expires_in  . '</div>';
                                echo '</div>';
                            }
                            $i++;
                        }
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="main-content-dashboard">
            <div class="left-section">

                <?php
                if (empty($packages)) {  ?>
                    <div class="subscription-card">
                        <div class="subscription-content">
                            <h2>Subscribe to a plan to get started!</h2>
                            <p>Unlock access to your advertiser dashboard and begin your hiring journey in just a few clicks.
                                Choose the plan that suits your goals and streamline your recruitment process.</p>
                            <button class="subscribe-btn">Subscribe Now</button>
                        </div>
                    </div>
                <?php } ?>

                <!-- Job Statistics Chart -->
                <div class="job-stats-card">
                    <div class="card-header">
                        <h3>Job statistics</h3>
                        <div class="time-period-buttons">
                            <button class="time-period-btn active" data-period="week">Week</button>
                            <button class="time-period-btn" data-period="month">Month</button>
                            <button class="time-period-btn" data-period="year">Year</button>
                        </div>
                    </div>
                    <div class="chart-subtitle"></div>
                    <div class="chart-container">
                        <canvas id="jobStatsChart"></canvas>
                    </div>
                    <!-- <div class="chart-label">Practice Leader - Central</div> -->
                </div>

                <div class="recent-jobs-card">
                    <div class="card-header">
                        <h3>Recent Job Posts</h3>
                        <button class="view-all-btn">View All</button>
                    </div>

                    <div class="recent-jobs-list">
                        <?php if ($recent_jobs->have_posts()):
                            while ($recent_jobs->have_posts()):
                                $recent_jobs->the_post();
                                $logo = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail') ?: esc_url(get_stylesheet_directory_uri() . '/images/default-company.png');
                                $company_name = get_post_meta(get_the_ID(), '_company_name', true) ?: esc_html(get_bloginfo('name'));
                                $types = wp_get_post_terms(get_the_ID(), 'job_listing_type');
                                $time_ago = human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago';
                        ?>
                                <div class="job-card">
                                    <div class="job-left">
                                        <img src="<?php echo esc_url($logo); ?>" alt="Company Logo" class="job-logo">
                                        <div class="job-info">
                                            <div class="job-title"><?php the_title(); ?></div>
                                            <div class="job-company"><?php echo esc_html($company_name); ?></div>
                                            <div class="job-tags">
                                                <?php
                                                if (!empty($types) && !is_wp_error($types)) {
                                                    foreach ($types as $t) {
                                                        echo '<span class="job-tag">' . esc_html($t->name) . '</span>';
                                                    }
                                                }
                                                // Add default tags if none exist
                                                if (empty($types) || is_wp_error($types)) {
                                                    echo '<span class="job-tag">Full Time</span>';
                                                    echo '<span class="job-tag">Experienced</span>';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="job-right">
                                        <div class="job-date"><?php echo esc_html($time_ago); ?></div>
                                    </div>
                                </div>
                            <?php endwhile;
                            wp_reset_postdata();
                        else: ?>
                            <div class="no-jobs-message">No jobs posted yet.</div>
                        <?php endif; ?>
                        <a href="<?php echo esc_url($post_a_job_page_url); ?>">
                            <button class="post-job-btn">
                                <img class="post-icon"
                                    src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/dashboard_employer/tabler_plus.png'); ?>"
                                    alt="Post Icon" style="vertical-align: middle;">
                                Post a New Job
                            </button></a>
                    </div>
                </div>
            </div>

            <div class="right-section">
                <div class="profile-card">
                    <div class="profile-chart">
              <img class="chart-image" src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/chart-icon/' . $chart_image); ?>"
                alt="Profile Chart" />

                        <div class="profile-percentage">
                            <div class="percentage-text"><?php echo $profile_score ?>%</div>
                            <div class="profile-label">PROFILE</div>
                        </div>
                    </div>
                    <div class="profile-content">
                        <h3>Complete Your Profile</h3>
                        <p>Make it easy for locums to trust your practice. Fill in your details and start hiring today.</p>
                    </div>
                </div>

                <div class="quick-actions-card">
                    <h3>Quick Actions</h3>
                    <a href="<?php echo esc_url($post_a_job_page_url); ?>">
                        <button class="action-btn">
                            <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/Frame-3.png'); ?>"
                                alt="Post Job Icon" />
                            Post New Job
                        </button>
                    </a>

                    <a href="<?php echo esc_url(get_permalink(get_page_by_path('search-for-locums'))); ?>">
                        <button class="action-btn">
                            <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/Frame-1.png'); ?>"
                                alt="Search Locum Icon" />
                            Search Locum
                        </button>
                    </a>

                    <a href="<?php echo esc_url(get_permalink(get_page_by_path('company-profile'))); ?>">
                        <button class="action-btn">
                            <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/Frame-2.png'); ?>"
                                alt="Manage Profile Icon" />
                            Manage Profile
                        </button>
                    </a>
                </div>

                <div class="support-card" style="margin-bottom:30px;">
                    <div class="support-header">
                        <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/Frame-4.png'); ?>"
                            alt="Support Icon" />
                        <h3>Need Help?</h3>
                    </div>
                    <p>Our support team is here to help you make the most of your hiring experience.</p>
                    <button class="contact-support-btn" onclick="location.href='<?php echo get_permalink(260);?>'" style="cursor:pointer">Contact Support</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartData = {
                week: {
                    labels: <?php echo json_encode($week_labels); ?>,
                    datasets: []
                },
                month: {
                    labels: <?php echo json_encode($month_labels); ?>,
                    datasets: []
                },
                year: {
                    labels: <?php echo json_encode($year_labels); ?>,
                    datasets: []
                }
            };

            <?php foreach ($job_data as $job):
                $color = substr(md5($job['id']), 0, 6); ?>
                chartData.week.datasets.push({
                    label: "<?php echo esc_js($job['title']); ?>",
                    data: <?php echo json_encode($job['week']); ?>,
                    borderColor: "#<?php echo $color; ?>",
                    borderWidth: 2,
                    pointBackgroundColor: "#<?php echo $color; ?>",
                    pointRadius: 4,
                    tension: 0.3,
                    fill: false
                });
                chartData.month.datasets.push({
                    label: "<?php echo esc_js($job['title']); ?>",
                    data: <?php echo json_encode($job['month']); ?>,
                    borderColor: "#<?php echo $color; ?>",
                    borderWidth: 2,
                    pointBackgroundColor: "#<?php echo $color; ?>",
                    pointRadius: 4,
                    tension: 0.3,
                    fill: false
                });
                chartData.year.datasets.push({
                    label: "<?php echo esc_js($job['title']); ?>",
                    data: <?php echo json_encode($job['year']); ?>,
                    borderColor: "#<?php echo $color; ?>",
                    borderWidth: 2,
                    pointBackgroundColor: "#<?php echo $color; ?>",
                    pointRadius: 4,
                    tension: 0.3,
                    fill: false
                });
            <?php endforeach; ?>

            const dateRangeText = {
                week: 'Showing Job statistic last 7 days',
                month: 'Showing Job statistic last 4 weeks',
                year: 'Showing Job statistic last 12 months'
            };

            function computeMaxVal(datasetArray) {
                let max = 0;
                datasetArray.forEach(ds => ds.data.forEach(v => {
                    if (v > max) max = v;
                }));
                return Math.max(max, 10);
            }

            const ctx = document.getElementById('jobStatsChart').getContext('2d');
            const initialMax = computeMaxVal(chartData.week.datasets);
            const jobStatsChart = new Chart(ctx, {
                type: 'line',
                data: chartData.week,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#000',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: '#000',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: true,
                            callbacks: {
                                title: context => context[0].dataset.label,
                                label: context => {
                                    const period = document.querySelector('.time-period-btn.active').dataset.period;
                                    if (period === 'week') return [context.parsed.y + ' views on this day'];
                                    else if (period === 'month') return [context.parsed.y + ' views this week'];
                                    else return [context.parsed.y + ' views this month'];
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: Math.ceil(initialMax * 1.2),
                            ticks: {
                                stepSize: Math.ceil(initialMax * 1.2 / 5),
                                color: '#64748b'
                            },
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            },
                            title: {
                                display: true,
                                text: 'User Views',
                                color: '#64748b',
                                font: {
                                    size: 12
                                },
                                padding: {
                                    top: 0,
                                    bottom: 20
                                }
                            }
                        },
                        x: {
                            ticks: {
                                color: '#64748b'
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            const timePeriodBtns = document.querySelectorAll('.time-period-btn');
            const chartSubtitle = document.querySelector('.chart-subtitle');

            timePeriodBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    timePeriodBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    const period = this.dataset.period;
                    jobStatsChart.data = chartData[period];
                    const maxVal = computeMaxVal(jobStatsChart.data.datasets);
                    jobStatsChart.options.scales.y.max = Math.ceil(maxVal * 1.2);
                    jobStatsChart.options.scales.y.ticks.stepSize = Math.ceil(jobStatsChart.options.scales.y.max / 5);
                    jobStatsChart.update();
                    chartSubtitle.textContent = dateRangeText[period];
                });
            });
        });
    </script>

<?php
    return ob_get_clean();
}

add_shortcode('employer_dashboard', 'employer_dashboard_shortcode');
?>