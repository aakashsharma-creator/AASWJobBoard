<?php
function hello_elementor_child_enqueue_styles()
{
    wp_enqueue_style('hello-elementor-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style(
        'hello-elementor-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('hello-elementor-style'),
        wp_get_theme()->get('Version')
    );
    wp_enqueue_style('shortcode-style', get_stylesheet_directory_uri() . '/css/shortcodes-custom.css');
}
add_action('wp_enqueue_scripts', 'hello_elementor_child_enqueue_styles');

// Loading fonts
function custom_enqueue_fonts()
{
    wp_enqueue_style('nunito-sans', 'https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700&display=swap', false);
}
add_action('wp_enqueue_scripts', 'custom_enqueue_fonts');

/**
 * Custom functions
 */

add_filter('imagick_thread_count', function () {
    return 1;
});

add_filter('wp_image_editors', function ($editors) {
    return array('WP_Image_Editor_GD');
});

// job package purchase multiple
add_filter('wcpl_job_package_is_sold_individually', '__return_false');

// Allow SVG + ICO upload
function custom_mime_types($mimes)
{
    $mimes['svg'] = 'image/svg+xml';
    $mimes['ico'] = 'image/x-icon'; // Add ICO support
    return $mimes;
}
add_filter('upload_mimes', 'custom_mime_types');

// Force WordPress file type check to allow ICO
function fix_wp_check_filetype_and_ext($data, $file, $filename, $mimes, $real_mime)
{
    $ext = pathinfo($filename, PATHINFO_EXTENSION);

    if ('ico' === strtolower($ext)) {
        $data['ext'] = 'ico';
        $data['type'] = 'image/x-icon';
        $data['proper_filename'] = $filename;
    }

    return $data;
}
add_filter('wp_check_filetype_and_ext', 'fix_wp_check_filetype_and_ext', 10, 5);

add_action('template_redirect', function () {
    // Check if user is logged in
    if (is_user_logged_in() && is_page('advertiser-registration')) {
        // Redirect logged-in users to homepage (change URL if needed)
        wp_redirect(home_url());
        exit;
    }

    if (is_user_logged_in() && is_page('locum-registration')) {
        // Redirect logged-in users to homepage (change URL if needed)
        wp_redirect(home_url());
        exit;
    }
});

// Includes other custom functions/files
include('includes/custom-functions.php');
include('includes/custom-post-types.php');
include('includes/theme-shortcodes.php');
include('includes/room-for-hire-form.php');
include('includes/room-list.php');
include('includes/room-inquiry-form.php');
include('includes/my_room_list.php');
include('includes/edit-room.php');


/**
 * Auto-remove existing Job Package from cart before adding a new one.
 */
add_action('woocommerce_before_calculate_totals', function ($cart) {
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }

    // Check if multiple job packages exist in cart
    $job_package_found = false;

    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        $product_id = $cart_item['product_id'];

        // Detect if this product is a Job Package (WooCommerce Paid Listings)
        if (get_post_meta($product_id, '_job_package', true) || get_post_meta($product_id, '_resume_package', true)) {
            if ($job_package_found) {
                // Remove duplicate package
                $cart->remove_cart_item($cart_item_key);
            } else {
                $job_package_found = true;
            }
        }
    }
}, 20);

/**
 * Always replace existing Job Package in cart with the latest one.
 */
add_filter('woocommerce_add_to_cart_validation', function ($passed, $product_id, $quantity) {
    // Check if product is a job package (WooCommerce Paid Listings)
    if (get_post_meta($product_id, '_job_package', true) || get_post_meta($product_id, '_resume_package', true)) {
        // Remove all other job packages before adding the new one
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $existing_id = $cart_item['product_id'];
            if (get_post_meta($existing_id, '_job_package', true) || get_post_meta($existing_id, '_resume_package', true)) {
                WC()->cart->remove_cart_item($cart_item_key);
            }
        }
    }
    return $passed;
}, 10, 3);


//
add_filter('woocommerce_add_to_cart_validation', 'allow_job_package_checkout', 10, 3);
function allow_job_package_checkout($passed, $product_id, $quantity)
{

    if ($product_id == 2257) {
        // If already in cart, prevent duplicate error
        foreach (WC()->cart->get_cart() as $cart_item_key => $values) {
            if ($values['product_id'] == $product_id) {
                return false; // Stops adding again, avoids error
            }
        }
    }
    return $passed;
}


// Flush cache
function my_custom_cache_flush_function()
{
    wp_cache_flush();
}
add_action('admin_init', 'my_custom_cache_flush_function'); // Example: Flush on admin init


