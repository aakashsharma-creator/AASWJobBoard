<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Easy Post Gallery
 * Plugin URI:        https://wordpress.org/plugins/easy-post-gallery/
 * Description:       Easy Post Gallery is the gallery management tool created for WordPress.
 * Version:           1.1
 * Requires PHP:      7.0
 * Author:            Megha Shah
 * Author URI:        https://profiles.wordpress.org/rathimegha/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       easy-post-gallery
 * Domain Path:       /languages
 * 
 * @package           Easy_Post_Gallery
 */

use Easy_PG\Config\Easy_PG_Config;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'EASY_PG_URL' ) ) {
	define( 'EASY_PG_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'EASY_PG_ABSPATH' ) ) {
	define( 'EASY_PG_ABSPATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
}
if ( ! defined( 'EASY_PG_DIR' ) ) {
	define( 'EASY_PG_DIR', EASY_PG_ABSPATH );
}
if ( ! defined( 'EASY_PG_BASENAME' ) ) {
	define( 'EASY_PG_BASENAME', plugin_basename( __FILE__ ) );
}
if ( ! defined( 'EASY_PG_VERSION' ) ) {
	define( 'EASY_PG_VERSION', '1.0.0' );
}

if ( ! function_exists( 'easy_pg_plugin' ) ) {
	/**
	 * Easy Post Gallery Plugin Function.
	 */
	function easy_pg_plugin() {

		require_once EASY_PG_ABSPATH . 'autoload.php';
		/**
		 * The code that runs during plugin activation.
		 * This action is documented in includes/class-easy-pg-activator.php
		 */

		if ( ! function_exists( 'easy_pg_activate' ) ) {
			/**
			 * The code that runs during plugin activation.
			 */
			function easy_pg_activate() {
				\Easy_PG\Core\Easy_PG_Activator::activate();
			}
			register_activation_hook( __FILE__, 'easy_pg_activate' );
		}
		/**
		 * The code that runs during plugin deactivation.
		 * This action is documented in includes/class-easy-pg-deactivator.php
		 */

		if ( ! function_exists( 'easy_pg_deactivate' ) ) {
			/**
			 * The code that runs during plugin deactivation.
			 */
			function easy_pg_deactivate() {
				\Easy_PG\Core\Easy_PG_Deactivator::deactivate();
			}
			register_deactivation_hook( __FILE__, 'easy_pg_deactivate' );
		}

		if ( ! function_exists( 'easy_pg_enqueue_custom_style' ) ) {
			/**
			 * Admin Enqueue Scripts and Styles.
			 */
			function easy_pg_enqueue_custom_style() {

				wp_register_style( 'easy_pg', EASY_PG_URL . 'css/easy-post-gallery-admin.css', null, EASY_PG_VERSION );
				wp_enqueue_style( 'easy_pg' );

				$easy_pg_nonce = wp_create_nonce( 'easy_pg_ajax_nonce' );
				wp_register_script( 'easy_pg-solid-js', EASY_PG_URL . 'js/solid.js', array( 'jquery' ), '6.6.0', true );
				wp_enqueue_script( 'easy_pg-solid-js' );

				wp_register_script( 'easy_pg-fontawesome-js', EASY_PG_URL . 'js/fontawesome.js', array( 'jquery' ), '6.6.0', true );
				wp_enqueue_script( 'easy_pg-fontawesome-js' );

				wp_register_script( 'easy_pg-custom-js', EASY_PG_URL . 'js/easy-post-gallery-admin.js', array( 'jquery' ), EASY_PG_VERSION, true );
				wp_enqueue_script( 'easy_pg-custom-js' );

				wp_enqueue_script( 'jquery-ui-sortable' );  
			}

			add_action( 'admin_enqueue_scripts', 'easy_pg_enqueue_custom_style' );
		}

		if( ! function_exists( 'easy_pg_action_links' ) ) {
					
			function easy_pg_action_links( $links ) {
				
				$url = 'options-general.php?page=easy_pg_option_settings';
				
				$settings_link = '<a href="'. esc_url( $url ) .'">'. esc_html__( 'Settings', 'easy-post-gallery' ) .'</a>';
				
				array_unshift( $links, $settings_link );
				
				return $links;
			}

			add_filter( 'plugin_action_links_' . EASY_PG_BASENAME, 'easy_pg_action_links' );
		}

		if ( ! function_exists( 'easy_pg_run' ) ) {
			/**
			 * Start the execution of the plugin.
			 */
			function easy_pg_run() {
				return \Easy_PG\Easy_PG_Run::instance();
			}
			easy_pg_run();
		}
	}
}
add_action( 'plugins_loaded', 'easy_pg_plugin', 999 );
