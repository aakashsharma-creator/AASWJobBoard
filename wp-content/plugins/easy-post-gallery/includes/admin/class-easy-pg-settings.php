<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and provides functionality for the admin settings page.
 *
 * @package    Easy_PG
 * @subpackage Easy_PG/admin
 */

namespace Easy_PG\Admin;

use Easy_PG\Helper\Easy_PG_Loader;
use Easy_PG\Config\Easy_PG_Config;

class Easy_PG_Settings {

	/**
	 * The ID of this plugin.
	 *
	 * @var string $plugin_name The ID of this plugin.
	 */
	protected $plugin_name = Easy_PG_Config::PLUGIN_NAME;

	/**
	 * The version of this plugin.
	 *
	 * @var string $version The current version of this plugin.
	 */
	protected $version = Easy_PG_Config::PLUGIN_VERSION;

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @var Easy_PG_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->loader = new Easy_PG_Loader();
		$this->loader->run();
		add_action( 'admin_menu', array( $this, 'easy_pg_add_options_page' ) );
	}

	/**
	 * Add options page for Easy Post Galleries settings.
	 *
	 * @since 1.0.0
	 */
	public function easy_pg_add_options_page() {
		add_options_page(
			esc_html__('Easy Post Galleries', 'easy-post-gallery'),
			esc_html__('Easy Post Galleries', 'easy-post-gallery'),
			'manage_options',
			'easy_pg_option_settings',
			array( $this, 'easy_pg_option_callback' )
		);
	}

	/**
	 * Callback function for rendering the settings page.
	 *
	 * @since 1.0.0
	 */
	public function easy_pg_option_callback() {
		if ( isset( $_POST['submit_easy_pg_cpts'] ) ) {
	
			// Verify nonce.
			if ( ! isset( $_POST['easy_pg_settings_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['easy_pg_settings_nonce'] ) ), 'save_easy_pg_cpts' ) ) {
				die( esc_html__( 'Security check failed', 'easy-post-gallery' ) );
			}
	
			// Sanitize and update options.
			$selected_easy_pg_cpts = isset( $_POST['easy_pg_settings'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['easy_pg_settings'] ) ) : array();
			update_option( 'easy_pg_settings', $selected_easy_pg_cpts );
	
			// Display success message.
			echo '<div class="updated"><p>';
			esc_html_e( 'Settings saved.', 'easy-post-gallery' );
			echo '</p></div>';
		}
	
		$saved_easy_pg_settings = get_option( 'easy_pg_settings', array() );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Easy Post Gallery Settings', 'easy-post-gallery' ); ?></h1>
			<p><?php esc_html_e( 'Please choose the post types for displaying Easy Post Galleries.', 'easy-post-gallery' ); ?></p>
			<form method="post">
				<?php wp_nonce_field( 'save_easy_pg_cpts', 'easy_pg_settings_nonce' ); ?>
				<ul>
					<?php
					$easy_pg_posts = get_post_types(
						array(
							'public'  => true,
							'show_ui' => true,
						),
						'objects'
					);
	
					$exclude = array( 'attachment', 'elementor_library', 'e-landing-page' );
					foreach ( $easy_pg_posts as $easy_pg_posts_val ) {
						if ( ! in_array( $easy_pg_posts_val->name, $exclude ) ) {
							$checked = in_array( $easy_pg_posts_val->name, $saved_easy_pg_settings, true ) ? 'checked' : '';
							echo '<li><label><input type="checkbox" name="easy_pg_settings[]" value="' . esc_attr( $easy_pg_posts_val->name ) . '" ' . esc_attr( $checked ) . '>' . esc_html( $easy_pg_posts_val->label ) . '</label></li>';
						}
					}
					?>
				</ul>
				<input type="submit" name="submit_easy_pg_cpts" class="button-primary" value="<?php esc_attr_e( 'Save Settings', 'easy-post-gallery' ); ?>">
			</form>
		</div>
		<?php
	}	
}
