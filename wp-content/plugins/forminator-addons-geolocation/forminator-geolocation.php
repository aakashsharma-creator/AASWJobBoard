<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Forminator geolocation addon
 *
 * @package Forminator geolocation
 *
 * Plugin Name: Forminator Geolocation Add-on
 * Version: 1.5.0
 * Plugin URI: https://wpmudev.com/project/forminator-pro/
 * Description: Collect your form submitter’s location information, and provide address auto-completion using Google Maps API.
 * Author: WPMU DEV
 * Author URI: https://wpmudev.com
 * Text Domain: forminator-geo
 * Domain Path: /languages/
 * WDP ID: 4276231
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! defined( 'FORMINATOR_GEOLOCATION_ADDON' ) ) {
	define( 'FORMINATOR_GEOLOCATION_ADDON', '1.5.0' );
}

/**
 * Class Forminator_Geolocation_Addon
 *
 * Main class. Initialize add-on
 */
if ( ! class_exists( 'Forminator_Geolocation_Addon' ) ) {

	/**
	 * Geolocation Add-on class
	 */
	class Forminator_Geolocation_Addon {
		/**
		 * Plugin instance
		 *
		 * @var null | Forminator_Geolocation_Addon
		 */
		private static $instance;

		/**
		 * Minimum version of Forminator, that the addon will work correctly
		 *
		 * @var string
		 */
		private $forminator_min_version = '1.27.0';

		/**
		 * Return the plugin instance
		 *
		 * @return Forminator_Geolocation_Addon
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 */
		private function __construct() {
			if ( $this->is_supported_version() && defined( 'FORMINATOR_PRO' ) && FORMINATOR_PRO ) {
				add_action( 'forminator_loaded', array( $this, 'forminator_loaded' ) );

				// Triggered when plugin is loaded.
				do_action( 'forminator_geolocation_addon_loaded' );
			} else {
				// Initialize subscription after main plugin loaded only if Forminator version is supported.
				add_action( 'admin_notices', array( $this, 'forminator_geolocation_show_admin_notice' ) );
			}
		}

		/**
		 * Initialise Subscription class
		 */
		public function forminator_loaded() {
			// Load required files.
			require_once dirname( __FILE__ ) . '/library/class-forminator-geolocation-admin.php';
			require_once dirname( __FILE__ ) . '/library/class-forminator-geolocation-frontend.php';

			if ( is_admin() ) {
				Forminator_Geolocation_Admin::get_instance();
			}
		}

		/**
		 * Check if Forminator version is supported
		 *
		 * @since 1.0
		 *
		 * @return bool
		 */
		public function is_supported_version() {
			if ( defined( 'FORMINATOR_VERSION' ) ) {
				return version_compare( FORMINATOR_VERSION, $this->forminator_min_version, '>=' );
			}

			return false;
		}

		/**
		 * Show geolocation admin notice
		 *
		 * @return void
		 */
		public function forminator_geolocation_show_admin_notice() {
			global $pagenow;
			$page = (string) filter_input( INPUT_GET, 'page' );
			if ( 'forminator' === substr( $page, 0, 10 ) || 'plugins.php' === $pagenow ) {
				?>
				<div class="notice notice-error">
					<p>
					<?php
					printf(
						/* translators: 1. Open B tag. 2. Close B tag. 3. Open A tag. 4. Close A tag. */
						esc_html__( '%1$sForminator Geolocation%2$s Add-on requires the latest version of Forminator Pro in order to work. Please install and activate the latest version %3$shere%4$s', 'forminator-geo' ),
						'<strong>',
						'</strong>',
						'<a href="https://wpmudev.com/project/forminator-pro/" target="_blank">',
						'</a>'
					);
					?>
					</p>
				</div>
				<?php
			}
		}

	}

	// Fail activation process if main plugin is inactive.
	add_action(
		'activate_plugin',
		function( $plugin ) {
			if ( class_exists( 'Forminator' ) || 'forminator-addons-geolocation/forminator-geolocation.php' !== $plugin ) {
				return;
			}
			wp_die(
				sprintf(
					/* translators: 1. Open H1 tag. 2. Close H1 tag and open P tag. 3. Open A tag. 4. Close A tag. 5. Close P tag. */
					esc_html__( '%1$sForminator Geolocation%2$s Add-on requires the latest version of Forminator Pro in order to work. Please install and activate the latest version %3$shere%4$s', 'forminator-geo' ),
					'<h1>',
					'</h1><p>',
					'<a href="https://wpmudev.com/project/forminator-pro/" target="_blank">',
					'</a></p>'
				),
				'',
				array(
					'response'  => 500,
					'back_link' => true,
				)
			);
		}
	);

	// Deactivate the current addon if main plugin is inactive.
	add_action(
		'admin_init',
		function() {
			if ( class_exists( 'Forminator' ) ) {
				return;
			}
			deactivate_plugins( plugin_basename( __FILE__ ) );
		}
	);
}

// Init the plugin and load the plugin instance.
add_action(
	'plugins_loaded',
	function() {
		Forminator_Geolocation_Addon::get_instance();
	},
	9
);