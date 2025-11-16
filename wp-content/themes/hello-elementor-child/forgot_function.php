<?php

add_action('wp_ajax_send_forgot_password', 'send_forgot_password');
add_action('wp_ajax_nopriv_send_forgot_password', 'send_forgot_password');


add_filter('wp_mail_from', function ($original_email_address) {
    return 'info@aaswjobstaging.com'; // Replace with your email
});

add_filter('wp_mail_from_name', function ($original_from_name) {
    return 'AASW JOBS'; // Replace with your name
});
function send_forgot_password()
{
    $email = sanitize_email($_POST['email'] ?? '');
    if (!is_email($email)) {
        wp_send_json_error(['message' => 'Invalid email address']);
    }

    $user = get_user_by('email', $email);
    if (!$user) {
        wp_send_json_error(['message' => 'No user found with this email']);
    }

    // Generate new password
    $new_password = wp_generate_password(12, false);
    wp_set_password($new_password, $user->ID);

    // Email subject and HTML content
$subject = 'Your AASW Jobs New Password';
$message = '
  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Your New Password</title>
    <style>
      @media only screen and (max-width: 600px) {
        .container { width: 100% !important; padding: 10px !important; }
        .password-box { font-size: 24px !important; letter-spacing: 3px !important; }
      }
    </style>
  </head>
  <body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f5f7fa; line-height: 1.6;">
    <div style="padding: 40px 20px; text-align: center;">
      <table role="presentation" class="container" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 8px 24px rgba(149,157,165,0.2);">
        
        <!-- Header -->
        <tr>
          <td align="center">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; font-family: Arial, sans-serif;">
              <tr>
                <td align="center">
                  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse;">
                    <tr>
                      <td style="padding: 15px;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                          <tr>
                            <!-- Logo -->
                            <td align="left" valign="middle">
                              <img src="https://aaswjobstaging.wpenginepowered.com/wp-content/themes/hello-elementor-child/images/aasw.png" alt="AASW Logo" width="180" style="display: block;">
                            </td>
                            <!-- Right side (blank) -->
                            <td align="right" valign="middle">
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

        <!-- Header Bar -->
        <tr>
          <td>
            <img src="https://aaswjobstaging.wpenginepowered.com/wp-content/themes/hello-elementor-child/images/header-bar.png" alt="header bar" style="width: 100%; display: block;" />
          </td>
        </tr>

        <!-- Email Content -->
        <tr>
          <td style="padding: 40px 30px; text-align: center;">
            <h2 style="color: #1a2a44; font-size: 24px; margin: 0 0 20px; font-weight: 600;">Your New Login Password</h2>
            <p style="font-size: 16px; color: #4a5c78; margin: 0 0 25px;">
              A new password has been generated for your account. Please use it to log in and change it immediately after signing in.
            </p>
            <div class="password-box" style="font-size: 36px; font-weight: 700; color: #0066A1; letter-spacing: 6px; background-color: #f8f9fa; padding: 15px 20px; border-radius: 8px; display: inline-block; margin: 20px 0;">
              ' . esc_html($new_password) . '
            </div>
            <p style="font-size: 14px; color: #4a5c78; margin: 20px 0 0;">
              Please do not share this password with anyone for your security.
            </p>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="background-color: #00608a; color: white; font-size: 11px; text-align: center; padding: 24px 20px;">
            <p style="margin: 0;">© ' . date('Y') . ' Australian Association of Social Workers. All rights reserved.</p>
            <p style="margin: 10px 0 0;">
              You are receiving this email to reset your password for the AASW Career Centre.
            </p>
            <p style="margin: 15px 0 0;">
              <a href="' . esc_url(get_permalink(get_page_by_path('privacy-policy'))) . '" style="color: white; font-weight: 700; text-decoration: none; margin: 0 8px;">Privacy Policy</a>
            </p>
          </td>
        </tr>
      </table>
    </div>
  </body>
  </html>';


  remove_all_filters('wp_mail');
  // remove_all_filters('wp_mail_from');
  //  remove_all_filters('wp_mail_from_name');
  remove_all_filters('wp_mail_content_type');
    $headers = ['Content-Type: text/html; charset=UTF-8'];

    if (wp_mail($email, $subject, $message, $headers)) {
        wp_send_json_success(['message' => 'A new password has been sent to your email.']);
    } else {
        wp_send_json_error(['message' => 'Failed to send email. Please try again later.']);
    }
}
