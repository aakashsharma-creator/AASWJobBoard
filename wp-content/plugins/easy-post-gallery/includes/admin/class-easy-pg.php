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

class Easy_PG {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		// Add actions to hooks.
		add_action( 'add_meta_boxes', array( $this, 'easy_pg_add_meta_box' ) );
		add_action( 'save_post', array( $this, 'easy_pg_save_meta_box' ) );
	}

	/**
	 * Add gallery meta box to post edit screen.
	 *
	 * @since    1.0.0
	 */
	public function easy_pg_add_meta_box() {
		$easy_pg_settings = get_option( 'easy_pg_settings', array() );
		if ( ! empty( $easy_pg_settings ) ) {
			add_meta_box(
				'easy_pg_custom', // Meta box ID
				esc_html__( 'Easy Post Gallery', 'easy-post-gallery' ), // Title
				array( $this, 'easy_pg_metabox_callback' ), // Callback function
				$easy_pg_settings, // Post type
				'normal', // Context
				'core' // Priority
			);
		}
	}

	/**
	 * Callback function to render the gallery meta box.
	 *
	 * @since    1.0.0
	 */
	public function easy_pg_metabox_callback() {
		wp_nonce_field( basename( __FILE__ ), 'metabox_nonce' );
		global $post;
		$easy_pg_data = get_post_meta( $post->ID, 'easy_pg_data', true );
		?>
	
		<div id="gallery_wrapper">
			<div id="img_box_container">
				<?php
				if ( isset( $easy_pg_data['image_url'] ) ) {
					$image_count = count( $easy_pg_data['image_url'] );
					for ( $i = 0; $i < $image_count; $i++ ) {
						?>
					<div class="gallery_single_row">
						<div class="gallery_area image_container ">
							<img class="gallery_img_img change-image" src="<?php echo esc_url( $easy_pg_data['image_url'][$i] ); ?>" height="55" width="55" />
							<input type="hidden" class="meta_image_url" name="easy_pg_images[image_url][]" value="<?php echo esc_url( $easy_pg_data['image_url'][$i] ); ?>" />
						</div>
						<div class="gallery_area">
							<span class="button remove remove-image" title="<?php esc_attr_e('Remove', 'easy-post-gallery'); ?>" ><i class="fa fa-trash"></i></span>
						</div>
						<div class="clear" ></div> 
					</div>
						<?php
					}
				}
				?>
			</div>
			<div id="master_box">
				<div class="gallery_single_row">
					<div class="gallery_area image_container hidden-image-upload" >
						<input class="meta_image_url" value="" type="hidden" name="easy_pg_images[image_url][]" />
					</div> 
					<div class="gallery_area"> 
						<span class="button remove remove-image" title="<?php esc_attr_e('Remove', 'easy-post-gallery'); ?>" ><i class="fa fa-trash"></i></span>
					</div>
					<div class="clear"></div>
				</div>
			</div>
			<div id="add_gallery_single_row">
				<input class="button add add-new-image" type="button" value="+" title="<?php esc_attr_e('Add image', 'easy-post-gallery'); ?>"/>
			</div>
			<button type="button" class="button button-primary button-large alignright remove_all_media">
				<?php echo esc_html__( 'Remove All Media', 'easy-post-gallery' ); ?>
			</button>
		</div>
	
		<?php
	}

	/**
	 * Save meta box data.
	 *
	 * @param int $post_id The ID of the post being saved.
	 *
	 * @since    1.0.0
	 */
	public function easy_pg_save_meta_box( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
			
		}
		$is_autosave    = wp_is_post_autosave( $post_id );
		$is_revision    = wp_is_post_revision( $post_id );
		$is_valid_nonce = ( isset( $_POST['metabox_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['metabox_nonce'] ) ), basename( __FILE__ ) ) ) ? 'true' : 'false';
		if ( $is_autosave || $is_revision || ! $is_valid_nonce ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( isset( $_POST['easy_pg_images'] ) ) {
			if ( isset( $_POST['easy_pg_images']['image_url'] ) && is_array( $_POST['easy_pg_images']['image_url'] ) ) {
				$image_count  = count( $_POST['easy_pg_images']['image_url'] );
				$easy_pg_data = array();
				for ( $i = 0; $i < $image_count; $i++ ) {
					if ( isset( $_POST['easy_pg_images']['image_url'][ $i ] ) && $_POST['easy_pg_images']['image_url'][ $i ] !== '' ) {
						$easy_pg_data['image_url'][] = esc_url_raw( wp_unslash( $_POST['easy_pg_images']['image_url'][ $i ] ) );
					}
				}
				if ( ! empty( $easy_pg_data ) ) {
					update_post_meta( $post_id, 'easy_pg_data', $easy_pg_data );
				} else {
					delete_post_meta( $post_id, 'easy_pg_data' );
				}
			}
		} else {
			delete_post_meta( $post_id, 'easy_pg_data' );
		}
	}
}
