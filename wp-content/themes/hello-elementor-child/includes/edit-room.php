<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
function rhf_edit_enqueue_datepicker_assets() {
    // Only load if the shortcode is on the current page.
    if ( is_a( $GLOBALS['post'], 'WP_Post' ) && has_shortcode( $GLOBALS['post']->post_content, 'my_room_edit_form' ) ) {
        
        // --- *** LOAD GOOGLE MAPS API FOR AUTOCOMPLETE *** ---
        $google_maps_api_key = 'AIzaSyCQCUlVYxaZU0vuFUi9fNX68QyFdKOvi2A'; // Key from user
        wp_enqueue_script(
            'google-maps-places',
            'https://maps.googleapis.com/maps/api/js?key=' . $google_maps_api_key . '&libraries=places&region=AU&callback=initAutocomplete',
            array(), // No dependencies
            null,    // No version number
            true     // Load in footer
        );
        // --- *** END GOOGLE MAPS API *** ---

        // Datepicker
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_style( 'jquery-ui-theme', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css' );

        // --- *** NEW: Enqueue Select2 *** ---
        wp_enqueue_style( 'select2-css', 'https_cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css' );
        wp_enqueue_script( 'select2-js', 'https_cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array( 'jquery' ), null, true );
    }
}
add_action( 'wp_enqueue_scripts', 'rhf_edit_enqueue_datepicker_assets' );

function rhf_edit_add_inline_styles() {
    if ( is_a( $GLOBALS['post'], 'WP_Post' ) && has_shortcode( $GLOBALS['post']->post_content, 'my_room_edit_form' ) ) {
        
        $css = <<<CSS
        @import url('https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;500;600&display=swap');
        
        /* --- Form Styles --- */
        .rhf-form-container,
        #rhf-preview-container { /* Apply base styles to both form and preview */
            background-color: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 30px 40px;
            max-width: 100%;
            margin: 40px auto;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            font-family: 'Nunito Sans', sans-serif;
            color: #333;
        }
        .rhf-form-row {
            display: flex;
            flex-wrap: wrap;
            border-top: 1px solid #f0f0f0;
            padding: 20px 0;
            align-items: flex-start;
        }
        .rhf-form-row:first-child { border-top: none; padding-top: 0; }
        .rhf-form-row:last-child { padding-bottom: 0; }
        .rhf-form-row label {
            width: 200px;
            font-weight: 500;
            color: #333;
            padding-right: 20px;
            padding-top: 10px;
            flex-shrink: 0;
        }
        .rhf-form-row .rhf-field-container {
            flex-grow: 1;
        }
        .rhf-form-row input[type="text"],
        .rhf-form-row input[type="email"],
        .rhf-form-row input[type="tel"],
        .rhf-form-row input[type="number"],
        .rhf-form-row select,
        .rhf-form-row textarea {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s, box-shadow 0.3s;
            font-family: 'Nunito Sans', sans-serif;
        }
        .rhf-form-row input:focus,
        .rhf-form-row select:focus,
        .rhf-form-row textarea:focus {
            outline: none;
            border-color: #A32441; /* CHANGED */
            box-shadow: 0 0 0 2px rgba(163, 36, 65, 0.2); /* CHANGED */
        }
        .rhf-form-row .required { color: #d9534f; margin-left: 4px; }
        .rhf-form-row small.helper-text {
            color: #777;
            font-size: 13px;
            display: block;
            margin-top: 8px;
        }
        .rhf-form-row .wp-editor-wrap {
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        .rhf-form-row .wp-editor-wrap:focus-within {
             border-color: #A32441; /* CHANGED */
             box-shadow: 0 0 0 2px rgba(163, 36, 65, 0.2); /* CHANGED */
        }
        .rhf-form-row .wp-editor-tools { background-color: #f7f7f7; border-bottom: 1px solid #ccc; border-radius: 6px 6px 0 0; }
        .rhf-form-row textarea#description { border: none !important; box-shadow: none !important; }
        .rhf-form-row .description-meta { text-align: right; padding-right: 5px; }
        .rhf-form-row input.rhf-datepicker {
            width: 50%;
            max-width: 200px;
        }
        .ui-datepicker {
            z-index: 100 !important;
            font-family: 'Nunito Sans', sans-serif;
            font-size: 14px;
            width: auto !important;
            padding: 5px;
        }
        .ui-datepicker-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2px 0;
            position: relative;
        }
        .ui-datepicker-title {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
            margin: 0 2.3em;
            line-height: 1.8em;
        }
        .ui-datepicker-title select {
            font-size: 1em !important;
            padding: 2px 4px;
            width: auto;
            margin: 0 2px;
            height: auto;
        }
        .ui-datepicker-title select.ui-datepicker-year {
            min-width: 70px;
            width: auto;
        }
        .ui-datepicker-prev, 
        .ui-datepicker-next {
            position: absolute;
            top: 2px;
            width: 1.8em;
            height: 1.8em;
        }
        .ui-datepicker-prev { left: 2px; }
        .ui-datepicker-next { right: 2px; }
        .ui-datepicker-calendar {
            width: 100%;
            border-collapse: collapse;
        }
        .ui-datepicker-calendar th,
        .ui-datepicker-calendar td {
            text-align: center;
            padding: 4px;
        }
        .checkbox-row { align-items: center; }
        .checkbox-row input[type="checkbox"] { width: auto; margin-right: 10px; flex-grow: 0; height: 1.2em; width: 1.2em; }
        .checkbox-row label { width: auto; font-weight: normal; color: #555; padding-top: 0; }
        .checkbox-row a { color: #0073aa; text-decoration: none; }
        .checkbox-row a:hover { text-decoration: underline; }
        .submit-buttons { justify-content: flex-end; gap: 15px; align-items: center; }
        .submit-buttons button { padding: 12px 20px; border: none; border-radius: 6px; font-size: 16px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: background-color 0.3s, transform 0.1s; font-family: 'Nunito Sans', sans-serif;}
        .submit-buttons button:active { transform: translateY(1px); }
        .submit-buttons .draft-button { background-color: #fff; border: 1px solid #A32441; color: #A32441; } /* CHANGED */
        .submit-buttons .draft-button:hover { background-color: #fdf4f6; } /* CHANGED */
        .submit-buttons .submit-button { background-color: #A32441; color: #fff; } /* CHANGED */
        .submit-buttons .submit-button:hover { background-color: #8a1f37; } /* CHANGED */
        .rhf-message { padding: 15px; margin-bottom: 20px; border-radius: 6px; border: 1px solid transparent; }
        .rhf-message.success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
        .rhf-error-message {
            color: #d9534f;
            font-size: 13px;
            display: block;
            margin-top: 8px;
        }
        .rhf-form-row input.error,
        .rhf-form-row textarea.error,
        .rhf-form-row select.error,
        .rhf-form-row .wp-editor-wrap.error,
        .rhf-form-row .select2-container--default .select2-selection--single.error { /* ADDED Select2 error */
             border-color: #d9534f !important;
        }

        /* --- *** START: CUSTOM SELECT2 STYLES (Copied) *** --- */
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #757575;
            padding-left: 2px; 
        }
        .select2-container--default .select2-selection--single {
            width: 100%;
            height: auto; 
            padding: 10px 15px;
            border: 1px solid #666;
            border-radius: 5px;
            font-size: 16px;
            font-family: 'Nunito Sans', sans-serif;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #A32441; /* CHANGED */
            box-shadow: 0 0 0 2px rgba(163, 36, 65, 0.2); /* CHANGED */
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            padding-left: 10px; 
            padding-right: 25px; 
            line-height: 1.5; 
            color: #333;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100%;
            right: 15px;
            top: 0;
            width: 15px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-width: 6px 6px 0 6px;
            border-color: #555 transparent transparent transparent;
            margin-left: -6px;
            margin-top: -3px;
        }
        .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-width: 0 6px 6px 6px;
            border-color: transparent transparent #555 transparent;
        }
        .select2-dropdown {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden; 
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #00688f; 
            color: #fff;
        }
        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #f0f0f0;
            color: #333;
        }
        /* --- *** END: CUSTOM SELECT2 STYLES *** --- */


        /* --- 
        START: PREVIEW STYLES (Copied from original)
        --- */
        #rhf-preview-container .preview-header {
            font-size: 18px;
            font-weight: 600;
            color: #555;
            padding-bottom: 20px;
            border-bottom: 1px solid #f0f0f0;
            margin-bottom: 30px;
        }
        #rhf-preview-container .preview-header strong {
            font-weight: 700;
            color: #111;
        }
        .room-detail-container {
            display: flex;
            flex-wrap: wrap;
            gap: 40px; 
            align-items: flex-start; 
        }
        .room-main-content {
            flex: 0 0 45%; /* Adjusted width */
            width: 45%;
            min-width: 300px; 
        }
        .room-sidebar {
            flex: 1; 
            min-width: 300px; 
        }
        .room-detail-container-lower {
            display: flex;
            flex-wrap: wrap;
            gap: 40px;
            margin-top: 40px; 
        }
        .room-main-content-lower {
            flex: 2;
            min-width: 0;
            width: calc(100% - 350px - 40px); 
        }
        .room-sidebar-sticky { 
            flex: 0 0 380px; 
            width: 380px;
            min-width: 380px;
            position: sticky;
            top: 40px;
            align-self: flex-start;
        }
        .room-image-slider {
            position: relative;
            width: 100%;
            padding-top: 56.25%; /* 16:9 Aspect Ratio */
            height: 0;
            margin-bottom: 10px;
            border-radius: 12px;
            overflow: hidden;
            background: #f0f0f0;
        }
        .room-featured-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%; 
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 0;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.4s ease-in-out, visibility 0.4s ease-in-out;
        }
        .room-featured-image.is-active {
            opacity: 1;
            visibility: visible;
            z-index: 1;
        }
        .slider-dots {
            text-align: center;
            margin-bottom: 25px;
        }
        .slider-dots .dot {
            height: 10px;
            width: 10px;
            border-radius: 50%;
            display: inline-block;
            margin: 0 4px;
            background-color: #bbb;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .slider-dots .dot.active {
            background-color: #0d6efd;
        }
        .room-sidebar .entry-header {
            flex-grow: 1; 
            margin: 0; 
        }
        .room-entry-title {
            font-size: 32px;
            margin-bottom: 15px;
            font-weight: 700;
            color: #111;
            line-height: 1.3;
        }
        .room-meta-icons {
            margin-bottom: 20px;
            font-size: 16px;
            color: #555;
            line-height: 1.6;
        }
        .room-meta-icons p {
            margin: 8px 0;
            display: flex;
            align-items: center;
            font-weight: 500;
        }
        .room-meta-icons .meta-icon {
            margin-right: 12px;
            width: 32px; 
            height: 32px; 
            flex-shrink: 0;
            object-fit: contain;
        }
        .room-meta-text {
            margin-bottom: 30px;
            font-size: 14px;
            color: #777;
        }
        .room-meta-text strong {
            color: #333;
            font-weight: 500;
        }
        .room-meta-text span + span {
            margin-left: 15px;
        }
        .room-section-title {
            font-size: 22px;
            font-weight: 600;
            margin-top: 20px;
            margin-bottom: 15px;
        }
        .entry-content {
            font-size: 16px;
            line-height: 1.7;
            color: #333;
            word-wrap: break-word;
        }
        .general-info {
            margin-top: 30px;
            width: 250px; 
        }
        .general-info-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .general-info-list li {
            display: flex; 
            align-items: baseline;
            padding: 6px 0;
            font-size: 16px;
            color: #555;
        }
        .general-info-list li span:first-child {
            font-weight: 500;
        }
        .general-info-list li span:last-child { 
            font-weight: 600;
            color: #111;
            margin-left: 20px; 
        }
        .sidebar-card {
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 24px;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .sidebar-card-title {
            font-size: 20px;
            font-weight: 600;
            margin-top: 0;
            margin-bottom: 24px;
            color: #111;
        }
        .owner-info {
            display: flex;
            align-items: center;
            margin-bottom: 24px;
        }
        .owner-logo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
            border: 1px solid #eee;
            flex-shrink: 0;
        }
        .owner-details {
            line-height: 1.4;
        }
        .owner-name {
            display: block;
            font-size: 18px;
            font-weight: 600;
            color: #111;
        }
        .owner-company {
            display: block;
            font-size: 14px;
            color: #0073e6;
            font-weight: 500;
        }
        .contact-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.2s;
            word-break: break-all;
        }
        .contact-button .contact-icon {
            width: 20px;
            height: 20px;
            object-fit: contain;
            flex-shrink: 0;
        }
        .contact-button-phone {
            background: #A32441;
            color: #fff;
            margin-bottom: 15px;
        }

        /* --- NEW: Fix for long text in buttons (e.g., email) --- */
        .contact-button span {
            word-break: break-all;
            overflow-wrap: break-word;
            min-width: 0; /* Allow flex item to shrink and wrap */
        }

        /* --- ADDED HOVER FIX --- */
        .contact-button-phone:hover {
            color: #fff !important; 
        }

        .contact-or-divider {
            text-align: center;
            margin: 15px 0;
            color: #999;
            font-size: 14px;
        }
        .contact-button-email {
            background: #fff;
            border: 1px solid #A32441;
            color: #A32441;
        }
        .contact-button-email:hover {
            background: #f8f8f8;
            border-color: #aaa;
            color: #333 !important; /* --- ADDED HOVER FIX --- */
        }
        .contact-button-email svg {
            stroke: #A32441;
            fill: none;
            stroke-width: 1.5;
        }

        /* --- New Preview-Specific Buttons --- */
        .rhf-preview-buttons {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
        }
        .rhf-preview-buttons button {
            padding: 12px 20px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            font-family: 'Nunito Sans', sans-serif;
            border: 1px solid transparent;
        }
        #rhf-edit-button {
            background-color: #fff;
            border-color: #333;
            color: #333;
        }
        #rhf-edit-button:hover {
            background-color: #f8f8f8;
        }
        #rhf-final-submit-button {
            background-color: #A32441; /* CHANGED */
            color: #fff;
            border-color: #A32441; /* CHANGED */
        }
        #rhf-final-submit-button:hover {
            background-color: #8a1f37; /* CHANGED */
        }
        
        /* --- --- --- --- --- --- --- --- --- --- --- --- */
        /* --- NEW/MODIFIED STYLES FOR IMAGE DELETION & PREVIEW --- */
        /* --- --- --- --- --- --- --- --- --- --- --- --- */
        .rhf-existing-images,
        #rhf-new-image-previews { /* --- NEW: Target new preview container --- */
            display: flex; 
            flex-wrap: wrap; 
            gap: 10px; 
            margin-top: 10px;
            padding: 10px;
            background: #f9f9f9;
            border: 1px solid #eee;
            border-radius: 4px;
        }
        .rhf-image-preview-item,
        .rhf-new-image-item { /* --- NEW: Target new preview item --- */
            position: relative;
            width: 100px; 
            height: 100px;
            display: block;
        }
        .rhf-image-preview-item img,
        .rhf-new-image-item img { /* --- NEW: Target new preview img --- */
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .rhf-image-preview-remove,
        .rhf-new-image-remove { /* --- NEW: Target new preview remove button --- */
            position: absolute;
            top: -5px;
            right: -5px;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #006881;
            color: #fff;
            border: 1px solid #fff;
            font-weight: bold;
            font-size: 16px;
            line-height: 20px;
            text-align: center;
            cursor: pointer;
            padding: 0;
            font-family: 'Arial', sans-serif;
            box-shadow: 0 1px 3px rgba(0,0,0,0.3);
            z-index: 2;
        }
        .rhf-image-preview-remove:hover,
        .rhf-new-image-remove:hover { /* --- NEW: Target new preview remove hover --- */
            background: #A32441;
        }

        /* --- START: TABLET RESPONSIVE STYLES --- */
        @media (max-width: 900px) {
            .room-detail-container,
            .room-detail-container-lower {
                flex-direction: column;
            }
            .room-main-content,
            .room-sidebar,
            .room-main-content-lower,
            .room-sidebar-sticky {
                width: 100%;
                flex: 1 1 100%;
                min-width: 0; /* --- ADDED THIS LINE TO FIX CROPPING --- */
            }
            .room-sidebar-sticky {
                position: static;
            }
        }
        /* --- END: TABLET RESPONSIVE STYLES --- */

        /* --- START: MOBILE RESPONSIVE STYLES (WITH OPTIMIZATIONS) --- */
        @media (max-width: 768px) {
            
            /* --- NEW: Reduce padding on main containers --- */
            .rhf-form-container,
            #rhf-preview-container {
                padding: 20px 15px; 
            }
            
            /* --- Form Stacking --- */
            .rhf-form-row { flex-direction: column; align-items: flex-start; }
            .rhf-form-row label { width: 100%; margin-bottom: 8px; padding-right: 0; padding-top: 0; }
            .rhf-form-row input, .rhf-form-row select, .rhf-form-row textarea, .rhf-form-row .rhf-field-container { width: 100%; }
            .submit-buttons { flex-direction: column-reverse; width: 100%; }
            .submit-buttons button { width: 100%; justify-content: center; }
            .rhf-form-row input.rhf-datepicker {
                width: 100%;
                max-width: 100%;
            }
            
            /* --- NEW: Preview Optimizations --- */
            .room-entry-title {
                font-size: 26px; /* Reduce title size on mobile */
            }
            .sidebar-card {
                padding: 15px; /* Reduce card padding on mobile */
                width: 98%; 
                margin-left: auto; 
                margin-right: auto; 
            }

            /* --- Preview Button Stacking --- */
            .rhf-preview-buttons {
                flex-direction: column-reverse;
                gap: 15px;
            }
            .rhf-preview-buttons button {
                width: 100%;
                justify-content: center;
            }
        }
        /* --- END: MOBILE RESPONSIVE STYLES --- */
