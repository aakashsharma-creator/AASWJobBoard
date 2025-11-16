<?php
/**
 * Added all the small custom functions
*/


function mytheme_enqueue_styles() {
    // Enqueue custom css
    wp_enqueue_style(
        'custom-theme-style', // Handle name
        get_stylesheet_directory_uri() . '/css/custom-theme.css', // File path
        array(), // Dependencies (leave empty if none)
        filemtime( get_stylesheet_directory() . '/css/custom-theme.css' ), // Version (uses file modified time to avoid caching issues)
        'all' // Media type
    );
}
add_action( 'wp_enqueue_scripts', 'mytheme_enqueue_styles' );   

add_role( 'rooms_for_hire', 'RoomsForHire', get_role( 'editor' )->capabilities );   

add_action( 'init', function() {
    $role = get_role( 'employer' );
    if ( $role ) {
        $role->add_cap( 'edit_job_listings' );
        $role->add_cap( 'edit_published_job_listings' );
    }
});

// Remove version query string from static resources
function remove_query_strings_from_static_resources($src) {
    if (strpos($src, '?ver='))
        $src = remove_query_arg('ver', $src);
    return $src;
}
add_filter('style_loader_src', 'remove_query_strings_from_static_resources', 9999);
add_filter('script_loader_src', 'remove_query_strings_from_static_resources', 9999);


/**
 * Get job listing types (taxonomy: job_listing_type).
 *
 * @return array
 */
function get_all_wpjob_types_array() {
    $terms = get_terms( array(
        'taxonomy'   => 'job_listing_type',
        'hide_empty' => false,
    ) );

    $result = array();

    if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
        foreach ( $terms as $term ) {
            $result[] = array(
                'term_id'   => $term->term_id,
                'term_name' => $term->name,
            );
        }
    }
    return $result;
}

function get_all_wpjob_category(){
    $terms = get_terms( array(
        'taxonomy'   => 'job_listing_category',
        'hide_empty' => false,
    ) );

    $result = array();

    if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
        foreach ( $terms as $term ) {
            $result[] = array(
                'term_id'   => $term->term_id,
                'term_name' => $term->name,
            );
        }
    }
    return $result;
}

function get_all_wpjob_locations(){
    $terms = get_terms( array(
        'taxonomy'   => 'job_location_category',
        'hide_empty' => false,
    ) );

    $result = array();

    if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
        foreach ( $terms as $term ) {
            $result[] = array(
                'term_id'   => $term->term_id,
                'term_name' => $term->name,
            );
        }
    }
    return $result;
}


/****************************************
* Include other custom functions file **
*****************************************/

include('post-job-custom-functions.php');    // all custom work done for post a job form functionality 
include('map_joblisting.php');

//add_filter( 'wc_stripe_manual_api_keys_allowed', '__return_true' );

// Add Email Address column to Job Alert admin list
add_filter( 'manage_job_alert_posts_columns', function($columns) {
    // Insert the new column after the title column
    $new_columns = [];
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'title') {
            $new_columns['alert_email'] = 'Email Address';
        }
    }
    return $new_columns;
});

// Populate the Email Address column
add_action( 'manage_job_alert_posts_custom_column', function($column, $post_id) {
    if ( $column === 'alert_email' ) {
        $email = get_post_meta( $post_id, 'alert_email', true );
        echo esc_html( $email );
    }
}, 10, 2 );

// Make the Email Address column sortable
add_filter( 'manage_edit-job_alert_sortable_columns', function($columns) {
    $columns['alert_email'] = 'alert_email';
    return $columns;
});

// Optional: Handle sorting by Email Address
add_action( 'pre_get_posts', function($query) {
    if (!is_admin() || !$query->is_main_query()) return;

    $orderby = $query->get('orderby');
    if ($orderby === 'alert_email') {
        $query->set('meta_key', 'alert_email');
        $query->set('orderby', 'meta_value');
    }
});


//   
/**
 * Prevent duplicate WooCommerce error messages on checkout/cart.
 */
add_filter( 'woocommerce_add_error', 'aasw_prevent_duplicate_wc_errors' );

function aasw_prevent_duplicate_wc_errors( $error ) {
    // Make sure WooCommerce session is available
    if ( ! isset( WC()->session ) ) {
        return $error;
    }

    // Get already stored errors from session
    $errors = WC()->session->get( 'wc_unique_errors', [] );

    // If this error is already logged, suppress it
    if ( in_array( $error, $errors, true ) ) {
        return false; // do not display again
    }

    // Otherwise, add it to the unique list and return it
    $errors[] = $error;
    WC()->session->set( 'wc_unique_errors', $errors );

    return $error;
}

