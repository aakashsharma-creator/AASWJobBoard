<?php
/**
 * This file includes all the Custom functions used for the website functionality
 * @author: InspiroWorks
*/

add_filter( 'body_class', 'add_user_role_body_class' );
function add_user_role_body_class( $classes ) {

    if ( is_user_logged_in() ) {
        $user = wp_get_current_user();
        if ( ! empty( $user->roles ) ) {
            foreach ( $user->roles as $role ) {
                $classes[] = 'role-' . sanitize_html_class( $role );
            }
        }
    }
    return $classes;
}




// ==== Register Shortcode for Job Alert form  [job_alert_form] ==== 
function aasw_job_alert_form_shortcode() {
    ob_start();
    ?>
	<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

	<div class="loader-overlay" id="loader" style="display:none;">
	  <img src="<?php echo site_url(); ?>/wp-content/uploads/2025/10/loading-aasw-logo-1.jpg" alt="Loading..." class="loader-logo">
	</div> 
	
    <form id="job-alert-form" class="job-alert-form" novalidate>
        <?php wp_nonce_field('job_alert_form_action', 'job_alert_nonce'); ?>
        
        <div class="job_alert_form_home">
            <div>
                <label for="job_keyword">Job Keyword</label>
                <input type="text" id="job_keyword" name="job_keyword" placeholder="Job Keyword" required />
            </div>
            <div>
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" onBlur="checkEmailExistsOrNot(this.value);" placeholder="mail@mail.com" required />
            </div>
            <div>
                <label for="job_type">Select Job Type</label>
                <?php
                $job_types = get_all_wpjob_types_array();
                ?>

                <select name="job_type" id="job_type" class="" required>
                    <option value="">Choose Job Type</option>
                    <?php foreach ( $job_types as $job_type ) : ?>
                        <option value="<?php echo esc_attr( $job_type['term_id'] ); ?>">
                            <?php echo esc_html( $job_type['term_name'] ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
              
            </div>
            <div>
                <label for="frequency">Email Frequency</label>
                <select id="frequency" name="frequency" required>
                    <option value="">Choose frequency</option>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                </select>
            </div>
            <div>
                <label for="category">Select Category</label>
                <?php
                    $job_category = get_all_wpjob_category();
                ?>
                <select name="job_category" id="job_category" required>
                    <option value="">Choose category</option>
                    <?php foreach ( $job_category as $job_cat ) : ?>
                        <option value="<?php echo esc_attr( $job_cat['term_id'] ); ?>">
                            <?php echo esc_html( $job_cat['term_name'] ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="location">Select Location</label>
                <?php
                    $job_location = get_all_wpjob_locations();
                ?>
                <select id="location" name="location" required>
                    <option value="">Choose location</option>
                    <?php foreach ( $job_location as $location ) : ?>
                        <option value="<?php echo esc_attr( $location['term_id'] ); ?>">
                            <?php echo esc_html( $location['term_name'] ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
			
        </div>
		<div>
			<label for="job_alert_agreement" class="job-alert-checkbox">
				<input type="checkbox" name="checkbox-2[]" value="job_alert_agreement" id="job_alert_agreement">
				<span class="forminator-checkbox-label">I accept the <a href="https://aaswjobstaging.wpenginepowered.com/terms-and-conditions/" target="_blank">Terms &amp; Conditions</a> and  <a href="https://aaswjobstaging.wpenginepowered.com/privacy-policy/" target="_blank">Privacy Policy</a> of AASW Social Work Jobs.</span>
			</label>
		</div>
        <div style="margin-top:20px;text-align:center;">
            <button type="submit" class="subscribe-btn">Subscribe to Job Alert <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/arrow-white.png"></button>
        </div>
        <p class="form-message" style="text-align:center;margin-top:5px;font-size: 14px;"></p>
        <p class="para">By subscribing, you agree to receive job alerts and updates. You can unsubscribe at any time.</p>
        
    </form>

    <script type="text/javascript">
	function checkEmailExistsOrNot(userEmail){
		//alert(userEmail);  
	}
	
    jQuery(document).ready(function($){
		$('#job_type, #frequency, #job_category').select2();
		
		/*$('#job_category').select2({
			placeholder: "Select category",
			allowClear: true
		});*/
		
		$('.job_alert_form_home #location').select2();
		
        $('#job-alert-form').on('submit', function(e){
            e.preventDefault();
			if ($(this).data('submitted')) {
				e.preventDefault();
				return false;
			}
           
            var form = $(this);
            var messageBox = form.find('.form-message');
            var valid = true;

            // Reset old errors
            form.find('.error-msg').remove();
            form.find('input, select').removeClass('error-field');

            // Validate required fields
            form.find('input[required], select[required]').each(function(){
                if($(this).val().trim() === ''){
                    $(this).addClass('error-field')
                           .after('<span class="error-msg">This field is required</span>');
                    valid = false;
                }
            });
			
			// Validate job keyword (only alphabets)
			var jobKeyword = $('#job_keyword').val().trim();
			if (jobKeyword !== '') {
				var keywordRegex = /^[A-Za-z\s]+$/; // allows only letters and spaces
				if (!keywordRegex.test(jobKeyword)) {
					$('#job_keyword').addClass('error-field')
									 .after('<span class="error-msg">Only alphabets are allowed</span>');
					valid = false;
				}
			}

            // Validate email format
            var email = $('#email').val().trim();
            if(email !== ''){
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if(!emailRegex.test(email)){
                    $('#email').addClass('error-field')
                               .after('<span class="error-msg">Enter a valid email</span>');
                    valid = false;
                }
            }

            if(!valid){
                messageBox.html('Please fix errors before submitting.').css('color','red');
                return;
            }
            
			//$('.loading').show();   
			jQuery('#loader').show(); 
			 
            // AJAX request if form is valid
            $.ajax({
                url: '<?php echo admin_url("admin-ajax.php"); ?>',
                type: 'POST',
                data: {
                    action: 'save_job_alert',
                    form_data: form.serialize()
                },
                beforeSend: function(){
                    messageBox.html('Submitting...').css('color','blue');
                },
                success: function(response){
                    if(response.success){
                        messageBox.html(response.data).css('color','green');
                        form[0].reset();
                        //$('.loading').hide();
						setTimeout(function() {
							jQuery('#loader').fadeOut('slow'); // you can use .hide() if you don't want animation
							jQuery('body').css('overflow','unset'); 
						}, 2000);
						$(this).data('submitted', true);
                    } else {
                        messageBox.html(response.data).css('color','red');
                        //$('.loading').hide();
						setTimeout(function() {
							jQuery('#loader').fadeOut('slow'); // you can use .hide() if you don't want animation
							jQuery('body').css('overflow','unset'); 
						}, 2000);
                    }
                },
                error: function(){
                    messageBox.html('Something went wrong. Try again!').css('color','red');
                    //$('.loading').hide();
					setTimeout(function() {
						jQuery('#loader').fadeOut('slow'); // you can use .hide() if you don't want animation
						jQuery('body').css('overflow','unset'); 
					}, 2000);
                }
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('job_alert_form', 'aasw_job_alert_form_shortcode');


// ==== Handle AJAX Submission ====
function aasw_save_job_alert() {
    if ( ! isset($_POST['form_data']) ) {
        wp_send_json_error("Invalid request.");
    }

    parse_str($_POST['form_data'], $form_data);

    // Verify nonce
    if ( ! isset($form_data['job_alert_nonce']) || 
         ! wp_verify_nonce($form_data['job_alert_nonce'], 'job_alert_form_action') ) {
        wp_send_json_error("Security check failed.");
    }

    // Example: sanitize inputs 
    $email      = sanitize_email( $form_data['email'] );
    $keyword    = sanitize_text_field( $form_data['job_keyword'] );
    $location   = sanitize_text_field( $form_data['location'] );
    $category   = intval( $form_data['job_category'] );
    $job_type   = intval( $form_data['job_type'] );
    $frequency  = sanitize_text_field( $form_data['frequency'] );

    $location_name = get_term( $location )->name;
    $category_name = get_term( $category )->name;

    // Build post title
    $post_title = $keyword . ', ' . $location_name . ', ' . $category_name;

    $post_author = get_current_user_id() ? get_current_user_id() : 0;

    // Insert post
    $post_id = wp_insert_post( array(
        'post_title'   => $post_title,
        'post_type'    => 'job_alert',
        'post_status'  => 'publish',
        'post_author'  => $post_author,
    ) );

    if ( $post_id ) {
        // Insert meta values
        $alert_search_terms = array(
            'categories' => array( $category ),
            'regions'    => array( $location ),
            'tags'       => null,
            'types'      => array( $job_type ),
        );

        update_post_meta( $post_id, 'alert_search_terms', $alert_search_terms );
        update_post_meta( $post_id, 'alert_frequency', $frequency );
        update_post_meta( $post_id, 'alert_keyword', $keyword );
        update_post_meta( $post_id, 'alert_location', $location_name );
        update_post_meta( $post_id, '_alert_permission_approved', 1 );

        // Optional: store user details if needed
        //update_post_meta( $post_id, 'alert_name', $name );
        update_post_meta( $post_id, 'alert_email', $email );
    }

    wp_send_json_success("Job Alert saved successfully for $email!");
}
add_action('wp_ajax_save_job_alert', 'aasw_save_job_alert');
add_action('wp_ajax_nopriv_save_job_alert', 'aasw_save_job_alert');



// ==== Register Shortcode for all Job Packages showing + Owl Slider [all_job_packages] ==== 
function aasw_job_packages_shortcodes() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return '<p>WooCommerce is not active.</p>';
    }
	
	//Get WooCommerce checkout page url
	$checkout_page_url = wc_get_checkout_url();

    // Enqueue Owl Carousel CSS & JS
    wp_enqueue_style( 'owl-carousel', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css', array(), '2.3.4' );
    wp_enqueue_style( 'owl-theme', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css', array(), '2.3.4' );
    wp_enqueue_script( 'owl-carousel', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js', array('jquery'), '2.3.4', true );

    ob_start();

    // Query WooCommerce job packages
    $args = array(
		'post_type'      => 'product',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
		'tax_query'      => array(
			'relation' => 'AND', // important if you have multiple conditions
			array(
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => array( 'job_package' ),
			),
			array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug', // or 'term_id'
				'terms'    => array( 'business', 'not-for-profit' ), // replace with your category slugs
				'operator' => 'IN', // can also be 'NOT IN' or 'AND'
			),
		),
	);

    $loop = new WP_Query( $args ); 

    if ( $loop->have_posts() ) {
        ?>
		<style>
		.loader-overlay {
		  position: fixed;
		  top: 0;
		  left: 0;
		  width: 100%;
		  height: 100%;
		  background-color: #0000ff4a; 
		  display: flex;
		  justify-content: center;
		  align-items: center;
		  z-index: 99999999;
		}

		.loader-logo {
		  width: 80px;
		  height: 80px;
		  animation: spin 2s linear infinite;
		}

		@keyframes spin {
		  0% { transform: rotate(0deg); }
		  100% { transform: rotate(360deg); }
		}

		.fade-out {
		  opacity: 0;
		  transition: opacity 0.8s ease;
		  pointer-events: none;
		}
		</style>
		
		<div class="loader-overlay" id="loader" style="display:none;">
		  <img src="<?php echo site_url(); ?>/wp-content/uploads/2025/10/loading-aasw-logo-1.jpg" alt="Loading..." class="loader-logo">
		</div> 
		
        <section class="packages owl-carousel">
		
			<?php
			$i = 1;
			while ( $loop->have_posts() ) : $loop->the_post();
				global $product;
				$class = $starter = $badge = $badge1 = '';
				
				//
				$product_id = $product->get_id();
				$product_cats = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'slugs' ) );

				$classes = '';

				if ( in_array( 'not-for-profit', $product_cats ) ) {
					$classes = 'not-for-profit';
					$starter = ' starter';
					$badge   = '<span>Non profit</span>';
				}else if ( in_array( 'business', $product_cats ) ) {
					if ( $i == 2 ) {
						$classes   = ' red';
						$starter = ' starter';
						$badge1   = '<span>Popular</span>';
					}
				} else {
					$classes = '';
					$badge   = '';
					$starter = '';
				}
				?>
					<div class="package <?php //echo esc_attr( $class ); ?> <?php echo $classes; ?>">
						<div class="package-header <?php echo esc_attr( $starter ); ?>">
							<?php the_title(); ?> <?php echo get_field('package_description'); ?> <?php echo $badge1; ?> <?php echo $badge; ?>
						</div>
						<div class="price"><?php echo $product->get_price_html(); ?></div>
						<div class="description"><?php echo wpautop( $product->get_short_description() ); ?></div>
						<?php the_content(); ?>
						<button class="ajax-add-to-cart choose-package <?php echo esc_attr( $starter ); ?>" 
								data-product_id="<?php echo esc_attr( $product->get_id() ); ?>">
							Choose Package <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/arrow.svg" alt="Arrow" />
						</button>
					</div>
				<?php
				$i++;
			endwhile;
			?>
        </section>
		
        <script>
        jQuery(document).ready(function($){
            $('.packages .package').on('hover', function(){
                $(this).addClass('red');
            });

            $('.packages.owl-carousel').owlCarousel({
                loop: false,
                margin: 20,
                nav: true,
                dots: false,
                autoplay: true,
                smartSpeed: 600,
				mouseDrag: true,     // allows dragging with mouse (for Mac trackpad)
				touchDrag: true,     // enables touch swiping on iPhone/iPad
				pullDrag: true,      // makes dragging feel more natural
				freeDrag: false,     // restricts slide to one at a time
				navText: [
					'<svg aria-hidden="true" viewBox="0 0 256 512" xmlns="http://www.w3.org/2000/svg"><path d="M31.7 239l136-136c9.4-9.4 24.6-9.4 33.9 0l22.6 22.6c9.4 9.4 9.4 24.6 0 33.9L128.8 256l96.4 96.4c9.4 9.4 9.4 24.6 0 33.9l-22.6 22.6c-9.4 9.4-24.6 9.4-33.9 0l-136-136c-9.5-9.4-9.5-24.6-.1-34z"></path></svg>', // prev
					'<svg aria-hidden="true" viewBox="0 0 256 512" xmlns="http://www.w3.org/2000/svg"><path d="M224.3 273l-136 136c-9.4 9.4-24.6 9.4-33.9 0l-22.6-22.6c-9.4-9.4-9.4-24.6 0-33.9l96.4-96.4-96.4-96.4c-9.4-9.4-9.4-24.6 0-33.9L54.3 103c9.4-9.4 24.6-9.4 33.9 0l136 136c9.5 9.4 9.5 24.6.1 34z"></path></svg>' // next
				],
                responsive:{
                    0:{
                        items:1
                    },
                    768:{
                        items:2
                    },
                    1024:{
                        items:3
                    },
                    1300:{
                        items:4
                    } 
                }
            });   
			
			jQuery(document).on('click', '.package .ajax-add-to-cart', function(e) {
				e.preventDefault();

				var $this = jQuery(this);
				var product_id = $this.data('product_id');  
	
				jQuery('#loader').show(); 

				jQuery.ajax({
					type: 'POST',
					url: wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart'),
					data: {
						product_id: product_id,
						quantity: 1
					},
					success: function(response) {
						if (response && response.error && response.product_url) {
							// If WooCommerce says it can't be added
							jQuery('#loader').fadeOut('slow'); // you can use .hide() if you don't want animation
							jQuery('body').css('overflow','unset'); 
							console.log('Cannot add this package again, as you already purchased this. Try with different package.');
							if (typeof elementorProFrontend !== "undefined" && elementorProFrontend.modules.popup) {
								elementorProFrontend.modules.popup.showPopup({ id: 3732 }); 
							}else{
								alert('Cannot add this package again, please check your checkout page.');
							}	
						} else {
							// Update cart fragments (mini-cart, counters etc.)
							jQuery(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $this]);

							setTimeout(function() {
								jQuery('#loader').fadeOut('slow'); // you can use .hide() if you don't want animation
								jQuery('body').css('overflow','unset'); 
							}, 2000);
							//console.log('Package added.');

							// Redirect to checkout if you want
							window.location = "<?php echo esc_url( wc_get_checkout_url() ); ?>";
						}
					},
					error: function(xhr, status, error) {
						console.log('AJAX Error.', error);
						setTimeout(function() {
							jQuery('#loader').fadeOut('slow'); // you can use .hide() if you don't want animation
							jQuery('body').css('overflow','unset'); 
						}, 2000);
						//console.log('Something went wrong. Try Again.');
					}
				});
			});
			
			$(document).on('click', '#close_popups', function() {
				$('.elementor-popup-modal').removeClass('elementor-active').hide();
				$('body').removeClass('elementor-popup-modal-open');
				//console.log('popup closed');
			});

        });
        </script>
        <?php
    } else {
        echo '<p>No job packages found.</p>';
    }

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode( 'all_job_packages', 'aasw_job_packages_shortcodes' );


// Active Subscription page shortcode for Advertiser Dashboard
function active_job_packages_shortcodes() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return '<p>WooCommerce is not active.</p>';
    }
	
	// Get current logged-in user ID
	$current_user_id = get_current_user_id();
	$packages = wc_paid_listings_get_user_packages( $current_user_id );
	
	//print_r($packages);    
	
	if ( empty( $packages ) ) {
		
		//Get WooCommerce checkout page url
		$checkout_page_url = wc_get_checkout_url();

		// Enqueue Owl Carousel CSS & JS
		wp_enqueue_style( 'owl-carousel', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css', array(), '2.3.4' );
		wp_enqueue_style( 'owl-theme', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css', array(), '2.3.4' );
		wp_enqueue_script( 'owl-carousel', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js', array('jquery'), '2.3.4', true );

		ob_start();

		// Query WooCommerce job packages in a specific category
		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'tax_query'      => array(
				'relation' => 'AND', // important if you have multiple conditions
				array(
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => array( 'job_package' ),
				),
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'slug', // or 'term_id'
					'terms'    => array( 'business', 'not-for-profit'  ), // replace with your category slugs
					'operator' => 'IN', // can also be 'NOT IN' or 'AND'
				),
			),
		);
		$loop = new WP_Query( $args );
		?>
		
		<div class="custom-info-box border_lining">
		  <div class="custom-info-icon">
			<div class="elementor-icon-box-icon">
				<span class="elementor-icon subs-icon">
				<img src="<?php echo get_stylesheet_directory_uri();?>/images/Group.png" alt=""></span>
			</div>
		  </div>
		  <div class="custom-info-content">
			<h4>No Active Package</h4>
			<p>You don't have an active subscription plan, Choose a plan to start posting jobs and access all premium features.</p>
		  </div>
		</div>

		<?php 
		if(is_user_logged_in()){
			// Show a notice to user if their job limit has lapse
			global $wpdb;
			$userID = $current_user_id;

			$result = $wpdb->get_var( $wpdb->prepare("
				SELECT COUNT(*) 
				FROM {$wpdb->prefix}wcpl_user_packages
				WHERE user_id = %d
				  AND package_type = 'job_listing'
				  AND package_limit = package_count
			", $userID) );

			if ( $result > 0 ) {
				echo '<div class="notice notice-warning" style="padding:10px; background:#fff3cd; border:1px solid #ffeeba;">
						You have consumed all your job listings. Please select a new package to continue posting jobs.
					  </div>';
			}
			?>

			<div class="main_dashboard_wrapper">
				<div class="top_info">
					<h2>Advertise Your Jobs with AASW Social Works</h2>
					<p>Reach qualified social workers across Australia with flexible job advertising options. Designed by AASW to connect you directly with your target audience.</p>
				</div>  
				<div class="gst_block">(All prices are GST exclusive)</div>
				<?php
				if ( $loop->have_posts() ) { ?>
					<section class="packages owl-carousel">
						<?php
						$i = 1;
						while ( $loop->have_posts() ) : $loop->the_post();
							global $product;
							$class = $starter = $badge = $badge1 = '';
							
							//
							$product_id = $product->get_id();
							$product_cats = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'slugs' ) );

							$classes = '';

							if ( in_array( 'not-for-profit', $product_cats ) ) {
								$classes = 'not-for-profit';
								$starter = ' starter';
								$badge   = '<span>Non profit</span>';
							}else if ( in_array( 'business', $product_cats ) ) {
								if ( $i == 2 ) {
									$classes   = ' red';
									$starter = ' starter';
									$badge1   = '<span>Popular</span>';
								}
							} else {
								$classes = '';
								$badge   = '';
								$starter = '';
							}
							?>
								<div class="package <?php //echo esc_attr( $class ); ?> <?php echo $classes; ?>">
									<div class="package-header <?php echo esc_attr( $starter ); ?>">
										<?php the_title(); ?> <?php echo get_field('package_description'); ?> <?php echo $badge1; ?> <?php echo $badge; ?>
									</div>
									<div class="price"><?php echo $product->get_price_html(); ?></div>
									<div class="description"><?php echo wpautop( $product->get_short_description() ); ?></div>
									<?php the_content(); ?>
									<button class="ajax-add-to-cart choose-package <?php echo esc_attr( $starter ); ?>" 
											data-product_id="<?php echo esc_attr( $product->get_id() ); ?>">
										Choose Package <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/arrow.svg" alt="Arrow" />
									</button>
								</div>
							<?php
							$i++;
						endwhile;
						?>
					</section>
					
					<style>
					.loader-overlay {
					  position: fixed;
					  top: 0;
					  left: 0;
					  width: 100%;
					  height: 100%;
					  background-color: #0000ff4a; 
					  display: flex;
					  justify-content: center;
					  align-items: center;
					  z-index: 99999999;
					}

					.loader-logo {
					  width: 80px;
					  height: 80px;
					  animation: spin 2s linear infinite;
					}

					@keyframes spin {
					  0% { transform: rotate(0deg); }
					  100% { transform: rotate(360deg); }
					}

					.fade-out {
					  opacity: 0;
					  transition: opacity 0.8s ease;
					  pointer-events: none;
					}
					</style>
					
					<div class="loader-overlay" id="loader" style="display:none;">
					  <img src="<?php echo site_url(); ?>/wp-content/uploads/2025/10/loading-aasw-logo-1.jpg" alt="Loading..." class="loader-logo">
					</div> 
					
					<script>
					jQuery(document).ready(function($){
						$('.packages .package').on('hover', function(){
							$(this).addClass('red');
						});
						
						$('.packages.owl-carousel').owlCarousel({
							loop: false,
							margin: 20,
							nav: true,
							dots: false,
							autoplay: true,
							smartSpeed: 600,
							mouseDrag: true,     // allows dragging with mouse (for Mac trackpad)
							touchDrag: true,     // enables touch swiping on iPhone/iPad
							pullDrag: true,      // makes dragging feel more natural
							freeDrag: false,     // restricts slide to one at a time
							navText: [
								'<svg aria-hidden="true" viewBox="0 0 256 512" xmlns="http://www.w3.org/2000/svg"><path d="M31.7 239l136-136c9.4-9.4 24.6-9.4 33.9 0l22.6 22.6c9.4 9.4 9.4 24.6 0 33.9L128.8 256l96.4 96.4c9.4 9.4 9.4 24.6 0 33.9l-22.6 22.6c-9.4 9.4-24.6 9.4-33.9 0l-136-136c-9.5-9.4-9.5-24.6-.1-34z"></path></svg>', // prev
								'<svg aria-hidden="true" viewBox="0 0 256 512" xmlns="http://www.w3.org/2000/svg"><path d="M224.3 273l-136 136c-9.4 9.4-24.6 9.4-33.9 0l-22.6-22.6c-9.4-9.4-9.4-24.6 0-33.9l96.4-96.4-96.4-96.4c-9.4-9.4-9.4-24.6 0-33.9L54.3 103c9.4-9.4 24.6-9.4 33.9 0l136 136c9.5 9.4 9.5 24.6.1 34z"></path></svg>' // next
							],
							responsive:{
								0:{
									items:1
								},
								768:{
									items:2
								},
								1024:{
									items:3
								},
								1300:{
									items:4
								}
							}
						});
						
						jQuery(document).on('click', '.package .ajax-add-to-cart', function(e) {
							e.preventDefault();
							var $this = jQuery(this);
							var product_id = $this.data('product_id');  
				
							jQuery('#loader').show(); 
							
							jQuery.ajax({
								type: 'POST',
								url: wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart'),
								data: {
									product_id: product_id,
									quantity: 1
								},
								success: function(response) {
									if (response && response.error && response.product_url) {
										// If WooCommerce says it can't be added
										setTimeout(function() {
											jQuery('#loader').fadeOut('slow'); // you can use .hide() if you don't want animation
											jQuery('body').css('overflow','unset'); 
										}, 500);
										console.log('Cannot add this package again, as you already purchased this. Try with different packages.');
										if (typeof elementorProFrontend !== "undefined" && elementorProFrontend.modules.popup) {
											elementorProFrontend.modules.popup.showPopup({ id: 3732 }); // Replace with your Popup ID
										}
									} else {
										// Update cart fragments (mini-cart, counters etc.)
										jQuery(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $this]);

										setTimeout(function() {
											jQuery('#loader').fadeOut('slow'); // you can use .hide() if you don't want animation
											jQuery('body').css('overflow','unset'); 
										}, 2000);

										// Redirect to checkout if you want
										window.location = "<?php echo esc_url( wc_get_checkout_url() ); ?>";
									}
								},
								error: function(xhr, status, error) {
									console.log('AJAX Error:', error);
									setTimeout(function() {
										jQuery('#loader').fadeOut('slow'); // you can use .hide() if you don't want animation
										jQuery('body').css('overflow','unset'); 
									}, 2000);
								}
							});
						});
					});
					</script>
					<?php
				} else {
					echo '<p>No job packages found.</p>';
				}
				?>
			</div>
			
			<div class="custom-info-box">
			  <div class="custom-info-icon">
				<div class="elementor-icon-box-icon">
					<span class="elementor-icon payment-icon-subscription"> 
						<img src="<?php echo get_stylesheet_directory_uri();?>/images/shield-cross.png" alt="">
					</span>
				</div>
			  </div>
			  <div class="custom-info-content">
				<h4>PAYMENT</h4>
				<p>Payment must be made at time of booking online by credit card or other payment method.</p>
			  </div>
			</div>
			
		<?php 
		}else{
			
			echo '<div id="error_notice"><div class="jobpostpage notice notice-warning" style="padding:10px; background:#fff3cd; border:1px solid #ffeeba;">
				Looks like you’re not logged in yet. Please log in or create an account to access your dashboard. Click here to <a href="'.get_the_permalink(3957).'" >Login</a>.
			  </div></div>';
			
		}
		?>
		
		<?php
		wp_reset_postdata();
		return ob_get_clean();

	}else{ // else show active plan/packages

		ob_start(); 
		?>
		
		<div class="subplan-dashboard">
		<style>
				.loader-overlay {
				  position: fixed;
				  top: 0;
				  left: 0;
				  width: 100%;
				  height: 100%;
				  background-color: #0000ff4a; 
				  display: flex;
				  justify-content: center;
				  align-items: center;
				  z-index: 9999;
				}

				.loader-logo {
				  width: 80px;
				  height: 80px;
				  animation: spin 2s linear infinite;
				}

				@keyframes spin {
				  0% { transform: rotate(0deg); }
				  100% { transform: rotate(360deg); }
				}

				.fade-out {
				  opacity: 0;
				  transition: opacity 0.8s ease;
				  pointer-events: none;
				}
				</style>
				
				<div class="loader-overlay" id="loader" style="display:none;">
				  <img src="<?php echo site_url(); ?>/wp-content/uploads/2025/10/loading-aasw-logo-1.jpg" alt="Loading..." class="loader-logo">
				</div> 
				<?php
				// Get current logged-in user ID
				$current_user_id = get_current_user_id();
				$packages = wc_paid_listings_get_user_packages( $current_user_id );

				if ( empty( $packages ) ) {
					$package_name = 'No Plan';
					$expiry_date_formatted = '-';
					$start_date_formatted = '-';
					$count_limit = 0;
					$is_expired = false;
				} else {
					$i = 0;
					foreach ( $packages as $package ) {
						// Get the WooCommerce order related to this package
						$order = wc_get_order( $package->order_id );

						if ( $order && ! empty( $package->package_duration ) && $package->package_duration > 0 ) {
							$order_date = $order->get_date_created(); // WC_DateTime object

							// Start date
							$start_date_formatted = $order_date->format('M d, Y');

							// Expiry date
							$expiry_date = clone $order_date;
							$expiry_date->modify( '+' . $package->package_duration . ' days' );
							$expiry_date_formatted = $expiry_date->format('M d, Y');
							
							// Compare expiry with today
							$today = new DateTime();
							// Calculate days remaining
							$interval = $today->diff( $expiry_date );
							$days_remaining = (int) $interval->format('%r%a'); // negative if already expired
							
							// Show renew button if expired OR within 3 days of expiry
							$can_renew = ( $days_remaining <= 3 );
							$is_expired = ( $expiry_date < $today );
							$is_limit_reached = ($package->package_limit == $package->package_count);
							
							// Enable button if either condition is true
							$enable_button = ( $can_renew || $is_limit_reached );

							// Then update the attributes accordingly
							$disabled_attr  = $enable_button ? '' : 'disabled';
							$disabled_class = $enable_button ? '' : ' disabled-btn';
							$tooltip        = $enable_button ? '' : 'You cannot renew this package now.';
							$checkvalidid   = $enable_button ? '' : 'invalid';
							
						} else {
							$start_date_formatted = 'No Start Date';
							$expiry_date_formatted = 'No Expiry';
							$is_expired = false;
							$can_renew='';  
						}

						if ( $i == 0 ) {
							$totalLists = $package->package_limit > 0 ? $package->package_limit : 'Unlimited';
							$package_name = esc_html( get_the_title( $package->product_id ) );
							
							$packageInfo = get_field('package_description', $package->product_id);
							if ( $package_limit === 'Unlimited' ) {
								$count_limit = 'Unlimited';
							} else {
								$used = intval( $package->package_count );
								$remaining = max( 0, $totalLists - $used );
								$count_limit = "{$remaining}";
							}
						}
						$i++;
					}
					$productData = wc_get_product( $package->product_id );
					$package_id = $package->product_id;
				} ?>
		  
		    <div class="custom-info-box check-icon">
			  <div class="custom-info-icon active_plan">
				<div class="elementor-icon-box-icon">
					<span class="elementor-icon ">
					<i class="mdi mdi-check-circle-outline"></i>
					</span>
				</div>
			  </div>
			  <div class="custom-info-content">
				<h4>Active Subscription Plan</h4>
				<p>You are currently subscribed to: <strong><?php echo $package_name; ?> <?php echo $packageInfo; ?></strong></p>
			  </div>
			</div>

			<!-- Cancel Plan -->
		  <div class="subplan-grid subplan-section">
			<div class="subplan-card">
			  <h5>Current Plan <span class="plan_names"><?php echo $package_name; ?></span></h5>
			  <span class="subplan-highlight"> <i class="fa-solid fa-hourglass-end"></i> <span id="limit_remains"><?php echo $count_limit; ?></span> Listing Remaining</span>
			  <a href="javascript:void(0)<?php //echo get_the_permalink(2819); ?>" class="subplan-btn" id="change_plan">Change Plan</a>
			  <a href="javascript:void(0)" class="subplan-btn secondary cancle_plan" id="<?php echo $current_user_id;?>" data-package-id="<?php echo esc_attr($package_id); ?>">Cancel Plan</a> 
			  
			</div>  

			<div class="subplan-card subplan-benefits">
			  <h4>Plan Benefits</h4>
			  <p style="font-size:14px; color: #767f8c;"><?php echo $productData->short_description; ?>.</p>
			  <?php echo $productData->description; ?>
			</div>
		  </div>

		  <div class="subplan-grid subplan-section">
			<div class="subplan-card plan_expireon">
			    <h4>Plan will expire on</h4>
			    <p style="font-size:23px; color:#00688f; font-weight:700;margin: 0 0 5px 0;"><span id="plan_expiry_date"><?php echo $expiry_date_formatted; ?></span></p>
			    <p style="font-size:14px; color:#6b7280;">Package started: <span class="black-text"><?php echo $start_date_formatted; ?></span></p>
			    <?php
				
				if(empty($tooltip)){
					echo '<button id="'.$checkvalidid.'"   class="renew-package-btn subplan-btn renew' . $disabled_class . '" data-package-id="' . esc_attr($package->product_id) . '" ' . $disabled_attr . '>Renew Package</button>';
				}else{
					echo '<button id="'.$checkvalidid.'"  data-tooltip="'.$tooltip.'" class="renew-package-btn subplan-btn renew' . $disabled_class . '" data-package-id="' . esc_attr($package->product_id) . '" ' . $disabled_attr . '>Renew Package</button>';
				}
				
				
				// Optional expiry message
				if ( $can_renew && ! $is_expired ) {
					echo '<p class="expiry-warning">Your plan will expire in ' . $days_remaining . ' day' . ( $days_remaining != 1 ? 's' : '' ) . '.</p>';
				} elseif ( ! $can_renew ) {
					
				}
				?>  
			</div>

			<div class="subplan-card">
			  <div class="subplan-payment">
				<div class="subplan-payment-info">
				  <h4>Payment Card  
				  <span class="edit-card">
					<!--<a href="<?php //echo site_url();?>/my-account/add-payment-method/" class="subplan-edit-link">Add new card</a>-->
					<a href="<?php echo site_url();?>/my-account/payment-methods/" class="subplan-edit-link">Manage your card</a>
				  </span>
				  </h4>
				    <?php
				    $user_id = get_current_user_id(); // or pass manually

					// Load WooCommerce functions
					if ( ! function_exists('wc_get_customer_saved_methods_list') ) {
						echo '<p>WooCommerce function not found. Make sure WooCommerce is active.</p>';
						get_footer();
						exit;
					}

					// Get all saved payment methods for the user
					$saved_methods = wc_get_customer_saved_methods_list( $user_id );
					
					// Check if any saved cards exist
					if ( ! empty( $saved_methods ) ) {
						
						$tokens = WC_Payment_Tokens::get_customer_tokens( $user_id, 'stripe' ); 
						
						foreach ( $tokens as $token ) {
							$token_value = $token->get_token();
							//print_r($token);    
							$cardtype =  $token->get_card_type();
							$cardnumber = esc_html( $token->get_last4() );
							//$card_user_name = $token->get_meta('cardholder_name');
							$card_user_name = '';
							if ( empty( $card_user_name ) ) {
								$first = get_user_meta( $user_id, 'billing_first_name', true );
								$last  = get_user_meta( $user_id, 'billing_last_name', true );
								if ( $first || $last ) {
									$card_user_name = trim( $first . ' ' . $last );
								}
							}
							
							
							$active_card = $token->is_default();  
							$classname = $active_card ? 'card-active' : ' ';   
							?>
							<div class="payment-card  <?php echo $classname; ?>">
							    <p class="cardholdername">
									<?php //echo ucfirst($cardtype); ?>   
									<img src="<?php echo get_stylesheet_directory_uri();?>/images/ms-card.png" alt="card-logo" />
									<span class="cardpersonname">
										<span class="blue-text">Name on card</span><br><?php echo ucfirst($card_user_name); ?>
									</span> 
									<span class="expirydate"><span class="blue-text">Expire date:</span> <br><?php echo esc_html( $token->get_expiry_month() . '/' . $token->get_expiry_year() ); ?></span>	  
								</p>
							    <?php if($cardnumber){ ?>
									<p class="subplan-card-number"><?php echo '**** **** **** ' . $cardnumber; ?></p>
								<?php } ?>
						    </div>
							
						<?php	
						}
						
					} else {
						echo '<p>No saved payment methods found.</p>';
					}
					?>
				</div>
				
			  </div>
			  
			</div>
		  </div>

		  <div class="subplan-section invoice_section">
			<?php echo do_shortcode('[user_order_history]');  ?>    
		  </div>
		  
		</div>
		
		<?php
		return ob_get_clean();
	}		
}
add_shortcode( 'active_job_packages', 'active_job_packages_shortcodes' );


// [user_order_history] shortcode
add_shortcode('user_order_history', 'aasw_user_order_history_table');
function aasw_user_order_history_table() {
    if (!is_user_logged_in()) {
        //return '<p>Please log in to view your order history.</p>';
    }
	//update_option( 'timezone_string', 'Australia/Sydney' );
    $user_id = get_current_user_id();
    $customer_email = wp_get_current_user()->user_email;

    $args = array(
        'limit'        => -1,
        'type'         => 'shop_order',
        'customer_id'  => $user_id,
        'status'       => array('wc-completed', 'wc-processing', 'wc-cancelled', 'wc-on-hold'),
    );
    $orders = wc_get_orders($args);

    if (empty($orders)) {
        $orders = wc_get_orders(array(
            'limit'         => -1,
            'billing_email' => $customer_email,
            'status'        => array('wc-completed', 'wc-processing', 'wc-cancelled', 'wc-on-hold'),
        ));
    }

    if (empty($orders)) {
        return '<p>No orders found.</p>';
    }
	
    ob_start();
    ?>
    <div class="aasw-order-history-wrapper">
        <h3>Latest Invoices</h3>
		<div class="table-responsive">
        <table class="aasw-order-history-table">
            <thead>
                <tr>
                    <th>#ID</th>
                    <th>Date</th>
                    <th>Package</th>
                    <th>Amount</th>
					<!--<th>Status</th>-->
					<th>Invoice</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order) : 
                    $order_id   = $order->get_id();
                    $date       = $order->get_date_created() ? $order->get_date_created()->date_i18n('M j, Y g:i A') : '';
                    $items      = $order->get_items();
                    $total      = $order->get_total();
					$status     = $order->get_status(); // returns e.g. 'completed'
					
					$order = wc_get_order( $order_id );
	
					$actions            = array_filter(
						wc_get_account_orders_actions( $order ),
						function ( $key ) {
							return 'view' !== $key;
						},
						ARRAY_FILTER_USE_KEY
					);
                    ?>
                    <?php foreach ($items as $item) : 
                        $product_name = $item->get_name();
                        ?>
                        <tr>
                            <td>#<?php echo esc_html($order_id); ?></td>   
                            <td><?php echo esc_html($date); ?></td>
                            <td><?php echo esc_html($product_name); ?></td>
                            <td><?php echo wc_price($total); ?></td>
							<!--<td><?php //echo ucfirst($status); ?></td>-->
							<td> 
							    <?php if ( ! empty( $actions ) ) : 
									// new button code
									$wp_button_class = wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '';
									foreach ( $actions as $key => $action ) { 
										if ( empty( $action['aria-label'] ) ) {
											// Generate the aria-label based on the action name.
											$action_aria_label = sprintf( __( '%1$s order number %2$s', 'woocommerce' ), $action['name'], $order->get_order_number() );
										} else {
											$action_aria_label = $action['aria-label'];
										}
										echo '<a href="' . esc_url( $action['url'] ) . '" class="woocommerce-button' . esc_attr( $wp_button_class ) . ' button ' . sanitize_html_class( $key ) . ' order-actions-button " aria-label="' . esc_attr( $action_aria_label ) . '"><img src="'.get_stylesheet_directory_uri().'/images/DownloadInvoice.png" alt="invoice-download" /> </a>';
										unset( $action_aria_label );
									} 
								endif; 
								?>
							   
							</td>  
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
		</div>
    </div>

    <?php
    return ob_get_clean();
}


// Job Expiry Date Script (load at the very end of footer)
add_action( 'wp_footer', 'custom_add_menu_as_selected', 999 ); // Priority 9999 ensures it loads last
function custom_add_menu_as_selected() {
    if( is_page( 'change-password' ) ) { ?>
		<script>
		jQuery(function($) {
			$('li.menu-item-3357').addClass('current-menu-item');
		});
		</script>
    <?php
	}
}


// Edit Job form
/**
 * Shortcode: [full_edit_job job_id="123"]
 * Full Edit Job Form 
 */
add_shortcode('full_edit_job', 'aasw_full_edit_job_shortcode');
function aasw_full_edit_job_shortcode($atts) {
    if ( ! is_user_logged_in() ) {
        return '<p class="notice error">You must be logged in to edit a job.</p>';
    }

    $atts = shortcode_atts(['job_id' => 0], $atts, 'full_edit_job');
    $job_id = absint( $atts['job_id'] ?: ( $_GET['job_id'] ?? 0 ) );

    if ( ! $job_id ) return '<p class="notice error">No job ID specified.</p>';

    $job = get_post($job_id);
    if ( ! $job || $job->post_type !== 'job_listing' ) {
        return '<p class="notice error">Invalid job listing.</p>';
    }

    // Permission check
    if ( (int) $job->post_author !== get_current_user_id() && ! current_user_can('edit_post', $job_id) ) {
        return '<p class="notice error">You do not have permission to edit this job.</p>';
    }

    // --- Taxonomy detection helper: tries common WPJM taxonomy slugs and returns existing one ---
    $taxonomy_candidates = [
        'job_category' => ['job_listing_category','job_category','job-category','job_listing_categories'],
        'job_type'     => ['job_listing_type','job_type','job-type','job_listing_types'],
        'job_location' => ['job_location_category','job_location','job-region','job_listing_region','region','job_listing_region'],
    ];
    function aasw_detect_taxonomy($field_key, $taxonomy_candidates) {
        foreach ( $taxonomy_candidates[$field_key] as $candidate ) {
            if ( taxonomy_exists($candidate) ) return $candidate;
        }
        return false;
    }

    $taxonomy_map = [];
    $taxonomy_map['job_category'] = aasw_detect_taxonomy('job_category', $taxonomy_candidates);
    $taxonomy_map['job_type']     = aasw_detect_taxonomy('job_type', $taxonomy_candidates);
    $taxonomy_map['job_location'] = aasw_detect_taxonomy('job_location', $taxonomy_candidates);

    // --- Load existing values ---
    // For taxonomy multi-selects we get term IDs
    $existing_job_location = ( $taxonomy_map['job_location'] ) ? wp_get_object_terms($job_id, $taxonomy_map['job_location'], ['fields'=>'ids']) : (array) get_post_meta($job_id, 'job_location', true);
    $existing_job_type     = ( $taxonomy_map['job_type'] ) ? wp_get_object_terms($job_id, $taxonomy_map['job_type'], ['fields'=>'ids']) : (array) get_post_meta($job_id, 'job_type', true);
    // job_category field originally uses name job_category[] (map to taxonomy)
    $existing_job_category = ( $taxonomy_map['job_category'] ) ? wp_get_object_terms($job_id, $taxonomy_map['job_category'], ['fields'=>'ids']) : (array) get_post_meta($job_id, 'job_category', true);

	$job_expires = get_post_meta($job_id, '__job_expires', true);
	if (empty($job_expires)) {
		$job_expires = get_post_meta($job_id, 'job_expiry_date', true);
	}

    $existing = [
        'job_title' => $job->post_title,
        'job_location_custom' => get_post_meta($job_id, '_job_location_custom', true),
        'job_description' => get_post_field('post_content', $job_id),
        'experience_level' => get_post_meta($job_id, '_experience_level', true),
        '__job_expires' => $job_expires,
        'job_salary' => get_post_meta($job_id, '_job_salary', true),
        'job_salary_currency' => get_post_meta($job_id, '_job_salary_currency', true),
        //'job_salary_unit' => get_post_meta($job_id, '_job_salary_unit', true),
        'how_to_apply' => get_post_meta($job_id, 'how_to_apply', true),
        'apply_link' => get_post_meta($job_id, '_apply_link', true),
        'job_attachments' => get_post_meta($job_id, '_job_attachments', true), // expected as array of URLs
        'show_company_info' => get_post_meta($job_id, '_show_company_info', true),
        'company_profile' => get_post_meta($job_id, '_company_profile', true),
        '_company_website' => get_post_meta($job_id, '__company_website', true),
        '_company_location' => get_post_meta($job_id, '__company_location', true),
        '_company_contact'  => get_post_meta($job_id, '__company_contact', true),
        '_company_email'    => get_post_meta($job_id, '__company_email', true),
        '_company_description' => get_post_meta($job_id, '__company_description', true),
    ];
	
	$editor_id = 'job_description_editor';
	$job_description = get_post_field('post_content', $job_id);
	
	$editor_id_apply = 'howto_apply_editor'; 
	//get_post_meta($job_id, 'how_to_apply', true),
	

    // Ensure arrays
    $existing['job_attachments'] = (array) $existing['job_attachments'];
    if ( empty($existing_job_location) ) $existing_job_location = [];
    if ( empty($existing_job_type) ) $existing_job_type = [];
    if ( empty($existing_job_category) ) $existing_job_category = [];

    // If attachments saved differently, try alternative meta keys:
    if ( empty($existing['job_attachments']) ) {
        $alt = get_post_meta($job_id, 'job_attachments', true);
        if ( $alt ) $existing['job_attachments'] = (array)$alt;
    }

    // Handle submission
    $messages = '';
    if ( isset($_POST['full_edit_job_nonce']) && wp_verify_nonce($_POST['full_edit_job_nonce'], 'full_edit_job_action') ) {

        $errors = [];
		
		//print_r($_POST);

        // sanitize inputs
        $job_title = sanitize_text_field($_POST['job_title'] ?? '');
        // tax arrays come as array of term IDs or comma-separated --- normalize to array of ints
        $job_location_terms = isset($_POST['job_location']) ? (array) $_POST['job_location'] : [];
        $job_location_terms = array_map('absint', $job_location_terms);

        $job_location_custom = sanitize_text_field($_POST['job_location_custom'] ?? '');
        $job_type_terms = isset($_POST['job_type']) ? (array) $_POST['job_type'] : [];
        $job_type_terms = array_map('absint', $job_type_terms);

        $job_category_terms = isset($_POST['job_category']) ? (array) $_POST['job_category'] : [];
        $job_category_terms = array_map('absint', $job_category_terms);

        $experience_level = sanitize_text_field($_POST['experience_level'] ?? '');
        $job_description = wp_kses_post($_POST['job_description'] ?? '');
        $job_expires = sanitize_text_field($_POST['_job_expires'] ?? '');
        $job_salary = sanitize_text_field($_POST['job_salary'] ?? '');
        $job_salary_currency = sanitize_text_field($_POST['job_salary_currency'] ?? '');
        //$job_salary_unit = sanitize_text_field($_POST['job_salary_unit'] ?? '');
        $how_to_apply = wp_kses_post($_POST['how_to_apply'] ?? '');
        $apply_link = esc_url_raw($_POST['apply_link'] ?? '');

        // attachments: keep current ones (array of URLs) from hidden inputs named current_job_attachments[]
        $current_job_attachments = isset($_POST['current_job_attachments']) ? (array) $_POST['current_job_attachments'] : [];
        $current_job_attachments = array_values(array_filter(array_map('esc_url_raw', $current_job_attachments)));

        $show_company_info = sanitize_text_field($_POST['show_company_info'] ?? '');
        $company_profile = sanitize_text_field($_POST['company_profile'] ?? '');
        $_company_website = sanitize_text_field($_POST['_company_website'] ?? '');
        $_company_location = sanitize_text_field($_POST['_company_location'] ?? '');
        $_company_contact = sanitize_text_field($_POST['_company_contact'] ?? '');
        $_company_email = sanitize_text_field($_POST['_company_email'] ?? '');
        $_company_description = wp_kses_post($_POST['_company_description'] ?? ''); 

        $agreement_checked = isset($_POST['agreement-checkbox']) ? true : false;

        // Required field validation (based on original markup that had "required-field")
        if ( empty($job_title) ) $errors[] = 'Job Title is required.';
        if ( empty($job_location_terms) && empty($job_location_custom) ) $errors[] = 'Please select at least one Job Location or enter Work Location.';
        if ( empty($job_type_terms) ) $errors[] = 'Please select at least one Type of Employment.';
        if ( empty($job_category_terms) ) $errors[] = 'Please select at least one Job Category.';
        if ( empty($experience_level) ) $errors[] = 'Experience Level is required.';
        if ( empty($job_description) ) $errors[] = 'Job Description is required.';
        if ( empty($show_company_info) ) $errors[] = 'Please choose whether to display company details.';
        if ( ! $agreement_checked ) $errors[] = 'You must accept the Terms and Conditions.';

        // Handle new file uploads for job_attachments[] (multiple)
        //$uploaded_urls = [];
        if ( ! empty($_FILES['job_attachments']) && ! empty($_FILES['job_attachments']['name']) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			
			$files = [];
			foreach ($_FILES['job_attachments']['name'] as $key => $value) {
				if ( ! empty($_FILES['job_attachments']['name'][$key]) ) {
					$files[] = [
						'name'     => $_FILES['job_attachments']['name'][$key],
						'type'     => $_FILES['job_attachments']['type'][$key],
						'tmp_name' => $_FILES['job_attachments']['tmp_name'][$key],
						'error'    => $_FILES['job_attachments']['error'][$key],
						'size'     => $_FILES['job_attachments']['size'][$key],
					];
				}
			}

			$uploaded_urls = [];
			$errors = [];
			
			foreach ( $files as $file ) {
				$overrides = [ 'test_form' => false ];
				$move = wp_handle_upload( $file, $overrides );

				if ( isset( $move['error'] ) ) {
					$errors[] = 'Attachment upload error: ' . esc_html( $move['error'] );
				} else {
					$uploaded_urls[] = esc_url_raw( $move['url'] );
				}
			}
		}

		
		

        // If no errors, save updates
        if ( empty($errors) ) {
            // Update post basics
            wp_update_post([
                'ID' => $job_id,
                'post_title' => $job_title,
                'post_content' => $job_description,
            ]);

            // Meta updates
            update_post_meta($job_id, '_job_location_custom', $job_location_custom); // show on frontend
			update_post_meta($job_id, '_job_location', $job_location_custom); // For admin side save
			
            update_post_meta($job_id, '_experience_level', $experience_level);
			
            update_post_meta($job_id, '_job_expires', $job_expires);
			update_post_meta($job_id, '__job_expires', $job_expires);
			update_post_meta($job_id, 'job_expiry_date', $job_expires);
			
            update_post_meta($job_id, '_job_salary', $job_salary);
            update_post_meta($job_id, '_job_salary_currency', $job_salary_currency);
            //update_post_meta($job_id, '_job_salary_unit', $job_salary_unit);
            update_post_meta($job_id, 'how_to_apply', $how_to_apply);
			update_post_meta($job_id, '_how_to_apply', $how_to_apply);
            update_post_meta($job_id, '_apply_link', $apply_link);

            // Company info
            update_post_meta($job_id, '_show_company_info', $show_company_info);
            update_post_meta($job_id, '_company_profile', $company_profile);
			update_post_meta($job_id, '_company_name', $company_profile);
            update_post_meta($job_id, '__company_website', $_company_website);
            update_post_meta($job_id, '__company_location', $_company_location);
            update_post_meta($job_id, '__company_contact', $_company_contact);
            update_post_meta($job_id, '__company_email', $_company_email);
            update_post_meta($job_id, '__company_description', $_company_description); 

            // Attachments: merge current kept + newly uploaded
            $final_attachments = array_merge( $current_job_attachments, $uploaded_urls );
			
            // store as post meta array under _job_attachments
            //update_post_meta($job_id, '_job_attachments', $final_attachments);
			update_post_meta($job_id, '_job_attachments', wp_json_encode( $final_attachments ));
			

            // Taxonomy updates: if detected taxonomy exists, set terms; otherwise fallback to saving meta
            if ( $taxonomy_map['job_location'] ) {
                // send term IDs (if empty array it clears)
                wp_set_object_terms($job_id, $job_location_terms, $taxonomy_map['job_location'], false);
            } else {
                update_post_meta($job_id, 'job_location', $job_location_terms);
            }

            if ( $taxonomy_map['job_type'] ) {
                wp_set_object_terms($job_id, $job_type_terms, $taxonomy_map['job_type'], false);
            } else {
                update_post_meta($job_id, 'job_type', $job_type_terms);
            }

            if ( $taxonomy_map['job_category'] ) {
                wp_set_object_terms($job_id, $job_category_terms, $taxonomy_map['job_category'], false);
            } else {
                update_post_meta($job_id, 'job_category', $job_category_terms);
            }

            //$messages = '<div class="notice success">Job updated successfully.</div>';
			$messages = 'Job updated successfully';

            // refresh existing arrays for display
            $existing_job_location = $job_location_terms;
            $existing_job_type = $job_type_terms;
            $existing_job_category = $job_category_terms;
            $existing['job_attachments'] = $final_attachments;
            $existing['job_title'] = $job_title;
            $existing['job_description'] = $job_description;
            $existing['job_location_custom'] = $job_location_custom;
            $existing['experience_level'] = $experience_level;
            $existing['_job_expires'] = $job_expires;
			$existing['__job_expires'] = $job_expires;
			$existing['job_expiry_date'] = $job_expires;
            $existing['job_salary'] = $job_salary;
            $existing['job_salary_currency'] = $job_salary_currency;
            //$existing['job_salary_unit'] = $job_salary_unit;
            $existing['how_to_apply'] = $how_to_apply;
            $existing['apply_link'] = $apply_link;
            $existing['show_company_info'] = $show_company_info;
            $existing['company_profile'] = $company_profile;
            $existing['_company_website'] = $_company_website;
            $existing['_company_location'] = $_company_location;
            $existing['_company_contact'] = $_company_contact;
            $existing['_company_email'] = $_company_email;
            $existing['_company_description'] = $_company_description;
        } else {
			
			// Attachments: Merge current hidden inputs ($current_job_attachments) 
			// with any files successfully uploaded ($uploaded_urls) before the error
			$reloaded_attachments = array_merge( 
				$current_job_attachments, 
				$uploaded_urls ?? [] // Use $uploaded_urls (defined above)
			);
			
			// Load the merged list back into the existing array for display
			$existing['job_attachments'] = $reloaded_attachments;
			
			// Also need to reload other fields from $_POST so they don't reset
			$existing_job_location = $job_location_terms;
			$existing_job_type = $job_type_terms;
			$existing_job_category = $job_category_terms;
			$existing['job_title'] = $job_title;
			$existing['job_description'] = $job_description;
			$existing['job_location_custom'] = $job_location_custom;
			// ... reload all other fields from $_POST into $existing as done in the success path ...
			$existing['experience_level'] = $experience_level;
			$existing['_job_expires'] = $job_expires;
			$existing['__job_expires'] = $job_expires;
			$existing['job_expiry_date'] = $job_expires;
			$existing['job_salary'] = $job_salary;
			$existing['job_salary_currency'] = $job_salary_currency;
			//$existing['job_salary_unit'] = $job_salary_unit;
			$existing['how_to_apply'] = $how_to_apply;
			$existing['apply_link'] = $apply_link;
			$existing['show_company_info'] = $show_company_info;
			$existing['company_profile'] = $company_profile;
			$existing['_company_website'] = $_company_website;
			$existing['_company_location'] = $_company_location;
			$existing['_company_contact'] = $_company_contact;
			$existing['_company_email'] = $_company_email;
			$existing['_company_description'] = $_company_description;
			
			// --- END FIX ---
            $messages = '<div class="notice error"><strong>Please fix the following:</strong><ul><li>' . implode('</li><li>', array_map('esc_html', $errors)) . '</li></ul></div>';
        }
    }

    // Helper: format attachments list (existing)
    ob_start();
    //echo $messages;
    ?>

    <form action="" method="post" id="full-edit-job-form" class="job-manager-form <?php echo $job_id; ?>" enctype="multipart/form-data" style="max-width:100%;">
        <?php wp_nonce_field('full_edit_job_action','full_edit_job_nonce'); ?>  

        <!-- Your account info (simple) -->
        <fieldset class="fieldset-logged_in">
            <label>Your account</label>
            <div class="field account-sign-in">
                You are currently signed in as <strong><?php echo esc_html( wp_get_current_user()->user_email ); ?></strong>.
                <!--<a class="button" href="<?php echo esc_url( wp_logout_url( get_permalink() ) ); ?>">Sign out</a>-->
            </div>
        </fieldset>

        <!-- Job Title -->
        <fieldset class="fieldset-job_title fieldset-type-text">
            <label for="job_title">Job Title / Position <br><small>(Job title describes a single position)</small></label>
            <div class="field required-field">
                <input type="text" class="input-text" name="job_title" id="job_title" placeholder="Job Title / Position" value="<?php echo esc_attr($existing['job_title']); ?>" maxlength="" required>
                <small class="description">(minimum 10 characters)</small>
            </div>
        </fieldset>

        <!-- Job Location (term multi) -->
        <fieldset class="fieldset-job_location fieldset-type-term-multiselect">
            <label for="job_location">Job Location <br><small>(You can select multiple job location)</small></label>
            <div class="field required-field">
                <?php
                // Render a multi-select of terms if taxonomy exists; else a multi-select numeric/hidden
                if ( $taxonomy_map['job_location'] ) {
                    $terms = get_terms(['taxonomy' => $taxonomy_map['job_location'], 'hide_empty' => false]);
                    echo '<select name="job_location[]" id="job_location" class="select2 required" required multiple data-placeholder="    Select Locations" style="width:100%;">';
                    foreach ($terms as $t) {
                        echo '<option value="'.esc_attr($t->term_id).'" '.(in_array($t->term_id,$existing_job_location) ? 'selected' : '').'>'.esc_html($t->name).'</option>';
                    }
                    echo '</select>';
                } else {
                    // fallback: simple multi text input (comma separated)
                    echo '<input type="text" name="job_location[]" value="'.esc_attr( implode(',', $existing_job_location) ).'">';
                }
                ?>
            </div>
        </fieldset>

        <!-- Work Location custom -->
        <fieldset class="fieldset-job_location_custom fieldset-type-text">
            <label for="job_location_custom">Work Location <small>(optional)</small></label>
            <div class="field ">
                <input type="text" class="input-text" name="job_location_custom" id="job_location_custom" placeholder="Enter work location" value="<?php echo esc_attr($existing['job_location_custom']); ?>">
                <div class="custom-error" style="color: red; margin-top: 5px; display: none;"></div>
                <small class="description">(Enter your exact work location.)</small>
            </div>
        </fieldset>

        <!-- Job Type (term multi) -->
        <fieldset class="fieldset-job_type fieldset-type-term-multiselect">
            <label for="job_type">Type of Employment <br><small>(You can select multiple type of employment)</small></label>
            <div class="field required-field">
                <?php
                if ( $taxonomy_map['job_type'] ) {
                    $terms = get_terms(['taxonomy' => $taxonomy_map['job_type'], 'hide_empty' => false]);
                    echo '<select name="job_type[]" id="job_type" class="select2" multiple style="width:100%;">';
                    foreach ($terms as $t) {
                        echo '<option value="'.esc_attr($t->term_id).'" '.(in_array($t->term_id,$existing_job_type) ? 'selected' : '').'>'.esc_html($t->name).'</option>';
                    }
                    echo '</select>';
                } else {
                    echo '<input type="text" name="job_type[]" value="'.esc_attr( implode(',', $existing_job_type) ).'">';
                }
                ?>
            </div>
        </fieldset>

        <!-- Job Category (term multi) -->
        <fieldset class="fieldset-job_category fieldset-type-term-multiselect">
            <label for="job_category">Job Category <br><small>(You can select multiple job category)</small></label>
            <div class="field required-field">
                <?php
                if ( $taxonomy_map['job_category'] ) {
                    $terms = get_terms(['taxonomy' => $taxonomy_map['job_category'], 'hide_empty' => false]);
                    echo '<select name="job_category[]" id="job_category" class="select2" multiple style="width:100%;">';
                    foreach ($terms as $t) {
                        echo '<option value="'.esc_attr($t->term_id).'" '.(in_array($t->term_id,$existing_job_category) ? 'selected' : '').'>'.esc_html($t->name).'</option>';
                    }
                    echo '</select>';
                } else {
                    echo '<input type="text" name="job_category[]" value="'.esc_attr( implode(',', $existing_job_category) ).'">';
                }
                ?>
            </div>
        </fieldset>

        <!-- Experience Level -->
        <fieldset class="fieldset-experience_level fieldset-type-select">
            <label for="experience_level">Experience Level</label>
            <div class="field required-field">
                <select name="experience_level" id="experience_level" class="select2" style="width:100%;" required>
                    <option value="">Select Level</option>
                    <option value="Beginner" <?php selected($existing['experience_level'],'Beginner'); ?>>Beginner</option>
                    <option value="Intermediate" <?php selected($existing['experience_level'],'Intermediate'); ?>>Intermediate</option>
                    <option value="Experienced" <?php selected($existing['experience_level'],'Experienced'); ?>>Experienced</option>
                </select>
            </div>
        </fieldset>

        <!-- Job Description (WP editor area) -->
        <fieldset class="fieldset-job_description fieldset-type-wp-editor">
            <label for="job_description">Job Description <br><small>(Describe the job position)</small></label>
            <div class="field required-field">
                <?PHP
				wp_editor(
					$job_description, // content
					$editor_id,       // editor ID
					array(
						'textarea_name' => 'job_description', // name attribute for form submission
						'media_buttons' => false,             // show/hide media upload buttons
						'textarea_rows' => 10,
						'tinymce'       => array(
							'toolbar1' => 'bold,italic,underline,bullist,numlist,link,unlink,undo,redo',
							'toolbar2' => '',
							'menubar'  => false,
							'statusbar'=> false,
							'wp_autoresize_on' => true,
						),              // enable TinyMCE
						'quicktags'     => true               // enable HTML editor tab
					)
				);
				?>
            </div>
        </fieldset>

        <!-- Job Expiry -->
        <fieldset class="fieldset-_job_expires fieldset-type-date">
            <label for="_job_expires">Job Expiry Date <small>(optional)</small></label>
            <div class="field ">
                <input type="text" class="input-date datepicker" name="_job_expires" id="_job_expires" placeholder="YYYY-MM-DD" value="<?php echo esc_attr($existing['__job_expires']); ?>">
                <small class="description">Select the date this job listing should expire</small>
            </div>
        </fieldset>  

        <!-- Salary -->
        <fieldset class="fieldset-job_salary fieldset-type-text">
            <label for="job_salary">Salary <small>(optional)</small></label>
            <div class="field ">
                <!--<input type="text" class="input-text" name="job_salary" id="job_salary" placeholder="20000 - 30000" value="<?php //echo esc_attr($existing['job_salary']); ?>">-->
				
				<select name="job_salary" id="job_salary" class="select2" aria-hidden="true">
					<option value="">-Select Salary-</option>
					<option value="10,000-20,000" <?php selected($existing['job_salary'],'10,000-20,000'); ?> >10,000 – 20,000</option>
					<option value="20,000-30,000" <?php selected($existing['job_salary'],'20,000-30,000'); ?> >20,000 – 30,000</option>
					<option value="30,000-40,000" <?php selected($existing['job_salary'],'30,000-40,000'); ?> >30,000 – 40,000</option>
					<option value="40,000-50,000" <?php selected($existing['job_salary'],'40,000-50,000'); ?> >40,000 – 50,000</option>
					<option value="50,000-60,000" <?php selected($existing['job_salary'],'50,000-60,000'); ?> >50,000 – 60,000</option>
					<option value="60,000-70,000" <?php selected($existing['job_salary'],'60,000-70,000'); ?> >60,000 – 70,000</option>
					<option value="70,000-80,000" <?php selected($existing['job_salary'],'70,000-80,000'); ?> >70,000 – 80,000</option>
					<option value="80,000-90,000" <?php selected($existing['job_salary'],'80,000-90,000'); ?> >80,000 – 90,000</option>
					<option value="90,000-100,000" <?php selected($existing['job_salary'],'90,000-100,000'); ?> >90,000 – 100,000</option>
					<option value="above-100,000" <?php selected($existing['job_salary'],'above-100,000'); ?> >Above 100,000</option>
			    </select>
				<small class="description">Select the annual salary range for this job listing</small>
            </div> 
        </fieldset>

        <!-- Salary Currency -->
        <fieldset class="fieldset-job_salary_currency fieldset-type-select">
            <label for="job_salary_currency">Salary Currency <small>(optional)</small></label>
            <div class="field ">
                <select name="job_salary_currency" id="job_salary_currency" class="select2" style="width:100%;">
                    <option value="">-Select Currency-</option>
                    <option value="USD" <?php selected($existing['job_salary_currency'],'USD'); ?>>USD - US Dollar</option>
                    <option value="EUR" <?php selected($existing['job_salary_currency'],'EUR'); ?>>EUR - Euro</option>
                    <option value="GBP" <?php selected($existing['job_salary_currency'],'GBP'); ?>>GBP - British Pound</option>
                    <option value="AUD" <?php selected($existing['job_salary_currency'],'AUD'); ?>>AUD - Australian Dollar</option>
                    <option value="CAD" <?php selected($existing['job_salary_currency'],'CAD'); ?>>CAD - Canadian Dollar</option>
                </select>
            </div>
        </fieldset>

        <!-- Salary Unit 
        <fieldset class="fieldset-job_salary_unit fieldset-type-select">
            <label for="job_salary_unit">Salary Unit <small>(optional)</small></label>
            <div class="field ">
                <select name="job_salary_unit" id="job_salary_unit" class="select2" style="width:100%;">
                    <option value="">--</option>
                    <option value="YEAR" <?php //selected($existing['job_salary_unit'],'YEAR'); ?>>Year</option>
                    <option value="MONTH" <?php //selected($existing['job_salary_unit'],'MONTH'); ?>>Month</option>
                    <option value="WEEK" <?php //selected($existing['job_salary_unit'],'WEEK'); ?>>Week</option>
                    <option value="DAY" <?php //selected($existing['job_salary_unit'],'DAY'); ?>>Day</option>
                    <option value="HOUR" <?php //selected($existing['job_salary_unit'],'HOUR'); ?>>Hour</option>
                </select>
                <small class="description">This field is optional. Leave it empty to use the default salary unit, if one is defined.</small>
            </div>
        </fieldset>-->

        <!-- How to apply (editor) -->
        <fieldset class="fieldset-how_to_apply fieldset-type-wp-editor">
            <label for="how_to_apply">How to Apply Direction <small>(optional)</small></label>
            <div class="field ">
				<?PHP
				$apply_content = $existing['how_to_apply'];
				wp_editor(
					$apply_content, // content
					$editor_id_apply,       // editor ID
					array(
						'textarea_name' => 'how_to_apply', // name attribute for form submission
						'media_buttons' => false,             // show/hide media upload buttons
						'textarea_rows' => 10,
						'tinymce'       => array(
							'toolbar1' => 'bold,italic,underline,bullist,numlist,link,unlink,undo,redo',
							'toolbar2' => '',
							'menubar'  => false,
							'statusbar'=> false,
							'wp_autoresize_on' => true,
						), // enable TinyMCE
						'quicktags'     => true               // enable HTML editor tab
					)
				);
				?>
                <small class="description">Provide clear instructions for applicants on how to apply for this job.</small>
            </div>
        </fieldset>

        <!-- Apply Link -->
        <fieldset class="fieldset-apply_link fieldset-type-text">
            <label for="apply_link">Apply Link <small>(optional)</small></label>
            <div class="field ">
                <input type="url" class="input-text" name="apply_link" id="apply_link" placeholder="www.example.com/application" value="<?php echo esc_attr($existing['apply_link']); ?>">
            </div> 
        </fieldset>  

        <!-- Job Attachments (multiple) -->
        <fieldset class="fieldset-job_attachments fieldset-type-file">
            <label for="job_attachments">Upload Attachment <small>(optional)</small></label>
            <div class="field ">
                <div class="job-manager-uploaded-files">
                    <?php
					if ( ! empty( $existing['job_attachments'] ) ) {

						$attachments = [];

						if ( isset( $existing['job_attachments'][0] ) && is_string( $existing['job_attachments'][0] ) ) {
							$decoded = json_decode( $existing['job_attachments'][0], true );
							if ( is_array( $decoded ) ) {
								$attachments = $decoded;
							}
						} elseif ( is_array( $existing['job_attachments'] ) ) {
							$attachments = $existing['job_attachments'];
						}

						if ( ! empty( $attachments ) ) {
							echo '<div class="job-manager-uploaded-file-list">'; // added wrapper
							foreach ( $attachments as $file_url ) {
								$basename = wp_basename( $file_url );
								echo '<div class="job-manager-uploaded-file" style="margin-bottom:6px;">';
								echo '<span class="job-manager-uploaded-file-name"><code>' . esc_html( $basename ) . '</code> 
										<a class="job-manager-remove-uploaded-file" href="#" data-url="' . esc_attr( $file_url ) . '">[remove]</a> | <a target="_blank" class="viewfile" href="' . esc_attr( $file_url ) . '">[view]</a>
									  </span>';
								echo '<input type="hidden" name="current_job_attachments[]" value="' . esc_attr( $file_url ) . '">';
								echo '</div>';
							}
							echo '</div>';
						}
					}
                    ?>
                </div>

                <input type="file" class="input-text" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" multiple name="job_attachments[]" id="job_attachments" placeholder="">
                <small class="description">Maximum file size: 50 MB.</small>
            </div>
        </fieldset>

        <!-- Show company info -->
        <fieldset class="fieldset-show_company_info fieldset-type-select">
            <label for="show_company_info">Do you want to display your company details on the job post?</label>
            <div class="field required-field">
                <select name="show_company_info" id="show_company_info" class="select2" style="width:100%;" required>
                    <option value="">-Select-</option>
                    <option value="yes" <?php selected($existing['show_company_info'],'yes'); ?>>Yes</option>
                    <option value="no" <?php selected($existing['show_company_info'],'no'); ?>>No</option>
                </select>
                <small class="description">(If yes, following details be added to the job post)</small>
            </div>
        </fieldset>

        <!-- Company fields -->
        <fieldset class="fieldset-company_profile fieldset-type-text">
            <label for="company_profile">Company Profile <small>(optional)</small></label>
            <div class="field ">
                <input type="text" class="input-text" name="company_profile" id="company_profile" value="<?php echo esc_attr($existing['company_profile']); ?>">
            </div>
        </fieldset>

        <fieldset class="fieldset-_company_website fieldset-type-text">
            <label for="_company_website">Company Website <small>(optional)</small></label>
            <div class="field ">
                <input type="url" class="input-text" name="_company_website" id="_company_website" value="<?php echo esc_attr($existing['_company_website']); ?>">
            </div>
        </fieldset>

        <fieldset class="fieldset-_company_location fieldset-type-text">
            <label for="_company_location">Company Location <small>(optional)</small></label>
            <div class="field ">
                <input type="text" class="input-text" name="_company_location" id="_company_location" value="<?php echo esc_attr($existing['_company_location']); ?>">
            </div>
        </fieldset>

        <fieldset class="fieldset-_company_contact fieldset-type-text">
            <label for="_company_contact">Company Contact <small>(optional)</small></label>
            <div class="field ">
                <input type="text" class="input-text" name="_company_contact" id="_company_contact" value="<?php echo esc_attr($existing['_company_contact']); ?>">
            </div>
        </fieldset>

        <fieldset class="fieldset-_company_email fieldset-type-text">
            <label for="_company_email">Company Email <small>(optional)</small></label>
            <div class="field ">
                <input type="email" class="input-text" name="_company_email" id="_company_email" value="<?php echo esc_attr($existing['_company_email']); ?>">
            </div>
        </fieldset>

        <fieldset class="fieldset-_company_description fieldset-type-textarea">
            <label for="_company_description">Company Description <small>(optional)</small></label>
            <div class="field ">
                <textarea name="_company_description" id="_company_description" rows="3" style="width:100%"><?php echo esc_textarea($existing['_company_description']); ?></textarea>
            </div>
        </fieldset>

        <!-- Agreement -->
        <fieldset class="fieldset-agreement-checkbox edit-form-agreement ">
            <div class="full-line-checkbox-field required-field">
                <input type="checkbox" class="input-checkbox" name="agreement-checkbox" id="agreement-checkbox" value="1" required <?php //checked(true, true); ?>>
                <label for="agreement-checkbox">I accept the <a href="<?php echo esc_url( get_permalink( 259 ?: 0 ) ); ?>" target="_blank">Terms and Conditions</a>.</label>
            </div>
        </fieldset>

        <p>
			<input type="hidden" name="job_expiry_date" id="job_expiry_date" value="" />
            <input type="hidden" name="job_id" value="<?php echo esc_attr($job_id); ?>">
            <input type="submit" name="submit_job_update" class="button" value="Update Job">
            <span class="spinner" style="display:none;"></span>
        </p>

    </form>
	
	<?php
		$current_user_id = get_current_user_id();
		$packages = wc_paid_listings_get_user_packages( $current_user_id );

		if ( empty( $packages ) ) {
			$expiry_date_formatted = '';
			$is_expired = false;
		} else {
			$i = 0;
			foreach ( $packages as $package ) {
				// Get the WooCommerce order related to this package
				$order = wc_get_order( $package->order_id );

				if ( $order && ! empty( $package->package_duration ) && $package->package_duration > 0 ) {
					$order_date = $order->get_date_created(); // WC_DateTime object

					// Expiry date
					$expiry_date = clone $order_date;
					$expiry_date->modify( '+' . $package->package_duration . ' days' );
					$expiry_date_formatted = $expiry_date->format('M d, Y');
					
				} else {
					$expiry_date_formatted = '';
				}
				$i++;
			}
		} 
	?>

    <style>
        .notice.error { background:#03658b; border-left:4px solid #c50a2d; padding:10px; margin-bottom:10px; }
        .job-manager-uploaded-file { margin-bottom:6px; }
    </style>

    <script>
	
	var maxDate2 = new Date('<?php echo esc_js( date('Y-m-d', strtotime($expiry_date_formatted)) ); ?>');
	console.log(maxDate2);
	
	jQuery(function($) {
	  $("#_job_expires").datepicker({
		dateFormat: "yy-mm-dd",  
		minDate: 0,           
		maxDate: maxDate2 ,
		onSelect: function(dateText, inst){
		  // dateText will be in yy-mm-dd as per dateFormat
		  $("#job_expiry_date").val(dateText);
		  console.log('dateText: '+dateText);
		}
	  }); 
	});  
	 
    (function($){
        // select2 init if available
        $(document).ready(function(){
			
			  
			
			// add google autocomplete api
			function initAutocomplete() {
				const input = document.getElementById("job_location_custom");
				if (!input) return;

				const autocomplete = new google.maps.places.Autocomplete(input, {
					componentRestrictions: { country: "au" }, // only for Australia
					fields: ["address_components", "geometry", "formatted_address"],
					types: ["address"],
				});

				autocomplete.addListener("place_changed", () => {
					const place = autocomplete.getPlace();
					if (!place.address_components) return;

					const components = {};
					place.address_components.forEach(c => {
						const type = c.types[0];
						components[type] = c.long_name;
					});

					// Fill individual fields
					/*document.getElementById("street").value = 
						(components.street_number ? components.street_number + " " : "") + (components.route || "");
					document.getElementById("city").value = components.locality || "";
					document.getElementById("state").value = components.administrative_area_level_1 || "";
					document.getElementById("postcode").value = components.postal_code || "";

					// Get Latitude & Longitude
					if (place.geometry && place.geometry.location) {
						document.getElementById("latitude").value = place.geometry.location.lat();
						document.getElementById("longitude").value = place.geometry.location.lng();
						console.log("Lat:", place.geometry.location.lat(), "Lng:", place.geometry.location.lng());
					} */
				});
			}

			// Ensure callback initializes autocomplete
			if (typeof google !== "undefined" && google.maps && google.maps.places) {
				initAutocomplete();
			} else {
				window.initAutocomplete = initAutocomplete;
			}
			
			// End of auto complete api
			
			
			// Validation for job title
			var interval = setInterval(function () {
				var $jobTitle = $('input[name="job_title"]');

				if ($jobTitle.length) {
					clearInterval(interval);

					// Function to set up validation for a field
					function setupValidation($field, maxLength) {
						// Create error container if missing
						if (!$field.next('.custom-error').length) {
							$('<div class="custom-error" style="color:red; margin-top:5px; display:none;"></div>')
								.insertAfter($field);
						}

						// Validation on input and blur
						$field.on('input blur', function () {
							var $this = $(this);
							var value = $this.val();
							var error = '';
							var hasError = false;

							// Allow only alphabets and spaces, minimum 10 characters
							var validPattern = /^[A-Za-z\s]{10,}$/;

							if (!validPattern.test(value)) {
								error = 'Only letters and spaces are allowed, and minimum 10 characters required.';
								// Remove invalid characters
								value = value.replace(/[^A-Za-z\s]/g, '');
								hasError = true;
							}

							// Enforce max length
							if (value.length > maxLength) {
								value = value.slice(0, maxLength);
								error = 'Maximum ' + maxLength + ' characters allowed.';
								hasError = true;
							}

							$this.val(value);

							if (hasError) {
								$this.css('border', '2px solid red');
								$this.next('.custom-error').text(error).show();
							} else {
								$this.css('border', '');
								$this.next('.custom-error').hide();
							}
						});
					}

					// Apply validation on job title
					setupValidation($jobTitle, 100);
				}
			}, 500);
			
			// job location validation 
			var interval2 = setInterval(function () {
				var $jobLocation = $('input[name="job_location_custom"]');

				if ($jobLocation.length) {
					clearInterval(interval2);

					// Generic validation function
					function setupValidation($field, maxLength) {
						// Create error container if missing
						if (!$field.next('.custom-error').length) {
							$('<div class="custom-error" style="color:red; margin-top:5px; display:none;"></div>')
								.insertAfter($field);
						}

						// Add input and blur event listeners
						$field.on('input blur', function () {
							var $this = $(this);
							var value = $this.val();
							var error = '';
							var hasError = false;

							// Allow letters, numbers, spaces, commas, dashes, and slashes
							var validPattern = /^[A-Za-z0-9\s,\/-]*$/;

							if (!validPattern.test(value)) {
								error = 'Only letters, numbers, spaces, commas, dashes, and slashes are allowed.';
								// Remove invalid characters
								value = value.replace(/[^A-Za-z0-9\s,\/-]/g, '');
								hasError = true;
							}

							// Limit max characters
							if (value.length > maxLength) {
								value = value.slice(0, maxLength);
								error = 'Maximum ' + maxLength + ' characters allowed.';
								hasError = true;
							}

							$this.val(value);

							// Show or hide error message
							var $errorDiv = $this.next('.custom-error');
							if (hasError) {
								$this.css('border', '2px solid red');
								$errorDiv.text(error).show();
							} else {
								$this.css('border', '');
								$errorDiv.hide();
							}
						});
					}

					// Apply validation (max length = 100)
					setupValidation($jobLocation, 100);
				}
			}, 500);
			
			<?php 
				// show popup when edit successfull
				if($messages == 'Job updated successfully'){ ?>  
				// Wait for Elementor Pro popup module (if available)
				var tryPopup = setInterval(function() {
					if (typeof elementorProFrontend !== "undefined" && elementorProFrontend.modules && elementorProFrontend.modules.popup) {
						clearInterval(tryPopup);
						elementorProFrontend.modules.popup.showPopup({ id: 3371 }); // Replace with your Popup ID
					}
				}, 1000);
				setTimeout(function() { // Safety timeout: stop trying after 10 seconds
					clearInterval(tryPopup);
				}, 10000);
			<?php } ?>
			
			$('li.menu-item-2484').addClass('current-menu-item');
			
			// Show company information
			$('#show_company_info').on('change', function(){
				console.log(this.value);
				var selectedVal = this.value;
				
				if (selectedVal=='yes') {
					//console.log("Company already selected: " + selectedVal);
					$('.fieldset-company_profile, .fieldset-_company_website, .fieldset-_company_location, .fieldset-_company_contact, .fieldset-_company_email, .fieldset-_company_description').show();
				}else{
					// Hide fields 
					$('.fieldset-company_profile, .fieldset-_company_website, .fieldset-_company_location, .fieldset-_company_contact, .fieldset-_company_email, .fieldset-_company_description').hide();
				}
			});
			
			
			// When a new file is uploaded by Job Manager
			$(document).on('change', 'input[type="file"][name="job_attachments[]"]', function(e){
				var input = this;
				var $container = $('.job-manager-uploaded-file-list'); // a wrapper we'll add below

				if ($container.length === 0) {
					// Create container if not present
					$container = $('<div class="job-manager-uploaded-file-list" style="margin-top:10px;"></div>');
					$(input).closest('.job-manager-upload-field, p').after($container);
				}

				// Loop through selected files
				$.each(input.files, function(i, file){
					var fakeUrl = file.name; // only show file name before upload
					var fileBlock = `
						<div class="job-manager-uploaded-file" style="margin-bottom:6px;">
							<span class="job-manager-uploaded-file-name"><code>${fakeUrl}</code> 
							<a class="job-manager-remove-uploaded-file" href="#" data-url="">[remove]</a></span>
						</div>`;
					$container.append(fileBlock);
				});
			});

			// Handle remove link (front-end)
			$(document).on('click', '.job-manager-remove-uploaded-file', function(e){
				e.preventDefault();
				$(this).closest('.job-manager-uploaded-file').remove();
			});
			
            if ($.fn.select2) {
                $('.select2').select2({ width:'100%' });
            }
            // remove uploaded file from DOM & remove hidden input before submit
            $(document).on('click', '.job-manager-remove-uploaded-file', function(e){
                e.preventDefault();
                var $wrap = $(this).closest('.job-manager-uploaded-file');
                // remove the hidden input so it's not submitted
                $wrap.find('input[type="hidden"]').remove();
                $wrap.remove();
            });
			
			if ($('#_job_expires').length) {
				$('#_job_expires').datepicker({
					dateFormat: 'yy-mm-dd', // format matches database value
					changeMonth: false,
					changeYear: false,
					minDate: 0 // prevent past dates (optional)
				});    
			}
			
			
		
        });
    })(jQuery);
    </script>

    <?php
    // helper: reformat PHP multiple files array into simpler array
    function reformat_files_array(&$file_post) {
        $files = [];
        $file_count = count($file_post['name']);
        $file_keys = array_keys($file_post);
        for ($i=0; $i<$file_count; $i++) {
            foreach ($file_keys as $key) {
                $files[$i][$key] = $file_post[$key][$i];
            }
        }
        return $files;
    }

    return ob_get_clean();
}



add_action('wp_enqueue_scripts', function() {
    // Ensure jQuery and jQuery UI Datepicker are loaded
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-datepicker');

    // Load a CSS theme for the datepicker
    wp_enqueue_style(
        'jquery-ui-css',
        'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css'
    );
});

