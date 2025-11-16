<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

function my_locum_profile_desc()
{
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


    $accreditations = [];

    $accreditation_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT meta_key, meta_value
     FROM {$wpdb->prefix}frmt_form_entry_meta
     WHERE entry_id = (
         SELECT entry_id
         FROM {$wpdb->prefix}frmt_form_entry_meta
         WHERE meta_key = 'email-1' AND meta_value = %s
         ORDER BY entry_id DESC
         LIMIT 1
     ) AND meta_key LIKE 'text-4%%'
     ORDER BY meta_key ASC",
        $email
    ));

    if ($accreditation_rows) {
        foreach ($accreditation_rows as $row) {
            $accreditations[] = trim($row->meta_value);
        }
    }

    // Prepare accreditation list or NA
    $accreditation_list = !empty(array_filter($accreditations))
        ? implode(', ', array_filter($accreditations))
        : 'NA';

    // Check if data exists
    if (!$entry) {
        return '<p>No locum information found for this account.</p>';
    }

    // Initialize variables with default values
    $first_name = '';
    $last_name = '';
    $linkedin_url = 'N/A';
    $phone = 'N/A';
    $about = 'No description available.';
    $specialization = 'N/A';
    $work_type = 'Locum';
    $qualification = 'N/A';
    $experience = 'N/A';
    $profile_image = get_stylesheet_directory_uri() . '/images/noimage.jpg';
    $street = $city = $state = $zip = $country = '';
    $cv_url = '';

    // Process entry data into an associative array
    $entry_data = [];
    foreach ($entry as $row) {
        $entry_data[$row->meta_key] = $row->meta_value;
    }

    // Assign values from entry_data if they exist
    if (isset($entry_data['name-2'])) {
        $first_name = esc_html($entry_data['name-2']);
    }
    if (isset($entry_data['name-3'])) {
        $last_name = esc_html($entry_data['name-3']);
    }
    if (isset($entry_data['url-1'])) {
        $linkedin_url = esc_url($entry_data['url-1']);
    }
    if (isset($entry_data['phone-1'])) {
        $phone = esc_html($entry_data['phone-1']);
    }
    if (isset($entry_data['textarea-1'])) {
        $about = wp_kses_post($entry_data['textarea-1']);
    }
    if (isset($entry_data['text-5'])) {
        $specialization = esc_html($entry_data['text-5']);
    }
    if (isset($entry_data['select-1'])) {
        $work_type = esc_html($entry_data['select-1']);
    }
    if (isset($entry_data['select-3'])) {
        $qualification = esc_html($entry_data['select-3']);
    }
    if (isset($entry_data['text-3'])) {
        $experience = esc_html($entry_data['text-3'] . ' years');
    }
    if (isset($entry_data['upload-2'])) {
        $image_data = maybe_unserialize($entry_data['upload-2']);
        if (is_array($image_data) && isset($image_data['file']) && isset($image_data['file']['file_url'])) {
            $profile_image = esc_url($image_data['file']['file_url']);
        }
    }
    if (isset($entry_data['address-1'])) {
        $address_data = maybe_unserialize($entry_data['address-1']);
        if (is_array($address_data)) {
            $street = esc_html($address_data['street_address'] ?? '');
            $city = esc_html($address_data['city'] ?? '');
            $zip = esc_html($address_data['zip'] ?? '');
        }
    }
    if (isset($entry_data['select-4'])) {
        $state = esc_html($entry_data['select-4']);
    }
    if (isset($entry_data['select-5'])) {
        $country = esc_html($entry_data['select-5']);
    }
    // Check and set CV URL if uploaded
    if (isset($entry_data['upload-1'])) {
        $cv_data = maybe_unserialize($entry_data['upload-1']);
        if (is_array($cv_data) && isset($cv_data['file']) && isset($cv_data['file']['file_url'][0])) {
            $cv_url = esc_url($cv_data['file']['file_url'][0]);
        }
    }

    // Prepare full name and address
    $full_name = trim($first_name . ' ' . $last_name);
    $address_full = trim($street . ', ' . $city . ', ' . $state . ' ' . $zip);
    $address_full = !empty($address_full) ? $address_full : 'N/A';
    $location = trim($city . ', ' . $country . ' ' . $zip);
    $location = !empty($location) ? $location : 'N/A';

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

        .profile-header {
            display: flex;
            gap: 30px;
            margin-bottom: 32px;
        }

        .profile-image-large {
            flex-shrink: 0;
            width: 150px;
            height: 140px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #231F20;
        }

        img.profile-img {
            width: 150px;
            height: auto;
            object-fit: contain;
        }

        .profile-details {
            flex: 1;
        }

        .profile-title {
            font-size: 30px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 26px;
        }

        .profile-meta {
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

        /* Download CV Button */
        .download-cv-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #00688f;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
            text-decoration: none !important;
            margin-top: 16px;
        }

        .download-cv-btn:hover {
            background-color: #045078;
            color: #ffffff;
        }

        .download-cv-btn:focus {
            background-color: #045078 !important;
        }

        .download-cv-btn svg {
            width: 17px;
            height: 17px;
            margin-right: 8px;
            fill: currentColor;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                max-width: 768px;
            }

            .header {
                padding: 12px 16px;
            }

            .profile-meta {
                flex-direction: column;
                gap: 16px;
            }

            .content-area {
                grid-template-columns: 1fr;
                gap: 24px;
            }

            .profile-header {
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

            .profile-info {
                gap: 8px;
            }

            .profile-name {
                font-size: 12px;
            }

            .profile-type {
                font-size: 11px;
            }

            .profile-title {
                font-size: 24px;
            }
        }
    </style>

    <div class="container">
        <!-- Main Content -->
        <main class="main-content">
            <!-- Profile Image and Header -->
            <div class="profile-header">
                <div class="profile-image-large">
                    <img src="<?php echo $profile_image; ?>" alt="<?php echo esc_attr($full_name);?>" class="profile-img">
                </div>
                <div class="profile-details">
                    <h1 class="profile-title"><?php echo $full_name ?: 'N/A'; ?></h1>
                    <div class="profile-meta">
                        <div class="meta-item">
                            <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/company_profile/loc-pin.png'); ?>" alt="location-icon" class="location-logo">
                            <div>
                                <div class="meta-label">Location</div>
                                <div class="meta-value"><?php echo $location; ?></div>
                            </div>
                        </div>
                        <div class="meta-item">
                            <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/company_profile/account-type.png'); ?>" alt="work-type-icon" class="work-type-logo">
                            <div>
                                <div class="meta-label">Work Type</div>
                                <div class="meta-value"><?php echo $work_type; ?></div>
                            </div>
                        </div>
                        <div class="meta-item">
                            <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/company_profile/gis_map-server.png'); ?>" alt="specialization-icon" class="specialization-logo">
                            <div>
                                <div class="meta-label">Specialization</div>
                                <div class="meta-value"><?php echo $specialization; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="content-area">
                <!-- About Locum Section -->
                <section class="about-section">
                    <h2>About Me</h2>
                    <div class="about-content">
                        <p>
                            <strong>
                                <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/GraduationCap.png'); ?>"
                                    alt="Qualification"
                                    style="width:20px;height:20px;vertical-align:middle;margin-right:8px;">
                                Qualification:
                            </strong>
                            <?php echo $qualification; ?>
                        </p>
                        <p>
                            <strong>
                                <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/Stack.png'); ?>"
                                    alt="Experience"
                                    style="width:20px;height:20px;vertical-align:middle;margin-right:8px;">
                                Experience:
                            </strong>
                            <?php echo $experience; ?>
                        </p>
                <p>
    <strong>
        <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/ClipboardText.png'); ?>"
                            alt="Accreditation" style="width:20px;height:20px;vertical-align:middle;margin-right:8px;">
                        Accreditation:
                    </strong>
                    <?php echo esc_html($accreditation_list); ?>
                </p>
                        <?php
                        // Split about into paragraphs
                        $paragraphs = array_filter(array_map('trim', explode("\n", $about)));
                        foreach ($paragraphs as $paragraph) {
                            echo '<p>' . $paragraph . '</p>';
                        }
                        ?>
                    </div>

                    <div class="action-buttons">
                        <a href="<?php echo esc_url(site_url('/locum-profile-edit')); ?>" class="edit-profile-btn">
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
                                <div class="contact-label">Linkedin</div>
                                <div class="contact-value"><a href="<?php echo $linkedin_url; ?>" target="_blank"><?php echo $linkedin_url; ?></a></div>
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
                    <!-- CV Download Button (if cv is uploaded) -->
                    <?php if (!empty($cv_url)): ?>
                        <a href="<?php echo $cv_url; ?>" class="download-cv-btn" download>
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

<?php
    return ob_get_clean();
}
add_shortcode('locum_profile_desc', 'my_locum_profile_desc');
?>