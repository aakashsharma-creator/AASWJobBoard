<?php
/**
 * Forminator_Geolocation_Frontend class.
 *
 * @package Forminator Geolocation
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Geolocation_Admin
 */
class Forminator_Geolocation_Frontend {
	/**
	 * Instance
	 *
	 * @var self|null
	 */
	private static $instance;

	/**
	 * Flag for loading scripts only 1 time
	 *
	 * @var bool
	 */
	private static $scripts_loaded = false;

	/**
	 * Valid Api Key
	 *
	 * @var string|null|false
	 */
	private static $valid_api_key;

	/**
	 * Get Instance
	 *
	 * @return self|null
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Forminator_Geolocation_Frontend constructor
	 */
	private function __construct() {
		// Add hidden input for Geolocation field.
		add_filter( 'forminator_render_form_submit_markup', array( __CLASS__, 'render_geolocation_field' ), 10, 4 );
		// Add geolocation map.
		add_filter( 'forminator_field_address_markup', array( __CLASS__, 'add_geolocation_map' ), 10, 2 );
		// Add default options.
		add_filter( 'forminator_localize_data', array( __CLASS__, 'add_default_options' ) );
	}

	/**
	 * Pass Geolocation Autosuggestion settings
	 *
	 * @param array $data Current data.
	 * @return array
	 */
	public static function add_default_options( $data ) {
		$data['geolocation_autosuggest_options'] = array(
			'fields' => array( 'address_components', 'geometry' ),
			'type'   => array( 'address' ),
			/**'componentRestrictions' => array( 'country' => array( 'us', 'ca' ) ), // filter by up to 5 countries.*/
		);

		return $data;
	}

	/**
	 * Load geolocation API script
	 *
	 * @param bool $force Load map on Submissions page even if API key is invalid.
	 */
	public static function load_scripts( $force = false ) {
		// Prevent double loading.
		if ( self::$scripts_loaded ) {
			return;
		}
		self::$scripts_loaded = true;

		$api_key = self::get_valid_api_key();
		$args    = array(
			'callback'  => 'forminatorGeolocationInit',
			'language'  => get_locale(),
			'libraries' => 'marker',
		);

		if ( $api_key ) {
			$args['key'] = $api_key;
		} elseif ( ! $force ) {
			return;
		}

		$src     = trailingslashit( plugin_dir_url( dirname( __FILE__ ) ) ) . 'build/front/geolocation-map.min.js';
		$api_src = add_query_arg( $args, 'https://maps.googleapis.com/maps/api/js' );

		if ( is_admin() && ! wp_doing_ajax() ) {
			self::enqueue_scripts( $src, $api_src );
			return;
		}
		add_filter(
			'forminator_enqueue_form_script',
			function( $script, $is_preview, $is_ajax_load ) use ( $src, $api_src ) {
				if ( ! $is_ajax_load ) {
					self::enqueue_scripts( $src, $api_src );
				} else {
					$script .= '<script type="text/javascript">'
							. "if(typeof forminatorGeolocationInit === 'function'){"
								. '$( document ).ready(function() {'
									. 'forminatorGeolocationInit();'
								. '});'
							. '}</script>';
				}

				return $script;
			},
			10,
			3
		);

		add_filter(
			'forminator_enqueue_form_scripts',
			function( $scripts, $is_preview, $is_ajax_load ) use ( $api_src, $src ) {
				if ( $is_ajax_load ) {
					// load later via ajax to avoid cache.
					$scripts['forminator-geolocation-map-api'] = array(
						'src'  => $api_src . '&ver=' . FORMINATOR_GEOLOCATION_ADDON,
						'on'   => '$',
						'load' => 'GeolocationMapApi',
					);
					$scripts['forminator-geolocation-script']  = array(
						'src'  => $src,
						'on'   => '$',
						'load' => 'GeolocationMap',
					);
				}

				return $scripts;
			},
			10,
			3
		);
	}

