<?php
/**
 * Enqueue parent and child theme styles
 */
function hello_elementor_child_enqueue_styles() {
    // Parent style
    wp_enqueue_style(
        'hello-elementor-style',
        get_template_directory_uri() . '/style.css'
    );

    // Child style
    wp_enqueue_style(
        'hello-elementor-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('hello-elementor-style'),
        wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'hello_elementor_child_enqueue_styles');


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

