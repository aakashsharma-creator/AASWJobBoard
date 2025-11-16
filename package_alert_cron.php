<?php
// =============================================================
// WordPress Package Expiry Email Script (Fixed)
// URL: https://aaswjobstaging.wpenginepowered.com/package_alert_cron.php
// =============================================================

	
// Load WordPress environment
require_once __DIR__ . '/wp-load.php';

global $wpdb;

$table_name = $wpdb->prefix . 'wcpl_user_packages';
$today      = current_time('Y-m-d');

// Fetch all user packages
$packages = $wpdb->get_results("SELECT * FROM {$table_name}");

if (empty($packages)) {
    echo "No packages found in {$table_name}.";
    exit;
}

foreach ($packages as $package) {

    $user_id      = $package->user_id;
    $order_id     = $package->order_id;
    $product_id   = $package->product_id;
    $product_name = get_the_title($product_id);
    $duration     = intval($package->package_duration);

    // === Get WooCommerce order ===
    $order = wc_get_order($order_id);
    if (!$order) {
       // echo "<p style='color:#999;'>Order not found for package ID: {$package->id}</p>";
        continue;
    }

    // === Calculate expiry date ===
    $order_date  = date('Y-m-d', strtotime($order->get_date_created()));
    $expiry_date = date('Y-m-d', strtotime($order_date . " +{$duration} days"));

    // Calculate days left (rounded for safety)
    $days_left = round((strtotime($expiry_date) - strtotime($today)) / DAY_IN_SECONDS);

    // Safer alternative: also compare actual dates directly
    $target_dates = [
        date('Y-m-d', strtotime('+3 days', strtotime($today))),
        date('Y-m-d', strtotime('+7 days', strtotime($today))),
    ];

    // Debug info
    //echo "<p>Package ID: {$package->id}, User ID: {$user_id}, Expiry: {$expiry_date}, Days Left: {$days_left}</p>";

    // Check if the package expires in 3 or 7 days
    if (in_array($expiry_date, $target_dates) || in_array((int)$days_left, [3, 7])) {

        $user = get_userdata($user_id);
        if (!$user) {
            //echo "<p style='color:#999;'>User not found for ID: {$user_id}</p>";
            continue;
        }

        $to         = $user->user_email;
        $user_name  = $user->display_name;
        $blog_name  = get_bloginfo('name');
        $formatted_expiry = date('M j, Y', strtotime($expiry_date));
		
		$privacyPolicyLink = get_the_permalink(3);
		$renewButton = get_the_permalink(2669);
		$aboutusLink = get_the_permalink(257);

        // === Email Subject & Message ===
        $subject = "Your Job Listing Package will expire in {$days_left} days";
		
        /*$message = '
        <html>
        <head>
          <title>Job Listing Package Expiry Notice</title>
        </head>
        <body style="font-family:Arial, sans-serif; font-size:16px; background:#f9f9f9; padding:20px;">
          <div style="max-width:600px; margin:auto; background:#fff; padding:20px; border-radius:8px;">
            <h2 style="color:#333;">Your Job Listing Package is Expiring Soon!</h2>
            <p>Dear ' . esc_html($user_name) . ',</p>
            <p>Your job listing subscription <strong>(' . esc_html($product_name) . ')</strong> will expire on <strong>' . esc_html($formatted_expiry) . '</strong>.</p>
            <p>Please renew your package to keep it active on our website.</p>
            <p><a href="https://aaswjobstaging.wpenginepowered.com/" target="_blank" style="background:#0073aa; color:#fff; padding:10px 16px; text-decoration:none; border-radius:4px;">Renew Now</a></p>
            <p>Thank you,<br>' . esc_html($blog_name) . '</p>
          </div>
        </body>
        </html>';*/
		
		
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
							Your Job Listing Package is Expiring Soon!
						</h2>
						<div style="max-width:600px; margin:auto; background:#fff; padding:20px; border-radius:8px;">
							<p>Dear ' . esc_html($user_name) . ',</p>
							<p>Your job listing subscription <strong>(' . esc_html($product_name) . ')</strong> will expire on <strong>' . esc_html($formatted_expiry) . '</strong>.</p>
							<p>Please renew your package to keep it active on our website.</p>
							<p><a href="'.$renewButton.'" target="_blank" style="background:#0073aa; color:#fff; padding:10px 16px; text-decoration:none; border-radius:4px;">Renew Now</a></p>
							<p>Thank you,<br>' . esc_html($blog_name) . '</p>
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

        // === Log the action ===
        /*echo "<div style='background:#e9ffe9; padding:10px; margin:10px 0; border-left:4px solid #46b450;'>
            <strong>Email sent to:</strong> {$to}<br>
            <strong>Subject:</strong> {$subject}<br>
            <strong>Expiry Date:</strong> {$expiry_date}<br>
            <strong>Days Left:</strong> {$days_left}<br>
        </div>";*/
    }
}

//echo "<p><strong>Package expiry email check complete.</strong></p>";
