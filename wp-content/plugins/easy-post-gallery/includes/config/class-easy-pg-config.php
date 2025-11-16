<?php
/**
 * Define the config constants
 *
 * @since      1.0.0
 *
 * @package    Easy_PG
 * @subpackage Easy_PG/config
 */

namespace Easy_PG\Config;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Easy_PG_Config {

	const PLUGIN_NAME = 'Easy_PG';
	const PLUGIN_VERSION = '1.0';
	const WC_MIN_PLUGIN_VERSION = '1.0';
	const WC_TAB_NAME = 'test';
}
