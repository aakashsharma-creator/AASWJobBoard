<?php
/**
 * Job listing preview when submitting job listings.
 *
 * This template can be overridden by copying it to yourtheme/job_manager/job-preview.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager
 * @category    Template
 * @version     1.41.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<form method="post" id="job_preview" action="<?php echo esc_url( $form->get_action() ); ?>">
	<?php
	/**
	 * Fires at the top of the preview job form.
	 *
	 * @since 1.32.2
	 */
	do_action( 'preview_job_form_start' );
	?>
	<div class="job_listing_preview_title">
		<?php
			// Get current logged-in user ID
			$current_user_id = get_current_user_id();
			$packages = wc_paid_listings_get_user_packages( $current_user_id );
			
			if ( empty( $packages ) ) {
				echo '<a class="button job-manager-button-submit-listing" href="'.get_the_permalink(2669).'">Choose a package →</a>';
			}else{
			?>
				<input type="submit" name="continue" id="job_preview_submit_button" class="button job-manager-button-submit-listing" value="<?php echo esc_attr( apply_filters( 'submit_job_step_preview_submit_text', __( 'Submit Listing', 'wp-job-manager' ) ) ); ?>" />
			<?php 
			} 
			?>
		<?php if ( ! WP_Job_Manager_Helper_Renewals::is_renew_action() ) : ?>
			<input type="submit" name="edit_job" class="button job-manager-button-edit-listing" value="<?php esc_attr_e( 'Edit listing', 'wp-job-manager' ); ?>" />
		<?php endif; ?>
		<h2><?php esc_html_e( 'Job Preview', 'wp-job-manager' ); ?></h2>
	</div>
	<div class="job_listing_preview single_job_listing">
		<h1><?php wpjm_the_job_title(); ?></h1>
		
		<?php get_job_manager_template_part( 'content-single', \WP_Job_Manager_Post_Types::PT_LISTING ); ?>
		
		<div class="extra-job-fields">
			<?php
			$job_id = $form->get_job_id();
			$meta   = get_post_meta( $job_id );

			// Map technical meta keys to friendly labels
			$friendly_labels = [
				'_job_type'             => 'Job Type',
				'_job_expires'          => 'Expiry Date',
				'_experience_level'     => 'Experience Level',
				'_application'          => 'Application Email/URL',
				'__company_website'     => 'Company Website',
				'__company_location'    => 'Company Address',
				'__company_contact'     => 'Company Contact no.',
				'__company_email'       => 'Company Email',
				'__company_description' => 'Company Description',
			];

			echo '<div class="job-all-meta table-responsive">';
			echo '<h3>Other Job Fields:</h3>';
			echo '<table class="widefat striped">';
			echo '<thead><tr><th>Field</th><th>Value</th></tr></thead><tbody>';

			// --- Categories ---
			$categories = wp_get_post_terms( $job_id, 'job_listing_category', ['fields' => 'names'] );
			if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
				echo '<tr><td><strong>Job Category</strong></td><td>' . esc_html( implode( ', ', $categories ) ) . '</td></tr>';
			}

			// --- Job Types ---
			$jobtypes = wp_get_post_terms( $job_id, 'job_listing_type', ['fields' => 'names'] );
			if ( ! empty( $jobtypes ) && ! is_wp_error( $jobtypes ) ) {
				echo '<tr><td><strong>Job Type</strong></td><td>' . esc_html( implode( ', ', $jobtypes ) ) . '</td></tr>';
			}

			// --- Check if company info should be shown ---
			$show_company_info = isset( $meta['_show_company_info'][0] ) ? $meta['_show_company_info'][0] : 'no';

			foreach ( $meta as $key => $values ) {
				// Skip hidden system fields unless they’re mapped
				if ( strpos( $key, '_' ) === 0 && ! array_key_exists( $key, $friendly_labels ) ) {
					continue;
				}

				// Skip company fields if _show_company_info != 'yes'
				if ( strpos( $key, '__company_' ) === 0 && $show_company_info !== 'yes' ) {
					continue;
				}

				$value = is_array( $values ) ? implode( ', ', $values ) : $values;
				$label = isset( $friendly_labels[ $key ] ) ? $friendly_labels[ $key ] : ucwords( str_replace( ['_', '-'], ' ', $key ) );

				if($label=='Expiry Date'){
					continue;
				}
				if($label=='How To Apply'){
					echo '<tr>';
					echo '<td><strong>' . esc_html( $label ) . '</strong></td>';
					echo '<td>' . wp_kses_post( $value ) . '</td>';
					echo '</tr>';
				}else{
					echo '<tr>';
					echo '<td><strong>' . esc_html( $label ) . '</strong></td>';
					echo '<td>' . esc_html( $value ) . '</td>';
					echo '</tr>';
				}
			}

			echo '</tbody></table>';
			echo '</div>';
			?>
		</div>



		<input type="hidden" name="job_id" value="<?php echo esc_attr( $form->get_job_id() ); ?>" />
		<input type="hidden" name="step" value="<?php echo esc_attr( $form->get_step() ); ?>" />
		<input type="hidden" name="job_manager_form" value="<?php echo esc_attr( $form->get_form_name() ); ?>" />
	</div>
	<?php
	/**
	 * Fires at the bottom of the preview job form.
	 *
	 * @since 1.32.2
	 */
	do_action( 'preview_job_form_end' );
	?>
</form>
