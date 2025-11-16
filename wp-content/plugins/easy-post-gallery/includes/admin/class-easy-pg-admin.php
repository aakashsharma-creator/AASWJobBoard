<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and examples of how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Easy_PG
 * @subpackage Easy_PG/admin
 */

namespace Easy_PG\Admin;

use Easy_PG\Helper\Easy_PG_Loader;
use Easy_PG\Config\Easy_PG_Config;
use Easy_PG\Admin\Easy_PG_Settings;

class Easy_PG_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	protected $plugin_name = Easy_PG_Config::PLUGIN_NAME;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	protected $version = Easy_PG_Config::PLUGIN_VERSION;

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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->loader = new Easy_PG_Loader();

		$this->loader->run();

		$this->loader = new Easy_PG();

		$this->loader = new Easy_PG_Settings();
	}

}
