<?php
/**
 * The template for displaying the header
 *
 * This is the template that displays all of the <head> section, opens the <body> tag and adds the site's header.
 *
 * @package HelloElementor
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$viewport_content = apply_filters( 'hello_elementor_viewport_content', 'width=device-width, initial-scale=1' );
$enable_skip_link = apply_filters( 'hello_elementor_enable_skip_link', true );
$skip_link_url = apply_filters( 'hello_elementor_skip_link_url', '#content' );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="<?php echo esc_attr( $viewport_content ); ?>">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>

	<style>
		.pac-container { background-color: #fff; position: absolute!important; z-index: 1000; top: 98px !important; left: 248px !important; border-radius: 2px; border-top: 1px solid #d9d9d9; font-family: Arial, sans-serif; -webkit-box-shadow: 0 2px 6px rgba(0, 0, 0, .3); box-shadow: 0 2px 6px rgba(0, 0, 0, .3); -webkit-box-sizing: border-box; box-sizing: border-box; overflow: hidden; }
	#address-1-street_address{
		position: relative!important;
	}
	</style>
</head>

<body <?php body_class(); ?>>

<?php wp_body_open(); ?>

<!-- Loader Section -->

<?php 
	if(is_page( array( 2270, 2335 ) )){ ?>
	<style>
	body {
	  margin: 0;
	  padding: 0;
	  height: 100vh;
	  overflow: hidden; /* prevent scroll during loading */
	  background-color: rgb(26 0 102 / 92%); /* Deep purple background */
	}
	body.loading {
	  overflow: hidden; 
	}
	.loader-overlay {
	  position: fixed;
	  top: 0;
	  left: 0;
	  width: 100%;
	  height: 100%;
	  background-color: rgb(26 0 102 / 92%); /* same as background for seamless look */
	  display: flex;
	  justify-content: center;
	  align-items: center;
	  z-index: 9999;
	}

	.loader-logo {
	  width: 80px;
	  height: 80px;
	  animation: spin 2s linear infinite;
	}

	@keyframes spin {
	  0% { transform: rotate(0deg); }
	  100% { transform: rotate(360deg); }
	}

	.fade-out {
	  opacity: 0;
	  transition: opacity 0.8s ease;
	  pointer-events: none;
	}
	</style>
	
	<div class="loader-overlay" id="loader" style="display:flex;">
	  <img src="<?php echo site_url(); ?>/wp-content/uploads/2025/10/loading-aasw-logo-1.jpg" alt="Loading..." class="loader-logo">
	</div> 

<?php } ?>

<?php if ( $enable_skip_link ) { ?>
<a class="skip-link screen-reader-text" href="<?php echo esc_url( $skip_link_url ); ?>"><?php echo esc_html__( 'Skip to content', 'hello-elementor' ); ?></a>
<?php } ?>

<?php
if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'header' ) ) {
	if ( hello_elementor_display_header_footer() ) {
		if ( did_action( 'elementor/loaded' ) && hello_header_footer_experiment_active() ) {
			get_template_part( 'template-parts/dynamic-header' );
		} else {
			get_template_part( 'template-parts/header' );
		}
	}
}
