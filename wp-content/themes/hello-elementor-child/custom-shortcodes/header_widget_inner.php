<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit;
}

function my_header_inner_widget()
{
  ob_start();
  ?>

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      background-color: #f8f9fa;
      color: #333;
      line-height: 1.6;
    }

    .dashboard-container {
      width: 100%;
      max-width: 100%;
      margin: 0 auto;
    }

    .dashboard-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 16px 24px;
      background: white;
      border-bottom: 1px solid #e5e7eb;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
      position: relative;
      width: 100%;
    }

    /* Left Section */
    .header-left {
      display: flex;
      align-items: center;
      gap: 12px;
      flex: 1;
    }

    .dashboard-icon {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      color: #6b7280;
      transition: color 0.3s ease;
    }

    .dashboard-icon:hover {
      color: #374151;
    }

    .dashboard-icon img {
      width: 24px;
      height: 24px;
      object-fit: contain;
    }

    .dashboard-title {
      font-size: 24px;
      font-weight: 600;
      color: #1f2937;
      margin: 0;
    }

    /* Right Section */
    .header-right {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: 16px;
      flex: 1;
    }

    .notification-wrapper {
      position: relative;
    }

    .notification-icon {
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
      width: 48px;
      height: 48px;
      color: #6b7280;
      cursor: pointer;
      border-radius: 8px;
      transition: all 0.3s ease;
    }

    .notification-icon:hover {
      background-color: #f3f4f6;
      color: #374151;
    }

    .notification-icon img {
      width: 24px;
      height: 24px;
      object-fit: contain;
    }

    .notification-badge {
      position: absolute;
      top: 8px;
      right: 8px;
      background: #dc2626;
      color: white;
      font-size: 12px;
      font-weight: 600;
      min-width: 20px;
      height: 20px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0 6px;
      box-shadow: 0 2px 4px rgba(220, 38, 38, 0.3);
    }


    .employer-info {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 8px 16px;
      border-radius: 8px;
      transition: background-color 0.3s ease;
      cursor: pointer;
    }

    .employer-info:hover {
      background-color: #f3f4f6;
    }

    .employer-logo {
      width: 50px;
      height: 40px;
      border-radius: 3px;
      overflow: hidden;
      flex-shrink: 0;
      border: 1px solid #cccccc;
    }

    .employer-logo img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border:1px solid #231F20;
    }

    .employer-details {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }

    .employer-name {
      font-size: 16px;
      font-weight: 700;
      color: #00688F;
      margin: 0;
      white-space: nowrap;
    }

    .employer-type {
      font-size: 14px;
      font-weight: 400;
      color: #6B6B6B;
      margin: 0;
    }

    /* Mobile Menu Button */
    .mobile-menu-btn {
      display: none !important;
      flex-direction: column;
      justify-content: space-around;
      width: 32px;
      height: 32px;
      background: transparent;
      border: none;
      cursor: pointer;
      padding: 4px;
      border-radius: 4px;
      transition: background-color 0.3s ease;
      justify-self: end;
    }

    .mobile-menu-btn:hover {
      background-color: #f3f4f6;
    }

    .mobile-menu-btn span {
      width: 100%;
      height: 2px;
      background-color: #374151;
      border-radius: 1px;
      transition: all 0.3s ease;
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
      .dashboard-header {
        padding: 12px 20px;
      }

      .employer-name {
        font-size: 15px;
      }

      .employer-type {
        font-size: 13px;
      }
    }

    @media (max-width: 768px) {
      .dashboard-header {
        padding: 12px 16px;
      }

      .header-right {
        gap: 12px;
      }

      .employer-details {
        display: none;
      }

      .employer-info {
        padding: 8px;
      }

      .dashboard-title {
        font-size: 20px;
      }
    }

    @media (max-width: 640px) {
      .dashboard-header {
        padding: 10px 12px;
      }

      .header-left {
        gap: 8px;
      }

      .header-right {
        gap: 8px;
      }

      .dashboard-icon {
        width: 36px;
        height: 36px;
      }

      .dashboard-icon img {
        width: 20px;
        height: 20px;
      }

      .dashboard-title {
        font-size: 18px;
      }

      .notification-icon {
        width: 44px;
        height: 44px;
      }

      .notification-icon img {
        width: 20px;
        height: 20px;
      }

      .employer-logo {
        width: 36px;
        height: 36px;
      }

      .mobile-menu-btn {
        display: flex;
      }

      /* Hide employer info on very small screens */
      .employer-info {
        display: none;
      }
    }

    @media (max-width: 480px) {
      .dashboard-header {
        padding: 8px 12px;
      }

      .header-right {
        gap: 6px;
      }

      .dashboard-title {
        font-size: 16px;
      }

      .dashboard-icon {
        width: 32px;
        height: 32px;
      }

      .dashboard-icon img {
        width: 18px;
        height: 18px;
      }

      .notification-icon {
        width: 40px;
        height: 40px;
      }

      .notification-icon img {
        width: 18px;
        height: 18px;
      }

      .notification-badge {
        min-width: 18px;
        height: 18px;
        font-size: 11px;
        top: 6px;
        right: 6px;
      }
    }

    /* Animation for mobile menu */
    @media (max-width: 640px) {
      .mobile-menu-btn.active span:nth-child(1) {
        transform: rotate(45deg) translate(6px, 6px);
      }

      .mobile-menu-btn.active span:nth-child(2) {
        opacity: 0;
      }

      .mobile-menu-btn.active span:nth-child(3) {
        transform: rotate(-45deg) translate(6px, -6px);
      }
    }

    /* Hover effects */
    .dashboard-icon img,
    .notification-icon img {
      transition: transform 0.2s ease;
    }

    .dashboard-icon:hover img {
      transform: scale(1.05);
    }

    .notification-icon:hover img {
      transform: scale(1.1);
    }

    /* Focus states for accessibility */
    .notification-icon:focus,
    .employer-info:focus,
    .mobile-menu-btn:focus {
      outline: 2px solid #3b82f6;
      outline-offset: 2px;
    }

    /* Smooth transitions */
    * {
      transition: color 0.3s ease, background-color 0.3s ease, transform 0.2s ease;
    }

    /* Loading state for images */
    .dashboard-icon img,
    .notification-icon img,
    .employer-logo img {
      transition: opacity 0.3s ease;
    }

    .dashboard-icon img:not([src]),
    .notification-icon img:not([src]),
    .employer-logo img:not([src]) {
      opacity: 0;
    }

    /* High contrast mode support */
    @media (prefers-contrast: high) {
      .dashboard-header {
        border-bottom-width: 2px;
      }

      .notification-badge {
        border: 2px solid white;
      }
    }

    /* Reduced motion support */
    @media (prefers-reduced-motion: reduce) {
      * {
        transition: none !important;
        animation: none !important;
      }
    }


    .back-arrow {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 38px;
  height: 38px;

  cursor: pointer;

  transition: all 0.3s ease;
}

