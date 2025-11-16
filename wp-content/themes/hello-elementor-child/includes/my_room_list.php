<?php
// Prevent direct file access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Registers the shortcode [my_room_listings]
 */
add_shortcode('my_room_listings', 'my_room_listings_shortcode_handler');

// --- NEW (Part 1): Define the message display function ---
if ( ! function_exists( 'my_dashboard_display_messages' ) ) {
  /**
   * Displays success messages on the room dashboard by reading URL parameters.
   */
  function my_dashboard_display_messages() {
      // Check if we have a success message in the URL
      if ( isset( $_GET['form_status'] ) && $_GET['form_status'] === 'success' && isset( $_GET['message'] ) ) {
          
          // Get the message and make it safe to display
          $success_message = esc_html( urldecode( $_GET['message'] ) );
          
          // Echo the message HTML, using the same classes as your form
          echo '<div class="rhf-message success" style="margin-left: auto; margin-right: auto; max-width: 100%;">' . $success_message . '</div>';
      }
  }
}
// --- END NEW ---

/**
 * The handler function for the [my_room_listings] shortcode.
 */
function my_room_listings_shortcode_handler()
{
  // 1. Get the current user
  $user_id = get_current_user_id();
  $user = wp_get_current_user();

  // 2. Check if user is logged in
  if ($user_id === 0) {
    return '<p>Please log in to view your listings.</p>';
  }

  // 2b. Check roles and set permissions
  $user_roles = (array) $user->roles;
  // --- Using your 'rooms_for_hire' role ---
  $is_roomsforhire = in_array('employer', $user_roles);
  $is_admin = current_user_can('administrator');

  // If user is not the 'rooms_for_hire' role AND not an 'admin', block access.
  if (!$is_roomsforhire && !$is_admin) {
    return '<p>You do not have permission to view this list.</p>';
  }

  // 3. Set up the query arguments
  //
  //    Post type is set to 'room-for-hire'
  //
  $args = array(
    'post_type' => 'room-for-hire', // This is your post type name
    'posts_per_page' => -1,       // Show all posts
    'post_status' => array('publish', 'draft', 'pending', 'future') // Get all statuses
  );

  // 4. Modify query based on role
  //    If the user is *not* an admin, only show their own posts.
  //    Admins will see ALL posts (the 'author' arg is not set).
  if (!$is_admin) {
    $args['author'] = $user_id;
  }

  $rooms_query = new WP_Query($args);

  // --- Get the post count for the new header ---
  $room_count = $rooms_query->post_count;

  //
  // --- **** EDIT THIS LINK **** ---
  //    Set the URL for your "Add New Room" button.
  //
  $add_new_room_url = '/post-a-room/'; // Change this to your actual submission page URL
  // $add_new_room_url = '/your-custom-frontend-submission-page/'; // Or use a custom page

  // --- Create Nonces for AJAX Actions ---
  $delete_nonce = wp_create_nonce('delete_room_nonce');
  $duplicate_nonce = wp_create_nonce('duplicate_room_nonce');


  // 5. Start output buffering to capture the HTML
  ob_start();

  // --- NEW (Part 3): Call the function to display the message ---
  my_dashboard_display_messages();
  // --- END NEW ---

  // --- Add AlertifyJS (for popups) ---
  ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
  <?php

  // 6. Inject CSS Styles
  static $styles_printed = false;
  if (!$styles_printed) {
    $styles_printed = true;
    ?>
    <style type="text/css" id="my-room-listings-styles">
      /* --- NEW (Part 2): Styles for the success message --- */
      .rhf-message {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 6px;
        border: 1px solid transparent;
      }
      .rhf-message.success {
        color: #00688f;
        background-color: #c5edff2d; /* Your form's success color */
        border-color: #00688f;
      }
      /* --- END NEW --- */


      /* --- Main Wrapper --- */
      .my-room-listings-wrapper {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        width: 100%;
        margin: 20px auto;
      }

      /* --- Top Header Styles (Layout Fixed) --- */
      .room-list-top-header {
        display: flex;
        justify-content: space-between; /* Keep Desktop Layout: Space Between */
        align-items: center;
        /* Vertically centers the left/right blocks */
        padding: 20px;
        border: 1px solid #e0e0e0;
        margin-bottom: 20px;
        background-color: #FFFFFF;
        border-radius: 10px;
      }

      .room-list-top-header .header-left {
        /* This block now stacks its children (h2, p) */
        display: flex;
        flex-direction: column;
        justify-content: center;
        margin-right: auto; /* Pushes right content to the right if space allows */
        /* Adds a safety gap */
      }

      .room-list-top-header .header-left h2 {
        font-size: 24px;
        font-weight: 700;
        color: #111;
        margin: 0 0 5px 0;
      }

      .room-list-top-header .header-left .status-message {
        margin: 0;
        font-size: 15px;
        color: #555;
        /* Default color for 'has-rooms' */
      }

      .room-list-top-header .header-left .status-message.no-rooms {
        color: #c92c2c;
        /* Reddish color from image */
        font-weight: 500;
      }

      /* --- NEW: Changed this section --- */
      .room-list-top-header .header-right {
        /* margin-left: auto; */
        /* This pushes the button to the far right */
      }

      .room-list-top-header .header-right .add-room-button {
        display: inline-block;
        background-color: #157a9a;
        /* Blue from image */
        color: #ffffff;
        padding: 12px 20px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        font-size: 15px;
        white-space: nowrap;
      }

      .room-list-top-header .header-right .add-room-button:hover {
        background-color: #11637e;
      }

      /* --- End Top Header Styles --- */

      /* --- Room List Shortcode Styles --- */
      .room-list-container {
        width: 100%;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        background-color: #fff;
        overflow: hidden;
        /* Ensures border-radius clips children */
      }

      /* Common cell styling */
      .room-list-header .room-cell,
      .room-list-item .room-cell {
        padding: 16px 12px;
        display: flex;
        align-items: center;
        overflow: hidden;
        text-overflow: ellipsis;
        /* white-space: nowrap; <-- REMOVED THIS LINE */
      }

      /* Grid layout for the list */
      .room-list-header,
      .room-list-item {
        display: grid;
        /* This template matches your image's column widths */
        grid-template-columns: 145px 1.3fr 1fr 1.2fr 1.3fr 1fr 0.5fr;
        gap: 10px;
        border-bottom: 1px solid #f0f0f0;
        align-items: center;
      }

      /* Header styles */
      .room-list-header {
        background-color: #fafafa;
        font-weight: 600;
        font-size: 14px;
        color: #555;
        border-bottom: 1px solid #e0e0e0;
      }

      /* Item row styles */
      .room-list-item {
        font-size: 15px;
        color: #333;
      }

      .room-list-item:last-child {
        border-bottom: none;
      }

      .room-list-item:hover {
        background-color: #f9f9f9;
      }

      /* Specific cell styles */
      .room-cell-image img {
        width: 120px;
        height: 80px;
        object-fit: cover;
        border-radius: 6px;
        display: block;
      }

      .room-cell-title strong {
        font-weight: 600;
        color: #222;
      }

      .room-cell-title {
        white-space: normal;
        /* Allow wrapping */
      }

      .room-cell-date,
      .room-cell-area,
      .room-cell-state {
        /* This is for your 'state' field */
        color: #666;
        font-size: 14px;
        white-space: nowrap; /* ADDED THIS */
      }

      .room-cell-status {
        white-space: nowrap; /* ADDED THIS */
      }

      .room-cell-actions {
        justify-content: center;
        text-align: center;
      }

      /* Status Badge Styles (matches your image) */
      .status-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 16px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.2;
        border: 1px solid transparent;
      }

      /* Available */
      .status-badge.status-available {
        border-radius: 5px;
        background-color: #20B2AA1A;
        border : 1px solid ;
        border-color: #00688F;
        color: #00688F;
      }

      /* Booked */
      .status-badge.status-booked {
        border-radius: 5px;
        background-color: #feebeB;
        border : 1px solid ;
        border-color: #f7b9b9;
        color: #d93030;
      }

      /* Draft */
      .status-badge.status-draft {
        border-radius: 5px;#00688F
        background-color: #d6ddeb;
        border : 1px solid ;
        border-color: #dcdcdc;
        color: #808184;
      }

      /* --- No Listings Message Styles (Big Image) --- */
      .no-room-listings-message {
        text-align: center;
        padding: 60px 20px;
        background-color: #fff;
        /* Match container bg */
      }

      .no-room-listings-message img {
        width: 291px;
        height: 194px;
        object-fit: contain;
        margin-bottom: 25px;
        filter: grayscale(100%);
        opacity: 0.7;
      }

      .no-room-listings-message h2 {
        font-size: 28px;
        font-weight: 700;
        color: #333;
        margin-bottom: 15px;
      }

      .no-room-listings-message p {
        font-size: 17px;
        color: #666;
        line-height: 1.6;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
      }

      /*
      --- ============================================= ---
      ---     STYLES FOR ACTION MENU (from list-job.php)
      --- ============================================= ---
      */

      .action-menu {
        position: relative;
        display: inline-block;
      }

      .action-dropdown {
        display: none;
        /* hide by default */
        position: absolute;
        right: 0;
        top: 90%;
        background: #fff;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        border-radius: 10px;
        min-width: 160px;
        z-index: 999;
        padding: 6px 0;
      }

      /* When JS adds 'show', make it visible */
      .action-dropdown.show {
        display: block;
      }

      /* Default styles */
      .action-dropdown button {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 15px;
        background: none;
        border: none;
        font-size: 14px;
        cursor: pointer;
        text-align: left;
        color: #333;
        transition: all 0.2s ease;
        border-radius: 6px;
      }

      /* Edit hover */
      .action-dropdown .action-edit:hover {
        background: #00688F;
        /* blue */
        color: #fff;
      }

      /* View hover */
      .action-dropdown .action-view:hover {
        background: #00688F;
        /* dark gray */
        color: #fff;
      }

      /* Duplicate hover */
      .action-dropdown .action-duplicate:hover {
        background: #00688F;
        /* orange */
        color: #fff;
      }

      /* Delete hover */
      .action-dropdown .action-delete:hover {
        background: #dc3545;
        /* red */
        color: #fff;
      }

      /* --- NEW --- */
      /* This rule targets the <img> icons on hover and makes them white */
      .action-dropdown .action-edit:hover .action-icon,
      .action-dropdown .action-view:hover .action-icon,
      .action-dropdown .action-delete:hover .action-icon {
        filter: brightness(0) invert(1);
      }
      /* --- END NEW --- */

      .action-menu .action-toggle {
        background: none;
        border: none;
        font-size: 22px;
        font-weight: 700;
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 50%;
        color: #555;
        transition: background 0.2s ease, color 0.2s ease;
      }

      .action-menu .action-toggle:hover {
        background: #f0f0f0;
        color: #000;
      }

      /* Remove button look from dropdown items */
      .action-dropdown button {
        background: none;
        border: none;
        outline: none;
      }

      .action-icon {
        width: 18px;
        height: 18px;
        object-fit: contain;
        vertical-align: middle;
        margin-right: 5px;
      }

      /*
      --- ============================================= ---
      ---     STYLES FOR ALERTIFY POPUP
      --- ============================================= ---
      */
      .ajs-dialog .ajs-footer {
        text-align: center !important;
        background: #fff !important;
        border-top: none !important;
        padding: 0px 10px;
      }

      .ajs-dialog .ajs-button {
        min-width: 120px;
        padding: 8px 16px !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        margin: 0 6px;
      }

      .ajs-dialog .ajs-ok {
        background: #A32441 !important;
        color: #ffffff !important;
        border: none !important;
      }

      .ajs-dialog .ajs-ok:hover {
        background: #A32441 !imporant;
      }

      .ajs-dialog .ajs-cancel {
        background: #AA233F33 !important;
        color: #A32441 !important;
        border: none !impo!important;
      }

      .ajs-dialog .ajs-cancel:hover {
        background: 2px #AA233F33 !important;
      }

      .ajs-dialog .ajs-header {
        display: none !important;
      }

      .alertify .ajs-dialog {
        min-height: 265px !important;
        min-width: 545px !important;
        border-radius: 10px !important; /* <-- ADJUSTED AS REQUESTED */
      }

      .alertify .ajs-footer .ajs-buttons.ajs-primary {
        text-align: center !important;
      }

      .alertify .ajs-footer .ajs-buttons .ajs-button {
        min-width: 120px !important;
      }

      .alertify .ajs-commands button.ajs-close {
        width: 30px !important;
        height: 30px !important;
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="%23000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>') !important;
        background-size: contain !important;
        background-repeat: no-repeat !important;
        background-position: center !important;
      }

      /*
      --- ============================================= ---
      ---     STYLES FOR LOADER & FLOATING DROPDOWN
      --- ============================================= ---
      */
      .spinner {
        border: 6px solid #f3f3f3;
        border-top: 6px solid #9fbdd1ff;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 1s linear infinite;
      }

      @keyframes spin {
        100% {
          transform: rotate(360deg);
        }
      }

      .floating-dropdown {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 6px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        min-width: 160px;
        max-width: 160px;
        padding: 6px 0;
        transition: all 0.2s ease;
        overflow-y: auto;
        max-height: 300px;
      }

      .floating-dropdown.drop-up {
        transform-origin: bottom;
      }

      .floating-dropdown:not(.drop-up) {
        transform-origin: top;
      }

      .floating-dropdown button {
        width: 100%;
        padding: 10px 15px;
        box-sizing: border-box;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }
    
      /* --- ================================== --- */
      /* ---     START: RESPONSIVE STYLES     --- */
      /* --- ================================== --- */


      /* --- NEW: MOBILE CARD LAYOUT (900px) --- */
      @media (max-width: 900px) {
        .room-list-header {
          display: none; /* Hide desktop header */
        }

        .room-list-container {
            border: none; /* Remove container border */
            background: none; /* Remove container bg on mobile */
            overflow: visible;
        }

        .room-list-item {
          display: block; /* Change from grid to block */
          position: relative; /* For action menu positioning */
          background-color: #fff;
          border: 1px solid #e0e0e0;
          border-radius: 12px;
          padding: 15px;
          margin-bottom: 20px;
          box-shadow: 0 2px 5px rgba(0,0,0,0.05);
          /* Remove grid properties */
          grid-template-columns: none;
          gap: 0;
        }
        
        /* Reset all cells */
        .room-list-item .room-cell {
            padding: 0;
            display: block; /* All cells are blocks */
            white-space: normal;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        
        /* Last cell no border */
        .room-list-item .room-cell:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        /* Image Cell */
        .room-cell-image {
            border-bottom: none; /* No border for image */
            margin-bottom: 15px;
        }
        .room-cell-image img {
          margin-top: 40px;
            width: 100%;
            height: 180px; /* Good mobile height */
            object-fit: cover;
            border-radius: 8px; /* Match card */
        }

        /* Title Cell */
        .room-cell-title {
            border-bottom: none; /* No border for title */
            margin-bottom: 15px;
            padding-right: 40px; /* Make space for action menu */
        }
        .room-cell-title strong {
            font-size: 20px;
            font-weight: 700;
        }
        /* Remove the old ::after trick */
        .room-list-item .room-cell-title::after {
            display: none;
        }
        
        /* Action Menu */
        .room-cell-actions {
            display: block; /* Make sure it's visible */
            position: absolute;
            top: 15px; /* <-- ADJUSTED */
            right: 15px; /* <-- ADJUSTED */
            border-bottom: none; /* No border */
            padding: 0;
            margin: 0;
            z-index: 10; /* Ensure it's on top */
        }
        
        /* --- NEW: Make toggle button visible --- */
        .room-list-item .room-cell-actions .action-toggle {
            background-color: #fff; /* Light grey background */
            border-radius: 50%; /* Make it a circle */
            width: 32px; /* Set fixed size */
            height: 32px; /* Set fixed size */
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0; /* Reset padding */
            line-height: 1; /* Adjust line height for ... */
        }
        .room-list-item .room-cell-actions .action-toggle:hover {
            background-color: #e0e0e0; /* Darker on hover */
        }
        /* --- END NEW TOGGLE STYLE --- */
        
        /* Data Cells (Listed On, Area, State, Status) */
        .room-cell-date,
        .room-cell-area,
        .room-cell-state,
        .room-cell-status {
            display: flex; /* Use flex for label/value */
            justify-content: space-between;
            align-items: center;
            font-size: 15px;
        }

        /* Add labels with ::before */
        .room-cell-date::before {
            content: 'Listed On:';
            font-weight: 600;
            color: #555;
        }
        .room-cell-area::before {
            content: 'Area (m²):';
            font-weight: 600;
            color: #555;
        }
        .room-cell-state::before {
            content: 'State:';
            font-weight: 600;
            color: #555;
        }
        
        .room-cell-status {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .room-cell-status::before {
            content: 'Status:';
            font-weight: 600;
            color: #555;
        }
        
        /* No listings message */
        .no-room-listings-message h2 {
          font-size: 22px;
        }
        .no-room-listings-message p {
          font-size: 15px;
        }
        .no-room-listings-message img {
          width: 100%;
          max-width: 291px;
          height: auto;
        }
      }
      /* --- END: 900px --- */


      /* --- SMALL MOBILE & POPUP (600px) --- */
      @media (max-width: 600px) {

        /* Responsive for new header */
        .room-list-top-header {
          /* Force column layout on mobile */
          flex-direction: column;
          align-items: flex-start; /* Align EVERYTHING to the left */
          gap: 15px;
        }

        .room-list-top-header .header-right {
          width: 49%;
          margin-left: 0;
        }

        .room-list-top-header .header-right .add-room-button {
          text-align: center;
          display: inline-block; /* Allow it to sit on left (not full width block) */
          /* Make button full width or keep left? "left aligned like texts" implies not full width.
             If you want full width, change display:block. 
             If you want simple left align, inline-block is fine. */
        }

        /* --- NEW: Responsive Alertify Popup --- */
        .alertify .ajs-dialog {
          min-height: auto !important;
          min-width: 0 !important;
          width: 90% !important; /* Use a percentage width */
          max-width: 545px; /* But don't exceed original max */
          margin: 10px auto !important;
        }
        
        /* Adjust padding for the custom HTML */
        .ajs-dialog .ajs-body {
            padding: 16px !important;
        }
        
        /* Adjust the custom text inside */
        .ajs-dialog h3 { /* This is from the custom HTML */
          font-size: 24px !important; /* Decrease from 33px */
        }
        .ajs-dialog p { /* This is from the custom HTML */
          font-size: 14px !important;
        }
        
        .ajs-dialog .ajs-footer {
            padding-left: 16px !important;
            padding-right: 16px !important;
            padding-bottom: 16px !important;
        }

        /* Stack the buttons */
        .ajs-dialog .ajs-button {
           min-width: 0 !important;
           width: 100% !important; /* Stack buttons */
           margin: 0 0 10px 0 !important;
           display: block !important;
        }
        .ajs-dialog .ajs-button.ajs-cancel {
           margin-bottom: 0 !important;
        }
        /* --- END NEW POPUP STYLES --- */
      }
      /* --- END: 600px --- */

    </style>
    <?php
  } // End of CSS injection

  // 7. Prepare dynamic content for the new header
  $status_message = '';
  $status_class = '';
  if ($room_count > 0) {
    $status_message = $room_count . ' active room listings';
    $status_class = 'has-rooms';
  } else {
    $status_message = 'No active room listings';
    $status_class = 'no-rooms';
  }
  ?>

  <div class="my-room-listings-wrapper">

    <div class="room-list-top-header">
      <div class="header-left">
        <h2>Room Listing</h2>
        <p class="status-message <?php echo $status_class; ?>">
          <?php echo $status_message; ?>
        </p>
      </div>
      <div class="header-right">
        <a href="<?php echo esc_url($add_new_room_url); ?>" class="add-room-button">+ Add New Room</a>
      </div>
    </div>

    <div class="room-list-container">
      <?php if ($rooms_query->have_posts()): ?>
        <div class="room-list-header">
          <div class="room-cell">Image</div>
          <div class="room-cell">Title</div>
          <div class="room-cell">Listed On</div>
          <div class="room-cell">Area(Square Metres)</div>
          <div class="room-cell">State</div>
          <div class="room-cell">Status</div>
          <div class="room-cell" style="justify-content: center;">Actions</div>
        </div>

        <?php while ($rooms_query->have_posts()):
          $rooms_query->the_post(); ?>
          <?php
          // Get the post ID
          $room_id = get_the_ID();

          // --- Get Post Status First (User Request) ---
          $current_post_status = get_post_status($room_id);

          //
          // --- ACF FIELD NAMES ---
          //
          $room_area = get_field('area', $room_id);

          // --- Get State Field Label ---
          // Get the field data (which might be an array)
          $room_state_data = get_field('state', $room_id);
          // Check if it's an array and get the 'label', otherwise just use the value
          $room_state = is_array($room_state_data) ? $room_state_data['label'] : $room_state_data;
          // --- End ---
  
          //
          // --- === NEW STATUS LOGIC (PER YOUR REQUEST) === ---
          //
          if ($current_post_status !== 'publish') {
            // If the post is not published (draft, pending, etc.), force status to "Draft"
            $room_status = 'Draft';
            $status_slug = 'draft';
          } else {
            // The post IS published, so check the ACF field for 'Available' or 'Booked'
            $room_status = get_field('room_status', $room_id);
            $status_slug = sanitize_title($room_status);
          }
          // --- === END NEW STATUS LOGIC === ---
          
          // Get the 'Listed On' date.
          $listed_on = get_the_date('M-j-Y', $room_id);

          // Get icon URLs (assuming they are in the same location as list-job.php)
          $edit_icon_url = esc_url(get_stylesheet_directory_uri() . '/images/tabler_edit.png');
          $view_icon_url = esc_url(get_stylesheet_directory_uri() . '/images/lucide_view.png');
          $delete_icon_url = esc_url(get_stylesheet_directory_uri() . '/images/material-symbols_delete-outline.png');
          ?>

          <div class="room-list-item">
            <div class="room-cell room-cell-image">
              <?php if (has_post_thumbnail()): ?>
                <?php the_post_thumbnail('thumbnail'); ?>
              <?php else: ?>
                <img src="https://aaswjobstaging.wpenginepowered.com/wp-content/uploads/2025/11/elementor-placeholder-image.png" alt="No image">
              <?php endif; ?>
            </div>
            <div class="room-cell room-cell-title" data-area="<?php echo esc_attr($room_area); ?>"
              data-state="<?php echo esc_attr($room_state); ?>"> <strong><?php the_title(); ?></strong>
            </div>
            <div class="room-cell room-cell-date"><?php echo esc_html($listed_on); ?></div>
            <div class="room-cell room-cell-area"><?php echo esc_html($room_area); ?></div>
            <div class="room-cell room-cell-state"><?php echo esc_html($room_state); ?></div>
            <div class="room-cell room-cell-status">
              <span class="status-badge status-<?php echo esc_attr($status_slug); ?>">
                <?php echo esc_html($room_status); ?>
              </span>
            </div>

            <div class="room-cell room-cell-actions">
              <div class="action-menu">
                <button class="action-toggle">...</button>
                <div class="action-dropdown">
                  <button class="action-edit"
                    onclick="window.location.href='/edit-room/?room_id=<?php echo get_the_ID(); ?>'">
                    <img src="<?php echo $edit_icon_url; ?>" alt="Edit" class="action-icon">
                    <span style="font-size:14px;">Edit</span>
                  </button>
                  <button class="action-view"
                    onclick="window.open('<?php echo get_permalink($room_id); ?>', '_blank')">
                    <img src="<?php echo $view_icon_url; ?>" alt="View" class="action-icon">
                    <span style="font-size:14px;"> View Room</span>
                  </button>
                  <button class="action-duplicate" data-id="<?php echo esc_attr($room_id); ?>">
                    <i class="dashicons dashicons-admin-page" style="font-size:14px; margin-right:5px;"></i>
                    <span style="font-size:14px;">Duplicate</span>
                  </button>
                  <button class="action-delete" data-id="<?php echo esc_attr($room_id); ?>">
                    <img src="<?php echo $delete_icon_url; ?>" alt="Delete" class="action-icon">
                    <span style="font-size:14px;"> Delete</span>
                  </button>
                </div>
              </div>
            </div>
            </div>

        <?php endwhile; ?>
        <?php wp_reset_postdata(); ?>
      <?php else: // No rooms found, display the custom message ?>
        <div class="no-room-listings-message">

          <img src="https://aaswjobstaging.wpenginepowered.com/wp-content/uploads/2025/10/NoRoomsBG.jpg"
            alt="No Room Listings" />
          <h2>No Room Listings Yet</h2>
          <p>
            Start connecting with social workers and professionals by
            listing your space. Your space could be someone's perfect
            workplace. Start by listing it today.
          </p>

        </div>
      <?php endif; ?>

    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function () {

      // Loader functions
      function showLoader() {
        let loader = document.createElement('div');
        loader.id = 'ajax-loader';
        loader.style.position = 'fixed';
        loader.style.top = '0';
        loader.style.left = '0';
        loader.style.width = '100%';
        loader.style.height = '100%';
        loader.style.background = 'rgba(255,255,255,0.6)';
        loader.style.zIndex = '9999';
        loader.style.display = 'flex';
        loader.style.alignItems = 'center';
        loader.style.justifyContent = 'center';
        loader.innerHTML = '<div class="spinner"></div>';
        document.body.appendChild(loader);
      }

      function hideLoader() {
        let loader = document.getElementById('ajax-loader');
        if (loader) loader.remove();
      }

      // Event Delegation for Action Toggle
      document.addEventListener("click", function (e) {
        if (e.target.classList.contains("action-toggle")) {
          e.stopPropagation();

          let originalDropdown = e.target.nextElementSibling;

          // Remove existing floating dropdown
          let existing = document.querySelector(".floating-dropdown");
          if (existing) existing.remove();

          // Clone dropdown
          let dropdown = originalDropdown.cloneNode(true);
          dropdown.classList.add("floating-dropdown");
          dropdown.style.position = "fixed";
          dropdown.style.zIndex = "9999";
          dropdown.style.display = "block";
          dropdown.style.visibility = "hidden"; // temporarily hide for measurement

          // Append to DOM to calculate size
          document.body.appendChild(dropdown);

          // Calculate position
          const rect = e.target.getBoundingClientRect();
          const dropdownWidth = 160; // fixed width
          const viewportWidth = window.innerWidth;
          const dropdownHeight = dropdown.offsetHeight;
          const viewportHeight = window.innerHeight;

          // Default position (below)
          let top = rect.bottom;
          let left = rect.left - dropdownWidth;

          // Check if dropdown goes below viewport
          if (rect.bottom + dropdownHeight > viewportHeight - 10) {
            // Open upward instead
            top = rect.top - dropdownHeight;
            dropdown.classList.add("drop-up");
          } else {
            dropdown.classList.remove("drop-up");
          }

          // Adjust horizontal position to stay within viewport
          if (left + dropdownWidth > viewportWidth) {
            left = viewportWidth - dropdownWidth - 10; // Add some padding from the right edge
          }
          if (left < 0) {
            left = rect.left; // Ensure it doesn't go off the left side
          }

          // Apply position
          dropdown.style.top = top + "px";
          dropdown.style.left = left + "px";
          dropdown.style.visibility = "visible";

          // === DELETE ACTION ===
          dropdown.querySelectorAll(".action-delete").forEach(deleteBtn => {
            deleteBtn.addEventListener("click", function (ev) {
              ev.preventDefault();
              let postId = this.dataset.id;

              alertify.confirm(
                "",
                `
          <div style="text-align:center; padding:10px;">
            <h3 style='color:#18191C;font-weight:700;font-size:33px;'>Delete this room listing?</h3>
            <p style='color:#5E6670;font-weight:400;font-size:15px;'>Are you sure you want to delete this room?</p>
          </div>
          `,
                function () {
                  showLoader();
                  fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: "POST",
                    headers: {
                      "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: new URLSearchParams({
                      action: "delete_room_listing", // <-- CHANGED
                      post_id: postId,
                      _ajax_nonce: '<?php echo $delete_nonce; ?>' // <-- USES NEW NONCE
                    })
                  })
                    .then(res => res.json())
                    .then(data => {
                      hideLoader();
                      if (data.success) {
                        alertify.success("Room deleted successfully!");
                        window.location.reload();
                      } else {
                        alertify.error(data.message || "Failed to delete room.");
                      }
                    });
                },
                function () { }
              );
            });
          });

          // === DUPLICATE ACTION ===
          dropdown.querySelectorAll(".action-duplicate").forEach(dupBtn => {
            dupBtn.addEventListener("click", function (ev) {
              ev.preventDefault();
              let postId = this.dataset.id;

              alertify.confirm(
                "",
                `
          <div style="text-align:center; padding:10px;">
            <h3 style='color:#18191C;font-weight:7Example Domain</h3>
            <p style='color:#5E6670;font-weight:400;font-size:15px;'>Do you want to duplicate this room listing?</p>
          </div>
          `,
                function () {
                  showLoader();
                  fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: "POST",
                    headers: {
                      "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: new URLSearchParams({
                      action: "duplicate_room_listing", // <-- CHANGED
                      post_id: postId,
                      _ajax_nonce: '<?php echo $duplicate_nonce; ?>' // <-- USES NEW NONCE
                    })
                  })
                    .then(res => res.json())
                    .then(data => {
                      hideLoader();
                      if (data.success) {
                        alertify.success("Room duplicated successfully!");
                        window.location.reload();
                      } else {
                        alertify.error(data.message || "Failed to duplicate room.");
                      }
                    });
                },
                function () { }
              );
            });
          });

          // === CLOSE WHEN CLICKING OUTSIDE ===
          document.addEventListener("click", function removeDropdown(event) {
            if (!dropdown.contains(event.target) && !event.target.classList.contains("action-toggle")) {
              dropdown.remove();
              document.removeEventListener("click", removeDropdown);
            }
          });
        }
      });

    });
  </script>
  <?php

  // 8. Return the captured HTML
  return ob_get_clean();
}