// AVIK ===========================================================

function enqueue_datatables_scripts()
{
    wp_enqueue_style('datatables-css', 'https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css', array(), '1.13.8');
    wp_enqueue_script('datatables-js', 'https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js', array('jquery'), '1.13.8', true);

    wp_add_inline_script('datatables-js', "
    (function($){
        function initJobListTable(){
            $('table.list-job').each(function(){
                if(! $.fn.DataTable.isDataTable(this) && $(this).is(':visible')){
                    var headCount = $(this).find('thead tr th').length;
                    var bodyCount = $(this).find('tbody tr:first td').length;
                    if(headCount === bodyCount && headCount > 0){
                        $(this).DataTable({
                            responsive: true,
                            autoWidth: false,
                            pageLength: 10,
                            lengthMenu: [5, 10, 25, 50],
                            ordering: true,
                            searching: true,
                            language: {
                                search: '',
                                lengthMenu: '_MENU_',
                                info: 'Showing _START_ to _END_ of _TOTAL_ jobs',
                                paginate: { previous: '<<', next: '>>' }
                            }
                        });
                    } else {
                        console.warn('Skipping table due to column mismatch:', this);
                    }
                }
            });
        }

        $(document).ready(initJobListTable);

        if(window.elementorFrontend){
            $(window).on('elementor/frontend/init', function(){
                elementorFrontend.hooks.addAction('frontend/element_ready/global', initJobListTable);
            });
        }

        var observer = new MutationObserver(function(){
            initJobListTable();
        });
        observer.observe(document.body, { childList: true, subtree: true });
    })(jQuery);
    ");
}

add_action('wp_enqueue_scripts', 'enqueue_datatables_scripts');


/////////////////////////////////avik////////////////////////////////////////////////////////////////////

///////////////for track record of views

function log_job_view($job_id)
{
    if (!$job_id)
        return;

    global $wpdb;
    $table = $wpdb->prefix . 'job_views_log';
    $user_id = get_current_user_id();

    // Create table if not exists
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        job_id BIGINT(20) NOT NULL,
        user_id BIGINT(20) NULL,
        view_date DATE NOT NULL,
        view_count BIGINT(20) NOT NULL DEFAULT 1,
        PRIMARY KEY (id),
        UNIQUE KEY job_date (job_id, view_date)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Today's date
    $today = date('Y-m-d', current_time('timestamp'));

    // Try to get today's record
    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE job_id = %d AND view_date = %s",
        $job_id,
        $today
    ));

    if ($row) {
        // If exists, increment view_count
        $wpdb->update(
            $table,
            ['view_count' => $row->view_count + 1],
            ['id' => $row->id],
            ['%d'],
            ['%d']
        );
    } else {
        // Insert new row for today
        $wpdb->insert(
            $table,
            [
                'job_id' => $job_id,
                'user_id' => $user_id ?: null,
                'view_date' => $today,
                'view_count' => 1
            ],
            ['%d', '%d', '%s', '%d']
        );
    }

    // Always increment total views meta
    $views = get_post_meta($job_id, '_job_views', true);
    $views = ($views) ? (int) $views + 1 : 1;
    update_post_meta($job_id, '_job_views', $views);
}


// Handle AJAX logout
/*add_action('wp_ajax_custom_user_logout', 'custom_user_logout');
add_action('wp_ajax_nopriv_custom_user_logout', 'custom_user_logout');

function custom_user_logout() {

 // Ensure jQuery is loaded first
 //   wp_enqueue_script('jquery');
    check_ajax_referer('custom_logout_nonce', 'security');

    if (is_user_logged_in()) {
        wp_logout();
        wp_send_json_success([
            'message' => 'You have been logged out.',
            'redirect' => home_url(), // You can change this to a login page
        ]);
    } else {
        wp_send_json_error(['message' => 'User not logged in.']);
    }

    wp_die();
}
*/

