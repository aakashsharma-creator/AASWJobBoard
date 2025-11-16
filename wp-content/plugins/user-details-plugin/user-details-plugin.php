<?php

/**
 * Plugin Name: User Details Plugin
 * Description: Lists Employer and Locum users with detailed modal view.
 * Version: 2.0
 * Author: Your Name
 */

if (!defined('ABSPATH'))
    exit;


// ? Enqueue scripts and styles
add_action('admin_enqueue_scripts', function ($hook) {
    if (strpos($hook, 'user-details') === false)
        return;

    wp_enqueue_style('datatables-css', 'https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css');
    wp_enqueue_script('jquery');
    wp_enqueue_script('datatables-js', 'https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js', ['jquery'], null, true);

    wp_enqueue_style('udp-style', plugin_dir_url(__FILE__) . 'assets/style.css');
    wp_enqueue_script('udp-script', plugin_dir_url(__FILE__) . 'assets/script.js', ['jquery'], null, true);

    // Pass ajax URL to JS
    wp_localize_script('udp-script', 'udp_ajax', [
        'ajax_url' => admin_url('admin-ajax.php')
    ]);
});

// ? Add menu pages
add_action('admin_menu', function () {
    add_menu_page(
        'User Details',
        'User Details',
        'manage_options',
        'user-details',
        function () {
            echo '<div class="wrap"><h1>User Details</h1><p>Select a subpage from the menu.</p></div>';
        },
        'dashicons-admin-users',
        20
    );

    add_submenu_page(
        'user-details',
        'List Employers',
        'List Employers',
        'manage_options',
        'list-employer',
        function () {
            include plugin_dir_path(__FILE__) . 'pages/list-employer.php';
        }
    );

    add_submenu_page(
        'user-details',
        'List Locums',
        'List Locums',
        'manage_options',
        'list-locum',
        function () {
            include plugin_dir_path(__FILE__) . 'pages/list-locum.php';
        }
    );
});

// ? AJAX for user details modal
add_action('wp_ajax_udp_get_user_details_employer', function () {
    global $wpdb;


    $user_id = intval($_POST['user_id']);
    $user = get_userdata($user_id);

    if (!$user) {
        wp_send_json_error(['message' => 'User not found']);
    }

    $email = $user->user_email;

    // Fetch company data from Forminator tables
    $entry = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT 
                MAX(CASE WHEN m.meta_key = 'email-1' THEN m.meta_value END) AS email, 
                MAX(CASE WHEN m.meta_key = 'name-1' THEN m.meta_value END) AS company_name, 
                MAX(CASE WHEN m.meta_key = 'name-2' THEN m.meta_value END) AS first_name, 
                MAX(CASE WHEN m.meta_key = 'name-3' THEN m.meta_value END) AS last_name, 
                MAX(CASE WHEN m.meta_key = 'url-1' THEN m.meta_value END) AS website_url, 
                MAX(CASE WHEN m.meta_key = 'address-1' THEN m.meta_value END) AS address,
                MAX(CASE WHEN m.meta_key = 'phone-1' THEN m.meta_value END) AS phone, 
                MAX(CASE WHEN m.meta_key = 'textarea-1' THEN m.meta_value END) AS about_company, 
                MAX(CASE WHEN m.meta_key = 'checkbox-1' THEN m.meta_value END) AS prefered_contact, 
                MAX(CASE WHEN m.meta_key = 'select-1' THEN m.meta_value END) AS sector,
                MAX(CASE WHEN m.meta_key = 'upload-1' THEN m.meta_value END) AS company_logo,
                MAX(CASE WHEN m.meta_key = 'select-2' THEN m.meta_value END) AS account_type 
            FROM {$wpdb->prefix}frmt_form_entry_meta m 
            WHERE m.entry_id = (
                SELECT entry_id 
                FROM {$wpdb->prefix}frmt_form_entry_meta 
                WHERE meta_key = 'email-1' AND meta_value = %s 
                ORDER BY entry_id DESC 
                LIMIT 1
            )",
            $email
        ),
        OBJECT
    );

    if (!$entry) {
        wp_send_json_error(['message' => 'No company data found for this user.']);
    }

    // Prepare fields (same logic from your function)
    $company_name = esc_html($entry->company_name ?? 'N/A');
    $first_name = esc_html($entry->first_name ?? '');
    $last_name = esc_html($entry->last_name ?? '');
    $full_name = trim($first_name . ' ' . $last_name);
    $website_url = esc_url($entry->website_url ?? '#');
    $phone = esc_html($entry->phone ?? 'N/A');
    $about_company = wp_kses_post($entry->about_company ?? 'No description available.');
    $sector = esc_html($entry->sector ?? 'N/A');
    $account_type = esc_html($entry->account_type ?? 'Employer');
    $email_safe = esc_attr($email);

    // Handle company logo
    $company_logo = get_stylesheet_directory_uri() . '/images/default-company.png';
    if (!empty($entry->company_logo)) {
        $logo_data = maybe_unserialize($entry->company_logo);
        if (is_array($logo_data) && isset($logo_data['file']['file_url'])) {
            $company_logo = esc_url($logo_data['file']['file_url']);
        }
    }

    // Handle address
    $address_raw = $entry->address ?? '';
    $street = $city = $state = $zip = $country = '';
    if (!empty($address_raw)) {
        $address_data = maybe_unserialize($address_raw);
        if (is_array($address_data)) {
            $street = esc_html($address_data['street_address'] ?? '');
            $city = esc_html($address_data['city'] ?? '');
            $state = esc_html($address_data['state'] ?? '');
            $zip = esc_html($address_data['zip'] ?? '');
            $country = esc_html($address_data['country'] ?? '');
        }
    }

    $address_full = trim($street . ', ' . $city . ', ' . $state . ' ' . $zip);
    $address_full = !empty($address_full) ? $address_full : 'N/A';
    $location = trim($city . ', ' . $country . ' ' . $zip);
    $location = !empty($location) ? $location : 'N/A';

    ob_start();
