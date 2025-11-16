<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit;
}

function my_header_widget_main()
{
  ob_start();
?>
  <style>



.elementor-element.elementor-element-dc04147.elementor-widget-button {
    width: 125px !important;
    height: 48px !important;
      border-color: #5299b4!important;
}

.header-right-2 .elementor-element .elementor-button-icon{
  width: 45px !important;
  height: 44px !important;
}

.elementor-widget-button .elementor-button-prev{
  background-color: #ffffff !important;
}

.elementor-element.elementor-element-dc04147.elementor-widget-button a.elementor-button-prev:hover .elementor-button-text {
    color: #ffffff !important; /* White text on hover */
}

.header-right-2 .elementor-element .elementor-button-text{
  font-size: 14px;
  font-weight: 600;

}

.header-right-2 .elementor-element .elementor-button-text {
  margin-left: 6px !important;
}


    .dashboard-container-2 {
      width: 27%;
      max-width: 1400px;

    }

    .dashboard-header-2 {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 16px 0px;


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
    .header-right-2 {
      display: flex;
      align-items: center;
      justify-content: flex-start;
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
      position: relative;
    }

    .employer-info:hover {
      background-color: #f3f4f6;
    }

    .employer-logo {
      width: 40px;
      height: 40px;
      border-radius: 6px;
      overflow: hidden;
      flex-shrink: 0;
      border: 1px solid #e5e7eb;
    }

    .employer-logo img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border: 1px solid #231F20;
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

    .employer-dropdown {
      position: absolute;
      top: 100%;
      right: 0;
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 6px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      display: none;
      z-index: 10;
      min-width: 175px;
    }

    .employer-dropdown ul {
      list-style: none;
      margin: 0;
      padding: 8px 0;
    }

    .employer-dropdown ul li a {
      display: block;
      padding: 10px 16px;
      color: #374151;
      text-decoration: none;
      font-size: 14px;
      transition: background 0.2s, color 0.2s;
    }

    .employer-dropdown ul li a:hover {
      background: #f3f4f6;
      color: #111827;
    }

    /* Show dropdown on hover */
    .employer-info:hover .employer-dropdown,
    .employer-dropdown:hover {
      display: block;
    }

    /* Mobile Menu Button */
    .mobile-menu-btn {
      display: none;
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
      .dashboard-header-2 {
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
      .dashboard-header-2 {
        padding: 12px 16px;
      }

      .header-right-2 {
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
      .dashboard-header-2 {
        padding: 10px 12px;
      }

      .header-left {
        gap: 8px;
      }

      .header-right-2 {
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
      .dashboard-header-2 {
        padding: 8px 12px;
      }

      .header-right-2 {
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
      .dashboard-header-2 {
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
  </style>

  <div class="dashboard-container-2">
    <header class="dashboard-header-2">

      <!-- Right Section -->
      <div class="header-right-2">

        <?php 
          $current_user = wp_get_current_user();
          // echo '<!-- Roles: ' . implode(', ', $current_user->roles) . ' -->';
        if ($current_user->ID) {
		
		global $wpdb;

		$email = $current_user->user_email; // your target email  
          $default_avatar = 'https://secure.gravatar.com/avatar/7de710854f9417db2afcc6db0a265691dce05d42934195039d21ec88bef494a6?s=40&d=mm&r=g';


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
            if (!empty($data['file']['file_url'])) {
              $logourl = esc_url($data['file']['file_url']);
            } else {
              $logourl = esc_url($default_avatar);
            }
          } else {
            $logourl = esc_url($default_avatar);
            $companyName = esc_html($user_name);
          }
		
		
		
		  // Show role(s)
		  $userRole =  implode(', ', array_map('ucfirst', $current_user->roles));
        ?>

          <div class="employer-info">
            <div class="employer-logo">
             <img src="<?php echo esc_url($logourl); ?>" alt="<?php echo $companyName; ?>" />
            </div>
            <div class="employer-details">
              <?php if ($current_user->ID): ?>
                <h3 class="employer-name"><?php echo esc_html($current_user->user_firstname ? $current_user->user_firstname : $current_user->display_name); ?></h3>
                <p class="employer-type" id="<?php echo $userRole; ?>">
                  <?php
				  if($userRole == 'Employer'){
					echo 'Advertiser';
				  }else{
					  echo $userRole;
				  }
                  ?>
                </p>
              <?php else: ?>
                <h3 class="employer-name">Guest</h3>
                <p class="employer-type">Visitor</p>
              <?php endif; ?>
            </div>

            <!-- Hover Dropdown -->
            <?php if ($current_user->ID): ?>
              <div class="employer-dropdown">
                <ul>
                  <?php
                  $role_key = !empty($current_user->roles) ? $current_user->roles[0] : '';

                  if ($role_key === 'employer') {
                    $account_link = site_url('/advertiser-dashboard/');
                  } elseif ($role_key === 'candidate') {
                    $account_link = site_url('/locum-dashboard/');
                  } elseif ($role_key === 'administrator') {
                    $account_link = site_url('/wp-admin/');  
                  } else {
                    $account_link = site_url('/my-account');
                  }
                  ?>
                  <li><a href="<?php echo esc_url($account_link); ?>">My Account</a></li>
                  <li><a href="<?php echo esc_url(wp_logout_url(home_url())); ?>">Logout</a></li>
                </ul>
              </div>
            <?php endif; ?>

          </div>

        <?php } else { ?>
		
		
          <div class="elementor-element elementor-element-dc04147 elementor-tablet-align-left elementor-widget-tablet__width-auto elementor-hidden-mobile elementor-widget elementor-widget-button" data-id="dc04147" data-element_type="widget" data-widget_type="button.default">
            <a class="elementor-button-prev elementor-button-link elementor-size-sm" href="#elementor-action%3Aaction%3Dpopup%3Aopen%26settings%3DeyJpZCI6IjIzMjYiLCJ0b2dnbGUiOmZhbHNlfQ%3D%3D">
              <span class="elementor-button-content-wrapper">
                <span class="elementor-button-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="21" viewBox="0 0 20 21" fill="none">
                    <path d="M10 3.20831C9.83424 3.20831 9.67527 3.27416 9.55806 3.39137C9.44085 3.50858 9.375 3.66755 9.375 3.83331C9.375 3.99907 9.44085 4.15804 9.55806 4.27525C9.67527 4.39246 9.83424 4.45831 10 4.45831C10.7934 4.45831 11.579 4.61459 12.312 4.91821C13.0451 5.22183 13.7111 5.66686 14.2721 6.22788C14.8331 6.7889 15.2781 7.45493 15.5818 8.18793C15.8854 8.92094 16.0417 9.70658 16.0417 10.5C16.0417 11.2934 15.8854 12.079 15.5818 12.812C15.2781 13.545 14.8331 14.2111 14.2721 14.7721C13.7111 15.3331 13.0451 15.7781 12.312 16.0818C11.579 16.3854 10.7934 16.5416 10 16.5416C9.83424 16.5416 9.67527 16.6075 9.55806 16.7247C9.44085 16.8419 9.375 17.0009 9.375 17.1666C9.375 17.3324 9.44085 17.4914 9.55806 17.6086C9.67527 17.7258 9.83424 17.7916 10 17.7916C11.9339 17.7916 13.7885 17.0234 15.156 15.656C16.5234 14.2885 17.2917 12.4338 17.2917 10.5C17.2917 8.56611 16.5234 6.71145 15.156 5.34399C13.7885 3.97654 11.9339 3.20831 10 3.20831Z" fill="white"></path>
                    <path d="M8.72516 8.44164C8.61476 8.32316 8.55466 8.16646 8.55752 8.00454C8.56037 7.84262 8.62597 7.68813 8.74048 7.57362C8.85499 7.45911 9.00948 7.39352 9.17139 7.39066C9.33331 7.3878 9.49002 7.44791 9.6085 7.55831L12.1085 10.0583C12.2255 10.1755 12.2913 10.3343 12.2913 10.5C12.2913 10.6656 12.2255 10.8245 12.1085 10.9416L9.6085 13.4416C9.55128 13.503 9.48228 13.5523 9.40561 13.5865C9.32895 13.6206 9.24618 13.639 9.16226 13.6405C9.07835 13.6419 8.99499 13.6265 8.91716 13.5951C8.83934 13.5636 8.76865 13.5169 8.7093 13.4575C8.64995 13.3982 8.60316 13.3275 8.57173 13.2496C8.54029 13.1718 8.52486 13.0885 8.52634 13.0045C8.52782 12.9206 8.54619 12.8379 8.58035 12.7612C8.61451 12.6845 8.66376 12.6155 8.72516 12.5583L10.1585 11.125H3.3335C3.16774 11.125 3.00876 11.0591 2.89155 10.9419C2.77434 10.8247 2.7085 10.6657 2.7085 10.5C2.7085 10.3342 2.77434 10.1752 2.89155 10.058C3.00876 9.94082 3.16774 9.87497 3.3335 9.87497H10.1585L8.72516 8.44164Z" fill="white"></path>
                  </svg> </span>
                <span class="elementor-button-text">Login</span>
              </span>
            </a>
          </div>




          <div class="elementor-element elementor-element-dc04147 elementor-tablet-align-left elementor-widget-tablet__width-auto elementor-hidden-mobile elementor-widget elementor-widget-button" data-id="2333788" data-element_type="widget" data-widget_type="button.default">
            <a class="elementor-button-prev elementor-button-link elementor-size-sm" href="#elementor-action%3Aaction%3Dpopup%3Aopen%26settings%3DeyJpZCI6IjIzMjMiLCJ0b2dnbGUiOmZhbHNlfQ%3D%3D">
              <span class="elementor-button-content-wrapper">
                <span class="elementor-button-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="21" viewBox="0 0 20 21" fill="none">
                    <path d="M12.5002 3.83331C11.6161 3.83331 10.7683 4.1845 10.1431 4.80962C9.51802 5.43474 9.16683 6.28259 9.16683 7.16665C9.16683 8.0507 9.51802 8.89855 10.1431 9.52367C10.7683 10.1488 11.6161 10.5 12.5002 10.5C13.3842 10.5 14.2321 10.1488 14.8572 9.52367C15.4823 8.89855 15.8335 8.0507 15.8335 7.16665C15.8335 6.28259 15.4823 5.43474 14.8572 4.80962C14.2321 4.1845 13.3842 3.83331 12.5002 3.83331ZM12.5002 5.41665C12.73 5.41665 12.9575 5.46191 13.1699 5.54986C13.3822 5.6378 13.5751 5.76671 13.7376 5.92921C13.9001 6.09171 14.029 6.28463 14.117 6.49695C14.2049 6.70927 14.2502 6.93683 14.2502 7.16665C14.2502 7.39646 14.2049 7.62402 14.117 7.83634C14.029 8.04866 13.9001 8.24158 13.7376 8.40408C13.5751 8.56659 13.3822 8.69549 13.1699 8.78344C12.9575 8.87138 12.73 8.91665 12.5002 8.91665C12.2704 8.91665 12.0428 8.87138 11.8305 8.78344C11.6181 8.69549 11.4252 8.56659 11.2627 8.40408C11.1002 8.24158 10.9713 8.04866 10.8834 7.83634C10.7954 7.62402 10.7502 7.39646 10.7502 7.16665C10.7502 6.70252 10.9345 6.2574 11.2627 5.92921C11.5909 5.60102 12.036 5.41665 12.5002 5.41665ZM3.3335 6.33331V8.83331H0.833496V10.5H3.3335V13H5.00016V10.5H7.50016V8.83331H5.00016V6.33331H3.3335ZM12.5002 11.3333C10.2752 11.3333 5.8335 12.4416 5.8335 14.6666V17.1666H19.1668V14.6666C19.1668 12.4416 14.7252 11.3333 12.5002 11.3333ZM12.5002 12.9166C14.9752 12.9166 17.5835 14.1333 17.5835 14.6666V15.5833H7.41683V14.6666C7.41683 14.1333 10.0002 12.9166 12.5002 12.9166Z" fill="white"></path>
                  </svg> </span>
                <span class="elementor-button-text">Register</span>
              </span>
            </a>
          </div>

        <?php } ?>


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
add_shortcode('header_widget_main', 'my_header_widget_main');
?>