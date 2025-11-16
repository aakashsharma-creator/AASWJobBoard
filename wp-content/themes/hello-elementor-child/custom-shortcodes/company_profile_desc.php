<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

function my_company_profile_desc() {
    global $wpdb;

    // Check if user is logged in
    if (!is_user_logged_in()) {
        return '<p>Please log in to view your profile.</p>';
    }

    // Get current user ID and email
    $user_id = get_current_user_id();
    $user_info = get_userdata($user_id);
    $email = $user_info->user_email;

    // Query to fetch user data from frmt_form_entry_meta
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

    // Check if data exists
    if (!$entry) {
        return '<p>No company information found for this account.</p>';
    }

    // Sanitize field values
    $company_name = esc_html($entry->company_name ?? 'N/A');
    $first_name = esc_html($entry->first_name ?? '');
    $last_name = esc_html($entry->last_name ?? '');
    $full_name = trim($first_name . ' ' . $last_name);
    $website_url = esc_url($entry->website_url ?? '#');
    $phone = esc_html($entry->phone ?? 'N/A');
    $about_company = wp_kses_post($entry->about_company ?? 'No description available.');
    $sector = esc_html($entry->sector ?? 'N/A');
    $account_type = esc_html($entry->account_type ?? 'Employer');

    // Handle company logo (unserialize and extract file_url)
    $company_logo = get_stylesheet_directory_uri() . '/images/default-company.png';
    if (!empty($entry->company_logo)) {
        $logo_data = maybe_unserialize($entry->company_logo);
        if (is_array($logo_data) && isset($logo_data['file']) && isset($logo_data['file']['file_url'])) {
            $company_logo = esc_url($logo_data['file']['file_url']);
        }
    }

    // Handle address data
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

//     echo '<pre>';
// print_r($address_data);
// echo '</pre>';

    ob_start();
?>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f8fafc;
            color: #334155;
            line-height: 1.5;
        }

        .container {
            max-width: 100%;
            margin: 0px 22px;
            background-color: white;
            min-height: 100vh;
        }

        /* Main Content */
        .main-content {
            padding: 32px 24px;
        }

        .company-header {
            display: flex;
            gap: 30px;
            margin-bottom: 32px;
        }

        .company-logo-large {
            flex-shrink: 0;
            width: 150px;
            height: 140px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
       border: 1px solid #231F20;
        }

        img.job-logo {
            width: 150px;
            height: auto;
            object-fit: contain;
     
        }

        .company-details {
            flex: 1;
        }

        .company-title {
            font-size: 30px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 26px;
        }

        .company-meta {
            display: flex;
            gap: 40px;
        }

        .meta-item {
            display: flex;
            align-items: flex-start;
            gap: 20px;
        }

        .meta-item img {
            margin-top: 6px;
            flex-shrink: 0;
            width: 24px;
        }

        .meta-label {
            font-size: 16px;
            color: #515B6F;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .meta-value {
            font-size: 14px;
            color: #1e293b;
            font-weight: 700;
        }

        /* Content Area */
        .content-area {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 32px;
        }

        /* About Section */
        .about-section h2 {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 20px;
        }

        .about-content p {
            margin-bottom: 16px;
            color: #475569;
            line-height: 1.6;
        }

        .action-buttons {
            display: flex;
            gap: 40px;
            margin-top: 60px;
        }

        .edit-profile-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            background-color: #00688f;
            color: white;
            border: none;
            padding: 14px 18px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
            text-decoration: none !important;
        }

        .edit-profile-btn:hover {
            background-color: #045078;
            color: #ffffff;
        }

        .edit-profile-btn:focus {
            background-color: #045078 !important;
        }

        .change-password-btn {
            background: none;
            border: none;
            color: #dc2626;
            font-weight: 600;
            cursor: pointer;
            padding: 14px 18px;
            border-radius: 6px;
            transition: background-color 0.2s;
            text-decoration: underline !important;
        }

        .change-password-btn:hover {
            background-color: #c36;
            color: #ffffff;
        }

        .change-password-btn:focus {
            background-color: #c36 !important;
            color: #ffffff !important;
        }

        /* Contact Section */
        .contact-section {
            background-color: #ffffff;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            height: fit-content;
        }

        .contact-header {
            background-color: #A32441;
            color: #ffffff;
            font-weight: 600;
            font-size: 18px;
            padding: 12px 16px;
        }

        .contact-info {
            padding: 16px;
        }

        .contact-item {
            display: flex;
            gap: 16px;
            align-items: flex-start;
            padding: 10px 0;
            border-bottom: 1px solid #eaeaea;
        }

        .contact-item:last-child {
            border-bottom: none;
        }

        .contact-icon {
            color: #007fa8;
            flex-shrink: 0;
            margin-top: 3px;
        }

        .contact-details {
            flex: 1;
        }

        .contact-label {
            font-size: 12px;
            color: #007fa8;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .contact-value {
            color: #333;
            font-size: 14px;
            font-weight: 600;
        }

        .contact-address {
            color: #555;
            font-size: 13px;
            margin-top: 2px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                max-width: 768px;
            }

            .header {
                padding: 12px 16px;
            }

            .company-meta {
                flex-direction: column;
                gap: 16px;
            }

            .content-area {
                grid-template-columns: 1fr;
                gap: 24px;
            }

            .company-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .main-content {
                padding: 24px 16px;
            }

            .action-buttons {
                flex-direction: column;
            }
        }

        @media (max-width: 480px) {
            .header-right {
                gap: 12px;
            }

            .company-info {
                gap: 8px;
            }

            .company-name {
                font-size: 12px;
            }

            .company-type {
                font-size: 11px;
            }

            .company-title {
                font-size: 24px;
            }
        }
    </style>

    <div class="container">
        <!-- Main Content -->
        <main class="main-content">
            <!-- Company Logo and Header -->
            <div class="company-header">
                <div class="company-logo-large">
                    <img src="<?php echo $company_logo; ?>" alt="<?php echo esc_attr($company_name);?>" class="job-logo">
                </div>
                <div class="company-details">
                    <h1 class="company-title"><?php echo $company_name; ?></h1>
                    <div class="company-meta">
                        <div class="meta-item">
                            <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/company_profile/loc-pin.png'); ?>" alt="<?php echo esc_attr($location); ?>" class="locationtype-logo">
                            <div>
                                <div class="meta-label">Location</div>
                                <div class="meta-value"><?php echo $location; ?></div>
                            </div>
                        </div>
                        <div class="meta-item">
                            <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/company_profile/account-type.png'); ?>" alt="account_type-icon" class="account_type-logo">
                            <div>
                                <div class="meta-label">Account Type</div>
                                <div class="meta-value"><?php echo $account_type; ?></div>
                            </div>
                        </div>
                        <div class="meta-item">
                            <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/company_profile/gis_map-server.png'); ?>" alt="server-icon" class="server-logo">
                            <div>
                                <div class="meta-label">Sector</div>
                                <div class="meta-value"><?php echo $sector; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="content-area">
                <!-- About Company Section -->
                <section class="about-section">
                    <h2>About Company</h2>
                    <div class="about-content">
                        <?php
                        // Split about_company into paragraphs
                        $paragraphs = array_filter(array_map('trim', explode("\n", $about_company)));
                        foreach ($paragraphs as $paragraph) {
                            echo '<p>' . $paragraph . '</p>';
                        }
                        ?>
                    </div>

                    <div class="action-buttons">
                        <a href="<?php echo esc_url(site_url('/company-profile-edit')); ?>" class="edit-profile-btn">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                            Edit Profile
                        </a>

                        <a href="<?php echo esc_url(site_url('/change-password')); ?>" class="change-password-btn">
                            Change Password
                        </a>
                    </div>
                </section>

                <!-- Contact Information Section -->
                <aside class="contact-section">
                    <div class="contact-header">Contact Information</div>
                    <div class="contact-info">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/company_profile/avatar 1.png'); ?>" alt="avatar-icon" class="avatar-logo">
                            </div>
                            <div class="contact-details">
                                <div class="contact-label">Name</div>
                                <div class="contact-value"><?php echo $full_name ?: 'N/A'; ?></div>
                            </div>
                        </div>

                        <div class="contact-item" style="overflow:hidden">
                            <div class="contact-icon">
                                <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/company_profile/GlobeSimple.png'); ?>" alt="globe-icon" class="globe-logo">
                            </div>
                            <div class="contact-details">
                                <div class="contact-label">Website</div>
                                <div class="contact-value"><a href="<?php echo $website_url; ?>" target="_blank"><?php echo $website_url; ?></a></div>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/company_profile/map-pin-line-duotone.png'); ?>" alt="location-icon" class="location-logo">
                            </div>
                            <div class="contact-details">
                                <div class="contact-label">Location</div>
                                <div class="contact-value"><?php echo $location; ?></div>
                                <div class="contact-address"><?php echo $address_full; ?></div>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/company_profile/phone-call-duotone.png'); ?>" alt="call-icon" class="call-logo">
                            </div>
                            <div class="contact-details">
                                <div class="contact-label">Phone</div>
                                <div class="contact-value"><?php echo $phone; ?></div>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/company_profile/Envelope.png'); ?>" alt="envelope-icon" class="envelope-logo">
                            </div>
                            <div class="contact-details">
                                <div class="contact-label">Email Address</div>
                                <div class="contact-value"><?php echo esc_attr($email); ?></div>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </main>
    </div>

<?php
    return ob_get_clean();
}
add_shortcode('company_profile_desc', 'my_company_profile_desc');
?>