?>
    <div class="udp-modal-content">
        <?php echo my_company_profile_html([
            'company_logo' => $company_logo,
            'company_name' => $company_name,
            'location' => $location,
            'account_type' => $account_type,
            'sector' => $sector,
            'about_company' => $about_company,
            'full_name' => $full_name,
            'website_url' => $website_url,
            'address_full' => $address_full,
            'phone' => $phone,
            'email' => $email_safe
        ]); ?>
    </div>
<?php
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
});

// ? AJAX for user details modal
add_action('wp_ajax_udp_get_user_details', function () {
    $user_id = intval($_POST['user_id']);
    $user = get_userdata($user_id);

    if (!$user) {
        wp_send_json_error(['message' => 'User not found']);
    }

    global $wpdb;
    $email = $user->user_email;

    // === Same query as your original my_locum_profile_desc() ===
    $entry = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT *
            FROM {$wpdb->prefix}frmt_form_entry_meta
            WHERE entry_id = (
                SELECT entry_id
                FROM {$wpdb->prefix}frmt_form_entry_meta
                WHERE meta_key = 'email-1' AND meta_value = %s
                ORDER BY entry_id DESC
                LIMIT 1
            )",
            $email
        ),
        OBJECT
    );

    if (!$entry) {
        wp_send_json_error(['message' => 'No locum profile found.']);
    }

    $accreditation_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT meta_value
         FROM {$wpdb->prefix}frmt_form_entry_meta
         WHERE entry_id = (
             SELECT entry_id
             FROM {$wpdb->prefix}frmt_form_entry_meta
             WHERE meta_key = 'email-1' AND meta_value = %s
             ORDER BY entry_id DESC
             LIMIT 1
         ) AND meta_key LIKE 'text-4%%'",
        $email
    ));

    $accreditations = array_filter(array_map('trim', wp_list_pluck($accreditation_rows, 'meta_value')));
    $accreditation_list = !empty($accreditations) ? implode(', ', $accreditations) : 'NA';

    $data = [];
    foreach ($entry as $row) {
        $data[$row->meta_key] = $row->meta_value;
    }

    // === Extract all fields ===
    $first_name = $data['name-2'] ?? '';
    $last_name = $data['name-3'] ?? '';
    $full_name = trim($first_name . ' ' . $last_name) ?: 'N/A';
    $linkedin_url = $data['url-1'] ?? 'N/A';
    $phone = $data['phone-1'] ?? 'N/A';
    $about = $data['textarea-1'] ?? 'No description available.';
    $specialization = $data['text-5'] ?? 'N/A';
    $work_type = $data['select-1'] ?? 'Locum';
    $qualification = $data['select-3'] ?? 'N/A';
    $experience = isset($data['text-3']) ? $data['text-3'] . ' years' : 'N/A';

    // Profile Image
    $profile_image = get_stylesheet_directory_uri() . '/images/noimage.jpg';
    if (!empty($data['upload-2'])) {
        $img = maybe_unserialize($data['upload-2']);
        if (is_array($img) && !empty($img['file']['file_url'])) {
            $profile_image = esc_url($img['file']['file_url']);
        }
    }

    // Address
    $address_full = $location = 'N/A';
    if (!empty($data['address-1'])) {
        $addr = maybe_unserialize($data['address-1']);
        if (is_array($addr)) {
            $street = $addr['street_address'] ?? '';
            $city = $addr['city'] ?? '';
            $zip = $addr['zip'] ?? '';
            $state = $data['select-4'] ?? '';
            $country = $data['select-5'] ?? '';

            $parts = array_filter([$street, $city, $state, $zip]);
            $address_full = !empty($parts) ? implode(', ', $parts) : 'N/A';
            $location = trim(implode(', ', array_filter([$city, $country, $zip]))) ?: 'N/A';
        }
    }

    // CV URL
    $cv_url = '';
    if (!empty($data['upload-1'])) {
        $cv = maybe_unserialize($data['upload-1']);
        if (is_array($cv) && !empty($cv['file']['file_url'][0])) {
            $cv_url = esc_url($cv['file']['file_url'][0]);
        }
    }

    ob_start();
