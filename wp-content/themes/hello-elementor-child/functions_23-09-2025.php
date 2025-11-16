<?php
function hello_elementor_child_enqueue_styles() {
	wp_enqueue_style( 'hello-elementor-style', get_template_directory_uri() . '/style.css' );
	wp_enqueue_style( 'hello-elementor-child-style',
					 get_stylesheet_directory_uri() . '/style.css',
					 array( 'hello-elementor-style' ),
					 wp_get_theme()->get('Version')
					);
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_enqueue_styles' );

// Loading fonts
function custom_enqueue_fonts() {
    wp_enqueue_style( 'nunito-sans', 'https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700&display=swap', false );
}
add_action( 'wp_enqueue_scripts', 'custom_enqueue_fonts' );

/**
* Custom functions
*/

add_filter( 'imagick_thread_count', function() { return 1; } );

add_filter( 'wp_image_editors', function( $editors ) {
    return array( 'WP_Image_Editor_GD' );
});

// job package purchase multiple
add_filter( 'wcpl_job_package_is_sold_individually', '__return_false' );

// Allow SVG + ICO upload
function custom_mime_types( $mimes ) {
    $mimes['svg']  = 'image/svg+xml';
    $mimes['ico']  = 'image/x-icon'; // Add ICO support
    return $mimes;
}
add_filter( 'upload_mimes', 'custom_mime_types' );

// Force WordPress file type check to allow ICO
function fix_wp_check_filetype_and_ext( $data, $file, $filename, $mimes, $real_mime ) {
    $ext = pathinfo( $filename, PATHINFO_EXTENSION );

    if ( 'ico' === strtolower( $ext ) ) {
        $data['ext']  = 'ico';
        $data['type'] = 'image/x-icon';
        $data['proper_filename'] = $filename;
    }

    return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'fix_wp_check_filetype_and_ext', 10, 5 );


// Includes other custom functions/files
include('includes/custom-functions.php');
include('includes/custom-post-types.php');
include('includes/theme-shortcodes.php');

/**
 * Auto-remove existing Job Package from cart before adding a new one.
 */
add_action('woocommerce_before_calculate_totals', function($cart) {
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
add_filter('woocommerce_add_to_cart_validation', function($passed, $product_id, $quantity) {
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
add_filter( 'woocommerce_add_to_cart_validation', 'allow_job_package_checkout', 10, 3 );
function allow_job_package_checkout( $passed, $product_id, $quantity ) {
    
    if ( $product_id == 2257 ) {
        // If already in cart, prevent duplicate error
        foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
            if ( $values['product_id'] == $product_id ) {
                return false; // Stops adding again, avoids error
            }
        }
    }
    return $passed;
}


// Flush cache
function my_custom_cache_flush_function() {
    wp_cache_flush();
}
add_action( 'admin_init', 'my_custom_cache_flush_function' ); // Example: Flush on admin init


// AVIK ===========================================================

function enqueue_datatables_scripts() {
    // Load DataTables CSS & JS from CDN
    wp_enqueue_style( 'datatables-css', 'https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css', array(), '1.13.8' );
    wp_enqueue_script( 'datatables-js', 'https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js', array( 'jquery' ), '1.13.8', true );

    // Initialize DataTable
    wp_add_inline_script( 'datatables-js', "
        jQuery(document).ready(function($) {
            $('table.job-list').DataTable({
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
        });
    " );
}
add_action( 'wp_enqueue_scripts', 'enqueue_datatables_scripts' );

include('custom-shortcodes/list-job.php');
include('custom-shortcodes/search-locum.php');
include('custom-shortcodes/search-job.php');
include('custom-shortcodes/saved-jobs.php');
include('custom-shortcodes/employer-dashboard.php');