CSS;
        // --- END OF CSS ---

        // --- START OF CODE CHANGE: Removed esc_html() to fix CSS rendering bug. ---
        echo '<style type="text/css">' . $css . '</style>';
        // --- END OF CODE CHANGE ---
    }
}
add_action( 'wp_head', 'rhf_edit_add_inline_styles' );


/**
 * 2. Register the shortcode [my_room_edit_form]
 */
function rhf_edit_form_shortcode_handler() {

    // --- EDIT: Security Check ---
    if ( ! is_user_logged_in() ) {
        return '<p class="rhf-message error">You must be logged in to edit a room.</p>';
    }

    if ( ! isset($_GET['room_id']) || empty($_GET['room_id']) ) {
        return '<p class="rhf-message error">Error: No room was specified for editing.</p>';
    }

    $post_id = intval($_GET['room_id']);
    $post_to_edit = get_post($post_id);

    // Security Check: Ensure the post exists, is the correct type, and belongs to the current user
    if ( ! $post_to_edit || $post_to_edit->post_author != get_current_user_id() || $post_to_edit->post_type != 'room-for-hire' ) {
        return '<p class="rhf-message error">Error: You do not have permission to edit this room, or the room does not exist.</p>';
    }
    // --- End Security Check ---


    // Make sure the ACF function `acf_form_head` is loaded on the page.
    if ( function_exists( 'acf_form_head' ) ) {
        acf_form_head();
    }
    
    // Start output buffering to capture the form HTML.
    ob_start();

    // Handle and display any success/error messages.
    rhf_edit_display_messages();

    // The main form function will be called here.
    rhf_render_edit_form( $post_to_edit );
    
    // Return the captured HTML.
    return ob_get_clean();
}
add_shortcode( 'my_room_edit_form', 'rhf_edit_form_shortcode_handler' );

