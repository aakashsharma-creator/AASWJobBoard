<?php
/**
 * Core class file to import all other classes
 *
 * @package  Easy_PG
 * @version  1.0.0
 */

namespace Easy_PG;

use Easy_PG\Helper\Easy_PG_Loader;
use Easy_PG\Config\Easy_PG_Config;
use Easy_PG\Core\Easy_PG_i18n;
use Easy_PG\Admin\Easy_PG_Admin;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Easy_PG
 * @subpackage Easy_PG/includes
 */
class Easy_PG_Run {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Easy_PG_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * Fired during plugin activation.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Easy_PG_Activator    $activator    Defines all code necessary to run during the plugin's activation.
	 */
	public $activator;

	/**
	 * Fired during plugin deactivation.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      Easy_PG_Deactivator    $deactivator    Defines all code necessary to run during the plugin's deactivation.
	 */
	public $deactivator;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name = Easy_PG_Config::PLUGIN_NAME;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version = Easy_PG_Config::PLUGIN_VERSION;

	/**
	 * Min required WC version.
	 *
	 * @var string
	 */
	private $wc_min_version = Easy_PG_Config::WC_MIN_PLUGIN_VERSION;

	/**
	 * The single instance of the class.
	 *
	 * @var Easy_PG_Run
	 */
	protected static $_instance = null;

	/**
	 * Tab name for settings.
	 *
	 * @var string
	 */
	protected $tab_name = Easy_PG_Config::WC_TAB_NAME;

	/**
	 * @var $request
	 */
	protected $request;

	/**
	 * Main Easy_PG_Run instance. Ensures only one instance is loaded or can be loaded.
	 *
	 * @static
	 * @return  Easy_PG_Run
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		// Add default option value.
		add_option( 'easy_pg_settings', array('post') );

		// Initialize object for actions and filters hooks.
		$this->loader = new Easy_PG_Loader();

		// Initialize languages translations.
		$this->set_locale_lang();

		// Load Admin Settings.
		$admin_setting = new Easy_PG_Admin();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Easy_PG_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale_lang() {

		$plugin_i18n = new Easy_PG_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}
}