/**
 * Clear stored errors when cart/checkout reloads, 
 * so new errors can be displayed fresh.
 */
add_action( 'woocommerce_before_checkout_form', 'aasw_clear_unique_wc_errors' );
add_action( 'woocommerce_before_cart', 'aasw_clear_unique_wc_errors' );

function aasw_clear_unique_wc_errors() {
    if ( isset( WC()->session ) ) {
        WC()->session->__unset( 'wc_unique_errors' );
    }
}


// Checkout popup

add_action( 'wp_footer', function() {
    // Only run on WooCommerce Order Received (Thank You) page
    if ( is_order_received_page() ) { 
	
		global $wp;
        $order_id  = absint( $wp->query_vars['order-received'] );
        $order     = wc_get_order( $order_id );
		
        if ( ! $order ) {
            return;
        }

        // Replace with your Elementor Popup IDs
        $success_popup_id = 2719; 
        $failed_popup_id  = 2720; 
		?>
        <script>
        jQuery(window).on('elementor/frontend/init', function() {
			//console.log("Popup module ready, opening...");
		 
			// Delay until popup module is ready
			var checkPopup = setInterval(function() {
			  if (typeof elementorProFrontend !== "undefined" && elementorProFrontend.modules.popup) {
				clearInterval(checkPopup);
				//console.log("Popup module ready, opening...");
				//elementorProFrontend.modules.popup.showPopup({ id: 2719 }); // Replace with your Popup ID
				<?php if ( $order->has_status('failed') ) : ?>
                    elementorProFrontend.modules.popup.showPopup({ id: <?php echo $failed_popup_id; ?> });
                <?php else : ?>
                    elementorProFrontend.modules.popup.showPopup({ id: <?php echo $success_popup_id; ?> });
                <?php endif; ?>
			  }
			}, 1500);
		  
		}); 
        </script>
    <?php 
	}
	
	if ( is_checkout() ) {
		$failed_popup_id  = 2720; 
		?>
		<script>
		jQuery(function($){
			$('form.checkout').on('change', 'input[name="payment_method"]', function(){
				$(document.body).trigger('update_checkout');
			});
		});
		jQuery(function($) {
			$('li.menu-item-2691').addClass('current-menu-item');
			// Ensure Elementor Pro frontend is loaded before binding
			$(window).on('elementor/frontend/init', function() {
				console.log("Elementor frontend initialized.");

				// Listen for WooCommerce checkout errors
				$(document.body).on('checkout_error', function(event, error_html) {
					console.log("Checkout error detected, opening popup...");

					// Remove default WooCommerce error messages
					$(".woocommerce-error, .woocommerce-message").remove();

					// Trigger Elementor popup
					if (typeof elementorProFrontend !== "undefined" && elementorProFrontend.modules.popup) {
						elementorProFrontend.modules.popup.showPopup({
							id: <?php echo $failed_popup_id; ?>
						});
					} else {
						//alert("Payment Failed: Please try again.");
					}
				});
			});
		});
		</script>
    <?php
    }
	
	if(is_page(2222)){ ?>
		<script>
		jQuery(function($) {
			$('li.menu-item-2484').addClass('current-menu-item');
		});
		</script>
	<?php } 
	
	if(is_page(2819)){ ?>
		<script>
		jQuery(function($) {
			$('li.menu-item-2691').addClass('current-menu-item');
		});
		</script>
	<?php }
	
	if(is_page(3649)){ ?>
		<script>
		jQuery(function($) {
			$('li.menu-item-3810').addClass('current-menu-item');
		});
		</script>
	<?php } 
	
	if(is_page(3715)){ ?>
		<script>
		jQuery(function($) {
			$('li.menu-item-3810').addClass('current-menu-item');
		});
		</script>
	<?php }   
	
	if(is_singular('job_listing')){ ?>
		<script>
		jQuery(function($) {
			$('li.menu-item-2484').addClass('current-menu-item');  
		});
		</script>
	<?php }
	
});


// Change checkout "Place order" button text
add_filter( 'woocommerce_order_button_text', 'custom_checkout_button_text' );

function custom_checkout_button_text( $button_text ) {
    return __( 'Pay Now', 'woocommerce' ); // Change "Pay Now" to your text
}