/**
 * 3. Render the EDIT form HTML
 */
function rhf_render_edit_form( $post_to_edit ) {
    
    $post_id = $post_to_edit->ID;
    
    // Get errors and previously submitted data from the session (for failed validation)
    $errors = $_SESSION['rhf_form_errors'] ?? [];
    $form_data = $_SESSION['rhf_form_data'] ?? [];

    // Clear the session data so it's not shown again on refresh.
    unset( $_SESSION['rhf_form_errors'], $_SESSION['rhf_form_data'] );

    // --- START: Data Loading Fix ---
    
    // 1. Load all data from the post first.
    $title   = $post_to_edit->post_title;
    $content = $post_to_edit->post_content;
    $area      = get_field('area', $post_id);
    $floor     = get_field('floor', $post_id);
    $total_room = get_field('total_room', $post_id);
    $state     = get_field('state', $post_id);
    $room_location = get_field('room_location', $post_id); // *** ADDED ***
    $listed_by = get_field('listed_by', $post_id);
    $contact_name = get_field('contact_person', $post_id); // Your meta key is 'contact_person'
    $contact_email = get_field('contact_email', $post_id);
    $contact_phone = get_field('contact_phone', $post_id);
    $current_room_status = get_field('room_status', $post_id);
    $preferred_date_acf = get_field('start_date', $post_id); // This is 'Ymd'

    // --- NEW: Load existing lat/lng ---
    $rhf_lat = get_post_meta($post_id, 'rhf_latitude', true);
    $rhf_lng = get_post_meta($post_id, 'rhf_longitude', true);

    // 2. If validation failed (form_data exists), overwrite with that data
    if ( ! empty( $form_data ) ) {
        $title   = $form_data['room_title'] ?? $title;
        $content = $form_data['description'] ?? $content;
        $area      = $form_data['area'] ?? $area;
        $floor     = $form_data['floor'] ?? $floor;
        $total_room = $form_data['total_room'] ?? $total_room;
        $state     = $form_data['state'] ?? $state;
        $room_location = $form_data['room_location'] ?? $room_location; // *** ADDED ***
        $listed_by = $form_data['listed_by'] ?? $listed_by;
        $contact_name = $form_data['contact_name'] ?? $contact_name;
        $contact_email = $form_data['contact_email'] ?? $contact_email;
        $contact_phone = $form_data['contact_phone'] ?? $contact_phone;
        $current_room_status = $form_data['room_status'] ?? $current_room_status;
        
        // --- NEW: Overwrite lat/lng with failed form data ---
        $rhf_lat = $form_data['rhf_lat'] ?? $rhf_lat;
        $rhf_lng = $form_data['rhf_lng'] ?? $rhf_lng;
        
        // Special handling for date, as formats differ
        if ( isset( $form_data['start_date'] ) ) {
            // Use data from failed validation (already in 'Y-m-d')
            $preferred_date_datepicker = $form_data['start_date']; 
        }
    }

    // 3. Prepare date for datepicker
    // Only run this if $preferred_date_datepicker was NOT set by form_data
    if ( ! isset( $preferred_date_datepicker ) ) {
        $preferred_date_datepicker = '';
        if ($preferred_date_acf) { // This is the 'Ymd' from get_field()
            $d = DateTime::createFromFormat('Ymd', $preferred_date_acf);
            if ($d) {
                $preferred_date_datepicker = $d->format('Y-m-d');
            }
        }
    }
    // --- END: Data Loading Fix ---
    
    // Get existing images
    $existing_gallery_data = get_post_meta($post_id, 'easy_pg_data', true);
    $existing_images = $existing_gallery_data['image_url'] ?? [];

    ?>
    <div class="rhf-form-container">
        <form id="rhf-edit-room-form" name="edit_room" method="post" action="" enctype="multipart/form-data" novalidate>
            
            <div class="rhf-form-row">
                <label for="room_title">Room Title<span class="required">*</span></label>
                <div class="rhf-field-container">
                    <input type="text" id="room_title" name="room_title" placeholder="Enter a title for your room listing" required class="<?php echo isset($errors['room_title']) ? 'error' : ''; ?>" value="<?php echo esc_attr( $title ); ?>">
                    <?php if ( isset( $errors['room_title'] ) ) : ?>
                        <span class="rhf-error-message"><?php echo esc_html( $errors['room_title'] ); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="rhf-form-row">
                <label for="description">Description <span class="required">*</span></label>
                <div class="rhf-field-container">
                    <?php
                    $editor_id = 'description';
                    $editor_class = isset($errors['description']) ? 'rhf-description-editor error' : 'rhf-description-editor';
                    $settings = array(
                        'textarea_name' => 'description',
                        'textarea_rows' => 8,
                        'media_buttons' => false,
                        'tinymce'       => array(
                            'toolbar1' => 'bold,italic,strikethrough,bullist,numlist,link',
                            'toolbar2' => '',
                        ),
                        'editor_class' => $editor_class,
                    );
                    wp_editor( $content, $editor_id, $settings );
                    ?>
                    <div class="description-meta">
                        <small class="helper-text" id="description-counter">Maximum 500 characters &nbsp; <span id="char-count">0 / 500</span></small>
                    </div>
                     <?php if ( isset( $errors['description'] ) ) : ?>
                        <span class="rhf-error-message"><?php echo esc_html( $errors['description'] ); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="rhf-form-row">
                <label for="area">Area (square metres)</label> <div class="rhf-field-container">
                    <input type="number" id="area" name="area" placeholder="Room size in square metres" value="<?php echo esc_attr( str_replace(' square metres', '', $area) ); ?>" class="<?php echo isset($errors['area']) ? 'error' : ''; ?>" min="0" step="any">
                    <?php if ( isset( $errors['area'] ) ) : ?>
                        <span class="rhf-error-message"><?php echo esc_html( $errors['area'] ); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="rhf-form-row">
                <label for="floor">Number of floors</label>
                <div class="rhf-field-container">
                    <input type="number" id="floor" name="floor" placeholder="Specify floor count" class="<?php echo isset($errors['floor']) ? 'error' : ''; ?>" value="<?php echo esc_attr( $floor ); ?>" min="0" step="1">
                    <?php if ( isset( $errors['floor'] ) ) : ?>
                        <span class="rhf-error-message"><?php echo esc_html( $errors['floor'] ); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="rhf-form-row">
                <label for="total_room">Total room no</label>
                <div class="rhf-field-container">
                    <input type="number" id="total_room" name="total_room" placeholder="Specify room count" class="<?php echo isset($errors['total_room']) ? 'error' : ''; ?>" value="<?php echo esc_attr( $total_room ); ?>" min="0" step="1">
                    <?php if ( isset( $errors['total_room'] ) ) : ?>
                        <span class="rhf-error-message"><?php echo esc_html( $errors['total_room'] ); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="rhf-form-row">
                <label for="room_status">Room Status <span class="required">*</span></label>
                 <div class="rhf-field-container">
                    <select id="room_status" name="room_status" required class="<?php echo isset($errors['room_status']) ? 'error' : ''; ?>">
                        <option value="">Select Status</option>
                        <?php
                        // --- REVERT: Restored original ACF loop logic ---
                        $room_status_choices = [];
                        if ( function_exists( 'acf_get_field_groups' ) && function_exists('acf_get_fields') ) {
                            $field_groups = acf_get_field_groups();
                            foreach ( $field_groups as $group ) {
                                $is_room_group = false;
                                if ( ! empty( $group['location'] ) ) {
                                    foreach ( $group['location'] as $location_rules ) {
                                        foreach ( $location_rules as $rule ) {
                                            if ( $rule['param'] === 'post_type' && $rule['operator'] === '==' && $rule['value'] === 'room-for-hire' ) {
                                                $is_room_group = true;
                                                break 2;
                                            }
                                        }
                                    }
                                }
                                if ( $is_room_group ) {
                                    $fields = acf_get_fields( $group['key'] );
                                    foreach ( $fields as $field ) {
                                        if ( $field['name'] === 'room_status' && ! empty( $field['choices'] ) ) {
                                            $room_status_choices = $field['choices'];
                                            break 2;
                                        }
                                    }
                                }
                            }
                        }
                        
                        if ( ! empty( $room_status_choices ) ) {
                            foreach ( $room_status_choices as $value => $label ) {
                                // We use $value for the option value and $label for the display text
                                echo '<option value="' . esc_attr( $value ) . '" ' . selected( $current_room_status, $value, false ) . '>' . esc_html( $label ) . '</option>';
                            }
                        } else {
                            // Fallback if ACF field isn't found (e.g., hardcode common values)
                            echo '<option value="available" ' . selected( $current_room_status, 'available', false ) . '>Available</option>';
                            echo '<option value="booked" ' . selected( $current_room_status, 'booked', false ) . '>Booked</option>';
                        }
                        ?>
                    </select>
                    <?php if ( isset( $errors['room_status'] ) ) : ?>
                        <span class="rhf-error-message"><?php echo esc_html( $errors['room_status'] ); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="rhf-form-row">
                <label for="state">State</label>
                 <div class="rhf-field-container">
                    <select id="state" name="state" class="<?php echo isset($errors['state']) ? 'error' : ''; ?>"> <?php
                        // --- REVERT: Restored original ACF loop logic ---
                        $location_choices = [];
                        if ( function_exists( 'acf_get_field_groups' ) && function_exists('acf_get_fields') ) {
                            $field_groups = acf_get_field_groups();
                            foreach ( $field_groups as $group ) {
                                $is_room_group = false;
                                if ( ! empty( $group['location'] ) ) {
                                    foreach ( $group['location'] as $location_rules ) {
                                        foreach ( $location_rules as $rule ) {
                                            if ( $rule['param'] === 'post_type' && $rule['operator'] === '==' && $rule['value'] === 'room-for-hire' ) {
                                                $is_room_group = true;
                                                break 2;
                                            }
                                        }
                                    }
                                }
                                if ( $is_room_group ) {
                                    $fields = acf_get_fields( $group['key'] );
                                    foreach ( $fields as $field ) {
                                        if ( $field['name'] === 'state' && ! empty( $field['choices'] ) ) {
                                            $location_choices = $field['choices'];
                                            break 2;
                                        }
                                    }
                                }
                            }
                        }
                        if ( ! empty( $location_choices ) ) {
                            $selected_location = $state;
                            echo '<option value="">Select State</option>'; // *** ADDED: Empty option ***
                            foreach ( $location_choices as $value => $label ) {
                                echo '<option value="' . esc_attr( $value ) . '" ' . selected( $selected_location, $value, false ) . '>' . esc_html( $label ) . '</option>';
                            }
                        }
                        ?>
                    </select>
                    <?php if ( isset( $errors['state'] ) ) : ?>
                        <span class="rhf-error-message"><?php echo esc_html( $errors['state'] ); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="rhf-form-row">
                <label for="room_location">Room Location <span class="required">*</span></label>
                <div class="rhf-field-container">
                    <input type="text" id="room_location" name="room_location" placeholder="Start typing an address..." required class="<?php echo isset($errors['room_location']) ? 'error' : ''; ?>" value="<?php echo esc_attr( $room_location ); ?>">
                    <small class="helper-text">Enter Room location.</small>
                    <?php if ( isset( $errors['room_location'] ) ) : ?>
                        <span class="rhf-error-message"><?php echo esc_html( $errors['room_location'] ); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="rhf-form-row">
                <label for="listed_by">Listed By</label>
                 <div class="rhf-field-container">
                    <input type="text" id="listed_by" name="listed_by" placeholder="Enter name of person listing " class="<?php echo isset($errors['listed_by']) ? 'error' : ''; ?>" value="<?php echo esc_attr( $listed_by ); ?>">
                    <?php if ( isset( $errors['listed_by'] ) ) : ?>
                        <span class="rhf-error-message"><?php echo esc_html( $errors['listed_by'] ); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="rhf-form-row">
                <label for="contact_name">Contact Person <span class="required">*</span></label>
                <div class="rhf-field-container">
                    <input type="text" id="contact_name" name="contact_name" placeholder="Your name" required class="<?php echo isset($errors['contact_name']) ? 'error' : ''; ?>" value="<?php echo esc_attr( $contact_name ); ?>">
                     <?php if ( isset( $errors['contact_name'] ) ) : ?>
                        <span class="rhf-error-message"><?php echo esc_html( $errors['contact_name'] ); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="rhf-form-row">
                <label for="contact_email">Contact Email <span class="required">*</span></label>
                 <div class="rhf-field-container">
                    <input type="email" id="contact_email" name="contact_email" placeholder="Enter your email address" required class="<?php echo isset($errors['contact_email']) ? 'error' : ''; ?>" value="<?php echo esc_attr( $contact_email ); ?>">
                     <?php if ( isset( $errors['contact_email'] ) ) : ?>
                        <span class="rhf-error-message"><?php echo esc_html( $errors['contact_email'] ); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="rhf-form-row">
                <label for="contact_phone">Contact Phone</label>
                 <div class="rhf-field-container">
                    <input type="tel" id="contact_phone" name="contact_phone" placeholder="Enter your phone number" class="<?php echo isset($errors['contact_phone']) ? 'error' : ''; ?>" value="<?php echo esc_attr( $contact_phone ); ?>">
                     <?php if ( isset( $errors['contact_phone'] ) ) : ?>
                        <span class="rhf-error-message"><?php echo esc_html( $errors['contact_phone'] ); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="rhf-form-row">
                 <label for="start_date">Preferred Start Date <span class="required">*</span></label>
                 <div class="rhf-field-container">
                    <input type="text" id="start_date" name="start_date" class="rhf-datepicker <?php echo isset($errors['start_date']) ? 'error' : ''; ?>" placeholder="Select a date" value="<?php echo esc_attr( $preferred_date_datepicker ); ?>" required>
                    <?php if ( isset( $errors['start_date'] ) ) : ?>
                        <span class="rhf-error-message"><?php echo esc_html( $errors['start_date'] ); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="rhf-form-row">
                <label for="room_images">Room Images</label>
                <div class="rhf-field-container">
                    <?php 
                    if ( ! empty($existing_images) ) : ?>
                        <div>
                            <strong style="font-weight: 500; font-size: 15px;">Existing Images:</strong>
                            <div class="rhf-existing-images">
                                <?php foreach ($existing_images as $img_url) : ?>
                                    <div class="rhf-image-preview-item" data-image-url="<?php echo esc_url($img_url); ?>">
                                        <img src="<?php echo esc_url($img_url); ?>" alt="Existing room image">
                                        <button type="button" class="rhf-image-preview-remove" title="Remove image">&times;</button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <br>
                    <?php endif; ?>
                    
                    <input type="file" id="room_images" name="room_images[]" multiple="multiple" accept="image/png, image/jpeg, image/gif">
                    
                    <div id="rhf-new-image-previews"></div>
                    
                    <small class="helper-text">
                        Click "X" on existing images to remove. Upload new images to add them.
                    </small>
                    
                    <input type="hidden" id="rhf_images_to_remove" name="rhf_images_to_remove" value="">

                </div>
            </div>
            <div class="rhf-form-row checkbox-row">
                <div></div> <div class="rhf-field-container">
                     <input type="checkbox" id="terms" name="terms" required>
                     <label for="terms">I have read and accept the <a href="/terms-and-conditions/" target="_blank">Terms & Conditions of Use</a>.</label>
                      <?php if ( isset( $errors['terms'] ) ) : ?>
                        <span class="rhf-error-message" style="margin-left: 25px;"><?php echo esc_html( $errors['terms'] ); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <input type="hidden" id="rhf_lat" name="rhf_lat" value="<?php echo esc_attr( $rhf_lat ); ?>">
            <input type="hidden" id="rhf_lng" name="rhf_lng" value="<?php echo esc_attr( $rhf_lng ); ?>">

            <input type="hidden" name="post_id_to_edit" value="<?php echo esc_attr($post_id); ?>" />
            <?php wp_nonce_field( 'rhf_edit_room_action', 'rhf_edit_nonce' ); ?>
            
            <div class="rhf-form-row submit-buttons">
                <button type="submit" name="submit_action" value="submit" class="submit-button" id="rhf-review-and-update-button">
                    Review & Update
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8z"/></svg>
                </button>
            </div>
        </form>
    </div>

    <div id="rhf-preview-container" style="display: none;">
        <div class="preview-header">
            <strong>Review Room Detail</strong>
            <span style="font-weight: 400; color: #555;"> - Please review your room details below.</span>
        </div>

        <article class="room-detail-article">
            
            <div class="room-detail-container">

                <div class="room-main-content">
                    <div class="room-image-slider" id="preview-image-slider">
                        <?php if ( ! empty($existing_images) ) : ?>
                            <?php foreach ($existing_images as $index => $img_url) : ?>
                                <img src="<?php echo esc_url($img_url); ?>" class="room-featured-image <?php echo $index === 0 ? 'is-active' : ''; ?>" data-slide-image="<?php echo $index; ?>" data-image-url="<?php echo esc_url($img_url); // --- NEW: Added URL for preview filtering --- ?>">
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="slider-dots" id="preview-slider-dots">
                        <?php if ( count($existing_images) > 1 ) : ?>
                            <?php foreach ($existing_images as $index => $img_url) : ?>
                                <span class="dot <?php echo $index === 0 ? 'active' : ''; ?>" data-slide-index="<?php echo $index; ?>"></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="room-sidebar"> 
                    <header class="entry-header">
                        <h1 class="room-entry-title" id="preview-room-title"></h1>
                    </header>
                
                    <div class="room-meta-icons">
                        <p id="preview-meta-state-wrapper" style="display: none;">
                            <img src="https://aaswjobstaging.wpenginepowered.com/wp-content/uploads/2025/10/location-icon.png" alt="Location Icon" class="meta-icon">
                            <span id="preview-meta-state"></span>
                        </p>
                        <p id="preview-meta-area-wrapper" style="display: none;">
                            <img src="https://aaswjobstaging.wpenginepowered.com/wp-content/uploads/2025/10/area-icon.png" alt="Dimensions Icon" class="meta-icon">
                            <span id="preview-meta-area"></span>
                        </p>
                    </div>

                    <div class="room-meta-text">
                        <span id="preview-meta-listedby-wrapper" style="display: none;">
                            Listed by: <strong id="preview-meta-listedby"></strong>
                        </span>
                        <span id="preview-meta-startdate-wrapper" style="display: none; margin-left: 15px;">
                            Start Date: <strong id="preview-meta-startdate"></strong>
                        </span>
                    </div>
                </div>
            </div>

            <div class="room-detail-container-lower">
                
                <div class="room-main-content-lower">
                    <div class="entry-content" id="preview-description-wrapper" style="display: none;">
                        <h2 class="room-section-title">Description</h2>
                        <div id="preview-description"></div>
                    </div>

                    <div class="general-info" id="preview-general-info-wrapper" style="display: none;">
                        <h2 class="room-section-title">General information</h2>
                        <ul class="general-info-list">
                            <li id="preview-info-floor-wrapper" style="display: none;">
                                <span style= "width: 110px;">Floor</span>
                                <span id="preview-info-floor"></span>
                            </li>
                            <li id="preview-info-totalroom-wrapper" style="display: none;">
                                <span style= "width: 110px;">Total room</span>
                                <span id="preview-info-totalroom"></span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="room-sidebar-sticky">
                    <div class="sidebar-card contact-owner-card">
                        <h3 class="sidebar-card-title">Contact Room Owner</h3>
                        
                        <div class="owner-info">
                            <div class="owner-details">
                                <span class="owner-name" id="preview-owner-name"></span>
                            </div>
                        </div>

                        <a href="#" id="preview-contact-phone-link" class="contact-button contact-button-phone" style="display: none;">
                            <img src="https://aaswjobstaging.wpenginepowered.com/wp-content/uploads/2025/10/Vector.png" alt="Phone Icon" class="contact-icon">
                            <span id="preview-contact-phone"></span>
                        </a>

                        <p class="contact-or-divider" id="preview-contact-divider" style="display: none;">or</p>

                        <a href="#" id="preview-contact-email-link" class="contact-button contact-button-email" style="display: none;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            <span id="preview-contact-email"></span>
                        </a>
                    </div>
                </div>
            </div>
        </article>

        <div class="rhf-preview-buttons">
            <button type="button" id="rhf-edit-button">
                <svg style="width: 16px; height: 16px;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/></svg>
                Edit
            </button>
            <button type="button" id="rhf-final-submit-button">
                Submit Update
                <svg style="width: 16px; height: 16px;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8z"/></svg>
            </button>
        </div>

    </div>
    
<script>
    // --- *** NEW: GOOGLE AUTOCOMPLETE FUNCTION *** ---
    // This function is called by the Google Maps API script (from Step 1)
    function initAutocomplete() {
        const input = document.getElementById('room_location');
        if (!input) {
            console.error('Room location input field not found.');
            return;
        }
        
        const autocomplete = new google.maps.places.Autocomplete(input, {
            componentRestrictions: { 'country': ['au'] }, // Restrict to Australia
            fields: ['address_components', 'name', 'geometry'], // --- MODIFIED: Added 'geometry' (THE FIX) ---
        });

        autocomplete.addListener('place_changed', () => {
            const place = autocomplete.getPlace();
            
            let street_number = '';
            let route = '';         // Street name
            let locality = '';      // Suburb
            let state = '';         // State
            
            if (!place.address_components) return;

            for (const component of place.address_components) {
                const types = component.types;
                
                if (types.includes('street_number')) {
                    street_number = component.long_name;
                }
                if (types.includes('route')) {
                    route = component.long_name;
                }
                if (types.includes('locality')) {
                    locality = component.long_name;
                }
                if (types.includes('administrative_area_level_1')) {
                    state = component.short_name; // e.g., "NSW"
                }
            }
            
            // 1. Set the 'room_location' field value
            let locationText = `${street_number} ${route}`.trim();
            if (locationText && locality) {
                input.value = `${locationText}, ${locality}`;
            } else if (locality) {
                input.value = locality;
            } else if (locationText) {
                input.value = locationText;
            }
            // Trigger blur to validate the field
            jQuery(input).trigger('blur');

            // 2. Set the 'state' dropdown value
            if (state) {
                jQuery('#state').val(state).trigger('change');
            }

            // --- NEW: Set the Lat/Lng hidden fields ---
            if (place.geometry && place.geometry.location) {
                jQuery('#rhf_lat').val(place.geometry.location.lat());
                jQuery('#rhf_lng').val(place.geometry.location.lng());
            } else {
                // Clear them if no geometry is found
                jQuery('#rhf_lat').val('');
                jQuery('#rhf_lng').val('');
            }
        });
    }
    // --- *** END OF NEW FUNCTION *** ---

    jQuery(document).ready(function($) {
        
        const $form = $('#rhf-edit-room-form');
        const $formContainer = $('.rhf-form-container');
        const $previewContainer = $('#rhf-preview-container');
        const existingImages = <?php echo json_encode($existing_images); ?>;
        
        // --- --- --- --- --- --- --- --- --- --- --- ---
        // --- NEW: LOGIC FOR IMAGE DELETION (EXISTING) ---
        // --- --- --- --- --- --- --- --- --- --- --- ---
        let imagesToRemove = [];
        const $hiddenRemoveInput = $('#rhf_images_to_remove');
        
        $form.on('click', '.rhf-image-preview-remove', function() {
            const $item = $(this).closest('.rhf-image-preview-item');
            const urlToRemove = $item.data('image-url');
            
            if (urlToRemove) {
                if (!imagesToRemove.includes(urlToRemove)) {
                    imagesToRemove.push(urlToRemove);
                }
                $hiddenRemoveInput.val(JSON.stringify(imagesToRemove));
                $item.hide();
            }
        });
        
        // --- --- --- --- --- --- --- --- --- --- --- ---
        // --- NEW: LOGIC FOR NEW IMAGE PREVIEW & DELETION ---
        // --- --- --- --- --- --- --- --- --- --- --- ---
        const $fileInput = $('#room_images');
        const $newPreviewsContainer = $('#rhf-new-image-previews');
        let newFileStore = new DataTransfer(); // This will hold our selected files

        // Function to render previews for the new files
        function renderNewPreviews() {
            $newPreviewsContainer.empty(); // Clear old previews
            
            Array.from(newFileStore.files).forEach((file, index) => {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const previewHtml = `
                        <div class="rhf-new-image-item">
                            <img src="${e.target.result}" alt="${file.name}">
                            <button type="button" class="rhf-new-image-remove" data-index="${index}" title="Remove image">&times;</button>
                        </div>
                    `;
                    $newPreviewsContainer.append(previewHtml);
                };
                
                reader.readAsDataURL(file);
            });
        }

        // Listen for when files are added
        $fileInput.on('change', function(e) {
            // Add the new files to our DataTransfer object
            for (let file of e.target.files) {
                newFileStore.items.add(file);
            }
            
            // Update the input's files with our stored files
            e.target.files = newFileStore.files;
            
            // Re-render the previews
            renderNewPreviews();
        });

        // Listen for clicks on the new preview remove buttons
        $newPreviewsContainer.on('click', '.rhf-new-image-remove', function() {
            const indexToRemove = $(this).data('index');
            
            // Create a new DataTransfer store and add all files *except* the one to remove
            let tempStore = new DataTransfer();
            for (let i = 0; i < newFileStore.files.length; i++) {
                if (i !== indexToRemove) {
                    tempStore.items.add(newFileStore.files[i]);
                }
            }
            
            // Replace our global store and update the file input
            newFileStore = tempStore;
            $fileInput[0].files = newFileStore.files;
            
            // Re-render the previews
            renderNewPreviews();
        });

        // --- --- --- --- --- --- --- --- --- --- --- ---
        // --- END: NEW IMAGE LOGIC ---
        // --- --- --- --- --- --- --- --- --- --- --- ---

        if (!$form.length) return;

        // --- *** NEW: Prevent form submission on Enter key for autocomplete *** ---
        $('#room_location').on('keydown keypress', function(e) {
            if (e.keyCode === 13 && $('.pac-container:visible').length > 0) {
                e.preventDefault();
            }
        });
        // --- *** END OF FIX *** ---

        // --- *** NEW: Initialize Select2 *** ---
        $('#state, #room_status').select2({ // --- MODIFIED: Added #room_status
            width: '100%', 
            minimumResultsForSearch: Infinity, 
            placeholder: 'Select State' 
        });
        // Note: No validation added to 'state' on change, as it's not a required field in this form.

        // --- NEW: CLEAR LAT/LNG IF ADDRESS MANUALLY CLEARED ---
        $('#room_location').on('input', function() {
            // If user types and clears the field, clear the coordinates
            if ($(this).val() === '') {
                $('#rhf_lat').val('');
                $('#rhf_lng').val('');
            }
        });

        // --- VALIDATION HELPER FUNCTIONS (Copied) ---
        const showError = ($field, message) => {
            clearError($field);
            $field.addClass('error');
            if ($field.is('select')) { // --- ADDED Select2 ---
                $field.next('.select2-container').find('.select2-selection--single').addClass('error');
            }
            const $container = $field.parent();
            const $error = $('<span></span>').addClass('rhf-error-message').text(message);
            if ($field.attr('type') === 'checkbox') {
                $error.css('margin-left', '25px');
                $container.append($error);
            } else {
                $container.append($error);
            }
        };
        const clearError = ($field) => {
            $field.removeClass('error');
            if ($field.is('select')) { // --- ADDED Select2 ---
                $field.next('.select2-container').find('.select2-selection--single').removeClass('error');
            }
            $field.parent().find('.rhf-error-message').remove();
        };
        const showTinyMCEError = (message) => {
            const $editorWrap = $('#wp-description-wrap');
            clearTinyMCEError();
            $editorWrap.addClass('error');
            const $container = $editorWrap.closest('.rhf-field-container');
            $container.append($('<span></span>').addClass('rhf-error-message').text(message));
        };
        const clearTinyMCEError = () => {
             const $editorWrap = $('#wp-description-wrap');
             $editorWrap.removeClass('error');
             $editorWrap.closest('.rhf-field-container').find('.rhf-error-message').remove();
        };

        // --- INDIVIDUAL VALIDATION FUNCTIONS (Copied) ---
        const validateRoomTitle = () => {
            const $field = $('#room_title');
            const value = $field.val().trim();
            if (value === '') { showError($field, 'This field is required. Please input the room title.'); return false; }
            // --- FIX: Removed number validation ---
            clearError($field); return true;
        };
        const validateDescription = () => {
            if (typeof tinymce !== 'undefined') {
                const editor = tinymce.get('description');
                if (editor && editor.getContent({ format: 'text' }).trim() === '') {
                    showTinyMCEError('This field is required. Please provide a description.'); return false;
                }
            }
            clearTinyMCEError(); return true;
        };
        // --- START: NEW Room Status ---
        const validateRoomStatus = () => {
            const $field = $('#room_status');
            const value = $field.val().trim();
            if (value === '') { showError($field, 'Please select a status.'); return false; } // --- MODIFIED: Changed message
            clearError($field); return true;
        };
        // --- END: NEW Room Status ---

        // --- *** START: NEW Room Location *** ---
        const validateRoomLocation = () => {
            const $field = $('#room_location');
            const value = $field.val().trim();
            if (value === '') { showError($field, 'This field is required. Please enter the room location.'); return false; }
            clearError($field); return true;
        };
        // --- *** END: NEW Room Location *** ---

        const validateListedBy = () => {
            const $field = $('#listed_by');
            // --- FIX: Removed number validation ---
            clearError($field); return true;
        };
        const validateContactName = () => {
            const $field = $('#contact_name');
            const value = $field.val().trim();
            if (value === '') { showError($field, 'This field is required. Please input your name.'); return false; }
            // --- FIX: Removed number validation ---
            clearError($field); return true;
        };
        const validateContactEmail = () => {
            const $field = $('#contact_email');
            const value = $field.val().trim();
            const emailPattern = /^\S+@\S+\.\S+$/;
            if (!emailPattern.test(value)) { showError($field, 'A valid email address is required.'); return false; }
            clearError($field); return true;
        };
        
        // --- START MODIFICATION: Phone Validation ---
        const validateContactPhone = () => {
            const $field = $('#contact_phone');
            const value = $field.val().trim();
            // Regex for Australian phone numbers (from user)
            const ausPhonePattern = /^(?:\+?(61))? ?(?:\((?=.*\)))?(0?[2-57-8])\)? ?(\d\d(?:[- ](?=\d{3})|(?!\d\d[- ]?\d[- ]))\d\d[- ]?\d[- ]?\d{3})$/;
            
            if (value !== '' && !ausPhonePattern.test(value)) { 
                showError($field, 'Please enter a valid Australian phone number (e.g., +61 2 9123 4567, 0412 345 678).'); 
                return false; 
            }
            clearError($field); 
            return true;
        };
        // --- END MODIFICATION ---

        const validateFloor = () => {
            const $field = $('#floor');
            const value = $field.val().trim();
            const digitPattern = /^\d+$/;
            if (value !== '' && ( !digitPattern.test(value) || parseInt(value) < 0 ) ) { showError($field, 'Please enter a valid whole number (0 or more).'); return false; }
            clearError($field); return true;
        };
        const validateTotalRoom = () => {
            const $field = $('#total_room');
            const value = $field.val().trim();
            const digitPattern = /^\d+$/;
            if (value !== '' && ( !digitPattern.test(value) || parseInt(value) < 0 ) ) { showError($field, 'Please enter a valid whole number (0 or more).'); return false; }
            clearError($field); return true;
        };
        // --- START: NEW Area Validation ---
        const validateArea = () => {
            const $field = $('#area');
            const value = $field.val().trim();
            if (value !== '' && (isNaN(value) || parseFloat(value) < 0)) { showError($field, 'Please enter a valid number (0 or more).'); return false; }
            clearError($field); return true;
        };
        // --- END: NEW Area Validation ---
        const validateTerms = () => {
            const $field = $('#terms');
            if (!$field.is(':checked')) { showError($field, 'You must accept the terms and conditions to proceed.'); return false; }
            clearError($field); return true;
        };

        // --- START MODIFICATION: Add Start Date Validation ---
        const validateStartDate = () => {
            const $field = $('#start_date');
            const value = $field.val().trim();
            if (value === '') { 
                showError($field, 'This field is required. Please select a start date.'); 
                return false; 
            }
            // Basic date format check (yyyy-mm-dd), though datepicker handles this
            const datePattern = /^\d{4}-\d{2}-\d{2}$/;
            if (!datePattern.test(value)) {
                 showError($field, 'Invalid date format. Please use the date picker.'); 
                return false;
            }
            clearError($field); 
            return true;
        };
        // --- END MODIFICATION ---
        
        // --- ATTACH BLUR/CHANGE HANDLERS (Copied) ---
        $('#room_title').on('blur', validateRoomTitle);
        $('#room_status').on('change', validateRoomStatus); // --- START: NEW Room Status ---
        $('#room_location').on('blur', validateRoomLocation); // --- *** ADDED *** ---
        $('#listed_by').on('blur', validateListedBy);
        $('#contact_name').on('blur', validateContactName);
        $('#contact_email').on('blur', validateContactEmail);
        $('#contact_phone').on('blur', validateContactPhone);
        $('#area').on('blur', validateArea); // --- ADDED Area Validation ---
        $('#floor').on('blur', validateFloor);
        $('#total_room').on('blur', validateTotalRoom);
        $('#terms').on('change', validateTerms);
        $('#start_date').on('change blur', validateStartDate); // --- NEW HANDLER ---
        
        if (typeof tinymce !== 'undefined') {
            tinymce.on('addeditor', (event) => {
                if (event.editor.id === 'description') {
                    event.editor.on('blur', validateDescription);
                    
                    // Add char counter
                    var counter = document.getElementById('char-count'), maxLength = 500;
                    function updateCounter() {
                        var len = event.editor.getContent({ format: 'text' }).length;
                        if (counter) {
                            counter.textContent = len + ' / ' + maxLength;
                            counter.style.color = len > maxLength ? 'red' : '';
                        }
                    }
                    event.editor.on('keyup', updateCounter);
                    event.editor.on('change', updateCounter);
                    updateCounter();
                }
            });
        }

        // --- Init Datepicker (Copied) ---
        $('#start_date').datepicker({
            dateFormat: 'yy-mm-dd', changeMonth: true, changeYear: true, yearRange: 'c:c+5'
        });

        // --- Helper function to format date (Copied) ---
        function formatPreviewDate(dateString) {
            if (!dateString) return '';
            try {
                const date = new Date(dateString + 'T00:00:00'); // Fix for timezone issues
                const options = { day: 'numeric', month: 'short', year: 'numeric' };
                return date.toLocaleDateString('en-US', options);
            } catch (e) {
                return dateString; // Fallback
            }
        }
        
        // --- Helper function to re-init slider dots (Copied) ---
        function initPreviewSliderDots() {
            const $dots = $previewContainer.find(".slider-dots .dot");
            const $images = $previewContainer.find(".room-featured-image");

            $dots.off('click').on('click', function() {
                const index = parseInt($(this).attr("data-slide-index"), 10);
                $images.removeClass("is-active");
                $dots.removeClass("active");
                $images.eq(index).addClass("is-active");
                $(this).addClass("active");
            });
        }
        
        initPreviewSliderDots();

        // --- FORM SUBMIT HANDLER (NOW PREVIEW) (Copied) ---
        $form.on('submit', function(event) {
            const submitAction = document.activeElement.value;
            
            if (submitAction === 'submit') {
                event.preventDefault(); // STOP the submission

                // --- MODIFICATION: Add Start Date to validation list ---
                const isValid = [
                    validateRoomTitle(), validateDescription(), validateRoomStatus(),
                    validateRoomLocation(),
                    validateListedBy(), validateContactName(), validateContactEmail(), 
                    validateContactPhone(), validateArea(), validateFloor(), validateTotalRoom(), validateTerms(),
                    validateStartDate() // --- NEWLY ADDED ---
                ].every(Boolean);
                // --- END MODIFICATION ---
                
                if (isValid) {
                    // --- VALIDATION PASSED ---
                    // 1. Populate Preview Data
                    $('#preview-room-title').text($('#room_title').val());
                    $('#preview-description').html(tinymce.get('description').getContent());
                    $('#preview-description-wrapper').toggle(!!tinymce.get('description').getContent({format:'text'}).trim());
                    
                    const area = $('#area').val();
                    $('#preview-meta-area').text(area); // --- REMOVED "square metres" ---
                    $('#preview-meta-area-wrapper').toggle(!!area);
 
                    // --- *** START: UPDATED PREVIEW LOGIC *** ---
                    const state = $('#state option:selected').text();
                    const stateVal = $('#state').val();
                    const location = $('#room_location').val(); // Get new field value
                    
                    let fullLocation = '';
                    if (location) {
                        fullLocation += location;
                    }
                    if (stateVal && location.indexOf(state) === -1) { // Only add state if not already in location string
                        if (fullLocation) fullLocation += ', ';
                        fullLocation += state;
                    }
                    
                    $('#preview-meta-state').text(fullLocation);
                    $('#preview-meta-state-wrapper').toggle(!!fullLocation); // Show if either state or location is present
                    // --- *** END: UPDATED PREVIEW LOGIC *** ---

                    const listedBy = $('#listed_by').val();
                    $('#preview-meta-listedby').text(listedBy);
                    $('#preview-owner-name').text(listedBy); // For contact card
                    $('#preview-meta-listedby-wrapper').toggle(!!listedBy);

                    const startDate = $('#start_date').val();
                    $('#preview-meta-startdate').text(formatPreviewDate(startDate));
                    $('#preview-meta-startdate-wrapper').toggle(!!startDate);
                    
                    const floor = $('#floor').val();
                    $('#preview-info-floor').text(floor);
                    $('#preview-info-floor-wrapper').toggle(!!floor);
                    
                    const totalRoom = $('#total_room').val();
                    $('#preview-info-totalroom').text(totalRoom);
                    $('#preview-info-totalroom-wrapper').toggle(!!totalRoom);

                    $('#preview-general-info-wrapper').toggle(!!floor || !!totalRoom);

                    const phone = $('#contact_phone').val();
                    $('#preview-contact-phone').text(phone);
                    $('#preview-contact-phone-link').attr('href', 'tel:' + phone).toggle(!!phone);
                    
                    const email = $('#contact_email').val();
                    $('#preview-contact-email').text(email);
                    $('#preview-contact-email-link').attr('href', 'mailto:' + email).toggle(!!email);

                    $('#preview-contact-divider').toggle(!!phone && !!email);

                    // 2. Populate Image Previews
                    const $imageSlider = $('#preview-image-slider');
                    const $dotsContainer = $('#preview-slider-dots');
                    $imageSlider.empty();
                    $dotsContainer.empty();
                    
                    // --- --- --- --- --- --- --- --- --- --- --- --- --- --- ---
                    // --- MODIFIED: Use newFileStore for preview ---
                    // --- --- --- --- --- --- --- --- --- --- --- --- --- --- ---
                    const files = newFileStore.files; // Get files from our custom store
                    
                    let imageIndex = 0;
                    
                    const imagesToShow = existingImages.filter(url => !imagesToRemove.includes(url));
                    
                    imagesToShow.forEach((imgUrl, index) => {
                        const isActive = imageIndex === 0 ? 'is-active' : '';
                        $imageSlider.append(
                            $('<img>').attr('src', imgUrl).addClass('room-featured-image ' + isActive).attr('data-slide-image', imageIndex)
                        );
                        $dotsContainer.append(
                            $('<span>').addClass('dot ' + isActive).attr('data-slide-index', imageIndex)
                        );
                        imageIndex++;
                    });

                    if (files.length > 0) {
                        Array.from(files).forEach((file, index) => {
                            const tempUrl = URL.createObjectURL(file);
                            const isActive = imageIndex === 0 ? 'is-active' : '';
                            
                            $imageSlider.append(
                                $('<img>').attr('src', tempUrl).addClass('room-featured-image ' + isActive).attr('data-slide-image', imageIndex)
                            );
                            $dotsContainer.append(
                                $('<span>').addClass('dot ' + isActive).attr('data-slide-index', imageIndex)
                            );
                            imageIndex++;
                        });
                    }
                    // --- --- --- --- --- --- --- --- --- --- --- --- --- --- ---
                    // --- END: Image Preview Modification ---
                    // --- --- --- --- --- --- --- --- --- --- --- --- --- --- ---
                    
                    initPreviewSliderDots(); 
                    $dotsContainer.toggle(imageIndex > 1);
                    
                    if (imageIndex === 0) {
                        $imageSlider.html('<div style="text-align:center; padding-top: 50px; color: #777;">No images</div>');
                        $dotsContainer.hide();
                    }

                    // 3. Hide Form, Show Preview
                    $formContainer.hide();
                    $previewContainer.show();
                    $('html, body').animate({ scrollTop: $previewContainer.offset().top - 100 }, 300);

                } else {
                    // --- VALIDATION FAILED ---
                    const $firstError = $form.find('.error').first();
                    if ($firstError.length) {
                         if ($firstError.is('select')) { // --- ADDED Select2 ---
                            $('html, body').animate({ scrollTop: $firstError.next('.select2-container').offset().top - 100 }, 300);
                        } else {
                            $('html, body').animate({ scrollTop: $firstError.offset().top - 100 }, 300);
                        }
                    }
                }
            }
        });
        
        // --- PREVIEW BUTTON HANDLERS (Copied) ---
        
        $('#rhf-edit-button').on('click', function() {
            $previewContainer.hide();
            $formContainer.show();
            
            // --- --- --- --- --- --- --- --- --- --- --- ---
            // --- NEW: Reset image removal state ---
            // --- --- --- --- --- --- --- --- --- --- --- ---
            // Show any images that were hidden
            $form.find('.rhf-image-preview-item').show();
            // Clear the removal list
            imagesToRemove = [];
            $hiddenRemoveInput.val('');
            // --- --- --- --- --- --- --- --- --- --- --- ---
            
            $('html, body').animate({ scrollTop: $formContainer.offset().top - 100 }, 300);
        });
        
        // ---
        // --- 🐞 BUG FIX: This is the corrected fix for the preview loop.
        // ---
        $('#rhf-final-submit-button').on('click', function() {
            // 1. Add a hidden input field to mimic the 'submit' button's value.
            //    This ensures $_POST['submit_action'] is set on the server.
            $form.append('<input type="hidden" name="submit_action" value="submit" />');
            
            // 2. Unbind the custom submit handler to prevent the preview from firing again.
            $form.off('submit');
            
            // 3. Trigger the native form submission.
            $form.submit();
        });

    });
    </script>
    <?php
}


