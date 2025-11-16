<?php get_header(); ?>
<style>
/* ### IMPORT NUNITO SANS FONT ### */
@import url('https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;500;600;700&display=swap');

header.site-header {
    /* These styles make the header "float" */
    position: relative !important;
    z-index: 100; /* Ensures header is on top of the banner */
    max-width: 1200px; /* Sets a max-width like your example */
    margin: 20px auto 0 auto !important; /* 20px from top, centered horizontally */
    background: #ffffff !important; /* Force white background */
    border-radius: 99px !important; /* Fully rounded corners */
    box-shadow: 0 6px 20px rgba(0,0,0,0.1) !important; /* Floating shadow */
}

/* --- 
    Step 2: Hide the header's original container.
    This tries to hide any full-width colored bars your header might be in.
    You may need to add your theme's specific selector here.
--- */
body .site-header-wrapper,
body #header-full-width-bar {
    background: none !important;
    border: none !important;
    box-shadow: none !important;
}

/* ============================================
--- ⬇︎ MODIFIED BANNER STYLES ⬇︎ ---
============================================
*/
.room-page-banner-wrapper {
    width: 100%;
    min-height: 250px; 
    background-size: cover;
    background-position: center center;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    box-sizing: border-box;
    color: #ffffff; 
    font-family: 'Nunito Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; 
    margin-top: -160px; /* <--- !! ADJUST THIS VALUE !! */
    padding-top: 200px; /* <--- !! ADJUST THIS VALUE !! */
    padding-bottom: 40px;
    padding-left: 20px;
    padding-right: 20px;
    z-index: 1; /* Puts banner behind the header (which has z-index: 100) */
}

.room-page-banner-wrapper-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
}
.room-page-banner-content {
    position: relative;
    z-index: 2; /* Sits on top of the overlay */
    max-width: 1200px; /* Max width for the text content */
    margin: 0 auto;
}
.room-banner-title {
    font-size: 64px; 
    font-weight: 700;
    line-height: 1.3;
    padding: 30px 0px 10px 0px;
    margin-top: 0;
    margin-bottom: 15px;
    color: #ffffff;
}
.room-banner-subtitle {
    font-size: 18px;
    font-weight: 600;
    line-height: 1.6;
    padding: 0 0px 60px 0px;
    margin: 0;
    opacity: 0.95;
}
/* --- End of Banner Styles --- */


/* --- General Layout --- */
#primary.room-for-hire-page {
    max-width: 1200px; 
    /* This margin-top is important so the content 
       doesn't touch the banner */
    margin: 40px auto; 
    padding: 20px;
    font-family: 'Nunito Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    color: #333;
    
    /* We make this relative so it sits on top of the banner's 
       negative margin pull */
    position: relative; 
    z-index: 5; /* Above banner (1), below header (100) */
    background: #fff;
    
    /* --- FIX: Add box-sizing --- */
    box-sizing: border-box;
}

/* ============================================
--- ⬇︎ REST OF YOUR ORIGINAL CSS (Unchanged) ⬇︎ ---
============================================
*/

/* This main container handles the image/title split */
.room-detail-container {
    display: flex;
    flex-wrap: wrap;
    gap: 40px; 
    align-items: flex-start; 
}

/* --- Left column for image (500px width) --- */
.room-main-content {
    flex: 0 0 500px; 
    width: 500px;
    min-width: 500px; 
}

.room-sidebar { /* Right column for title and meta */
    flex: 1; 
    min-width: 300px; 
}

/* --- NEW containers for the lower part --- */
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


/* --- Header Icons --- */
.room-back-arrow {
    display: block;
    text-decoration: none;
    line-height: 1;
    width: 40px; 
    height: 40px;
    margin-bottom: 21px; 
}
.room-back-arrow img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

/* ### NEW SLIDER STYLES ### */
.room-image-slider {
    position: relative;
    width: 100%;
    height: 280px; /* Match image height */
    margin-bottom: 10px;
    border-radius: 12px;
    overflow: hidden; /* Clips the images to the container's shape */
}