// Move Order Review and Payment below Additional Information
function move_order_review_and_payment() {
    // Remove Order Review (Your Order table)
    remove_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review', 10 );

    // Remove Payment Section
    remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );

    // Add Order Review + Payment after Additional Information
    add_action( 'woocommerce_after_order_notes', 'woocommerce_order_review', 10 );
    add_action( 'woocommerce_after_order_notes', 'woocommerce_checkout_payment', 20 );
}
add_action( 'woocommerce_before_checkout_form', 'move_order_review_and_payment' );  

// Default the checkbox to checked for save cards
add_filter( 'woocommerce_payment_gateway_save_new_payment_method_checkbox_default', '__return_true' );


function my_custom_checkout_fields( $fields ) {
    // Get suburb terms
    $suburbs = get_all_wpjob_locations();

    $options = array( '' => 'Select a suburb' );
    if ( ! is_wp_error( $suburbs ) ) {
        foreach ( $suburbs as $suburb ) {
            $options[ $suburb['term_name'] ] = $suburb['term_name'];
        }
    }

    // Replace billing_city with a dropdown
    $fields['billing']['billing_city'] = array(
        'type'        => 'select',
        'label'       => __( 'Suburb', 'woocommerce' ),
        'required'    => true,
        'options'     => $options,
        'class'       => array( 'form-row-wide' ),
        'priority'    => 65,
    );

    return $fields;
}
//add_filter( 'woocommerce_checkout_fields', 'my_custom_checkout_fields' );


add_action( 'wp_footer', function() {
	if(is_page(257)){
		if ( is_user_logged_in() ) : ?>
			<script>
				document.addEventListener('DOMContentLoaded', function() {
					var btn = document.getElementById('register_button_about');
					if (btn) btn.style.display = 'none';
				});
			</script>
		<?php endif;
	}
});

/**
* Added below code on 04_November_2025
*/

/**
* Add payment processing fee on all order details and invoice pdf
*/
add_action('woocommerce_cart_calculate_fees', 'add_stripe_fee_after_gst', 99);
function add_stripe_fee_after_gst() {
    if ( is_admin() && ! defined('DOING_AJAX') ) return;

    $chosen_gateway = WC()->session->get('chosen_payment_method');
    if ( $chosen_gateway !== 'stripe' ) return;

    $cart = WC()->cart;

    // Calculate total including taxes (GST)
    $cart_total_incl_tax = $cart->get_subtotal() + $cart->get_subtotal_tax();

    $billing_country = WC()->customer->get_billing_country();

    // Default (Australia domestic)
    $percentage = 0.020;

    // International cards
    /*if ( $billing_country && $billing_country !== 'AU' ) {
        $percentage = 0.029;
        $fixed_fee  = 0.30;
    }*/

    // Calculate Stripe fee on total (after GST)
    $fee = ($cart->get_subtotal() * $percentage);

    // Add fee as NON-TAXABLE so GST is not recalculated on it
    $cart->add_fee('Payment Processing Fee', $fee, false);
}


/**
* Change Tax to GST
*/ 
add_filter( 'gettext', 'rename_tax_to_gst', 20, 3 );
function rename_tax_to_gst( $translated_text, $text, $domain ) {
    if ( 'woocommerce' === $domain ) {
        if ( trim( $translated_text ) === 'Tax' ) {
            $translated_text = 'GST';
        }
    }
    return $translated_text;
}

/**
Remove the clickable product links from the checkout, order details, or thank you pages in WooCommerce
*/
add_filter( 'woocommerce_order_item_name', 'remove_product_link_in_order_pages', 10, 2 );
function remove_product_link_in_order_pages( $item_name, $item ) {
    // Remove the <a> link tag and keep only the plain product name
    return wp_strip_all_tags( $item_name );
}

/**
Remove all existing items in the cart. Add only the latest (new) product.
*/ 
add_action( 'woocommerce_add_to_cart', 'wcpl_allow_only_one_package_in_cart', 10, 6 );
function wcpl_allow_only_one_package_in_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
    
    // Get cart instance
    $cart = WC()->cart;

    // Remove all other items (keep only last added)
    foreach ( $cart->get_cart() as $key => $item ) {
        if ( $key !== $cart_item_key ) {
            $cart->remove_cart_item( $key );
        }
    }

    // Ensure quantity for the added product stays 1
    if ( isset( $cart->cart_contents[ $cart_item_key ] ) ) {
        $cart->set_quantity( $cart_item_key, 1, false );
    }
}



