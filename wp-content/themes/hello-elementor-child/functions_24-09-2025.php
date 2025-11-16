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
    wp_enqueue_style( 'datatables-css', 'https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css', array(), '1.13.8' );
    wp_enqueue_script( 'datatables-js', 'https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js', array( 'jquery' ), '1.13.8', true );

   wp_add_inline_script( 'datatables-js', "
(function($){
    function initJobListTable(){
        $('table.list-job').each(function(){
            if(! $.fn.DataTable.isDataTable(this) && $(this).is(':visible')){
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
add_action( 'wp_enqueue_scripts', 'enqueue_datatables_scripts' );


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



include('custom-shortcodes/list-job.php');
include('custom-shortcodes/search-locum.php');
include('custom-shortcodes/search-job.php');
include('custom-shortcodes/saved-jobs.php');
include('custom-shortcodes/employer-dashboard.php');