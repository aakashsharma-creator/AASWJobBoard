<?php
/**
 * Forminator_Geolocation_Admin class.
 *
 * @package Forminator Geolocation
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Geolocation_Admin
 */
class Forminator_Geolocation_Admin {
	/**
	 * Instance
	 *
	 * @var self|null
	 */
	private static $instance;

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
	 * Forminator_Geolocation_Admin constructor
	 */
	private function __construct() {
		// Global settings.
		add_filter( 'forminator_settings_sections', array( __CLASS__, 'add_settings_section' ) );
		add_action( 'forminator_settings_content', array( __CLASS__, 'global_settings_markup' ) );

		// Set default setting for new forms.
		add_filter( 'forminator_form_default_settings', array( __CLASS__, 'set_geolocation' ) );

		// Helper texts.
		add_filter( 'forminator_data', array( __CLASS__, 'add_geolocation_data' ) );
		add_filter( 'forminator_form_settings', array( __CLASS__, 'add_form_settings' ), 10, 2 );

		// AJAX calls.
		add_action( 'wp_ajax_forminator_geolocation_settings_modal', array( $this, 'geo_settings_modal' ) );
		add_action( 'wp_ajax_forminator_save_geolocation_settings_popup', array( $this, 'save_settings' ) );
		add_action( 'wp_ajax_forminator_geolocation_check_api', array( $this, 'check_saved_api_key' ) );

		// Save geolocation.
		add_filter( 'forminator_custom_form_submit_field_data', array( __CLASS__, 'add_custom_field' ), 10, 2 );
		add_filter( 'forminator_custom_form_submit_errors', array( __CLASS__, 'custom_form_submit_errors' ), 10, 3 );

		// Display geolocation field.
		add_filter( 'forminator_fields_mappers', array( __CLASS__, 'add_geolocation_to_fields_mappers' ), 10, 2 );
		add_filter( 'forminator_get_entry_field_value', array( __CLASS__, 'display_geolocation' ), 10, 6 );

		// Update widget on Reports page.
		add_filter( 'forminator_reports_geolocation_widget', array( __CLASS__, 'geolocation_widget' ), 10, 2 );
		// Update widget content on Reports page (AJAX).
		add_filter( 'forminator_report_data', array( __CLASS__, 'update_report_data' ), 10, 5 );
	}

	/**
	 * Update geolocation widget arguments according stored information
	 *
	 * @param array $args Old arguments for template.
	 * @param array $report_data Report data.
	 * @return array New arguments for template.
	 */
	public static function geolocation_widget( $args, $report_data ) {
		// Load styles unconditionally to accommodate scenarios where geolocation data may be retrieved via AJAX after a period change.
		wp_enqueue_style( 'forminator_geolocation_flags', 'https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.5.0/css/flag-icon.min.css', array(), '1.0' );

		$notice  = '';
		$form_id = $report_data['form_id'];

		$settings_form_url = admin_url( 'admin.php?page=forminator-cform-wizard&id=' . $form_id . '&gotosection=settings' );

		if ( ! self::is_geolocation_enabled( $form_id ) ) {
			$notice = sprintf(
				/* translators: 1. Open link tag. 2. Close link tag. */
				__( 'User’s Geolocation is disabled for this form. You can enable it in %1$sForm settings > User Geolocation%2$s.', 'forminator-geo' ),
				'<a href="' . esc_url( $settings_form_url ) . '" target="_blank">',
				'</a>'
			);
			$args['notice'] = $notice;
		} elseif ( ! Forminator_Geolocation_Frontend::get_valid_api_key() ) {
			$notice = sprintf(
				/* translators: 1. Open link tag. 2. Close link tag. */
				__( 'Failed to connect to the Google Maps API. Please ensure you have entered a valid API key in the %1$sSettings page%2$s.', 'forminator-geo' ),
				'<a href="' . esc_url( menu_page_url( 'forminator-settings', false ) . '&section=geolocation' ) . '" target="_blank">',
				'</a>'
			);
			$args['notice']      = $notice;
			$args['notice_type'] = 'error';
		} else {
			$content = self::get_widget_content( $form_id, $report_data['reports']['start_date'], $report_data['reports']['end_date'] );

			if ( $content ) {
				$args['notice']  = '';
				$args['content'] = $content;
			} else {
				$args['notice'] = __( 'No location data is available for this form yet. You can check back when your form has new submissions.', 'forminator-geo' );
			}
		}

		return $args;
	}