?>
    <div class="udp-modal-content">
        <?php echo my_locum_profile_html([
            'profile_image' => $profile_image,
            'full_name' => $full_name,
            'location' => $location,
            'work_type' => $work_type,
            'specialization' => $specialization,
            'qualification' => $qualification,
            'experience' => $experience,
            'accreditation_list' => $accreditation_list,
            'about' => $about,
            'linkedin_url' => $linkedin_url,
            'phone' => $phone,
            'email' => $email,
            'address_full' => $address_full,
            'cv_url' => $cv_url
        ]); ?>
    </div>
<?php
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
});


function my_company_profile_html($data)
{
    extract($data);
    ob_start();
?>
    <style>
        .udp-emp * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif
        }

        .udp-emp .container {
            max-width: 100%;
            margin: 22px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .1);
            overflow: hidden
        }

        .udp-emp .main-content {
            padding: 32px 24px
        }

        .udp-emp .company-header {
            display: flex;
            gap: 30px;
            margin-bottom: 32px
        }

        .udp-emp .company-logo-large {
            width: 150px;
            height: 140px;
            border: 1px solid #231F20;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center
        }

        .udp-emp img.job-logo {
            width: 150px;
            height: auto;
            object-fit: contain
        }

        .udp-emp .company-title {
            font: 700 30px/1.2 #1e293b;
            margin-bottom: 26px
        }

        .udp-emp .company-meta {
            display: flex;
            gap: 40px
        }

        .udp-emp .meta-item {
            display: flex;
            gap: 20px;
            align-items: flex-start
        }

        .udp-emp .meta-item img {
            width: 24px;
            margin-top: 6px
        }

        .udp-emp .meta-label {
            font: 600 16px/1.2 #515B6F
        }

        .udp-emp .meta-value {
            font: 700 14px/1.2 #1e293b
        }

        .udp-emp .content-area {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 32px
        }

        .udp-emp .about-section h2 {
            font: 700 24px/1.2 #1e293b;
            margin-bottom: 20px
        }

        .udp-emp .about-content p {
            margin-bottom: 16px;
            color: #475569;
            line-height: 1.6
        }

        .udp-emp .action-buttons {
            display: flex;
            gap: 40px;
            margin-top: 60px
        }

        .udp-emp .edit-profile-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #00688f;
            color: white;
            padding: 14px 18px;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none
        }

        .udp-emp .edit-profile-btn:hover {
            background: #045078
        }

        .udp-emp .change-password-btn {
            color: #dc2626;
            font-weight: 600;
            padding: 14px 18px;
            border-radius: 6px;
            text-decoration: underline
        }

        .udp-emp .change-password-btn:hover {
            background: #c36;
            color: white
        }

        .udp-emp .contact-section {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1)
        }

        .udp-emp .contact-header {
            background: #A32441;
            color: white;
            font: 600 18px/1;
            padding: 12px 16px
        }

        .udp-emp .contact-info {
            padding: 16px
        }

        .udp-emp .contact-item {
            display: flex;
            gap: 16px;
            padding: 10px 0;
            border-bottom: 1px solid #eaeaea
        }

        .udp-emp .contact-item:last-child {
            border-bottom: none
        }

        .udp-emp .contact-label {
            font: 600 12px/1 #007fa8;
            text-transform: uppercase
        }

        .udp-emp .contact-value {
            font: 600 14px/1 #333
        }

        .udp-emp .contact-address {
            font: 13px/1.4 #555;
            margin-top: 2px
        }

        @media(max-width:768px) {
            .udp-emp .company-header {
                flex-direction: column;
                align-items: center;
                text-align: center
            }

            .udp-emp .company-meta {
                flex-direction: column;
                gap: 16px
            }

            .udp-emp .content-area {
                grid-template-columns: 1fr
            }

            .udp-emp .action-buttons {
                flex-direction: column
            }
        }

        .udp-emp .last-login {
            text-align: right;
            font-weight: 500;
            font-size: 14px;
            color: #555;
            /* margin-bottom: 8px; */
            padding: 5px 20px;
        }
    </style>

    <div class="udp-emp">
        <div class="container">
            <div class="last-login">Last Login</div>
            <main class="main-content">
                <div class="company-header">
                    <div class="company-logo-large">
                        <img src="<?php echo $company_logo; ?>" alt="Logo" class="job-logo">
                    </div>
                    <div class="company-details">
                        <h1 class="company-title"><?php echo $company_name; ?></h1>
                        <div class="company-meta">
                            <div class="meta-item">
                                <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/company_profile/loc-pin.png">
                                <div>
                                    <div class="meta-label">Location</div>
                                    <div class="meta-value"><?php echo $location; ?></div>
                                </div>
                            </div>
                            <div class="meta-item">
                                <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/company_profile/account-type.png">
                                <div>
                                    <div class="meta-label">Account Type</div>
                                    <div class="meta-value"><?php echo $account_type; ?></div>
                                </div>
                            </div>
                            <div class="meta-item">
                                <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/company_profile/gis_map-server.png">
                                <div>
                                    <div class="meta-label">Sector</div>
                                    <div class="meta-value"><?php echo $sector; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content-area">
                    <section class="about-section">
                        <h2>About Company</h2>
                        <div class="about-content">
                            <?php foreach (array_filter(array_map('trim', explode("\n", $about_company))) as $p): ?>
                                <p><?php echo wp_kses_post($p); ?></p>
                            <?php endforeach; ?>
                        </div>
                        <!-- <div class="action-buttons">
                            <a href="<?php echo esc_url(site_url('/company-profile-edit')); ?>" class="edit-profile-btn">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                                Edit Profile
                            </a>
                            <a href="<?php echo esc_url(site_url('/change-password')); ?>" class="change-password-btn">
                                Change Password
                            </a>
                        </div> -->
                    </section>

                    <aside class="contact-section">
                        <div class="contact-header">Contact Information</div>
                        <div class="contact-info">
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/company_profile/avatar 1.png">
                                </div>
                                <div class="contact-details">
                                    <div class="contact-label">Name</div>
                                    <div class="contact-value"><?php echo $full_name; ?></div>
                                </div>
                            </div>
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/company_profile/GlobeSimple.png">
                                </div>
                                <div class="contact-details">
                                    <div class="contact-label">Website</div>
                                    <div class="contact-value">
                                        <a href="<?php echo $website_url; ?>" target="_blank"><?php echo $website_url; ?></a>
                                    </div>
                                </div>
                            </div>
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/company_profile/map-pin-line-duotone.png">
                                </div>
                                <div class="contact-details">
                                    <div class="contact-label">Location</div>
                                    <div class="contact-value"><?php echo $location; ?></div>
                                    <div class="contact-address"><?php echo $address_full; ?></div>
                                </div>
                            </div>
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/company_profile/phone-call-duotone.png">
                                </div>
                                <div class="contact-details">
                                    <div class="contact-label">Phone</div>
                                    <div class="contact-value"><?php echo $phone; ?></div>
                                </div>
                            </div>
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/company_profile/Envelope.png">
                                </div>
                                <div class="contact-details">
                                    <div class="contact-label">Email</div>
                                    <div class="contact-value"><?php echo $email; ?></div>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </main>
        </div>
    </div>