/* (Updated) Image styles for the slider */
.room-featured-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%; /* Fill the container */
    object-fit: cover;
    border-radius: 12px;
    margin-bottom: 0; /* Override margin */
    
    /* CSS for the fade effect */
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.4s ease-in-out, visibility 0.4s ease-in-out;
}

/* This class will be toggled by JS to show the active image */
.room-featured-image.is-active {
    opacity: 1;
    visibility: visible;
    z-index: 1;
}
/* ### END OF NEW SLIDER STYLES ### */


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
    cursor: pointer; /* Add cursor for clickable items */
    transition: background-color 0.2s;
}
.slider-dots .dot.active {
    background-color: #0d6efd;
}

/* --- Title and Meta (Right of Image) --- */
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

/* --- Lower Left Content --- */
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
}
/* ### UPDATED WIDTH ### */
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

.map-placeholder img {
    width: 100%;
    height: auto;
    display: block;
    border-radius: 12px;
    border: 1px solid #ddd;
}

/* --- Right Sticky Sidebar --- */
.sidebar-card {
    border: 1px solid #ddd;
    border-radius: 12px;
    padding: 24px;
    background: #fff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
.sidebar-card + .sidebar-card {
    margin-top: 30px;
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

/* --- Contact Buttons --- */
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
}
.contact-button svg { /* For the email icon */
    width: 20px;
    height: 20px;
}
.contact-button .contact-icon {
    width: 20px;
    height: 20px;
    object-fit: contain;
}
.contact-button-phone {
    background: #A32441;
    color: #fff;
    margin-bottom: 15px;
}

/* ### MODIFICATION 1: Added specific hover rule for phone ### */
.contact-button-phone:hover {
    background: #A32441; /* Keep same background */
    color: #fff;       /* Keep same text color */
}

.contact-or-divider {
    text-align: center;
    margin: 15px 0;
    color: #999;
    font-size: 14px;
}
.contact-button-email {
    background: #fff;
    border: 2px solid #a32441;
    color: #a32441;
}
.contact-button-email svg {
    stroke: #a32441;
    fill: none;
    stroke-width: 1.5;
}

/* ### MODIFICATION 2: Added specific hover rules for email button and its SVG icon ### */
.contact-button-email:hover {
    background: #fff;
    border: 2px solid #a32441;
    color: #a32441;
}
.contact-button-email:hover svg {
    stroke: #a32441;
}


/* ### CSS for Custom Form ### */
.inquiry-form-card .form-placeholder {
    width: 100%; 
    height: auto; 
    box-sizing: border-box; 
}

.inquiry-form-card .custom-inquiry-form {
    margin: 0;
    padding: 0;
    display: flex; 
    flex-direction: column;
}
.inquiry-form-card .custom-inquiry-form .form-group {
    margin-bottom: 15px; 
}
.inquiry-form-card .custom-form-control {
    width: 100%;
    padding: 12px 15px; 
    border: 1px solid #ced4da; 
    border-radius: 8px; 
    font-size: 16px;
    box-sizing: border-box; 
    font-family: inherit; 
    color: #495057; 
    line-height: 1.5;
}
.inquiry-form-card .custom-form-textarea {
    min-height: 100px; 
    resize: vertical; 
}
.inquiry-form-card .custom-form-control:focus {
    border-color: #86b7fe; 
    outline: 0;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}
.inquiry-form-card .custom-form-submit {
    width: 100%;
    background: #00799e; 
    color: #fff;
    font-weight: 600;
    font-size: 16px;
    padding: 12px 15px; 
    border: none;
    border-radius: 8px; 
    cursor: pointer;
    transition: background-color 0.2s;
}
.inquiry-form-card .custom-form-submit:hover {
    background: #005f7c; 
}
.inquiry-form-card .form-message {
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 6px;
    font-weight: 500;
    border: 1px solid transparent;
}
.inquiry-form-card .form-message.error {
    background: #f8d7da;
    color: #721c24;
    border-color: #f5c6cb;
}
.inquiry-form-card .form-message.success {
    background: #d4edda;
    color: #155724;
    border-color: #c3e6cb;
}


/* ============================================
--- ⬇︎ NEW RESPONSIVE STYLES ⬇︎ ---
============================================
*/

/* --- Tablet (and larger mobile) --- */
@media (max-width: 992px) {
    #primary.room-for-hire-page {
        /* Remove horizontal padding on mobile */
        padding: 0 10px; 
        margin-top: 20px;
    }

    .room-detail-container,
    .room-detail-container-lower {
        /* Stack the columns */
        flex-direction: column;
        gap: 20px; /* Reduce gap */
    }

    /* Reset widths and min-widths that cause cropping */
    .room-main-content,
    .room-sidebar,
    .room-main-content-lower,
    .room-sidebar-sticky {
        width: 100%;
        min-width: 0; /* <-- This is the main fix */
        flex-basis: auto;
    }

    /* Disable sticky sidebar on mobile/tablet */
    .room-sidebar-sticky {
        position: relative;
        top: auto;
        margin-top: 20px; /* Add space above the stacked card */
    }
}