	/**
	 * Is Current Geolocation is enabled on form
	 *
	 * @param int $form_id Form ID.
	 * @return bool
	 */
	private static function is_geolocation_enabled( $form_id ) {
		$model = Forminator_Base_Form_Model::get_model( $form_id );
		return ! empty( $model->settings['geolocation_field'] );
	}


	/**
	 * Get Report geolocation widget content
	 *
	 * @param int    $form_id Form ID.
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return string html
	 */
	private static function get_widget_content( $form_id, $start_date, $end_date ) {
		$content  = '';
		$geo_data = self::get_geolocation_data( $form_id, $start_date, $end_date );

		if ( $geo_data ) {
			$content = self::widget_table( $geo_data );
		}

		return $content;
	}

	/**
	 * Get relevant report geolocation widget content
	 *
	 * @param array  $response Original response.
	 * @param int    $form_id Form ID.
	 * @param string $form_type Type.
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array
	 */
	public static function update_report_data( $response, $form_id, $form_type, $start_date, $end_date ) {
		if ( 'forminator_forms' === $form_type ) {
			$content = self::get_widget_content( $form_id, $start_date, $end_date );

			$response['geolocation'] = $content;
		}
		return $response;
	}

	/**
	 * Prepare line in Report widget
	 *
	 * @param float $count Count.
	 * @param float $percentage Percentage.
	 * @return string
	 */
	private static function percentage_format( $count, $percentage ) {
		$string = number_format( $count ) . '&nbsp;&nbsp;&nbsp;&nbsp;(' . esc_html( number_format( $percentage ) ) . '%)';
		$string = apply_filters( 'forminator_geolocation_report_widget_line_format', $string, $count, $percentage );

		return $string;
	}

	/**
	 * Sort countries by count, desc
	 *
	 * @param int $a First count.
	 * @param int $b Second count.
	 * @return int
	 */
	private static function sort_countries( $a, $b ) {
		return $b['all_data']['count'] - $a['all_data']['count'];
	}

	/**
	 * Sort states by count, desc
	 *
	 * @param int $a First count.
	 * @param int $b Second count.
	 * @return int
	 */
	private static function sort_states( $a, $b ) {
		return $b['count'] - $a['count'];
	}

	/**
	 * Prepare table for Report widget
	 *
	 * @param array $geo_data Geolocation information.
	 * @return string
	 */
	private static function widget_table( $geo_data ) {
		uasort( $geo_data, array( self::class, 'sort_countries' ) );
		ob_start();
		?>
		<table class="sui-table sui-table-flushed sui-accordion">
			<thead>
			<tr>
				<th><?php esc_html_e( 'Locations', 'forminator-geo' ); ?></th>
				<th width="50%" style="text-align: right;"><?php esc_html_e( 'Submissions', 'forminator-geo' ); ?></th>
			</tr>
			</thead>

			<tbody>
			<?php foreach ( $geo_data as $key => $info ) { ?>
				<tr class="sui-accordion-item">
					<td class="sui-table-item-title">
						<div class="fui-app--wrapper">
							<i class="flag-icon flag-icon-<?php echo esc_attr( strtolower( $key ) ); ?>"></i>
							<span style="margin-top: 2px;">&nbsp;&nbsp;<?php echo esc_html( $info['all_data']['country'] ); ?></span>
						</div>
					</td>
					<td width="50%" style="text-align: right;">
						<?php echo esc_html( self::percentage_format( $info['all_data']['count'], $info['all_data']['percentage'] ) ); ?>
						<span class="sui-accordion-open-indicator">
							<i class="sui-icon-chevron-down"></i>
						</span>
					</td>
				</tr>
				<tr class="sui-accordion-item-content">
					<td colspan="2">
						<div class="sui-box" tabindex="0">
							<div class="sui-box-header">
								<span><?php esc_html_e( 'State / Province', 'forminator-geo' ); ?></span>
							</div>
							<div class="sui-box-body forminator-geo-state-block">
								<?php unset( $info['all_data'] ); ?>
								<?php uasort( $info, array( self::class, 'sort_states' ) ); ?>
								<?php foreach ( $info as $state_name => $state_data ) { ?>
								<div class="sui-box-settings-slim-row sui-sm">
									<div class="sui-box-settings-col-1">
										<span class="sui-settings-label sui-sm"><?php echo esc_html( $state_name ); ?></span>
									</div>
									<div class="sui-box-settings-col-2">
										<span class="sui-description" style="text-align: right;">
											<?php echo esc_html( self::percentage_format( $state_data['count'], $state_data['percentage'] ) ); ?>
										</span>
									</div>
								</div>
								<?php } ?>
							</div>
						</div>
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
		<?php
		$html = ob_get_clean();
		$html = apply_filters( 'forminator_geolocation_report_widget_content', $html, $geo_data );

		return $html;
	}

	/**
	 * Get geolocation data for countries and states
	 *
	 * @global object $wpdb Global WP object.
	 * @param int    $form_id Form ID.
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array
	 */
	private static function get_geolocation_data( $form_id, $start_date, $end_date ) {
		global $wpdb;

		$entry_meta_table = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY_META );
		$entry_table      = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );
		$where_date       = '';
		if ( ! empty( $start_date ) && ! empty( $end_date ) ) {
			$where_date .= $wpdb->prepare( ' AND ( m.date_created >= %s AND m.date_created <= %s )', esc_sql( $start_date ), esc_sql( $end_date ) );
		}