/**
* Changes the prices always in 2 decimals.
* Woocommerce
*/
// Always use 2 decimals in WooCommerce
add_filter( 'wc_get_price_decimals', function() {
    return 2;
}, 999 );

// Always show decimals, even if zero (like 100.00)
add_filter( 'woocommerce_price_trim_zeros', '__return_false' );

// Format prices with 2 decimals for display
add_filter( 'formatted_woocommerce_price', function( $formatted_price, $price, $decimals, $decimal_separator, $thousand_separator ) {
    return number_format( (float) $price, 2, $decimal_separator, $thousand_separator );
}, 10, 5 );  


/**
* Change placeholder text of Order Notes field on checkout page.
* Woocommerce
*/ 
add_filter( 'woocommerce_checkout_fields', 'custom_order_comments_placeholder' );   
function custom_order_comments_placeholder( $fields ) {

    // Change the placeholder text
    $fields['order']['order_comments']['placeholder'] = 'Notes about your order';

    // (Optional) You can also change the label if you want
    //$fields['order']['order_comments']['label'] = 'Order Notes (Optional)';

    return $fields;
}

function custom_admin_colors() {
    echo '<style>
        #adminmenu, #adminmenuback, #adminmenuwrap { background-color: #0a6c8c; }
        #adminmenu a { color: #fff !important; }
        #wpadminbar { background-color: #A32441; }
        .wrap h1 { color: #0073aa; }
		#adminmenu .wp-has-current-submenu .wp-submenu .wp-submenu-head, #adminmenu li.current a.menu-top, #adminmenu li.wp-has-current-submenu a.wp-has-current-submenu {
			background: #A32441;
			color: #fff;
		}
		#adminmenu .wp-submenu {
			background-color: #6b90ab;
		}
    </style>';
}
add_action('admin_head', 'custom_admin_colors');  


/**
TEsting google auto complete api
*/

/**
 * Plugin Name: Google Address Autocomplete Shortcode
 * Description: Adds a shortcode [google_address_autocomplete] that displays a form with Google Address Autocomplete restricted to Australia (includes lat/lng).
 */

add_shortcode( 'google_address_autocomplete', function() {
	ob_start();
	?>
	<form id="google-address-form" style="max-width:500px; margin:auto;">
		<input id="address" type="text" placeholder="Start typing your address..." style="width:100%; padding:10px; margin-bottom:10px;">
		
		<!-- Optional individual address fields -->
		<input id="street" type="text" placeholder="Street" style="width:100%; padding:10px; margin-bottom:10px;">
		<input id="city" type="text" placeholder="City" style="width:100%; padding:10px; margin-bottom:10px;">
		<input id="state" type="text" placeholder="State" style="width:100%; padding:10px; margin-bottom:10px;">
		<input id="postcode" type="text" placeholder="Postcode" style="width:100%; padding:10px; margin-bottom:10px;">
		
		<!-- New: Latitude & Longitude fields -->
		<input id="latitude" type="text" placeholder="Latitude" readonly style="width:100%; padding:10px; margin-bottom:10px; background:#f9f9f9;">
		<input id="longitude" type="text" placeholder="Longitude" readonly style="width:100%; padding:10px; margin-bottom:10px; background:#f9f9f9;">
	</form>

	<script>
	function initAutocomplete() {
		const input = document.getElementById("address");
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
			document.getElementById("street").value = 
				(components.street_number ? components.street_number + " " : "") + (components.route || "");
			document.getElementById("city").value = components.locality || "";
			document.getElementById("state").value = components.administrative_area_level_1 || "";
			document.getElementById("postcode").value = components.postal_code || "";

			// Get Latitude & Longitude
			if (place.geometry && place.geometry.location) {
				document.getElementById("latitude").value = place.geometry.location.lat();
				document.getElementById("longitude").value = place.geometry.location.lng();
				console.log("Lat:", place.geometry.location.lat(), "Lng:", place.geometry.location.lng());
			}
		});
	}

	// Ensure callback initializes autocomplete
	if (typeof google !== "undefined" && google.maps && google.maps.places) {
		initAutocomplete();
	} else {
		window.initAutocomplete = initAutocomplete;
	}
	</script>
	<?php
	return ob_get_clean();
});

// function enqueue_google_maps_script()
// {
//     $api_key = 'AIzaSyCQCUlVYxaZU0vuFUi9fNX68QyFdKOvi2A';
//     $google_maps_url = 'https://maps.googleapis.com/maps/api/js?key=' . $api_key . '&libraries=places&region=AU';