	/**
	 * Enqueue scripts
	 *
	 * @param string $src Source addon js.
	 * @param string $api_src Source Google js.
	 */
	public static function enqueue_scripts( $src, $api_src ) {
		wp_enqueue_script(
			'forminator-geolocation-script',
			$src,
			array( 'jquery' ),
			FORMINATOR_GEOLOCATION_ADDON,
			true
		);
		wp_enqueue_script(
			'forminator-google-geolocation-map-api',
			$api_src,
			array( 'forminator-geolocation-script' ),
			FORMINATOR_GEOLOCATION_ADDON,
			true
		);
	}

	/**
	 * Get valid api key
	 *
	 * @return string|false
	 */
	public static function get_valid_api_key() {
		if ( is_null( self::$valid_api_key ) ) {
			$settings = self::get_settings();
			if ( empty( $settings['api_key'] ) ) {
				self::$valid_api_key = false;
			} else {
				$valid = self::check_api_key( $settings['api_key'] );

				if ( ! $valid ) {
					self::$valid_api_key = false;
				} else {
					self::$valid_api_key = $settings['api_key'];
				}
			}
		}

		return self::$valid_api_key;
	}

	/**
	 * Get Map Id
	 *
	 * @param array $settings Settings.
	 * @return string
	 */
	public static function get_map_id( $settings = array() ) {
		if ( empty( $settings ) ) {
			$settings = self::get_settings();
		}
		return ! empty( $settings['map_id'] ) ? $settings['map_id'] : 'DEMO_MAP_ID';
	}

	/**
	 * Check API key
	 *
	 * @param string $api_key Api key.
	 * @return boolean
	 */
	public static function check_api_key( $api_key ) {
		$url      = 'https://maps.googleapis.com/maps/api/geocode/json?address=New+York&key=' . rawurlencode( $api_key );
		$response = wp_remote_get( $url );

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false; // Error fetching API response.
		}
		$decoded_response = json_decode( wp_remote_retrieve_body( $response ) );

		if ( ! $decoded_response || ( ! empty( $decoded_response->error_message ) && 'API keys with referer restrictions cannot be used with this API.' !== $decoded_response->error_message ) ) {
			return false; // API key error.
		}