		$sql = "SELECT m.`meta_value` FROM {$entry_meta_table} m LEFT JOIN {$entry_table} e ON(e.`entry_id` = m.`entry_id`) "
			. "WHERE e.`form_id` = %d AND m.`meta_key` = 'current_address' {$where_date}";

		$addresses = $wpdb->get_col( $wpdb->prepare( $sql, $form_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

		$prepared_data = self::prepare_widget_data( $addresses );

		return $prepared_data;
	}

	/**
	 * Calculate statistics for countries and states
	 *
	 * @param array $addresses Address values from DB.
	 * @return array
	 */
	private static function prepare_widget_data( $addresses ) {
		if ( ! $addresses ) {
			return array();
		}

		$total_elements = count( $addresses );
		$country_counts = array();
		$country_names  = array();
		$states         = array();
		$state_slug     = apply_filters( 'forminator_geolocation_widget_state_slug', 'state' );

		// Loop through the array and count entries from each country.
		foreach ( $addresses as $json_string ) {
			// Decode the JSON string into a PHP associative array.
			$data = json_decode( $json_string, true );

			// Get the country from the data.
			$country = $data['countryCode'];
			// Increment the count for this country or initialize it to 1.
			$country_counts[ $country ] = isset( $country_counts[ $country ] ) ? $country_counts[ $country ] + 1 : 1;

			// Get the state from the data.
			$state = $data[ $state_slug ];
			// Increment the count for this state or initialize it to 1.
			$states[ $country ][ $state ] = isset( $states[ $country ][ $state ] ) ? $states[ $country ][ $state ] + 1 : 1;
			$country_names[ $country ]    = $data['country'];
		}

		// Calculate percentages for each country.
		$info = array();

		foreach ( $country_counts as $country => $count ) {
			// Calculate the percentage for this country.
			$percentage = ( $count / $total_elements ) * 100;

			// Store the country and its percentage in the result array.
			$info[ $country ]['all_data'] = array(
				'count'      => $count,
				'percentage' => $percentage,
				'country'    => $country_names[ $country ],
			);

			// Calculate state percentage for this country.
			$total_states = array_sum( $states[ $country ] );
			foreach ( $states[ $country ] as $state => $count ) {
				$percentage = ( $count / $total_states ) * 100;

				$info[ $country ][ $state ] = array(
					'count'      => $count,
					'percentage' => $percentage,
				);
			}
		}

		return $info;
	}

	/**
	 * Add Settings Section.
	 *
	 * @param array $sections Existed sections.
	 * @return array
	 */
	public static function add_settings_section( $sections ) {
		$sections['geolocation'] = __( 'Geolocation', 'forminator-geo' );

		return $sections;
	}

	/**
	 * Display main feature description
	 */
	private static function description_text() {
		printf(
			/* translators: 1. Open link tag. 2. Close link tag. 3. Open link tag for map id. */
			esc_html__( 'Set up Geolocation to gather your users\' location and add auto-completion to address field(s). Find your %1$sGoogle Maps API key %2$s and %3$sMap ID%2$s.', 'forminator-geo' ),
			'<a href="https://wpmudev.com/docs/wpmu-dev-plugins/forminator/#find-google-maps-api" target="_blank">',
			'</a>',
			'<a href="https://wpmudev.com/docs/wpmu-dev-plugins/forminator/#generate-map-id" target="_blank">'
		);
	}

	/**
	 * Display map API description
	 */
	private static function map_api_description_text() {
		printf(
			/* translators: 1. Open link tag. 2. Close link tag. 3. Open link tag for map id. */
			esc_html__( 'Set up Geolocation to gather your users\' location and add auto-completion to address field(s). Find your %1$sGoogle Maps API key %2$s.', 'forminator-geo' ),
			'<a href="https://wpmudev.com/docs/wpmu-dev-plugins/forminator/#find-google-maps-api" target="_blank">',
			'<span class="sui-icon-open-new-window sui-sm" aria-hidden="true"></span></a>'
		);
	}

	/**
	 * Display Map Id description
	 */
	private static function map_id_description_text() {
		printf(
			/* translators: 1. Open link tag. 2. Close link tag. */
			esc_html__( 'Enter your Map ID to apply custom Cloud Map Styles. If left blank, the default Google Map style will be applied. %1$sLearn how to find your Map ID %2$s.', 'forminator-geo' ),
			'<a href="https://wpmudev.com/docs/wpmu-dev-plugins/forminator/#generate-map-id" target="_blank">',
			'<span class="sui-icon-open-new-window sui-sm" aria-hidden="true"></span></a>'
		);
	}

	/**
	 * Handle Geolocation settings
	 */
	public function geo_settings_modal() {
		// Validate nonce.
		forminator_validate_ajax( 'forminator_geolocation_settings_modal', false, 'forminator-addons' );

		$data = array();

		$settings           = Forminator_Geolocation_Frontend::get_settings();
		$is_connect_request = filter_input( INPUT_POST, 'connect', FILTER_VALIDATE_BOOLEAN );

		if ( $is_connect_request ) {
			$res = self::save_settings_to_db();

			if ( is_wp_error( $res ) ) {
				$settings              = self::get_new_settings();
				$settings['api_error'] = true;
			} else {
				$data['notification'] = array(
					'type'     => 'success',
					'text'     => sprintf(
						/* Translators: 1. Opening <strong> tag, 2. closing <strong> tag. */
						esc_html__( '%1$sGeolocation Add-on configuration%2$s successfully saved.', 'forminator-geo' ),
						'<strong>',
						'</strong>'
					),
					'duration' => '4000',
				);
			}
		}

		ob_start();
		?>
			<p class="sui-description" style="text-align: center;">
				<?php self::description_text(); ?>
			</p>

			<form class="sui-form-field" data-slug="geolocation">

				<div class="sui-form-field ">
					<?php self::api_key_option( $settings ); ?>
				</div>

				<div class="sui-form-field ">
					<?php self::map_id_option( $settings ); ?>
				</div>

				<div class="sui-form-field ">
					<?php self::enable_by_default_option( $settings ); ?>
				</div>

			</form>
		<?php
		$html = ob_get_clean();

		$data['html'] = $html;

		$data['buttons'] = array();

		$data['buttons']['connect']['markup'] = '<div>' .
												'<button class="sui-button  sui-button-blue forminator-connect-addon" type="button" data-nonce="' . wp_create_nonce( 'forminator_geolocation_settings_modal' ) . '">' .
												'<span class="sui-loading-text">' . esc_html__( 'Save', 'forminator-geo' ) . '</span>' .
												'<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>' .
												'</button>' .
												'</div>';

		wp_send_json_success( $data );
	}

	/**
	 * Add Geolocation data
	 *
	 * @param array $data Data.
	 * @return array
	 */
	public static function add_geolocation_data( $data ) {
		$data['geolocation'] = array(
			'nonce' => wp_create_nonce( 'forminator_geolocation_check_api' ),
		);

		return $data;
	}

	/**
	 * Save global settings
	 */
	public static function save_settings() {
		// Validate nonce.
		forminator_validate_ajax( 'forminator_save_geolocation_settings', false, 'forminator-settings' );

		$res = self::save_settings_to_db();

		if ( is_wp_error( $res ) ) {
			wp_send_json_error();
		}

		wp_send_json_success();
	}

	/**
	 * Get new settings from POST
	 *
	 * @return array
	 */
	private static function get_new_settings() {
		return array(
			'api_key'            => filter_input( INPUT_POST, 'api_key', FILTER_SANITIZE_SPECIAL_CHARS ),
			'map_id'             => filter_input( INPUT_POST, 'map_id', FILTER_SANITIZE_SPECIAL_CHARS ),
			'enable_geolocation' => filter_input( INPUT_POST, 'enable_geolocation', FILTER_VALIDATE_INT ),
		);
	}

	/**
	 * Save setting to database
	 *
	 * @return \WP_Error|boolean
	 */
	private static function save_settings_to_db() {

		$settings = self::get_new_settings();

		$valid = true;
		if ( '' !== $settings['api_key'] ) {
			$valid = Forminator_Geolocation_Frontend::check_api_key( $settings['api_key'] );
		}

		if ( ! $valid ) {
			return new WP_Error( 'forminator_geolocation_invalid_api', esc_html__( 'Google API key is invalid', 'forminator-geo' ) );
		}

		update_option( 'forminator_geolocation_settings', $settings );

		return true;
	}

	/**
	 * Check API
	 */
	public static function check_saved_api_key() {
		// Validate nonce.
		forminator_validate_ajax( 'forminator_geolocation_check_api', false, 'forminator' );

		$api_key = Forminator_Geolocation_Frontend::get_valid_api_key();

		if ( $api_key ) {
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
	}

	/**
	 * Display HTML markup for Global settings.
	 */
	public static function global_settings_markup() {
		$settings = Forminator_Geolocation_Frontend::get_settings();
		$section  = Forminator_Core::sanitize_text_field( 'section', 'dashboard' );
		$nonce    = wp_create_nonce( 'forminator_save_geolocation_settings' );
		?>

		<div class="sui-box" data-nav="geolocation" style="<?php echo esc_attr( 'geolocation' !== $section ? 'display: none;' : '' ); ?>">

			<div class="sui-box-header">
				<h2 class="sui-box-title"><?php esc_html_e( 'Geolocation', 'forminator-geo' ); ?></h2>
			</div>

			<form class="forminator-settings-save" action="">

				<div class="sui-box-body" id="sui-box-geolocation">

					<div class="sui-box-settings-row">

						<div class="sui-box-settings-col-1">

							<span class="sui-settings-label"><?php esc_html_e( 'Google Maps API', 'forminator-geo' ); ?></span>

							<span class="sui-description"><?php self::map_api_description_text(); ?></span>

						</div>

						<div class="sui-box-settings-col-2">

							<div class="sui-form-field">
								<?php self::api_key_option( $settings ); ?>
							</div>

						</div>

					</div>

					<div class="sui-box-settings-row">

						<div class="sui-box-settings-col-1">

							<span class="sui-settings-label"><?php esc_html_e( 'Map ID', 'forminator-geo' ); ?></span>

							<span class="sui-description"><?php self::map_id_description_text(); ?></span>

						</div>

						<div class="sui-box-settings-col-2">

							<div class="sui-form-field">
								<?php self::map_id_option( $settings ); ?>
							</div>

						</div>

					</div>

					<div class="sui-box-settings-row">

						<div class="sui-box-settings-col-1">

							<span class="sui-settings-label"><?php esc_html_e( 'Default Form Settings', 'forminator-geo' ); ?></span>

						</div>

						<div class="sui-box-settings-col-2">

							<div class="sui-form-field">
								<?php self::enable_by_default_option( $settings ); ?>
							</div>

						</div>
					</div>

				</div>

				<div class="sui-box-footer">

					<div class="sui-actions-right">

						<button
							class="sui-button sui-button-blue wpmudev-action-done"
							data-title="<?php esc_attr_e( 'Geolocation settings', 'forminator-geo' ); ?>"
							data-action="geolocation_settings"
							data-nonce="<?php echo esc_attr( $nonce ); ?>"
						>
							<span class="sui-loading-text"><?php esc_html_e( 'Save Settings', 'forminator-geo' ); ?></span>
							<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
						</button>

					</div>

				</div>

			</form>

		</div>
		<?php
	}

	/**
	 * Display HTML markup for Api key option.
	 *
	 * @param array $settings Saved settings.
	 */
	public static function api_key_option( $settings ) {
		$api_key = isset( $settings['api_key'] ) ? $settings['api_key'] : '';
		?>

		<label for="forminator-settings--place-ip"
			class="sui-label"><?php esc_html_e( 'Google Maps API', 'forminator-geo' ); ?></label>
		<input type="text"
			name="api_key"
			class="sui-form-control"
			placeholder="<?php esc_attr_e( 'Enter Google Maps API', 'forminator-geo' ); ?>"
			value="<?php echo esc_attr( $api_key ); ?>"
			id="forminator-settings--place-ip"
			/>
		<span class="sui-error-message<?php echo empty( $settings['api_error'] ) ? ' sui-hidden' : ''; ?>">
			<?php esc_html_e( 'Invalid Google Maps API Key.', 'forminator-geo' ); ?>
		</span>
		<?php
	}

	/**
	 * Load Map Id option
	 *
	 * @param mixed $settings Settings.
	 * @return void
	 */
	public static function map_id_option( $settings ) {
		$map_id = $settings['map_id'] ?? '';
		?>

		<label for="forminator-settings--map-id"
			class="sui-label"><?php esc_html_e( 'Map ID', 'forminator-geo' ); ?></label>
		<input type="text"
			name="map_id"
			class="sui-form-control"
			placeholder="<?php esc_attr_e( 'Enter Map ID', 'forminator-geo' ); ?>"
			value="<?php echo esc_attr( $map_id ); ?>"
			id="forminator-settings--map-id"
			/>
		<span class="sui-error-message<?php echo empty( $settings['api_error'] ) ? ' sui-hidden' : ''; ?>">
			<?php esc_html_e( 'Invalid Google Map ID.', 'forminator-geo' ); ?>
		</span>
		<?php
	}

	/**
	 * Display HTML markup for Enable by default option.
	 *
	 * @param array $settings Saved settings.
	 */
	public static function enable_by_default_option( $settings ) {
		$enable_geolocation = isset( $settings['enable_geolocation'] ) ? $settings['enable_geolocation'] : '';
		?>

		<label class="sui-toggle">

			<input type="checkbox"
					name="enable_geolocation"
					id="enable_geolocation"
					value="1"
				<?php checked( 1, $enable_geolocation ); ?>>
			<span class="sui-toggle-slider"></span>
			<span class="sui-toggle-label" for="enable_geolocation">
				<?php esc_html_e( 'Enable Geolocation for all new forms by default', 'forminator-geo' ); ?>
			</span>
			<span id="enable_geolocation-description" class="sui-description">
				<?php esc_html_e( 'You can disable this option for each form in the form settings.', 'forminator-geo' ); ?>
			</span>
		</label>
		<?php
	}

	/**
	 * Add custom field
	 *
	 * @param array $field_data_array Field data.
	 * @param int   $module_id Module ID.
	 * @return array
	 */
	public static function add_custom_field( $field_data_array, $module_id ) {
		if ( ! empty( Forminator_CForm_Front_Action::$module_settings['geolocation_field'] ) ) {
			if ( ! empty( Forminator_CForm_Front_Action::$prepared_data['forminator_current_location'] ) ) {
				$field_data_array[] = array(
					'name'  => 'geolocation',
					'value' => Forminator_CForm_Front_Action::$prepared_data['forminator_current_location'],
				);
			} elseif ( ! empty( Forminator_CForm_Front_Action::$prepared_data['forminator_current_location_error'] ) ) {
				$field_data_array[] = array(
					'name'  => 'geolocation_error',
					'value' => Forminator_CForm_Front_Action::$prepared_data['forminator_current_location_error'],
				);
			}
			if ( ! empty( Forminator_CForm_Front_Action::$prepared_data['forminator_current_address'] ) ) {
				$field_data_array[] = array(
					'name'  => 'current_address',
					'value' => Forminator_CForm_Front_Action::$prepared_data['forminator_current_address'],
				);
			}
		}

		return $field_data_array;
	}

	/**
	 * Get default map settings
	 *
	 * @return array
	 */
	public static function get_default_map_settings() {
		$defaults = Forminator_Geolocation_Frontend::get_map_defaults();

		$defaults['zoom'] = 8;

		/**
		 * Filter default map settings on Admin area
		 *
		 * @param array $settings Default settings.
		 */
		return apply_filters( 'forminator_geolocation_default_map_admin_settings', $defaults );
	}

	/**
	 * Retrieve the current address as a string
	 *
	 * @param object $entry Entry objct.
	 * @return srting
	 */
	private static function get_current_address_value( $entry ) {
		$current_address = $entry->get_meta( 'current_address' );
		$current_address = json_decode( $current_address );

		$info = '';
		if ( $current_address ) {
			$info = $current_address->city . ', ' . $current_address->state . ', ' . $current_address->country;

			/**
			 * Filter current geolocation address on Submissions page
			 *
			 * @param string $info Default markup.
			 * @param object $current_address Current address object.
			 */
			$info = apply_filters( 'forminator_geolocation_submission_current_location_info', $info, $current_address );
		}

		return $info;
	}

	/**
	 * Display geolocation field
	 *
	 * @param string  $value Field value.
	 * @param object  $entry Forminator_Form_Entry_Model object.
	 * @param array   $mapper Mapper property.
	 * @param string  $sub_meta_key Sub meta key.
	 * @param boolean $allow_html Allow html.
	 * @param int     $truncate Truncate.
	 * @return string
	 */
	public static function display_geolocation( $value, $entry, $mapper, $sub_meta_key, $allow_html, $truncate ) {
		if ( ! empty( $mapper['meta_key'] ) && 'geolocation' === $mapper['meta_key'] ) {
			$coordinates     = self::get_coordinates( $value );
			$current_address = self::get_current_address_value( $entry );
			$error           = $entry->get_meta( 'geolocation_error' );
			if ( $coordinates ) {
				$original_value               = $value;
				list( $latitude, $longitude ) = $coordinates;

				$default = self::get_default_map_settings();
				$id      = 'forminator_current_geolocation_' . $entry->entry_id;
				$value   = '<table class="sui-table"><tbody>';

				if ( $current_address ) {
					$value .= '<tr><td style="padding-top: 5px; padding-bottom: 5px;">
						<strong>' . esc_html__( 'User\'s Location', 'forminator-geo' ) . '</strong>
						&nbsp;&nbsp;&nbsp;
						<span>' . esc_html( $current_address ) . '</span>
					</td></tr>';
				}

				$map_id = Forminator_Geolocation_Frontend::get_map_id();
				$value .= '<tr><td style="padding-top: 5px; padding-bottom: 5px;">
					<strong>' . esc_html__( 'Lat/Long', 'forminator-geo' ) . '</strong>
					&nbsp;&nbsp;&nbsp;
					<span>
						<a href="https://www.google.com/maps/search/' . $original_value . '" target="_blank" rel="noopener noreferrer" title="' . esc_attr__( 'View Map', 'forminator-geo' ) . '">' . $original_value . '</a>
					</span>
				</td></tr>
				<tr><td style="padding: 0;">
					<div id="' . esc_attr( $id ) . '" class="forminator-geolocation-map forminator-submissions"'
						. ' data-height="300"'
						. ' data-lat="' . esc_attr( $latitude ) . '"'
						. ' data-lng="' . esc_attr( $longitude ) . '"'
						. ' data-zoom="' . esc_attr( $default['zoom'] ) . '"'
						. ' data-maptypecontrol="' . esc_attr( $default['mapTypeControl'] ) . '"'
						. ' data-maptype="' . esc_attr( $default['mapTypeId'] ) . '"'
						. ( $default['styles'] ? ' data-styles="' . esc_attr( wp_json_encode( $default['styles'] ) ) . '"' : '' )
						. ' data-mapid="' . esc_attr( $map_id ) . '"'
						. ' data-styled_type="' . esc_attr( $default['styled_type'] ) . '"'
					. '></div>
				</td></tr>

				<tr><td style="padding-top: 20px; padding-bottom: 20px;">
					<div
						class="sui-notice sui-active"
						style="display: block;"
						aria-live="assertive"
					>
						<div class="sui-notice-content">
							<div class="sui-notice-message">
								<span class="sui-notice-icon sui-icon-info" aria-hidden="true"></span>
									<p>'
									. esc_html__( 'User’s Geolocation is automatically fetched based on form’s submitter’s current location.', 'forminator-geo' )
								. '</p>
							</div>
						</div>
					</div>
				</td></tr>

				</tbody></table>';

				Forminator_Geolocation_Frontend::load_scripts( true );
			} elseif ( $current_address ) {
				$value = '<span class="sui-description">' . esc_html( $current_address ) . '</span>';
			} elseif ( $error ) {
				$value = '<div class="sui-form-field">
					<div
						class="sui-notice sui-active"
						style="display: block;"
						aria-live="assertive"
					>
						<div class="sui-notice-content">
							<div class="sui-notice-message">
								<span class="sui-notice-icon sui-icon-info" aria-hidden="true"></span>
									<p>'
									. esc_html__( 'Could not fetch user’s Geolocation.', 'forminator-geo' ) . ' ' . $error
								. '</p>
							</div>
						</div>
					</div>
				</div>';
			} else {

				if ( current_user_can( forminator_get_permission( 'forminator-settings' ) ) ) {
					$link = sprintf(
						/* translators: 1. Open link tag. 2. Close link tag. */
						esc_html__( 'User Geolocation data was anonymized per your %1$sSubmissions Settings%2$s.', 'forminator-geo' ),
						'<a href="' . esc_url( menu_page_url( 'forminator-settings', false ) . '&section=submissions' ) . '" target="_blank">',
						'</a>'
					);
				} else {
					$link = esc_html__( 'User Geolocation data was anonymized per your Submissions Settings.', 'forminator-geo' );
				}

				$value = '<div class="sui-form-field">
					<div
						class="sui-notice sui-active"
						style="display: block;"
						aria-live="assertive"
					>
						<div class="sui-notice-content">
							<div class="sui-notice-message">
								<span class="sui-notice-icon sui-icon-info" aria-hidden="true"></span>
								<p>' . $link . '</p>
							</div>
						</div>
					</div>
				</div>';
			}
		}

		return $value;
	}

	/**
	 * Parse location
	 *
	 * @param string $value Location.
	 * @return boolean|array
	 */
	public static function get_coordinates( $value ) {
		if ( ! empty( $value ) ) {
			$elements = explode( ' ', $value );
			if ( 2 === count( $elements ) ) {
				return array( $elements[0], $elements[1] );
			}
		}

		return false;
	}

	/**
	 * Add geolocation field into fields mappers
	 *
	 * @param array  $fields_mappers Fields mappers.
	 * @param object $model Forminator_Form_Model object.
	 * @return type
	 */
	public static function add_geolocation_to_fields_mappers( $fields_mappers, $model ) {
		if ( ! empty( $model->get_form_settings()['geolocation_field'] ) ) {
			$fields_mappers[] = array(
				'meta_key' => 'geolocation', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'label'    => esc_html__( 'User’s Geolocation', 'forminator-geo' ),
				'type'     => 'geolocation',
			);
		}

		return $fields_mappers;
	}

	/**
	 * Add geolocation settings.
	 *
	 * @param array  $settings Settings.
	 * @param object $obj Forminator_Form_Model object.
	 * @return type
	 */
	public static function add_form_settings( $settings, $obj ) {
		if ( empty( $settings['require_geolocation_field'] ) ) {
			$settings['require_geolocation_field'] = esc_html__( 'Oops! We need access to your location to submit the form. Please allow location access to continue.', 'forminator-geo' );
		}

		return $settings;
	}

	/**
	 * Set geolocation setting by default
	 *
	 * @param array $default_settings Default settings.
	 * @return array
	 */
	public static function set_geolocation( $default_settings ) {
		$settings = Forminator_Geolocation_Frontend::get_settings();
		if ( ! empty( $settings['enable_geolocation'] ) ) {
			$default_settings['geolocation_field'] = '1';
		}

		return $default_settings;
	}

	/**
	 * Required geolocation error
	 *
	 * @param array $submit_errors Errors array.
	 * @param int   $module_id Module ID.
	 * @param array $field_data_array Field data.
	 * @return boolean
	 */
	public static function custom_form_submit_errors( $submit_errors, $module_id, $field_data_array ) {
		$field_name    = 'forminator_current_location';
		$form_settings = Forminator_CForm_Front_Action::$module_settings;
		if ( ! empty( $form_settings['geolocation_field_required'] )
				&& ! empty( $form_settings['geolocation_field'] )
				&& empty( Forminator_CForm_Front_Action::$prepared_data[ $field_name ] ) ) {
			$submit_errors[][ $field_name ] = true;
			add_filter(
				'forminator_custom_form_invalid_form_message',
				function () {
					return Forminator_CForm_Front_Action::$module_settings['require_geolocation_field'];
				}
			);
		}

		return $submit_errors;
	}
}