/* --- Small Mobile Phones --- */
@media (max-width: 768px) {
    .room-page-banner-wrapper {
        /* Reduce padding to pull text up */
        padding-top: 180px; 
        padding-bottom: 30px;
        min-height: 200px;
    }
    
    .room-banner-title {
        font-size: 42px; /* Reduce huge font size */
        padding-top: 10px;
        margin-bottom: 10px;
    }
    
    .room-banner-subtitle {
        font-size: 16px;
    }

    .room-entry-title {
        font-size: 28px; /* Reduce post title size */
    }

    /* Make image slider responsive */
    .room-image-slider {
        height: 0;
        /* This creates a 5:3 aspect ratio */
        padding-bottom: 60%; 
    }
    
    .general-info {
        width: 100%; /* Was fixed at 250px */
    }
}

</style>

<?php
$banner_image_url = 'https://aaswjobstaging.wpenginepowered.com/wp-content/uploads/2025/11/09-Rooms-For-Hire-Detail-417x232-2.jpg'; // <-- REPLACE THIS URL
$banner_title = 'Rooms for Hire in Australia'; // <-- REPLACE THIS TITLE
$banner_subtitle = 'The Rooms for Hire section of the AASW Career Centre connects social workers, counsellors, therapists, and allied health professionals with suitable consulting spaces across Australia. Whether you’re looking for a private therapy room for a few hours a week, or you have fully equipped consulting rooms available for long-term lease, this is the place to connect.'; // <-- REPLACE THIS SUBTITLE
// --- END OF EDITABLE CONTENT ---
?>

<div class="room-page-banner-wrapper" style="background-image: url('<?php echo esc_url( $banner_image_url ); ?>');">
    <div class="room-page-banner-wrapper-overlay"></div>
    <div class="room-page-banner-content">
        <h1 class="room-banner-title"><?php echo esc_html( $banner_title ); ?></h1>
        <p class="room-banner-subtitle"><?php echo esc_html( $banner_subtitle ); ?></p>
    </div>
</div>

<?php
// ### END OF STATIC BANNER HTML ###
?>


