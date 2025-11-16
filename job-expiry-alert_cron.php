<?php
// =============================================================
// WordPress Job Expiry Email Alert Script
// URL: https://aaswjobstaging.wpenginepowered.com/job-expiry-alerts.php
// =============================================================

// Load WordPress environment
require_once __DIR__ . '/wp-load.php';

global $wpdb;

// === CONFIG ===
$post_type   = 'job_listing';
$meta_key1    = '__job_expires'; // WP Job Manager expiry meta key
$meta_key2    = 'job_expiry_date'; // WP Job Manager expiry meta key
$today       = current_time('Y-m-d');
$blogname    = get_bloginfo('name');

if(empty($meta_key1)){
	$meta_key1=$meta_key2;
}

// === Fetch all jobs with expiry dates ===
$jobs = $wpdb->get_results("
    SELECT p.ID, p.post_title, p.post_author, pm.meta_value AS expiry_date
    FROM {$wpdb->posts} p
    INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
    WHERE p.post_type = '{$post_type}'
      AND p.post_status = 'publish'
      AND (pm.meta_key = '{$meta_key1}')
      AND pm.meta_value != ''
");

if (empty($jobs)) {
    echo "No job listings found with expiry dates.";
    exit;
}

foreach ($jobs as $job) {

    // Handle both formats safely (e.g., 2025-11-18 or 18-11-2025)
    $raw_date = trim($job->expiry_date);
    if (strpos($raw_date, '-') !== false) {
        $parts = explode('-', $raw_date);
        if (strlen($parts[0]) === 4) {
            // Y-m-d format
            $expiry_date = date('Y-m-d', strtotime($raw_date));
        } else {
            // d-m-Y format
            $expiry_date = date('Y-m-d', strtotime(str_replace('-', '/', $parts[2] . '-' . $parts[1] . '-' . $parts[0])));
        }
    } else {
        $expiry_date = date('Y-m-d', strtotime($raw_date));
    }

    $days_left = (strtotime($expiry_date) - strtotime($today)) / DAY_IN_SECONDS;

    // Skip expired or invalid
    if ($days_left < 0) {
        continue;
    }

    // Only trigger at 3 or 7 days before expiry
    if (in_array($days_left, [3, 7])) {
        $user = get_userdata($job->post_author);
        if (!$user) continue;

        $to       = $user->user_email;
		$user_name  = $user->display_name;
        $subject  = "Your job listing \"{$job->post_title}\" will expire in {$days_left} days";
        $formatted_expiry = date('M j, Y', strtotime($expiry_date));
		$privacyPolicyLink = get_the_permalink(3);
		$aboutusLink = get_the_permalink(257);
		
        /*$message  = "
        <html>
        <body>
			<h2>Your Job Listing is Expiring Soon!</h2>
            <p>Hi {$user->display_name},</p>
            <p>Your job listing titled <strong>{$job->post_title}</strong> is set to expire on <strong>{$formatted_expiry}</strong>.</p>
            <p>Please renew or repost it soon to keep it active on our website. <strong><a href='https://aaswjobstaging.wpenginepowered.com/' target='_blank'>Click here</a></strong></p>
            <p>Thank you,<br>{$blogname}</p>
        </body>
        </html>
        ";*/

        $message = '
		<!DOCTYPE html>
		<html lang="en">
		<head>
			<meta charset="UTF-8" />
			<meta name="viewport" content="width=device-width, initial-scale=1" />
			<title>Job Listing Package Expiry Notice</title>
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
														  <p style="margin:0; font-weight:bold; color:#222; font-size:14px;">'.esc_html($user_name).'</p>
														  <p style="margin:0; color:#555; font-size:12px;">'.$to.'</p>
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
							Your Job Listing is Expiring Soon!
						</h2>
						<div style="max-width:600px; margin:auto; background:#fff; padding:20px; border-radius:8px;">
							<p>Hi ' . esc_html($user_name) . ',</p>
						<p>Your job listing titled <strong>'.$job->post_title.'</strong> is set to expire on <strong>'.$formatted_expiry.'</strong>.</p>
						<p>Please renew or repost it soon to keep it active on our website. <strong><a href="https://aaswjobstaging.wpenginepowered.com/" target="_blank">Click here</a></strong></p>
						<p>Thank you,<br>'.$blogname.'</p>
						</div>
					</div>

					<!-- Footer -->
					<div style="background-color:#00608a; color:white; font-size:11px; text-align:center; padding:24px 20px;">
						<p style="margin:0;">© 2025 - Australian Association of Social Workers. All rights reserved.</p>
						
						<p style="margin:15px 0 0;">
							<a href="'.site_url().'" target="_blank" style="color:white; font-weight:700; text-decoration:none; margin:0 8px;">Home</a> |
							<a href="'.$aboutusLink.'" target="_blank" style="color:white; font-weight:700; text-decoration:none; margin:0 8px;">About Us</a> |
							<a href="'.$privacyPolicyLink.'" target="_blank" style="color:white; font-weight:700; text-decoration:none; margin:0 8px;">Privacy Policy</a>
						</p> 
					</div>
				</div>
			</div>
		</body>
		</html>';

        // === Headers ===
        $headers = ['Content-Type: text/html; charset=UTF-8'];

		remove_all_filters('wp_mail');
		remove_all_filters('wp_mail_content_type');

        // === Send email ===
        wp_mail($to, $subject, $message, $headers);

        /*echo "<p>
            <strong>Email prepared for:</strong> {$to}<br>
            <strong>Job:</strong> {$job->post_title} (ID: {$job->ID})<br>
            <strong>Expiry Date:</strong> {$formatted_expiry}<br>
            <strong>Days Left:</strong> {$days_left}<br>
            <strong>Subject:</strong> {$subject}<br>
        </p><hr>";*/
		
		
    }
	
}

//echo "<p>Job expiry email check complete.</p>";