function enqueue_custom_logout_script()
{
    wp_enqueue_script('jquery');

    // Enqueue your JS file
    wp_enqueue_script(
        'custom-logout',
        get_template_directory_uri() . '/js/custom-logout.js',
        array('jquery'),
        null,
        true
    );

    // Localize variables for use in JS
    wp_localize_script('custom-logout', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('custom_logout_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_custom_logout_script');


add_action('wp_ajax_custom_user_logout', 'custom_user_logout');
add_action('wp_ajax_nopriv_custom_user_logout', 'custom_user_logout');

function custom_user_logout()
{
    check_ajax_referer('custom_logout_nonce', 'security');

    if (is_user_logged_in()) {
        wp_logout();
        wp_send_json_success([
            'message' => 'You have been logged out.',
            'redirect' => home_url(),
        ]);
    } else {
        wp_send_json_error(['message' => 'User not logged in.']);
    }

    wp_die();
}

add_action('wp_ajax_download_job_attachments', 'download_job_attachments');
add_action('wp_ajax_nopriv_download_job_attachments', 'download_job_attachments');

function download_job_attachments()
{
    if (empty($_GET['post_id'])) {
        wp_die('No post ID.');
    }

    $post_id = intval($_GET['post_id']);
    $attachment_urls = get_post_meta($post_id, '_job_attachments', true);
    $attachment_urls = json_decode($attachment_urls, true);

    if (empty($attachment_urls)) {
        wp_die('No attachments found.');
    }

    $zip = new ZipArchive();
    $zip_name = tempnam(sys_get_temp_dir(), 'attachments') . '.zip';

    if ($zip->open($zip_name, ZipArchive::CREATE) !== TRUE) {
        wp_die('Cannot create zip file.');
    }

    foreach ($attachment_urls as $file) {
        $file_path = str_replace(home_url('/'), ABSPATH, $file);
        if (file_exists($file_path)) {
            $zip->addFile($file_path, basename($file_path));
        }
    }

    $zip->close();

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="attachments.zip"');
    header('Content-Length: ' . filesize($zip_name));
    readfile($zip_name);
    unlink($zip_name);
    exit;
}

function get_company_info_by_post($post_id)
{
    if (empty($post_id)) {
        return false;
    }

    global $wpdb;

    // Get post author
    $author_id = get_post_field('post_author', $post_id);
    if (empty($author_id)) {
        return false;
    }

    $user_info = get_userdata($author_id);
    if (!$user_info) {
        return false;
    }

    $email = $user_info->user_email;
    $default_avatar = 'https://secure.gravatar.com/avatar/7de710854f9417db2afcc6db0a265691dce05d42934195039d21ec88bef494a6?s=40&d=mm&r=g';

    // Query Forminator meta for company logo + name
    $query = $wpdb->prepare("
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
    ", 'email-1', $email, 'upload-1', 'name-1');

    $result = $wpdb->get_row($query);

    // Default values
    $company_logo = $default_avatar;
    $company_name = $user_info->display_name;

    // Process result if found
    if ($result) {
        $company_name = !empty($result->name_value) ? esc_html($result->name_value) : esc_html($user_info->display_name);
        $data = maybe_unserialize($result->upload_value);
        if (!empty($data['file']['file_url'])) {
            $company_logo = esc_url($data['file']['file_url']);
        }
    } else {
        // Optional fallback: use post thumbnail if company logo not found
        $post_thumbnail = get_the_post_thumbnail_url($post_id, 'thumbnail');
        if ($post_thumbnail) {
            $company_logo = $post_thumbnail;
        }
    }

    return [
        'logo' => $company_logo,
        'name' => $company_name,
        'email' => $email,
        'author_id' => $author_id,
    ];
}

include('custom-shortcodes/locum_profile.php');
include('custom-shortcodes/locum_profile_desc.php');


include('custom-shortcodes/list-job.php');

include('custom-shortcodes/search-job.php');
include('custom-shortcodes/saved-jobs.php');
include('custom-shortcodes/saved-locum.php');
include('custom-shortcodes/employer-dashboard.php');
include('custom-shortcodes/search-job-home_widget.php');
include('custom-shortcodes/recent-jobs.php');
include('custom-shortcodes/search-locum.php');
include('custom-shortcodes/header_widget_inner.php');
include('custom-shortcodes/header_widget_main.php');

include('custom-shortcodes/change_password.php');
include('custom-shortcodes/company_profile.php');

include('custom-shortcodes/job_details.php');
include('custom-shortcodes/locum-dashboard.php');

include('login_function.php');
include('forgot_function.php');
include('delete_user.php');
include('custom-shortcodes/company_profile_desc.php');


include('includes/custom_validation_functions.php'); // Added by aakash


// added on 20 Oct 2025 by aakash
add_filter('wc_price_args', function ($args) {
    $args['decimals'] = 0; // Removes decimals globally
    return $args;
});

// 🔒 Disable WP Job Manager activation email (runs late enough)
add_action('init', function () {
    remove_action('job_manager_user_activated', 'job_manager_user_activated_notification');
}, 999);

// 🔒 Disable default WordPress user notification emails
add_filter('wp_new_user_notification_email', '__return_false');
add_filter('wp_new_user_notification_email_admin', '__return_false');
add_filter('wp_send_new_user_notifications', '__return_false');

remove_action('register_new_user', 'wp_send_new_user_notifications');
remove_action('edit_user_created_user', 'wp_send_new_user_notifications');

// 🔒 Block any remaining unwanted emails
add_filter('wp_mail', function ($args) {
    if (
        isset($args['subject']) && (
            stripos($args['subject'], 'Account Activated') !== false ||
            stripos($args['subject'], 'Your account has been created') !== false
        )
    ) {
        return false;
    }
    return $args;
});


// custom function
// Custom registration welcome email
add_action('user_register', 'custom_user_welcome_email', 10, 1);
function custom_user_welcome_email($user_id)
{
    $user = get_user_by('id', $user_id);
    if (!$user)
        return;

    $site_name = get_bloginfo('name');
    $site_url = home_url();
    $login_page = wp_login_url();

    $user_first_name = get_user_meta($user_id, 'first_name', true);
    $greeting_name = !empty($user_first_name) ? $user_first_name : $user->user_login;

    // Generate password reset link
    //  $key = get_password_reset_key($user);
    //  $pass_reset_link = $key ? network_site_url("wp-login.php?action=rp&key={$key}&login=" . rawurlencode($user->user_login), 'login') : '#';

    $subject = "Welcome to {$site_name}! Your Account is Ready";

    //  HTML Email Template (same design as forgot password)
    $message = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <title>Welcome to ' . esc_html($site_name) . '</title>
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
            <h2 style="color: #1a2a44; font-size: 24px; margin: 0 0 20px; font-weight: 600;">Welcome, ' . esc_html($greeting_name) . '!</h2>
            <p style="font-size: 16px; color: #4a5c78; margin: 0 0 25px;">
                We are excited to have you on board at <strong>' . esc_html($site_name) . '</strong>! Your account has been successfully created. You can now log in to access your dashboard and manage your profile.
            </p>
            <p style="font-size: 16px; color: #4a5c78; margin: 20px 0;">
                Click below to login and get started:
            </p>
            <p style="font-size: 14px; color: #4a5c78; margin: 5px 0 0;">
                Username: <strong>' . esc_html($user->user_login) . '</strong>
            </p>
            <a href="' . esc_url($site_url) . '" 
                style="display: inline-block; padding: 12px 30px; background-color: #0066A1; color: #ffffff; font-size: 16px; font-weight: 600; border-radius: 6px; text-decoration: none;" target="_blank">
                Go to Login
            </a>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="background-color: #00608a; color: white; font-size: 11px; text-align: center; padding: 24px 20px;">
            <p style="margin: 0;">© ' . date('Y') . ' Australian Association of Social Workers. All rights reserved.</p>
            <p style="margin: 10px 0 0;">
                You are receiving this email because you signed up for an account with the AASW Career Centre.
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
    remove_all_filters('wp_mail_content_type');

    $headers = ['Content-Type: text/html; charset=UTF-8'];

    wp_mail($user->user_email, $subject, $message, $headers);
}

// Helper function to find user ID from submission data (might be needed for the password reset link)
if (!function_exists('forminator_find_user_id_from_submitted_data')) {
    function forminator_find_user_id_from_submitted_data($submitted_data)
    {
        if (isset($submitted_data['registration-username'])) {
            $user = get_user_by('login', $submitted_data['registration-username']);
            if ($user) {
                return $user->ID;
            }
        }
        return false;
    }
}

// end of added on 20 Oct 2025 by aakash
?>

<?php
// Make sure to add this to your CHILD THEME's functions.php file

// --- Enqueue Font Awesome ---
function my_theme_enqueue_scripts()
{
    wp_enqueue_style(
        'font-awesome-6',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css',
        array(),
        '6.5.2'
    );
}
add_action('wp_enqueue_scripts', 'my_theme_enqueue_scripts');


// --- Global variable to store registration errors ---
global $reg_form_errors;
$reg_form_errors = array();

/**
 * PART 1: The Form Processing Function (UPDATED)
 */
function my_custom_registration_processor()
{

    global $reg_form_errors;

    if (isset($_POST['hire_reg_submit']) && isset($_POST['hire_reg_nonce'])) {

        if (!wp_verify_nonce($_POST['hire_reg_nonce'], 'hire_reg_action')) {
            die('Security check failed!');
        }

        $fields = array(
            'name' => $_POST['reg_name'],
            'email' => $_POST['reg_email'],
            'password' => $_POST['reg_password'],
            'confirm' => $_POST['reg_confirm_password'],
        );

        // 1. Name Validation
        if (empty($fields['name'])) {
            $reg_form_errors['name'] = 'This field is required.';
        }

        // 2. Email Validation
        if (empty($fields['email'])) {
            $reg_form_errors['email'] = 'This field is required.';
        } elseif (!is_email($fields['email'])) {
            $reg_form_errors['email'] = 'Please input a valid email.';
        } elseif (email_exists($fields['email'])) {
            $reg_form_errors['email'] = 'This email address is already in use.';
        }

        // 3. Password Validation
        if (empty($fields['password'])) {
            $reg_form_errors['password'] = 'Your password is required.';
        } else {
            $has_error = false;
            if (strlen($fields['password']) < 8)
                $has_error = true;
            if (!preg_match('/[A-Z]/', $fields['password']))
                $has_error = true;
            if (!preg_match('/[a-z]/', $fields['password']))
                $has_error = true;
            if (!preg_match('/[0-9]/', $fields['password']))
                $has_error = true;
            if (!preg_match('/[\W_]/', $fields['password']))
                $has_error = true;

            if ($has_error) {
                $reg_form_errors['password'] = 'Password must be at least 8 characters long, include uppercase, lowercase, number, and special character.';
            }
        }

        // 4. Confirm Password Validation
        if (empty($fields['confirm'])) {
            $reg_form_errors['confirm_password'] = 'You must confirm your chosen password.';
        } elseif ($fields['password'] !== $fields['confirm']) {
            $reg_form_errors['confirm_password'] = 'The two passwords do not match.';
        }

        // 5. Terms & Conditions Validation
        if (empty($_POST['reg_terms'])) {
            $reg_form_errors['terms'] = 'You must accept the Terms & Conditions and Privacy Policy.';
        }

        // --- USER CREATION ---

        if (empty($reg_form_errors)) {

            $clean_name = sanitize_text_field($fields['name']);
            $clean_email = sanitize_email($fields['email']);

            // Split name into first and last
            $name_parts = explode(' ', $clean_name, 2);
            $first_name = $name_parts[0];
            $last_name = isset($name_parts[1]) ? $name_parts[1] : '';

            $user_data = array(
                'user_login' => $clean_email, // Use EMAIL as the unique username
                'user_email' => $clean_email,
                'user_pass' => $fields['password'],
                'role' => 'RoomsForHire', // REMINDER: Make sure this custom role is registered!
                'display_name' => $clean_name,
                'nickname' => $clean_name,
                'first_name' => $first_name,
                'last_name' => $last_name
            );

            $user_id = wp_insert_user($user_data);

            if (is_wp_error($user_id)) {
                $reg_form_errors['general'] = 'An unknown error occurred. Please try again.';
            } else {
                $user = get_user_by('ID', $user_id);

                if ($user && !in_array('rooms_for_hire', (array) $user->roles, true)) {
                    $user->add_role('rooms_for_hire');
                }

                // --- (NEW) SEND WELCOME EMAIL ---
                $site_name = get_bloginfo('name');
                $site_url = home_url();
                $domain = wp_parse_url($site_url, PHP_URL_HOST); // Gets example.com

                $to = $clean_email;
                $subject = "Welcome to " . $site_name . "!";

                $message = "<html><body>";
                $message .= "<p>Hi " . esc_html($clean_name) . ",</p>";
                $message .= "<p>Welcome! Your account has been successfully created at " . esc_html($site_name) . ".</p>";
                $message .= "<p>You can now log in using your email and the password you set.</p>";
                $message .= "<p>Regards,<br>";
                $message .= "The " . esc_html($site_name) . " Team</p>";
                $message .= "</body></html>";

                $headers = array('Content-Type: text/html; charset=UTF-8', 'From: ' . $site_name . ' <noreply@' . $domain . '>');

                wp_mail($to, $subject, $message, $headers);
                // --- (END) SEND WELCOME EMAIL ---


                // --- AUTO-LOGIN & REDIRECT ---

                $username_for_login = $clean_email; // Login with the email
                wp_set_current_user($user_id, $username_for_login);
                wp_set_auth_cookie($user_id, true, false);
                do_action('wp_login', $username_for_login, get_user_by('id', $user_id));



                wp_redirect(home_url('/room-dashboard'));
                exit;
            }
        }
    }
}
add_action('init', 'my_custom_registration_processor');


/**
 * PART 2: The Form Display Shortcode
 */
function my_custom_registration_form_display()
{

    // --- [The 'is_user_logged_in' check is unchanged] ---
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        ob_start(); ?>
        <div class="hire-registration-form-wrapper"
            style="text-align: center; padding: 20px; border: 1px solid #eee; border-radius: 4px;">
            <h3 class="form-header-title" style="font-size: 1.5rem; margin-bottom: 15px; font-weight: 700; text-align: center;">
                You are already logged in</h3>
            <p style="font-size: 1rem; margin-bottom: 20px; color: #333; text-align: center;">
                You are currently logged in as <strong><?php echo esc_html($current_user->display_name); ?></strong>.
            </p>
            <p style="text-align: center;">
                <a href="<?php echo esc_url(wp_logout_url(get_permalink())); ?>"
                    style="background-color: #a4263a; color: #ffffff; text-decoration: none; padding: 10px 20px; border-radius: 4px; font-weight: 600; display: inline-block;">
                    Log Out
                </a>
            </p>
        </div>
    <?php
        return ob_get_clean();
    }

    global $reg_form_errors;
    if (!is_array($reg_form_errors)) {
        $reg_form_errors = array();
    }

    $posted_name = isset($_POST['reg_name']) ? sanitize_text_field($_POST['reg_name']) : '';
    $posted_email = isset($_POST['reg_email']) ? sanitize_email($_POST['reg_email']) : '';

    ob_start(); ?>

    <style>
        .hire-registration-form-wrapper {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            max-width: 500px;
            margin: 20px auto;
        }

        .hire-registration-form-wrapper .form-header-title {
            font-size: 2.25rem;
            font-weight: 700;
            color: #002c5f;
            margin-bottom: 8px;
            text-align: center;
        }

        .hire-registration-form-wrapper .form-header-subtitle {
            font-size: 1rem;
            color: #00688F;
            margin-bottom: 24px;
            text-align: center;
        }

        .hire-registration-form {
            width: 100%;
        }

        .hire-registration-form .form-row {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .hire-registration-form .form-row label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }

        .hire-registration-form .form-row .required-star {
            color: #d9534f;
        }

        .hire-registration-form .form-row input[type="email"],
        .hire-registration-form .form-row input[type="text"],
        .hire-registration-form .form-row input[type="password"] {
            width: 100%;
            height: 48px;
            padding: 10px 15px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 1rem;
        }

        .hire-registration-form .password-input-container {
            position: relative;
            display: block;
            line-height: 1;
        }

        .hire-registration-form .password-input-container input {
            padding-right: 45px !important;
        }

        .hire-registration-form .toggle-password-eye {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            user-select: none;
            font-size: 1.1rem;
            color: #00688F;
            line-height: 1;
            opacity: 0.7;
            width: 20px;
            text-align: center;
        }

        .hire-registration-form .toggle-password-eye:hover {
            opacity: 1;
        }

        .hire-registration-form .form-error {
            display: block;
            margin-top: 6px;
            font-size: 0.9rem;
            color: #d9534f;
        }

        .hire-registration-form .form-helper-text {
            display: block;
            margin-top: 6px;
            font-size: 0.9rem;
            color: #6c757d;
        }

        .hire-registration-form .form-row.has-error input {
            border-color: #d9534f;
        }

        .hire-registration-form .form-row.has-error .form-helper-text {
            color: #d9534f;
        }

        .hire-registration-form .form-submit-row {
            text-align: right;
            margin-top: 2rem;
        }

        .hire-registration-form input[type="submit"] {
            background-color: #a4263a;
            color: #ffffff;
            border: none;
            padding: 12px 24px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .hire-registration-form input[type="submit"]:hover {
            background-color: #8a2030;
        }

        /* --- CSS for the terms label --- */
        .hire-registration-form .terms-label {
            display: flex;
            align-items: center;
            font-weight: 400;
            font-size: 14px;
            cursor: pointer;
        }

        .hire-registration-form .terms-label input[type="checkbox"] {
            width: auto;
            height: auto;
            margin-right: 10px;
        }

        .hire-registration-form .terms-label a {
            color: #005a87;
            text-decoration: none;
        }

        .hire-registration-form .terms-label a:hover {
            text-decoration: underline;
        }
    </style>

    <div class="hire-registration-form-wrapper">
        <h2 class="form-header-title">Create Your Account</h2>
        <p class="form-header-subtitle">Setup your login details to securely access your Dashboard.</p>

        <?php if (!empty($reg_form_errors['general'])): ?>
            <div class="form-row">
                <span class="form-error"><?php echo esc_html($reg_form_errors['general']); ?></span>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="hire-registration-form" novalidate>
            <div class="form-row <?php echo !empty($reg_form_errors['name']) ? 'has-error' : ''; ?>">
                <label for="reg_name">Name <span class="required-star">*</span></label>
                <input type="text" name="reg_name" id="reg_name" placeholder="Enter your full name"
                    value="<?php echo esc_attr($posted_name); ?>" required>
                <?php if (!empty($reg_form_errors['name'])): ?>
                    <span class="form-error"><?php echo esc_html($reg_form_errors['name']); ?></span>
                <?php endif; ?>
            </div>

            <div class="form-row <?php echo !empty($reg_form_errors['email']) ? 'has-error' : ''; ?>">
                <label for="reg_email">Email Address <span class="required-star">*</span></label>
                <input type="email" name="reg_email" id="reg_email" placeholder="Enter email address"
                    value="<?php echo esc_attr($posted_email); ?>" required>
                <?php if (!empty($reg_form_errors['email'])): ?>
                    <span class="form-error"><?php echo esc_html($reg_form_errors['email']); ?></span>
                <?php endif; ?>
            </div>

            <div class="form-row <?php echo !empty($reg_form_errors['password']) ? 'has-error' : ''; ?>">
                <label for="reg_password">Password <span class="required-star">*</span></label>
                <span class="password-input-container">
                    <input type="password" name="reg_password" id="reg_password" placeholder="Enter password" required
                        autocomplete="new-password">
                    <i class="fa-regular fa-eye toggle-password-eye"></i>
                </span>
                <?php if (!empty($reg_form_errors['password'])): ?>
                    <span class="form-error"><?php echo esc_html($reg_form_errors['password']); ?></span>
                <?php else: ?>
                    <span class="form-helper-text">Password must be at least 8 characters long, include uppercase, lowercase,
                        number, and special character.</span>
                <?php endif; ?>
            </div>

            <div class="form-row <?php echo !empty($reg_form_errors['confirm_password']) ? 'has-error' : ''; ?>">
                <label for="reg_confirm_password">Confirm Password <span class="required-star">*</span></label>
                <span class="password-input-container">
                    <input type="password" name="reg_confirm_password" id="reg_confirm_password"
                        placeholder="Confirm password" required autocomplete="new-password">
                    <i class="fa-regular fa-eye toggle-password-eye"></i>
                </span>
                <?php if (!empty($reg_form_errors['confirm_password'])): ?>
                    <span class="form-error"><?php echo esc_html($reg_form_errors['confirm_password']); ?></span>
                <?php endif; ?>
            </div>

            <div class="form-row <?php echo !empty($reg_form_errors['terms']) ? 'has-error' : ''; ?>">
                <label class="terms-label" for="reg_terms">
                    <input type="checkbox" name="reg_terms" id="reg_terms" value="1" required>
                    <span>
                        I accept the <a href="https://aaswjobstaging.wpenginepowered.com/terms-conditions"
                            target="_blank">Terms & Conditions</a> and <a
                            href="https://aaswjobstaging.wpenginepowered.com/privacy-policy/" target="_blank">Privacy
                            Policy</a> of the AASW Career Centre <span class="required-star">*</span>
                    </span>
                </label>
                <?php if (!empty($reg_form_errors['terms'])): ?>
                    <span class="form-error"><?php echo esc_html($reg_form_errors['terms']); ?></span>
                <?php endif; ?>
            </div>

            <?php wp_nonce_field('hire_reg_action', 'hire_reg_nonce'); ?>

            <div class="form-submit-row">
                <input type="submit" name="hire_reg_submit" value="Continue">
            </div>
        </form>
    </div>

    <script>
        jQuery(document).ready(function($) {

            // --- [The JavaScript is unchanged] ---

            // --- Password Toggle ---
            $('.toggle-password-eye').on('click', function() {
                $(this).toggleClass('fa-eye fa-eye-slash');
                var $input = $(this).prev('input');
                var type = $input.attr('type') === 'password' ? 'text' : 'password';
                $input.attr('type', type);
            });

            // --- Validation Helpers ---
            function showError($input, message) {
                const $row = $input.closest('.form-row');
                $row.addClass('has-error');

                let $elementToInsertAfter = $input;
                if ($input.parent().hasClass('password-input-container')) {
                    $elementToInsertAfter = $input.parent();
                }

                let $err = $row.find('.form-error');
                if ($err.length === 0) {
                    $err = $('<span class="form-error js-error"></span>').insertAfter($elementToInsertAfter);
                }
                $err.text(message);
            }

            function clearError($input) {
                const $row = $input.closest('.form-row');
                $row.removeClass('has-error');
                // Only remove JavaScript-added errors, not PHP errors
                $row.find('.form-error.js-error').remove();
            }

            // --- Validators ---
            function validateName($input) {
                const val = $.trim($input.val());
                clearError($input);
                if (!val) return showError($input, 'This field is required.'), false;
                if (val.length < 2) return showError($input, 'Please enter your full name.'), false;
                return true;
            }

            function validateEmail($input) {
                const val = $.trim($input.val());
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                clearError($input);
                if (!val) return showError($input, 'This field is required.'), false;
                if (!re.test(val)) return showError($input, 'Please enter a valid email.'), false;
                return true;
            }

            function validatePassword($input) {
                const val = $input.val();
                clearError($input);
                if (!val) return showError($input, 'Password is required.'), false;
                if (val.length < 8 || !/[A-Z]/.test(val) || !/[a-z]/.test(val) || !/[0-9]/.test(val) || !/[\W_]/.test(val))
                    return showError($input, 'Password must include uppercase, lowercase, number, and special character.'), false;
                return true;
            }

            function validateConfirmPassword($confirm, $password) {
                const val = $confirm.val();
                const pass = $password.val();
                clearError($confirm);
                if (!val) return showError($confirm, 'Please confirm your password.'), false;
                if (val !== pass) return showError($confirm, 'Passwords do not match.'), false;
                return true;
            }

            // --- Terms Validator ---
            function validateTerms($input) {
                const $row = $input.closest('.form-row');
                $row.removeClass('has-error');
                $row.find('.form-error.js-error').remove(); // Remove only JS-added errors

                if (!$input.is(':checked')) {
                    $row.addClass('has-error');
                    // Only add an error if one doesn't already exist from PHP
                    if ($row.find('.form-error').length === 0) {
                        $('<span class="form-error js-error" style="display: block; margin-top: 6px;">You must accept the Terms & Conditions and Privacy Policy.</span>').insertAfter($input.closest('label'));
                    }
                    return false;
                }
                return true;
            }

            // --- Bind Live Validation ---
            const $form = $('.hire-registration-form');
            const $name = $('#reg_name');
            const $email = $('#reg_email');
            const $password = $('#reg_password');
            const $confirm = $('#reg_confirm_password');
            const $terms = $('#reg_terms');

            $name.on('input blur', function() {
                validateName($(this));
            });
            $email.on('input blur', function() {
                validateEmail($(this));
            });
            $password.on('input blur', function() {
                validatePassword($(this));
                validateConfirmPassword($confirm, $(this));
            });
            $confirm.on('input blur', function() {
                validateConfirmPassword($(this), $password);
            });
            $terms.on('change blur', function() {
                validateTerms($(this));
            });

            // --- On Submit ---
            $form.on('submit', function(e) {
                // Use & to run all validation checks, not &&
                const valid = validateName($name) &
                    validateEmail($email) &
                    validatePassword($password) &
                    validateConfirmPassword($confirm, $password) &
                    validateTerms($terms);

                if (!valid) {
                    e.preventDefault();
                    // Scroll to the first error
                    const $firstError = $form.find('.has-error').first();
                    if ($firstError.length) {
                        $('html, body').animate({
                            scrollTop: $firstError.offset().top - 100
                        }, 300);
                    }
                }
            });
        });
    </script>
<?php
    return ob_get_clean();
}
add_shortcode('hire_registration_form', 'my_custom_registration_form_display');


function restrict_pages_by_role()
{
    if (is_admin())
        return; // Skip admin area

    // Define your slug-to-role mapping
    $restricted_pages = array(
        'search-job' => 'candidate',
        'search-for-locums' => 'employer',

        // 'saved-jobs' => 'candidate',
        'saved-locum' => 'employer',

        'advertiser-dashboard' => 'employer',
        'locum-dashboard' => 'candidate',

        'locum-profile' => 'candidate',
        'company-profile' => 'employer',

        'locum-profile-edit' => 'candidate',
        'company-profile-edit' => 'employer',

        'job-management' => 'employer',
        'post-a-job' => 'employer',
    );

    global $post;

    if (!isset($post) || !is_page()) {
        return; // Only check on pages
    }

    $slug = $post->post_name;

    // Check if this page has a role restriction
    if (array_key_exists($slug, $restricted_pages)) {

        // If user not logged in → redirect to home
        if (!is_user_logged_in()) {
            wp_redirect(home_url());
            exit;
        }

        // Get current user info
        $user = wp_get_current_user();

        // ✅ If user is admin, allow access to everything
        if (in_array('administrator', (array) $user->roles)) {
            return;
        }

        $required_role = $restricted_pages[$slug];

        // If user doesn't have required role → redirect
        if (!in_array($required_role, (array) $user->roles)) {
            wp_redirect(home_url());
            exit;
        }
    }
}
add_action('template_redirect', 'restrict_pages_by_role');



?>