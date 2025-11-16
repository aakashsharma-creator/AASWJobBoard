<?php
get_header();

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly.
}
?>

<style>
footer{
display:none !important;
}
</style>
<?php 

// Redirect non-logged-in users to homepage
if (!is_user_logged_in()) {
    wp_redirect(home_url());
    exit;
}

// Get current user info
$current_user = wp_get_current_user();
$user_roles = (array) $current_user->roles; // Get user roles as array

?>

<style>
header.elementor.elementor-9.elementor-location-header {
  display: none;
}
</style>

<?php
// Check role and load Elementor template accordingly
if (in_array('candidate', $user_roles)) {
    echo do_shortcode('[elementor-template id="2793"]'); // Admin template
} else {
    echo do_shortcode('[elementor-template id="2796"]'); // Default / Guest template
}

get_footer();
?>