<div id="primary" class="content-area room-for-hire-page">
    <main id="main" class="site-main">

        <?php
        // Start the Loop
        while ( have_posts() ) :
            the_post();

            // --- Get all custom fields ---
            
            // ### REFINED STATE LABEL FIX ###
            $location_label = ''; // Initialize label variable
            $state_value = get_post_meta( get_the_ID(), 'state', true ); // Get the saved value (e.g., 'nsw')
            
            if ( !empty($state_value) && function_exists('get_field_object') ) {
                $field_obj = get_field_object('state'); // Get the field settings
                // Check if field object exists, has choices, and the saved value is a key in choices
                if ( $field_obj && isset( $field_obj['choices'] ) && array_key_exists( $state_value, $field_obj['choices'] ) ) {
                    $location_label = $field_obj['choices'][ $state_value ]; // Assign the label (e.g., "New South Wales")
                }
            }
            
            // If label wasn't found via ACF, use the raw value as fallback
            if ( empty($location_label) ) {
                $location_label = $state_value;
            }
            // ### END OF STATE FIX ###

            // ### NEW: Get the room_location field ###
            $room_location = get_post_meta( get_the_ID(), 'room_location', true );
            
            $dimensions = get_post_meta( get_the_ID(), 'area', true );
            $listed_by = get_post_meta( get_the_ID(), 'listed_by', true );
            $start_date = get_post_meta( get_the_ID(), 'start_date', true );
            $email = get_post_meta( get_the_ID(), 'contact_email', true );
            $phone = get_post_meta( get_the_ID(), 'contact_phone', true );
            // --- !! NEW Fields !! --
            $floor = get_post_meta( get_the_ID(), 'floor', true );
            $total_room = get_post_meta( get_the_ID(), 'total_room', true );
            $owner_company = get_post_meta( get_the_ID(), 'owner_company', true );
            $owner_logo_id = get_post_meta( get_the_ID(), 'owner_logo', true );
            $owner_logo_url = $owner_logo_id ? wp_get_attachment_image_url( $owner_logo_id, 'thumbnail' ) : '';
            
            // --- *** NEW: GET LAT/LNG COORDINATES *** ---
            $rhf_latitude = get_post_meta( get_the_ID(), 'rhf_latitude', true );
            $rhf_longitude = get_post_meta( get_the_ID(), 'rhf_longitude', true );
            // --- *** END OF NEW CODE *** ---

            // ### GALLERY LOGIC (ONLY $gallery_data) ###
           // $gallery_image_ids = get_post_meta( get_the_ID(), 'easy_pg_data', true );
           // $gallery_image_ids = $gallery_image_ids['image_url'];
            
			
			$gallery_data = get_post_meta( get_the_ID(), 'easy_pg_data', true );

			if ( is_array( $gallery_data ) && isset( $gallery_data['image_url'] ) ) {
				$gallery_image_ids = $gallery_data['image_url'];
			} else {
				$gallery_image_ids = [];
			}

			if ( ! is_array( $gallery_image_ids ) ) {
				$gallery_image_ids = [];
			}
					   
        //$index=0;
        ?>


        <article id="post-<?php the_ID(); ?>" <?php post_class('room-detail-article'); ?>>
            
            <a href="https://aaswjobstaging.wpenginepowered.com/rooms-for-hire" title="Go back" class="room-back-arrow">
                <img src="https://aaswjobstaging.wpenginepowered.com/wp-content/uploads/2025/10/mingcute_arrow-up-fill.png" alt="Back">
            </a>
            
            <div class="room-detail-container">

                <div class="room-main-content">

					<?php if ( ! empty( $gallery_image_ids ) ) : ?>
						
						<div class="room-image-slider">
							<?php foreach ( $gallery_image_ids as $index => $image_url ) : ?>
								<?php
								// Get alt text (optional: you can replace this with attachment alt if IDs are used)
								$image_alt = get_the_title();
								
								// Set 'is-active' class for the first image
								$active_class = ( $index === 0 ) ? 'is-active' : '';
								?>
								<img 
									src="<?php echo esc_url( $image_url ); ?>" 
									alt="<?php echo esc_attr( $image_alt ); ?>" 
									class="room-featured-image <?php echo esc_attr( $active_class ); ?>"
									data-slide-image="<?php echo esc_attr( $index ); ?>"
								>
							<?php endforeach; ?>
						</div>

						<?php if ( count( $gallery_image_ids ) > 1 ) : // Show dots only if more than one image ?>
							<div class="slider-dots">
								<?php foreach ( $gallery_image_ids as $index => $image_url ) : ?>
									<span 
										class="dot <?php echo ( $index === 0 ) ? 'active' : ''; ?>" 
										data-slide-index="<?php echo esc_attr( $index ); ?>"
									></span>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

					<?php else : ?>
						<?php // --- START OF PLACEHOLDER --- ?>
						<?php // Show a placeholder if no images are in the gallery ?>
						<div class="room-image-slider">
							<img 
								src="https://aaswjobstaging.wpenginepowered.com/wp-content/uploads/2025/11/elementor-placeholder-image-1.png"  <?php // <-- !! REPLACE THIS URL with your placeholder image URL!! ?>
								alt="Room image not available" 
								class="room-featured-image is-active"
							>
						</div>
						<?php // --- END OF PLACEHOLDER --- ?>
					<?php endif; ?>

				</div>



                <div class="room-sidebar"> 
                
                    <header class="entry-header">
                        <?php the_title( '<h1 class="room-entry-title">', '</h1>' ); ?>
                    </header>
                
                    <div class="room-meta-icons">
                        
                        <?php // --- MODIFIED LOCATION DISPLAY --- ?>
                        <?php if ( $room_location || $location_label ) : // Check if either field exists ?>
                            <p>
                                <img src="https://aaswjobstaging.wpenginepowered.com/wp-content/uploads/2025/10/location-icon.png" alt="Location Icon" class="meta-icon">
                                
                                <?php 
                                // Display room_location if it exists
                                if ( $room_location ) {
                                    echo esc_html( $room_location );
                                }
                                
                                // If both exist, add a comma and space
                                if ( $room_location && $location_label ) {
                                    echo ', ';
                                }
                                
                                // Display location_label (state) if it exists
                                if ( $location_label ) {
                                    echo esc_html( $location_label );
                                }
                                ?>
                            </p>
                        <?php endif; ?>
                        <?php // --- END OF MODIFIED LOCATION DISPLAY --- ?>

                        <?php if ( $dimensions ) : ?>
                            <p>
                                <img src="https://aaswjobstaging.wpenginepowered.com/wp-content/uploads/2025/10/area-icon.png" alt="Dimensions Icon" class="meta-icon">
                                <?php echo esc_html( $dimensions ); ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="room-meta-text">
                        <?php if ( $listed_by ) : ?>
                            <span>Listed by: <strong><?php echo esc_html( $listed_by ); ?></strong></span>
                        <?php endif; ?>
                        <?php if ( $start_date ) : ?>
                            <span style="margin-left: 15px;">Start Date: <strong><?php echo esc_html( date("d M Y", strtotime($start_date)) ); ?></strong></span>
                        <?php endif; ?>
                    </div>
                </div>

            </div><div class="room-detail-container-lower">
                
                <div class="room-main-content-lower">

                    <div class="entry-content">
                        <h2 class="room-section-title">Description</h2>
                        <?php the_content(); ?>
                    </div>

                    <?php if ( $floor || $total_room ) : ?>
                    <div class="general-info">
                        <h2 class="room-section-title">General information</h2>
                        <ul class="general-info-list">
                            <?php if ( $floor ) : ?>
                            <li>
                                <span style= "width: 110px;">Floor</span>
                                <span><?php echo esc_html( $floor ); ?></span>
                            </li>
                            <?php endif; ?>
                            <?php if ( $total_room ) : ?>
                            <li>
                                <span style= "width: 110px;">Total room</span>
                                <span><?php echo esc_html( $total_room ); ?></span>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <?php // --- *** NEW: GOOGLE MAP DISPLAY *** --- ?>
                    <?php if ( ! empty( $rhf_latitude ) && ! empty( $rhf_longitude ) ) : ?>
                        
                        <div class="room-map-container" style="margin-top: 30px;">
                            <h2 class="room-section-title">Location</h2>
                            <div id="rhf-single-room-map" style="height: 400px; width: 100%; border-radius: 12px; border: 1px solid #ddd; background-color: #f0f0f0;"></div>
                        </div>

                        <?php // This script is placed here to have access to the PHP variables ?>
                        <script>
                        function initRoomMap() {
                            // Get the coordinates from PHP
                            const mapLat = <?php echo json_encode( $rhf_latitude ); ?>;
                            const mapLng = <?php echo json_encode( $rhf_longitude ); ?>;
                            
                            if ( !mapLat || !mapLng ) {
                                return; // Don't run if coordinates are missing
                            }

                            const mapElement = document.getElementById('rhf-single-room-map');
                            if ( !mapElement ) {
                                return; // Don't run if map element isn't here
                            }

                            // Create the LatLng object
                            const location = { lat: parseFloat(mapLat), lng: parseFloat(mapLng) };
                            
                            // Initialize the map
                            const map = new google.maps.Map(mapElement, {
                                zoom: 15,
                                center: location,
                                mapTypeControl: false,
                                streetViewControl: false,
                                fullscreenControl: false,
                            });

                            // Add a marker
                            const marker = new google.maps.Marker({
                                position: location,
                                map: map,
                            });
                        }
                        </script>
                        
                        <?php // Load the Google Maps API, which will call our function
                        // Using the key from your form file
                        $google_maps_api_key = 'AIzaSyCQCUlVYxaZU0vuFUi9fNX68QyFdKOvi2A'; 
                        ?>
                        <script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo $google_maps_api_key; ?>&callback=initRoomMap"></script>

                    <?php endif; ?>
                    <?php // --- *** END: GOOGLE MAP DISPLAY *** --- ?>


                </div><div class="room-sidebar-sticky">

                    <div class="sidebar-card contact-owner-card">
                        <h3 class="sidebar-card-title">Contact Room Owner</h3>
                        
                        <div class="owner-info">
                            <?php if ( $owner_logo_url ) : ?>
                                <img src="<?php echo esc_url( $owner_logo_url ); ?>" alt="<?php echo esc_attr( $listed_by ); ?> logo" class="owner-logo">
                            <?php endif; ?>
                            <div class="owner-details">
                                <?php if ( $listed_by ) : ?>
                                    <span class="owner-name"><?php echo esc_html( $listed_by ); ?></span>
                                <?php endif; ?>
                                <?php if ( $owner_company ) : ?>
                                    <span class="owner-company"><?php echo esc_html( $owner_company ); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ( $phone ) : ?>
                            <a href="tel:<?php echo esc_attr( str_replace('-', '', $phone) ); ?>" class="contact-button contact-button-phone">
                                
                                <img src="https://aaswjobstaging.wpenginepowered.com/wp-content/uploads/2025/10/Vector.png" alt="Phone Icon" class="contact-icon">
                                
                                <?php echo esc_html( $phone ); ?>
                            </a>
                        <?php endif; ?>

                        <p class="contact-or-divider">or</p>

                        <?php if ( $email ) : ?>
                            <a href="mailto:<?php echo esc_attr( $email ); ?>" class="contact-button contact-button-email">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                                <?php echo esc_html( $email ); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                             <div class="form-placeholder">
                                 <?php echo do_shortcode('[room_inquiry_form]'); ?>
                             </div>
                        </div>

                </div></div></article><?php
        endwhile; // End of the loop.
        ?>

    </main></div>

<?php get_footer(); ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const images = document.querySelectorAll(".room-featured-image");
    const dots = document.querySelectorAll(".slider-dots .dot");

    if (images.length === 0 || dots.length === 0) return;

    dots.forEach(dot => {
        dot.addEventListener("click", function() {
            const index = parseInt(this.getAttribute("data-slide-index"), 10);

            // Remove active classes
            images.forEach(img => img.classList.remove("is-active"));
            dots.forEach(d => d.classList.remove("active"));

            // Add active to selected
            images[index].classList.add("is-active");
            this.classList.add("active");
        });
    });
});
</script>