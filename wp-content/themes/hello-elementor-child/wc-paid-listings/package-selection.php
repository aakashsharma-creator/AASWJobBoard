<?php
/**
 * Template for choosing a package during the Job Listing submission.
 *
 * This template can be overridden by copying it to yourtheme/wc-paid-listings/package-selection.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager-resumes
 * @category    Template
 * @since       1.0.0
 * @version     2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( $packages || $user_packages ) :
	$checked = 1;
	?>
	<style>
	/* Hide the original radio */
	.hidden-radio {
		display: none;
	}

	/* Card container */
	.job-package-card {
		display: flex;
		flex-direction: column;
		justify-content: space-between;
		border: 2px solid #ddd;
		border-radius: 10px;
		padding: 20px;
		cursor: pointer;
		transition: all 0.3s ease;
		background: #fff;
		position: relative;
		height: 100%;
	}

	/* Active (checked) state */
	.hidden-radio:checked + .job-package-card {
		border-color: #cccccc !important;
		box-shadow: 0 0 0 3px rgba(0, 115, 170, 0.3);
	}

	/* Featured header */
	.job-package-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
	}

	.job-package-header.featured {
		background: #c11d3c;
		color: #fff;
		border-radius: 8px;
		padding: 10px;
	}

	.badge {
		background: #fff;
		color: #c11d3c;
		padding: 3px 8px;
		border-radius: 6px;
		font-size: 12px;
		font-weight: bold;
	}

	/* Title & Price */
	.job-package-title {
		font-size: 1.2rem;
		font-weight: 600;
		margin: 0;
	}

	.job-package-price .price {
		font-size: 2rem;
		font-weight: bold;
		color: #004e74;
		margin-top: 10px;
		display: block;
	}

	/* Description */
	.job-package-description {
		font-size: 0.9rem;
		color: #555;
		margin-top: 10px;
		line-height: 1.5;
	}

	/* Choose button */
	.choose-btn {
		margin-top: 20px;
		background: #0073aa;
		color: #fff;
		border: none;
		border-radius: 6px;
		padding: 10px 15px;
		cursor: pointer;
		font-weight: 600;
		transition: background 0.3s;
	}

	.choose-btn:hover {
		background: #005a87;
	}

	/* Active style */
	.hidden-radio:checked + .job-package-card .choose-btn {
		background: #005a87;
	}

	/* Grid layout (optional for parent UL) */
	ul.job-packages {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
		gap: 20px;
	}
	</style>
	
	<ul class="job_packages">
		<?php if ( $user_packages ) : ?>
			<li class="package-section"><?php _e( 'Your Packages:', 'wp-job-manager-wc-paid-listings' ); ?></li>
			<?php foreach ( $user_packages as $key => $package ) :
				$package = wc_paid_listings_get_package( $package );
				
				$product_id = $package->get_product_id();
				$product = wc_get_product( $product_id );
				$price = $product ? $product->get_price() : '';
				$product_price = isset($price) ? $price : '0.00';
				?>
				<li class="user-job-package <?php echo $package->is_featured() ? 'user-job-package-featured' : ''; ?>">
					<input 
						type="radio" 
						<?php checked($checked, 1); ?> 
						name="job_package" 
						value="user-<?php echo $key; ?>" 
						id="user-package-<?php echo $package->get_id(); ?>" 
						class="hidden-radio"
					/>

					<label for="user-package-<?php echo $package->get_id(); ?>" class="job-package-card">
						<div class="job-package-header <?php echo $package->is_featured() ? 'featured' : ''; ?>">
							<h3 class="job-package-title"><?php echo esc_html($package->get_title()); ?></h3>
							<?php if ($package->is_featured()) : ?>
							<!--	<span class="badge">Popular</span> -->
							<?php endif; ?>
						</div>

						<div class="job-package-price">
							<span class="price"><?php echo $product->get_price_html(); ?></span>
						</div>

						<div class="job-package-description">
							<?php //echo wpautop( $product->get_short_description() ); ?>
							<?php
							$featured_marking = $package->is_featured() ? __('featured', 'wp-job-manager-wc-paid-listings') : '';
							if ($package->get_limit()) {
								$package_description = _n(
									'%1$s %2$s job posted out of %3$d',
									'%1$s %2$s jobs posted out of %3$d',
									$package->get_count(),
									'wp-job-manager-wc-paid-listings'
								);
								printf($package_description, $package->get_count(), $featured_marking, $package->get_limit());
							} else {
								$package_description = _n(
									'%1$s %2$s job posted',
									'%1$s %2$s jobs posted',
									$package->get_count(),
									'wp-job-manager-wc-paid-listings'
								);
								printf($package_description, $package->get_count(), $featured_marking);
							}

							if ($package->get_duration()) {
								printf(
									', ' . _n('listed for %s day', 'listed for %s days', $package->get_duration(), 'wp-job-manager-wc-paid-listings'),
									$package->get_duration()
								);
							}
							$checked = 0;
							?>
						</div>

						<!--<div class="job-package-footer">
							<button type="button" class="choose-btn">Choose Package</button>
						</div>-->
					</label>
				</li>
			<?php endforeach; ?>
		<?php endif; ?>
		
	</ul>
<?php else : ?>

	<p><?php _e( 'No packages found', 'wp-job-manager-wc-paid-listings' ); ?></p>

<?php endif; ?>