/*
--- ====================================================================== ---
---     AJAX HANDLERS FOR 'ROOM-FOR-HIRE' POST TYPE
---     (Adapted from list-job.php)
--- ====================================================================== ---
*/

// AJAX Delete handler for Rooms
add_action('wp_ajax_delete_room_listing', 'my_delete_room_listing');
function my_delete_room_listing()
{
  // Check the new nonce
  check_ajax_referer('delete_room_nonce');

  $post_id = intval($_POST['post_id'] ?? 0);
  $post = get_post($post_id);

  // Validate post and post type
  if (!$post || $post->post_type !== 'room-for-hire') {
    wp_send_json_error(array('message' => 'Invalid room ID.'));
  }

  $current_user_id = get_current_user_id();

  // Allow only the post owner or admins to delete
  // You might want to change 'delete_others_posts' to a more specific capability
  if ($post->post_author != $current_user_id && !current_user_can('delete_others_posts')) {
    wp_send_json_error(array('message' => 'You are not allowed to delete this room.'));
  }

  // Delete permanently
  $deleted = wp_delete_post($post_id, true);

  if ($deleted) {
    wp_send_json_success(array('message' => 'Room deleted successfully.'));
  } else {
    wp_send_json_error(array('message' => 'Could not delete room. Try again.'));
  }
}