//     wp_register_script('google-maps', $google_maps_url, [], null, true);

//     add_filter('script_loader_tag', function ($tag, $handle) {
//         if ('google-maps' !== $handle)
//             return $tag;
//         return str_replace(' src', ' async defer src', $tag);
//     }, 10, 2);

//     wp_enqueue_script('google-maps');
// }
// add_action('wp_enqueue_scripts', 'enqueue_google_maps_script');





/**
* Change PDF invoice number to custom format.
* Woocommerce ("PDF Invoices & Packing Slips for WooCommerce")
*/
//add_filter( 'wpo_wcpdf_invoice_number', 'custom_wpo_invoice_number_format_new', 10, 4 );
function custom_wpo_invoice_number_format_new( $number, $order_number, $order, $document ) {

    // Get the current year
    $year = date('Y');

    // Get last used year from options
    $last_year = get_option( 'custom_invoice_last_year' );

    // If new year, reset the counter to 1
    if ( $last_year != $year ) {
        update_option( 'custom_invoice_last_year', $year );
        update_option( 'custom_invoice_counter', 1 );
        $counter = 1;
    } else {
        // Otherwise, continue incrementing from the stored counter
        $counter = get_option( 'custom_invoice_counter', 1 );
        $counter++;
        update_option( 'custom_invoice_counter', $counter );
    }

    // Pad counter (0001, 0002, etc.)
    $formatted_number = str_pad( $counter, 4, '0', STR_PAD_LEFT );

    // Combine year + counter
    $custom_number = $year . '-' . $formatted_number;

    return $custom_number;
}  



/***
custom auto login user function
*/

// ======================================================
// 1. Add "Login as User" link in Users table (unchanged)
// ======================================================
add_filter('user_row_actions', function($actions, $user) {
    if (current_user_can('administrator') && get_current_user_id() !== $user->ID) {
        $url = add_query_arg([
            'action' => 'login_as_user',
            'user_id' => $user->ID,
            '_wpnonce' => wp_create_nonce('login_as_user_'.$user->ID)
        ], admin_url('users.php'));
        $actions['login_as_user'] = '<a href="' . esc_url($url) . '">Login as this user</a>';
    }
    return $actions;
}, 10, 2);


// ======================================================
// 2. Handle the "Login as User" request
// ======================================================
add_action('admin_init', function() {
    if (!isset($_GET['action']) || $_GET['action'] !== 'login_as_user') return;
    if (!current_user_can('administrator')) return;

    $user_id = intval($_GET['user_id']);
    check_admin_referer('login_as_user_'.$user_id);

    $user = get_user_by('id', $user_id);
    if (!$user) wp_die('User not found.');

    // Store original admin ID in a secure, short-lived cookie
    setcookie('original_admin_id', get_current_user_id(), time() + 900, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);

    // Log in as target user
    wp_set_auth_cookie($user_id);
    wp_set_current_user($user_id);

    wp_redirect(home_url('/'));
    exit;
});


// ======================================================
// 3. Add "Switch back to Admin" button on frontend
// ======================================================
add_action('wp_footer', function() {
    // Show only if original admin cookie exists
    if (isset($_COOKIE['original_admin_id']) && is_user_logged_in()) {
        $url = add_query_arg([
            'action' => 'switch_back_admin',
            '_wpnonce' => wp_create_nonce('switch_back_admin')
        ], home_url('/'));

        echo '<div style="position:fixed;bottom:20px;right:20px;z-index:9999;">
            <a href="' . esc_url($url) . '" style="background:#0073aa;color:#fff;padding:10px 15px;border-radius:4px;text-decoration:none;font-size:14px;">⬅ Switch back to Admin</a>
        </div>';
    }
});


// ======================================================
// 4. Handle the "Switch back to Admin" action
// ======================================================
add_action('init', function() {
    if (!isset($_GET['action']) || $_GET['action'] !== 'switch_back_admin') return;
    if (!isset($_COOKIE['original_admin_id'])) return;

    $original_admin_id = intval($_COOKIE['original_admin_id']);
    check_admin_referer('switch_back_admin');

    $admin_user = get_user_by('id', $original_admin_id);
    if (!$admin_user) wp_die('Original admin session expired or invalid.');

    // Log back in as admin
    wp_set_auth_cookie($original_admin_id);
    wp_set_current_user($original_admin_id);

    // Remove the cookie (cleanup)
    setcookie('original_admin_id', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN);

    // Redirect to admin dashboard
    wp_redirect(admin_url());
    exit;
});