.back-arrow:hover {
  transform: scale(1.05);
}

.back-arrow img {
  width: 28px;
  height: 28px;
  object-fit: contain;
}
  </style>

<div class="dashboard-container">
  <header class="dashboard-header">
    <!-- Left Section -->
    <div class="header-left">
<?php
$current_page_slug = basename(get_permalink());

if ($current_page_slug !== 'locum-dashboard' && $current_page_slug !== 'employer-dashboard') :
?>
  <div class="back-arrow" onclick="window.history.back()" title="Go Back">
    <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/arrow-left.png" alt="Back">
  </div>
<?php endif; ?>

<div class="dashboard-icon">
  <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/Vector_dashboard.jpg" alt="Dashboard Icon">
</div>
<h1 class="dashboard-title">
  <?php echo wp_title('', false); ?>
</h1>

    <!-- Right Section -->
    <div class="header-right">
      <!-- Notifications -->
      <!-- <div class="notification-wrapper">
        <div class="notification-icon">
          <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/ic_bell.jpg" alt="Notification">
          <span class="notification-badge">5</span>
        </div>
      </div> -->

      <!-- Logged-in user info -->
<?php if (is_user_logged_in()):
      $current_user = wp_get_current_user();
      $user_name = $current_user->display_name;
      $user_roles = implode(', ', $current_user->roles);
      $user_avatar = get_avatar_url($current_user->ID, ['size' => 40]);

      global $wpdb;

      $email = $current_user->user_email; // current user email
  
      // Choose correct fields based on user role
      if (in_array('candidate', $current_user->roles)) {
        $upload_key = 'upload-2';
        $name_key = 'name-2';
      } else {
        $upload_key = 'upload-1';
        $name_key = 'name-1';
      }

      $query1 = $wpdb->prepare("
        SELECT 
            upload.meta_value AS upload_value,
            name.meta_value AS name_value
        FROM {$wpdb->prefix}frmt_form_entry_meta AS email
        INNER JOIN {$wpdb->prefix}frmt_form_entry_meta AS upload
            ON email.entry_id = upload.entry_id
        INNER JOIN {$wpdb->prefix}frmt_form_entry_meta AS name
            ON email.entry_id = name.entry_id
        WHERE email.meta_key = %s
          AND email.meta_value = %s
          AND upload.meta_key = %s
          AND name.meta_key = %s
    ", 'email-1', $email, $upload_key, $name_key);

      $result1 = $wpdb->get_row($query1);

      if ($result1) {
        $companyName = !empty($result1->name_value) ? esc_html($result1->name_value) : esc_html($user_name);
        $data = maybe_unserialize($result1->upload_value);
        $profile_image = !empty($data['file']['file_url']) ? $data['file']['file_url'] : $user_avatar;
      } else {
        $companyName = esc_html($user_name);
        $profile_image = $user_avatar;
      }

      // Set dashboard URL
      if (in_array('employer', $current_user->roles)) {
        $dashboard_url = site_url('/advertiser-dashboard/');
      } elseif (in_array('candidate', $current_user->roles)) {
        $dashboard_url = site_url('/locum-dashboard/');
      } else {
        $dashboard_url = home_url();
      }
      ?>
		<a href="<?php echo esc_url($dashboard_url); ?>" class="employer-info">
		  <div class="employer-logo">
			<img src="<?php echo esc_url($profile_image); ?>" alt="<?php echo esc_attr($companyName); ?>" />
		  </div>
		  <div class="employer-details">
			<h3 class="employer-name">
			  <?php echo esc_html($current_user->user_firstname ? $current_user->user_firstname : $current_user->display_name); ?>
			</h3>
			<?php
			if($user_roles == 'employer'){
				$user_roles = 'Advertiser';
			}else{
				$user_roles = ucfirst($user_roles);    
			}
			?>
			<p id="<?php echo $user_roles; ?>" class="employer-type"><?php echo $user_roles; ?></p>
		  </div>
		</a>
      <?php endif; ?>
    </div>

    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" aria-label="Toggle menu">
      <span></span>
      <span></span>
      <span></span>
    </button>
  </header>
</div>

  <?php
  return ob_get_clean();
}
add_shortcode('header_inner_widget', 'my_header_inner_widget');
?>