<?php
    return ob_get_clean();
}

function my_locum_profile_html($args)
{
    extract($args);
    ob_start();
?>
    <div class="container">
        <div class="last-login">Last Login</div>
        <main class="main-content">
            <div class="profile-header">
                <div class="profile-image-large">
                    <img src="<?php echo $profile_image; ?>" alt="Profile" class="profile-img">
                </div>
                <div class="profile-details">
                    <h1 class="profile-title"><?php echo $full_name; ?></h1>
                    <div class="profile-meta">
                        <div class="meta-item">
                            <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/company_profile/loc-pin.png" alt="Location">
                            <div>
                                <div class="meta-label">Location</div>
                                <div class="meta-value"><?php echo $location; ?></div>
                            </div>
                        </div>
                        <div class="meta-item">
                            <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/company_profile/account-type.png" alt="Work Type">
                            <div>
                                <div class="meta-label">Work Type</div>
                                <div class="meta-value"><?php echo $work_type; ?></div>
                            </div>
                        </div>
                        <div class="meta-item">
                            <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/company_profile/gis_map-server.png" alt="Specialization">
                            <div>
                                <div class="meta-label">Specialization</div>
                                <div class="meta-value"><?php echo $specialization; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-area">
                <section class="about-section">
                    <h2>About Me</h2>
                    <div class="about-content">
                        <p><strong><img src="<?php echo get_stylesheet_directory_uri(); ?>/images/GraduationCap.png" alt="Qualification" style="width:20px;height:20px;vertical-align:middle;margin-right:8px;"> Qualification:</strong> <?php echo $qualification; ?></p>
                        <p><strong><img src="<?php echo get_stylesheet_directory_uri(); ?>/images/Stack.png" alt="Experience" style="width:20px;height:20px;vertical-align:middle;margin-right:8px;"> Experience:</strong> <?php echo $experience; ?></p>
                        <p><strong><img src="<?php echo get_stylesheet_directory_uri(); ?>/images/ClipboardText.png" alt="Accreditation" style="width:20px;height:20px;vertical-align:middle;margin-right:8px;"> Accreditation:</strong> <?php echo esc_html($accreditation_list); ?></p>
                        <?php
                        $paragraphs = array_filter(array_map('trim', explode("\n", $about)));
                        foreach ($paragraphs as $p) {
                            echo '<p>' . wp_kses_post($p) . '</p>';
                        }
                        ?>
                    </div>
                </section>

                <aside class="contact-section">
                    <div class="contact-header">Contact Information</div>
                    <div class="contact-info">
                        <div class="contact-item">
                            <div class="contact-icon"><img src="<?php echo get_stylesheet_directory_uri(); ?>/images/company_profile/avatar 1.png" alt="Name"></div>
                            <div class="contact-details">
                                <div class="contact-label">Name</div>
                                <div class="contact-value"><?php echo $full_name; ?></div>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon"><img src="<?php echo get_stylesheet_directory_uri(); ?>/images/company_profile/GlobeSimple.png" alt="LinkedIn"></div>
                            <div class="contact-details">
                                <div class="contact-label">LinkedIn</div>
                                <div class="contact-value">
                                    <?php echo $linkedin_url !== 'N/A' ? '<a href="' . esc_url($linkedin_url) . '" target="_blank">' . esc_html($linkedin_url) . '</a>' : 'N/A'; ?>
                                </div>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon"><img src="<?php echo get_stylesheet_directory_uri(); ?>/images/company_profile/map-pin-line-duotone.png" alt="Location"></div>
                            <div class="contact-details">
                                <div class="contact-label">Location</div>
                                <div class="contact-value"><?php echo $location; ?></div>
                                <div class="contact-address"><?php echo $address_full; ?></div>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon"><img src="<?php echo get_stylesheet_directory_uri(); ?>/images/company_profile/phone-call-duotone.png" alt="Phone"></div>
                            <div class="contact-details">
                                <div class="contact-label">Phone</div>
                                <div class="contact-value"><?php echo $phone; ?></div>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon"><img src="<?php echo get_stylesheet_directory_uri(); ?>/images/company_profile/Envelope.png" alt="Email"></div>
                            <div class="contact-details">
                                <div class="contact-label">Email</div>
                                <div class="contact-value"><?php echo esc_attr($email); ?></div>
                            </div>
                        </div>
                    </div>
                    <?php if ($cv_url): ?>
                        <a href="<?php echo $cv_url; ?>" class="download-cv-btn" target="_blank" download>
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                <polyline points="7 10 12 15 17 10" />
                                <line x1="12" y1="15" x2="12" y2="3" />
                            </svg>
                            Download CV
                        </a>
                    <?php endif; ?>
                </aside>
            </div>
        </main>
    </div>

    <style>
        /* === Paste your FULL original CSS here === */
        .udp-modal-content * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .udp-modal-content .container {
            max-width: 100%;
            background: white;
            padding: 12px 24px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .udp-modal-content .main-content {
            min-height: 100%;
        }

        .udp-modal-content .profile-header {
            display: flex;
            gap: 30px;
            margin-bottom: 32px;
            flex-direction: column;
        }

        .udp-modal-content .profile-image-large {
            width: 150px;
            height: 140px;
            border: 1px solid #231F20;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .udp-modal-content img.profile-img {
            width: 150px;
            height: auto;
            object-fit: contain;
        }

        .udp-modal-content .profile-title {
            font-size: 30px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 26px;
        }

        .udp-modal-content .profile-meta {
            display: flex;
            gap: 40px;
        }

        .udp-modal-content .meta-item {
            display: flex;
            align-items: flex-start;
            gap: 20px;
        }

        .udp-modal-content .meta-item img {
            width: 24px;
            margin-top: 6px;
        }

        /* .udp-modal-content .meta-label { font-size:16px; color:#515B6F; font-weight:600; } */
        /* .udp-modal-content .meta-value { font-size:14px; color:#1e293b; font-weight:700; } */
        .udp-modal-content .content-area {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 32px;
        }

        .udp-modal-content .about-section h2 {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 20px;
        }

        .udp-modal-content .about-content p {
            margin-bottom: 16px;
            color: #475569;
            line-height: 1.6;
        }

        .udp-modal-content .contact-section {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .udp-modal-content .contact-header {
            background: #A32441;
            color: #fff;
            font-weight: 600;
            font-size: 18px;
            padding: 12px 16px;
        }

        .udp-modal-content .contact-info {
            padding: 16px;
        }

        .udp-modal-content .contact-item {
            display: flex;
            gap: 16px;
            padding: 10px 0;
            border-bottom: 1px solid #eaeaea;
        }

        .udp-modal-content .contact-item:last-child {
            border-bottom: none;
        }

        /* .udp-modal-content .contact-label { font-size:12px; color:#007fa8; text-transform:uppercase; font-weight:600; }
            .udp-modal-content .contact-value { font-size:14px; font-weight:600; color:#333; } */
        .udp-modal-content .contact-address {
            font-size: 13px;
            color: #555;
            margin-top: 2px;
        }

        .udp-modal-content .download-cv-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #00688f;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            margin: 16px;
            font-weight: 600;
        }

        .udp-modal-content .download-cv-btn:hover {
            background: #045078;
        }

        .udp-modal-content .download-cv-btn svg {
            width: 17px;
            height: 17px;
            fill: currentColor;
        }

        @media (max-width: 768px) {
            .udp-modal-content .profile-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .udp-modal-content .profile-meta {
                flex-direction: column;
                gap: 16px;
            }

            .udp-modal-content .content-area {
                grid-template-columns: 1fr;
            }
        }

        .udp-modal-content .last-login {
            text-align: right;
            font-weight: 500;
            font-size: 14px;
            color: #555;
            margin-bottom: 8px;
        }
    </style>
<?php
    return ob_get_clean();
}