// AJAX Duplicate handler for Rooms
add_action('wp_ajax_duplicate_room_listing', 'my_duplicate_room_listing');
function my_duplicate_room_listing()
{
  // Check the new nonce
  check_ajax_referer('duplicate_room_nonce');

  $post_id = intval($_POST['post_id'] ?? 0);
  $post = get_post($post_id);

  // Validate post and post type
  if (!$post || $post->post_type !== 'room-for-hire') {
    wp_send_json_error(['message' => 'Invalid room ID.']);
  }

  // Ensure the user owns the job
  if ($post->post_author != get_current_user_id()) {
    wp_send_json_error(['message' => 'You can only duplicate your own rooms.']);
  }

  // Create duplicated post
  $new_post = [
    'post_title' => $post->post_title . ' (Copy)',
    'post_content' => $post->post_content,
    'post_status' => 'draft', // always start as draft
    'post_type' => 'room-for-hire', // <-- SET TO NEW POST TYPE
    'post_author' => get_current_user_id(),
  ];

  $new_post_id = wp_insert_post($new_post);

  if (is_wp_error($new_post_id) || !$new_post_id) {
    wp_send_json_error(['message' => 'Could not duplicate room.']);
  }

  // Copy all meta fields (this will copy all ACF fields)
  $meta = get_post_meta($post_id);
  foreach ($meta as $key => $values) {
    foreach ($values as $value) {
      update_post_meta($new_post_id, $key, maybe_unserialize($value));
    }
  }

  // Copy all taxonomies
  $taxonomies = get_object_taxonomies('room-for-hire'); // <-- SET TO NEW POST TYPE
  foreach ($taxonomies as $taxonomy) {
    $terms = wp_get_object_terms($post_id, $taxonomy, ['fields' => 'ids']);
    wp_set_object_terms($new_post_id, $terms, $taxonomy);
  }

  // Return success
  wp_send_json_success([
    'message' => 'Room duplicated successfully!',
    'new_post_id' => $new_post_id,
    'edit_url' => get_edit_post_link($new_post_id),
  ]);
}