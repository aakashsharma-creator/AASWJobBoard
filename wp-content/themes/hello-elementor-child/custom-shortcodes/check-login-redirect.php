<?php
// check-login-redirect.php
if ( ! defined('ABSPATH') ) {
    exit; // Prevent direct access
}

// Check if user is not logged in
if ( ! is_user_logged_in() ) {
    wp_safe_redirect( home_url() );
    exit;
}
?>
