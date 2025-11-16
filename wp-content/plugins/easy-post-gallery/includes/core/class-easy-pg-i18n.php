<?php

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Easy_PG
 * @subpackage Easy_PG/core
 */

namespace Easy_PG\Core;

class Easy_PG_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'easy-post-gallery',
			false,
			Easy_PG_PLUGIN_DIR . '/languages/'
		);

	}



}
