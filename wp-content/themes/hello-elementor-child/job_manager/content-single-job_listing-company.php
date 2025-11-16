<?php
/**
 * Single view Company information box
 *
 * Hooked into single_job_listing_start priority 30
 *
 * This template can be overridden by copying it to yourtheme/job_manager/content-single-job_listing-company.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager
 * @category    Template
 * @since       1.14.0
 * @version     1.32.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! get_the_company_name() ) {
	return;
}
?>
<div class="company">
	
	<?php
		//get company Logo
		global $wpdb;
		$current_user = wp_get_current_user();
		$company_email = $current_user->user_email; // your target email

		$query = $wpdb->prepare("
			SELECT upload.meta_value 
			FROM {$wpdb->prefix}frmt_form_entry_meta AS email
			INNER JOIN {$wpdb->prefix}frmt_form_entry_meta AS upload
				ON email.entry_id = upload.entry_id
			WHERE email.meta_key = %s
			  AND email.meta_value = %s
			  AND upload.meta_key = %s
		", 'email-1', $company_email, 'upload-1');

		$result = $wpdb->get_var($query);

		if ( $result ) {
			//echo '<pre>';
			$data = maybe_unserialize( $result );
			$logourl = $data['file']['file_url'];
			//echo '</pre>';
		} else {
			$logourl = esc_url(site_url() . '/wp-content/plugins/wp-job-manager/assets/images/company.png');
		}
	?>
	<?php //the_company_logo(); ?>
	
	<img decoding="async" class="company_logo ss" src="<?php echo $logourl; ?>" alt="<?php the_company_name(); ?>">
	
	

	<div class="company_header">
		<p class="name">
			<?php if ( $website = get_the_company_website() ) : ?>
				<a class="website" href="<?php echo esc_url( $website ); ?>" rel="nofollow"><?php esc_html_e( 'Website', 'wp-job-manager' ); ?></a>
			<?php endif; ?>
			<?php the_company_twitter(); ?>
			<?php the_company_name( '<strong>', '</strong>' ); ?>
		</p>
		<?php the_company_tagline( '<p class="tagline">', '</p>' ); ?>
	</div>

	<?php the_company_video(); ?>
</div>
