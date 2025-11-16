<?php
require __DIR__ . '/wp-load.php';

error_log("Job Alert Mailer started at " . date('Y-m-d H:i:s'));

set_time_limit(300);
ini_set('memory_limit', '512M');

// Fetch all job alerts
$alerts = get_posts([
  'post_type' => 'job_alert',
  'post_status' => 'publish',
  'posts_per_page' => -1,
]);

if (empty($alerts)) {
  echo "No job_alert posts found.<br>";
  error_log("No job_alert posts found.");
  exit;
}

foreach ($alerts as $alert) {
  $post_id = $alert->ID;
  $meta = get_post_meta($post_id);

  $email = $meta['alert_email'][0] ?? '';
  $frequency = $meta['alert_frequency'][0] ?? 'daily';

  if (!is_email($email)) {
    echo "Invalid email: $email<br>";
    continue;
  }

  // User info
  $user = get_user_by('email', $email);
  $user_name = $user ? $user->display_name : 'Member';
  $user_role = $user ? ucfirst($user->roles[0] ?? 'locum') : 'Candidate';

  $user_image = 'https://i.ibb.co/HNjj5Q3/profile-placeholder.png';
  if ($user) {
    $img_id = get_user_meta($user->ID, 'profile_image', true);
    if ($img_id) {
      $user_image = wp_get_attachment_url($img_id) ?: $user_image;
    }
  }

  // Categories
  $search_terms = maybe_unserialize($meta['alert_search_terms'][0] ?? '');
  $category_ids = $search_terms['categories'] ?? [];

  if (empty($category_ids)) {
    continue;
  }

  $category_names = [];
  foreach ($category_ids as $cat_id) {
    $term = get_term($cat_id, 'job_listing_category');
    if ($term && !is_wp_error($term)) {
      $category_names[] = $term->name;
    }
  }
  $categories_list = !empty($category_names) ? implode(', ', $category_names) : 'Job Alerts';

  // Fetch matching jobs
  $job_args = [
    'post_type' => 'job_listing',
    'post_status' => 'publish',
    'posts_per_page' => 10,
    'tax_query' => [
      [
        'taxonomy' => 'job_listing_category',
        'field' => 'term_id',
        'terms' => $category_ids,
      ],
    ],
  ];

  $jobs = get_posts($job_args);
  if (empty($jobs)) {
    continue;
  }

  // Build job rows dynamically
  $job_rows = '';
  foreach ($jobs as $job) {
    $title = esc_html(get_the_title($job->ID));
    $link = 'https://aaswjobstaging.wpenginepowered.com/list-job-custom/?selected_job=' . $job->ID;

    $company = esc_html(get_post_meta($job->ID, '_company_name', true)) ?: 'NA';
    $salary = esc_html(get_post_meta($job->ID, '_job_salary', true)) ?: 'Salary Not Provided';

    // Location
    $region_terms = wp_get_post_terms($job->ID, 'job_location_category', ['fields' => 'names']);
    $region_name = (!is_wp_error($region_terms) && !empty($region_terms))
      ? implode(', ', $region_terms)
      : 'Australia Wide';

    // Job type
    $type_terms = wp_get_post_terms($job->ID, 'job_type', ['fields' => 'names']);
    $employment_tag = (!is_wp_error($type_terms) && !empty($type_terms))
      ? $type_terms[0]
      : 'Full Time';

    // Logo
    $logo = get_the_post_thumbnail_url($job->ID, 'medium');
    if (!$logo) {
      $logo = 'https://aaswjobstaging.wpenginepowered.com/wp-content/themes/hello-elementor-child/images/noimage.jpg';
    }

    $job_rows .= "
        <tr>
          <td style=\"padding:20px; border-bottom:1px solid #eaeaea;\">
            <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
              <tr>
                <td width=\"60\" valign=\"top\">
                  <img src=\"$logo\" width=\"60\" style=\"display:block; border-radius:6px;\" alt=\"Company Logo\">
                </td>
                <td style=\"padding-left:15px; vertical-align:top;\">
                  <p style=\"margin:0; font-size:16px; font-weight:bold; color:#1c3c6e;\">$title</p>
                  <p style=\"margin:4px 0; font-size:14px; color:#555;\">$company</p>
                  <p style=\"margin:4px 0; font-size:13px; color:#777;\">Location: $region_name</p>
                  <p>
                    <span style=\"background:#0077b6; color:#fff; font-size:12px; padding:4px 8px; border-radius:12px;\">$employment_tag</span>
                  </p>
                </td>
                <td align=\"right\" valign=\"top\">
                  <p style=\"margin:0; font-weight:bold; color:#004b6b;\">$salary</p>
                 
<a href=" . esc_url($link) . " style=\"display:inline-block; margin-top:10px; background:#005f83; color:#fff; text-decoration:none; font-size:13px; padding:8px 16px; border-radius:4px;\">View Job</a>
                </td>
              </tr>
            </table>
          </td>
        </tr>";
  }

  // Final Email HTML (Table-based, email-safe)
  $message = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>AASW Job Matching Email</title>
    </head>
    <body style="margin:0; background:#f5f7fa; font-family:Arial, sans-serif;">
        <div style="padding:40px 20px; text-align:center;">
            <div style="width:100%; margin:0 auto; background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 8px 24px rgba(149,157,165,0.2);">

                <!-- Header -->
	<table width="100%" cellpadding="0" cellspacing="0" border="0" align="center" style="background-color:#ffffff; font-family:Arial, sans-serif;">
	<tr>
	<td align="center">
	<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
	<tr>
	<td style="padding:15px;">
	<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
	<!-- Logo -->
	<td align="left" valign="middle">
	<img src="https://aaswjobstaging.wpenginepowered.com/wp-content/themes/hello-elementor-child/images/aasw.png" alt="AASW Logo" width="180" style="display:block;">
	</td>
	
	<!-- User Info -->
	<td align="right" valign="middle">
<table cellpadding="0" cellspacing="0" border="0">
  <tr>
    <td align="right" style="padding-right:10px;">
      <p style="margin:0; font-weight:bold; color:#222; font-size:14px;">' . esc_html($user_name) . '</p>
      <p style="margin:0; color:#555; font-size:12px;">' . esc_html($email) . '</p>
    </td>
  </tr>
</table>

	</td>
	</tr>
	</table>
	</td>
	</tr>
	</table>
	</td>
	</tr>
	</table>

                <!-- Header Bar -->
                <div>
                    <img src="https://aaswjobstaging.wpenginepowered.com/wp-content/themes/hello-elementor-child/images/header-bar.png" alt="header bar" style="width:100%; display:block;" />
                </div>

                <!-- Main Content -->
                <div style="padding:30px;">
                    <h2 style="font-size:20px; font-weight:700; color:#1c3c6e; margin:0 0 10px; text-align:center;">
                        New Jobs Matching Your Preferences
                    </h2>
                    <p style="font-size:13px; color:#707583; text-align:center; margin:0 0 28px;">
                        We found ' . count($jobs) . ' new opportunities that match your career interests
                    </p>

                    <!-- Jobs Table -->
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f5f5f5; padding:30px 0;">
                        <tr>
                            <td align="center">
                                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#ffffff; border-radius:8px; overflow:hidden;">
                                    ' . $job_rows . '
                                </table>

                                <!-- Explore More Button -->
                                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:25px;">
                                    <tr>
                                        <td align="center">
                                            <a href="' . esc_url(home_url('/list-job-custom/')) . '" style="background:#a10028; color:#fff; text-decoration:none; font-weight:bold; font-size:15px; padding:12px 30px; border-radius:4px; display:inline-block;">
                                                Explore More Jobs
                                            </a>
                                        </td>
                                    </tr>
                                </table>

                                <p style="font-size:12px; color:#777; margin-top:25px; text-align:center;">
                                    Don’t miss out on your perfect career opportunity
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Footer -->
                <div style="background-color:#00608a; color:white; font-size:11px; text-align:center; padding:24px 20px;">
                    <p style="margin:0;">© ' . date('Y') . ' Australian Association of Social Workers. All rights reserved.</p>
                    <p style="margin:10px 0 0;">
                        You are receiving this email because you have subscribed to AASW Career Centre job alerts.
                    </p>
                    <p style="margin:15px 0 0;">
                        <a href="#" style="color:white; font-weight:700; text-decoration:none; margin:0 8px;">Update Preferences</a> |
                        <a href="#" style="color:white; font-weight:700; text-decoration:none; margin:0 8px;">Unsubscribe</a> |
                        <a href="#" style="color:white; font-weight:700; text-decoration:none; margin:0 8px;">Privacy Policy</a>
                    </p>
                </div>
            </div>
        </div>
    </body>
    </html>';

  remove_all_filters('wp_mail');
  // remove_all_filters('wp_mail_from');
  //  remove_all_filters('wp_mail_from_name');
  remove_all_filters('wp_mail_content_type');

  // Send email
  $subject = "New Job Alerts: " . $categories_list;

  add_filter('wp_mail_content_type', function () {
    return 'text/html';
  });
  $sent = wp_mail(
    'avik@inspiroworks.com', // Change to $email in production
    $subject,
    $message,
    ['From: AASW Career Centre <no-reply@' . parse_url(home_url(), PHP_URL_HOST) . '>']
  );
  remove_filter('wp_mail_content_type', 'text/html');


  if ($sent) {
    error_log("Job Alert Mailer: Email sent to $email");
  } else {
    error_log("Job Alert Mailer: wp_mail() failed for $email");
  }
  sleep(1);
}

// error_log("Job Alert Mailer finished at " . date('Y-m-d H:i:s'));
echo "Job Alert Mailer completed successfully!";