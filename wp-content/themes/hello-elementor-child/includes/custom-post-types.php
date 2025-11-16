<?php
/**
 * Added custom post types
*/

// Register "Location Categories" taxonomy for Job Listings
function aasw_register_job_location_category() {
    $labels = array(
        'name'              => _x( 'Location', 'taxonomy general name', 'wp-job-manager' ),
        'singular_name'     => _x( 'Location', 'taxonomy singular name', 'wp-job-manager' ),
        'search_items'      => __( 'Search Location', 'wp-job-manager' ),
        'all_items'         => __( 'All Location', 'wp-job-manager' ),
        'parent_item'       => __( 'Parent Location', 'wp-job-manager' ),
        'parent_item_colon' => __( 'Parent Location:', 'wp-job-manager' ),
        'edit_item'         => __( 'Edit Location', 'wp-job-manager' ),
        'update_item'       => __( 'Update Location', 'wp-job-manager' ),
        'add_new_item'      => __( 'Add New Location', 'wp-job-manager' ),
        'new_item_name'     => __( 'New Location Name', 'wp-job-manager' ),
        'menu_name'         => __( 'Locations', 'wp-job-manager' ),
    );

    register_taxonomy(
        'job_location_category',
        'job_listing',
        array(
            'hierarchical'      => true, // acts like categories
            'labels'            => $labels,
            'show_ui'           => true,
            'show_in_nav_menus' => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'job-location-category' ),
        )
    );
}
add_action( 'init', 'aasw_register_job_location_category' );