		return true; // API key is valid.
	}

	/**
	 * Get default map settings for frontend
	 *
	 * @param array $field Address field settings.
	 * @return array
	 */
	public static function get_default_map_settings( $field ) {
		$defaults = self::get_map_defaults();

		// Get default coordinates from field settings.
		if ( ! empty( $field['map_location'] ) && 'custom' === $field['map_location']
				&& ! empty( $field['default_coordinates'] ) ) {
			$explode_by = ' ';
			if ( strpos( $field['default_coordinates'], ',' ) !== false ) {
				$explode_by = ',';
			}
			$elements = explode( $explode_by, trim( $field['default_coordinates'] ) );
			if ( 2 === count( $elements ) ) {
				$lat = trim( $elements[0] );
				if ( is_numeric( $lat ) ) {
					$defaults['latitude'] = $lat;
				}
				$lng = trim( $elements[1] );
				if ( is_numeric( $lng ) ) {
					$defaults['longitude'] = $lng;
				}
			}
		}

		/**
		 * Filter default map settings
		 *
		 * @param array $defaults Default settings.
		 */
		return apply_filters( 'forminator_geolocation_default_map_frontend_settings', $defaults );
	}

	/**
	 * Get default map settings
	 *
	 * @return array
	 */
	public static function get_map_defaults() {
		return array(
			'latitude'       => 0,
			'longitude'      => 0,
			'zoom'           => 15,
			'mapTypeControl' => true,
			'mapTypeId'      => 'roadmap', // roadmap|satellite|hybrid|terrain .
			'styled_type'    => 'Styled Map',
			'styles'         => '',
			/**
			'styles'         => [
				[
					"elementType" => "geometry",
					"stylers" => [
						["color" => "#ebe3cd"]
					]
				],
				[
					"elementType" => "labels.text.fill",
					"stylers" => [
						["color" => "#523735"]
					]
				],
				[
					"elementType" => "labels.text.stroke",
					"stylers" => [
						["color" => "#f5f1e6"]
					]
				],
				[
					"featureType" => "administrative",
					"elementType" => "geometry.stroke",
					"stylers" => [
						["color" => "#c9b2a6"]
					]
				],
				[
					"featureType" => "administrative.land_parcel",
					"elementType" => "geometry.stroke",
					"stylers" => [
						["color" => "#dcd2be"]
					]
				],
				[
					"featureType" => "administrative.land_parcel",
					"elementType" => "labels.text.fill",
					"stylers" => [
						["color" => "#ae9e90"]
					]
				],
				[
					"featureType" => "landscape.natural",
					"elementType" => "geometry",
					"stylers" => [
						["color" => "#dfd2ae"]
					]
				],
				[
					"featureType" => "poi",
					"elementType" => "geometry",
					"stylers" => [
						["color" => "#dfd2ae"]
					]
				],
				[
					"featureType" => "poi",
					"elementType" => "labels.text.fill",
					"stylers" => [
						["color" => "#93817c"]
					]
				],
				[
					"featureType" => "poi.park",
					"elementType" => "geometry.fill",
					"stylers" => [
						["color" => "#a5b076"]
					]
				],
				[
					"featureType" => "poi.park",
					"elementType" => "labels.text.fill",
					"stylers" => [
						["color" => "#447530"]
					]
				],
				[
					"featureType" => "road",
					"elementType" => "geometry",
					"stylers" => [
						["color" => "#f5f1e6"]
					]
				],
				[
					"featureType" => "road.arterial",
					"elementType" => "geometry",
					"stylers" => [
						["color" => "#fdfcf8"]
					]
				],
				[
					"featureType" => "road.highway",
					"elementType" => "geometry",
					"stylers" => [
						["color" => "#f8c967"]
					]
				],
				[
					"featureType" => "road.highway",
					"elementType" => "geometry.stroke",
					"stylers" => [
						["color" => "#e9bc62"]
					]
				],
				[
					"featureType" => "road.highway.controlled_access",
					"elementType" => "geometry",
					"stylers" => [
						["color" => "#e98d58"]
					]
				],
				[
					"featureType" => "road.highway.controlled_access",
					"elementType" => "geometry.stroke",
					"stylers" => [
						["color" => "#db8555"]
					]
				],
				[
					"featureType" => "road.local",
					"elementType" => "labels.text.fill",
					"stylers" => [
						["color" => "#806b63"]
					]
				],
				[
					"featureType" => "transit.line",
					"elementType" => "geometry",
					"stylers" => [
						["color" => "#dfd2ae"]
					]
				],
				[
					"featureType" => "transit.line",
					"elementType" => "labels.text.fill",
					"stylers" => [
						["color" => "#8f7d77"]
					]
				],
				[
					"featureType" => "transit.line",
					"elementType" => "labels.text.stroke",
					"stylers" => [
						["color" => "#ebe3cd"]
					]
				],
				[
					"featureType" => "transit.station",
					"elementType" => "geometry",
					"stylers" => [
						["color" => "#dfd2ae"]
					]
				],
				[
					"featureType" => "water",
					"elementType" => "geometry.fill",
					"stylers" => [
						["color" => "#b9d3c2"]
					]
				],
				[
					"featureType" => "water",
					"elementType" => "labels.text.fill",
					"stylers" => [
						["color" => "#92998d"]
					]
				]
			],
			*/
		);
	}

	/**
	 * Get saved addon settings
	 *
	 * @return array
	 */
	public static function get_settings() {
		$settings = get_option(
			'forminator_geolocation_settings',
			array(
				'enable_geolocation' => '1',
			)
		);

		return $settings;
	}

	/**
	 * Add geolocation map
	 *
	 * @param string $html HTML.
	 * @param array  $field Field settings.
	 * @return string
	 */
	public static function add_geolocation_map( $html, $field ) {
		if ( ! empty( $field['auto_suggest'] ) && 'enable' === $field['auto_suggest'] ) {
			$html = preg_replace( '/type\="text"/', 'type="text" data-forminator_autosuggest="true"', $html, 1 );
			self::load_scripts();
		}

		if ( ! empty( $field['show_map'] ) && 'show' === $field['show_map'] ) {
			$height = '300';
			$width  = '';
			if ( ! empty( $field['map_size'] ) && 'custom' === $field['map_size'] ) {
				if ( ! empty( $field['map_height'] ) && 0 < intval( $field['map_height'] ) ) {
					$height = $field['map_height'];
				}
				if ( ! empty( $field['map_width'] ) && 0 < intval( $field['map_width'] ) ) {
					$width = $field['map_width'];
				}
			}
			$default   = self::get_default_map_settings( $field );
			$map_id    = self::get_map_id();
			$map_html  = '<div class="forminator-row forminator-geolocation-map-wrapper">';
			$map_html .= '<div class="forminator-col">';
			$map_html .= '<div id="' . esc_attr( $field['element_id'] . '_geolocation_map' ) . '"'
				. ' class="forminator-geolocation-map"'
				. ' data-lat="' . esc_attr( $default['latitude'] ) . '"'
				. ' data-lng="' . esc_attr( $default['longitude'] ) . '"'
				. ' data-zoom="' . esc_attr( $default['zoom'] ) . '"'
				. ' data-height="' . intval( $height ) . '"'
				. ' data-width="' . intval( $width ) . '"'
				. ' data-maptypecontrol="' . esc_attr( $default['mapTypeControl'] ) . '"'
				. ' data-maptype="' . esc_attr( $default['mapTypeId'] ) . '"'
				. ( $default['styles'] ? ' data-styles="' . esc_attr( wp_json_encode( $default['styles'] ) ) . '"' : '' )
				. ' data-mapid="' . esc_attr( $map_id ) . '"'
				. ' data-styled_type="' . esc_attr( $default['styled_type'] ) . '"'
			. '></div>';
			$map_html .= '</div>';
			$map_html .= '</div>';

			if ( ! empty( $field['map_placement'] ) && 'before' === $field['map_placement'] ) {
				$html = $map_html . $html;
			} else {
				$html .= $map_html;
			}

			self::load_scripts();
		}

		return $html;
	}

	/**
	 * Render Geolocation field
	 *
	 * @param string $html - the button html.
	 * @param int    $form_id - the current form id.
	 * @param int    $post_id - the current post id.
	 * @param string $nonce - the nonce field.
	 * @return string $html
	 */
	public static function render_geolocation_field( $html, $form_id, $post_id, $nonce ) {
		// Get the Forminator form object.
		$form = Forminator_Form_Model::model()->load( $form_id );

		if ( $form && ! empty( $form->settings['geolocation_field'] ) ) {
			$html .= '<input id="forminator_current_geolocation_' . esc_attr( $form_id ) . '" type="hidden" name="forminator_current_location" value="" autocomplete="off">';	 	 	 	 	  	 	 		 			 	
			$html .= '<input id="forminator_current_address_' . esc_attr( $form_id ) . '" type="hidden" name="forminator_current_address" value="" autocomplete="off">';
			$html .= '<input type="hidden" name="forminator_current_location_error" value="" autocomplete="off">';

			self::load_scripts();
			add_filter(
				'forminator_enqueue_form_script',
				function( $script, $is_preview, $is_ajax_load ) {
					if ( ! $is_ajax_load ) {
						wp_enqueue_script(
							'forminator-geolocation-current-script',
							trailingslashit( plugin_dir_url( dirname( __FILE__ ) ) ) . 'build/front/geolocation-current.min.js',
							array(),
							FORMINATOR_GEOLOCATION_ADDON,
							true
						);

					} else {
						$current_location = file_get_contents( dirname( dirname( __FILE__ ) ) . '/build/front/geolocation-current.min.js' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

						$script .= "<script type=\"text/javascript\" id=\"forminator-geolocation-current-script\">$current_location</script>";
					}

					return $script;
				},
				10,
				3
			);

		}

		return $html;
	}

}

Forminator_Geolocation_Frontend::get_instance();