/**
 * 4. Handle the EDIT form submission.
 */
function rhf_handle_edit_form_submission() {

    if ( isset( $_POST['submit_action'] ) && isset( $_POST['rhf_edit_nonce'] ) && wp_verify_nonce( $_POST['rhf_edit_nonce'], 'rhf_edit_room_action' ) && isset($_POST['post_id_to_edit']) ) {
        
        $post_id = intval($_POST['post_id_to_edit']);
        
        $post_to_edit = get_post($post_id);
        if ( ! $post_to_edit || $post_to_edit->post_author != get_current_user_id() || $post_to_edit->post_type != 'room-for-hire' ) {
            return;
        }

        // --- DATA SANITIZATION & VALIDATION (Copied from original) ---
        $errors = [];
        $trimmed_post = array_map('trim', $_POST);

        $room_title      = sanitize_text_field( $trimmed_post['room_title'] );
        $description     = wp_kses_post( $_POST['description'] );
        $area            = sanitize_text_field( $trimmed_post['area'] );
        $floor           = sanitize_text_field( $trimmed_post['floor'] );
        $total_room      = sanitize_text_field( $trimmed_post['total_room'] );
        $state           = sanitize_text_field( $trimmed_post['state'] );
        $room_location   = sanitize_text_field( $trimmed_post['room_location'] ); // *** ADDED ***
        $listed_by       = sanitize_text_field( $trimmed_post['listed_by'] );
        $contact_name    = sanitize_text_field( $trimmed_post['contact_name'] );
        $contact_email   = sanitize_email( $trimmed_post['contact_email'] );
        $contact_phone   = sanitize_text_field( $trimmed_post['contact_phone'] );
        $preferred_date  = sanitize_text_field( $trimmed_post['start_date'] );
        
        // --- START: NEW Room Status ---
        $selected_room_status = sanitize_text_field( $trimmed_post['room_status'] );
        // --- END: NEW Room Status ---

        // --- NEW: Sanitize lat/lng ---
        $rhf_lat = sanitize_text_field( $trimmed_post['rhf_lat'] ?? '' );
        $rhf_lng = sanitize_text_field( $trimmed_post['rhf_lng'] ?? '' );
        
        // Server-side validation (Copied)
        if ( empty( $room_title ) ) $errors['room_title'] = 'This field is required. Please input the room title.';
        
        // --- FIX: Typo "isM" corrected to "is" ---
        if ( empty( $description ) || trim( strip_tags( $description ) ) === '' ) $errors['description'] = 'This field is required. Please provide a description.';
        
        // --- START: NEW Room Status ---
        if ( empty( $selected_room_status ) ) $errors['room_status'] = 'This field is required. Please select a status.';
        // --- END: NEW Room Status ---
        
        if ( empty( $contact_name ) ) $errors['contact_name'] = 'This field is required. Please input your name.';
        if ( ! is_email( $contact_email ) ) $errors['contact_email'] = 'A valid email address is required.';
        if ( ! isset( $_POST['terms'] ) ) $errors['terms'] = 'You must accept the terms and conditions to proceed.';
        
        if ( empty( $room_location ) ) $errors['room_location'] = 'This field is required. Please enter the room location.'; // *** ADDED ***

        // --- START MODIFICATION: Add Start Date validation ---
        if ( empty( $preferred_date ) ) $errors['start_date'] = 'This field is required. Please select a start date.';
        // --- END MODIFICATION ---

        // --- FIX: Removed validation for numbers in names/titles ---
        
        // --- START MODIFICATION: Update Phone validation ---
        if ( !empty($contact_phone) ) {
            // Regex for Australian phone numbers (from user)
            $aus_phone_regex = '/^(?:\+?(61))? ?(?:\((?=.*\)))?(0?[2-57-8])\)? ?(\d\d(?:[- ](?=\d{3})|(?!\d\d[- ]?\d[- ]))\d\d[- ]?\d[- ]?\d{3})$/';
            if ( !preg_match($aus_phone_regex, $contact_phone) ) {
                $errors['contact_phone'] = 'Please enter a valid Australian phone number.';
            }
        }
        // --- END MODIFICATION ---

        if ( strlen(wp_strip_all_tags($description)) > 500 ) $errors['description'] = 'Description cannot exceed 500 characters.';
        
        // --- UPDATED Numeric Validation ---
        if ( !empty($area) && ( !is_numeric($area) || $area < 0 ) ) $errors['area'] = 'Please enter a valid number (0 or more).';
        if ( !empty($floor) && ( !ctype_digit( $floor ) || $floor < 0 ) ) $errors['floor'] = 'Please enter a valid whole number (0 or more).';
        if ( !empty($total_room) && ( !ctype_digit( $total_room ) || $total_room < 0 ) ) $errors['total_room'] = 'Please enter a valid whole number (0 or more).';

        // --- MODIFICATION: Check date format if not empty ---
        if ( !empty($preferred_date) ) {
            $d = DateTime::createFromFormat('Y-m-d', $preferred_date);
            if ( !$d || $d->format('Y-m-d') !== $preferred_date ) {
                $errors['start_date'] = 'Please enter a valid date in YYYY-MM-DD format.';
            }
        }
        // --- END MODIFICATION ---
        

        // If there are errors, store them and redirect back.
        if ( ! empty( $errors ) ) {
            $_SESSION['rhf_form_errors'] = $errors;
            $_SESSION['rhf_form_data'] = $_POST;
            $redirect_url = add_query_arg( 'room_id', $post_id, wp_get_referer() );
            wp_safe_redirect( $redirect_url );
            exit;
        }

        // --- POST UPDATE (The main change) ---
        // --- START: NEW Room Status ---
        if ( $selected_room_status === 'booked' ) {
            $post_status = 'publish'; // Set to draft if booked -- Keeping as publish based on file
        } else {
            $post_status = 'publish'; // Set to publish for 'available' or any other status
        }
        // --- END: NEW Room Status ---
        
        $post_data = array(
            'ID'           => $post_id,
            'post_type'    => 'room-for-hire',
            'post_title'   => $room_title,
            'post_content' => $description,
            'post_status'  => $post_status, // This is now dynamic
        );
        
        $updated_post_id = wp_update_post( $post_data, true );
        
        if ( is_wp_error( $updated_post_id ) ) {
            $_SESSION['rhf_form_errors'] = ['general' => $updated_post_id->get_error_message()];
            $_SESSION['rhf_form_data'] = $_POST;
            $redirect_url = add_query_arg( 'room_id', $post_id, wp_get_referer() );
            wp_safe_redirect( $redirect_url );
            exit;
        }

        // --- UPDATE ACF FIELDS ---
        $preferred_date_acf = str_replace( '-', '', $preferred_date );
        
        // --- START: NEW Room Status ---
        update_field( 'room_status', $selected_room_status, $post_id );
        // --- END: NEW Room Status ---
        
        // --- MODIFICATION: Save only the area number ---
        update_field( 'area', $area, $post_id );
        // --- END MODIFICATION ---
 
        update_field( 'floor', $floor, $post_id );
        update_field( 'total_room', $total_room, $post_id );
        update_field( 'state', $state, $post_id );
        update_field( 'room_location', $room_location, $post_id ); // *** ADDED ***
        update_field( 'listed_by', $listed_by, $post_id );
        update_field( 'contact_person', $contact_name, $post_id );
        update_field( 'contact_email', $contact_email, $post_id );
        update_field( 'contact_phone', $contact_phone, $post_id );
        update_field( 'start_date', $preferred_date_acf, $post_id );

        // --- NEW: Save Lat/Lng Coordinates as Post Meta ---
        if ( ! empty( $rhf_lat ) && is_numeric( $rhf_lat ) ) {
            update_post_meta( $post_id, 'rhf_latitude', $rhf_lat );
        } else {
            // If the field is empty (e.g., user cleared address), delete the meta
            delete_post_meta( $post_id, 'rhf_latitude' );
        }
        
        if ( ! empty( $rhf_lng ) && is_numeric( $rhf_lng ) ) {
            update_post_meta( $post_id, 'rhf_longitude', $rhf_lng );
        } else {
            // If the field is empty, delete the meta
            delete_post_meta( $post_id, 'rhf_longitude' );
        }

        // --- --- --- --- --- --- --- --- --- --- --- ---
        // --- MODIFIED: IMAGE HANDLING (with Deletion) ---
        // --- --- --- --- --- --- --- --- --- --- --- ---
        
        // 1. Get the current list of URLs ONCE
        $existing_gallery = get_post_meta( $post_id, 'easy_pg_data', true );
        $gallery_image_urls = $existing_gallery['image_url'] ?? []; // This is our working array
        
        // 2. Handle Deletions (if any)
        if ( isset($_POST['rhf_images_to_remove']) && !empty($_POST['rhf_images_to_remove']) ) {
            $urls_to_remove = json_decode( stripslashes($_POST['rhf_images_to_remove']), true );
            
            if ( is_array($urls_to_remove) && !empty($urls_to_remove) ) {
                
                // Remove the specified URLs from our working array
                $gallery_image_urls = array_diff($gallery_image_urls, $urls_to_remove);
                $gallery_image_urls = array_values($gallery_image_urls); // Re-index the array

                // Check if the featured image was one of the ones removed
                $current_thumb_id = get_post_thumbnail_id( $post_id );
                $current_thumb_url = wp_get_attachment_image_url($current_thumb_id, 'full');
                
                if ( in_array($current_thumb_url, $urls_to_remove) ) {
                    delete_post_thumbnail( $post_id );
                    
                    // Set the new featured image to be the first in the *remaining* list
                    if ( !empty($gallery_image_urls) ) {
                        // We need to find the attachment ID from the URL
                        // Note: attachment_url_to_postid() only works for *local* uploads.
                        // This assumes your media library URLs are local.
                        $new_thumb_id = attachment_url_to_postid($gallery_image_urls[0]);
                        if ($new_thumb_id) {
                            set_post_thumbnail($post_id, $new_thumb_id);
                        }
                    }
                }
            }
        }

        // 3. Handle New Uploads (if any)
        if ( ! empty( $_FILES['room_images']['name'][0] ) ) {
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            require_once( ABSPATH . 'wp-admin/includes/media.php' );
            
            // $gallery_image_urls variable already contains the correct list of *remaining* images
            $files = [];
            foreach ( $_FILES['room_images'] as $k => $l ) {
                foreach ( $l as $i => $v ) {
                    if ( ! array_key_exists( $i, $files ) ) $files[$i] = [];
                    $files[$i][$k] = $v;
                }
            }

            foreach ( $files as $file ) {
                $attachment_id = media_handle_sideload( $file, $post_id );
                if ( is_wp_error( $attachment_id ) ) continue;

                $attachment_url = wp_get_attachment_url( $attachment_id );
                if ( $attachment_url ) {
                    $gallery_image_urls[] = $attachment_url; // Add new URL to our working array
                }
                
                // Set as featured image IF no featured image is currently set
                // (This handles cases where the post had no images, or all were deleted)
                if ( ! get_post_thumbnail_id( $post_id ) ) {
                    set_post_thumbnail( $post_id, $attachment_id );
                }
            }
        }

        // 4. Save the final, combined list ONCE.
        // Ensure no duplicates and re-index the array.
        $final_image_urls = array_values(array_unique($gallery_image_urls));
        
        $easy_pg_data_structure = [
            'image_url' => $final_image_urls
        ];
        update_post_meta( $post_id, 'easy_pg_data', $easy_pg_data_structure );

        // --- --- --- --- --- --- --- --- --- --- --- ---
        // --- END: IMAGE HANDLING ---
        // --- --- --- --- --- --- --- --- --- --- --- ---

        
        // --- SUCCESS REDIRECT ---
        // --- EDIT: Changed redirect URL to the dashboard ---
        
        $dashboard_url = 'https://aaswjobstaging.wpenginepowered.com/room-dashboard/';
        $success_message = 'Your room has been updated successfully!';
        
        // Add a query arg to the dashboard URL so you can display the message
        $redirect_url = add_query_arg( [ 
            'update_status' => 'success', 
            'message' => urlencode($success_message)
        ], $dashboard_url );
        
        wp_safe_redirect( $redirect_url );
        exit;
    }
}
// This line (add_action) should already exist in your file
add_action( 'template_redirect', 'rhf_handle_edit_form_submission' );


/**
 * 5. Display success messages above the form.
 */
function rhf_edit_display_messages() {
    if ( isset( $_GET['form_status'] ) && $_GET['form_status'] === 'success' && isset( $_GET['message'] ) ) {
        $success_message = esc_html( urldecode( $_GET['message'] ) );
        echo '<div class="rhf-message success">' . $success_message . '</div>';
    }
}
?>