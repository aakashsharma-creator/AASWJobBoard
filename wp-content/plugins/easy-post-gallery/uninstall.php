<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://wordpress.org
 * @since      1.0.0
 *
 * @package    Easy_Post_Gallery
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Remove plugin settings when the plugin is uninstalled.
delete_option( 'easy_pg_settings' ); // Updated option name with new prefix
