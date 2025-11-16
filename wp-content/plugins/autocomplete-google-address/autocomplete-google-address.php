<?php 
/*
Plugin Name: Autocomplete Google Address
Description: Adds Google Address Autocomplete functionality to WordPress forms.
Version: 3.0.8
Author: Md Nishath Khandakar
Author URI: https://devsupport.vercel.app/
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: ga-auto
Domain Path: /languages
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Freemius Integration
if ( ! function_exists( 'google_autocomplete' ) ) {
    // Create a helper function for easy SDK access.
    function google_autocomplete() {
        global $google_autocomplete;

        if ( ! isset( $google_autocomplete ) ) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $google_autocomplete = fs_dynamic_init( array(
                'id'                  => '6886',
                'slug'                => 'form-autocomplete-nish',
                'premium_slug'        => 'google-autocomplete-premium',
                'type'                => 'plugin',
                'public_key'          => 'pk_f939b69fc6977108e74fa9e7e3136',
                'is_premium'          => false,
                // If your plugin is a serviceware, set this option to false.
                'has_premium_version' => false,
                'has_addons'          => false,
                'has_paid_plans'      => true,
                'trial'               => array(
                    'days'               => 3,
                    'is_require_payment' => true,
                ),
                'has_affiliation'     => 'all',
                'menu'                => array(
                    'slug'           => 'google-autocomplete-plugin',
                    'first-path'     => 'admin.php?page=google-autocomplete-plugin',
                    'support'        => false,
                ),
            ) );
        }

        return $google_autocomplete;
    }

    // Init Freemius.
    google_autocomplete();
    // Signal that SDK was initiated.
    do_action( 'google_autocomplete_loaded' );
}
// Define constants
define('GAP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GAP_PLUGIN_URL', plugin_dir_url(__FILE__));

// // Include necessary files
// require_once GAP_PLUGIN_DIR . 'includes/class-rest-api.php';
require_once GAP_PLUGIN_DIR . 'includes/class-autocomplete-api.php';
require_once GAP_PLUGIN_DIR . 'includes/class-enqueue-scripts.php';




// Database Table Creation on Activation
register_activation_hook(__FILE__, 'gap_create_db_table');
function gap_create_db_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'gap_configs';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        config_name VARCHAR(255) NOT NULL,
        api_key TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

// // Freemius Uninstall Hook
google_autocomplete()->add_action('after_uninstall', 'google_autocomplete_uninstall_cleanup');

// Uninstall Cleanup Logic
function google_autocomplete_uninstall_cleanup() {
    global $wpdb;

    // Delete plugin settings
    delete_option('google_api_key');
    delete_option('gap_configs');
    delete_option('gap_language');
    delete_option('gap_clear_log');

    // Drop the `gap_configs` table
    $table_name = $wpdb->prefix . 'gap_configs';
    $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
}

// Render Admin Page
function gap_render_admin_page() {
    $page = isset($_GET['page']) && $_GET['page'] === 'google-autocomplete-settings' ? 'settings' : 'configurations';
    ?>
    <div id="root" class="wrap">
        <h1>Google Autocomplete <?php echo ucfirst($page); ?></h1>
    </div>
    <script>
        window.gapPageContext = "<?php echo esc_js($page); ?>";
    </script>
    <?php
}

// Add Admin Menu
add_action('admin_menu', function () {
    add_menu_page(
        'Google Autocomplete',
        'Autocomplete',
        'manage_options',
        'google-autocomplete-plugin',
        'gap_render_admin_page',
        'dashicons-location-alt',
        20 // Position in the menu
    );

    add_submenu_page(
        'google-autocomplete-plugin',
        'Autocomplete Settings',
        'Settings',
        'manage_options',
        'google-autocomplete-settings',
        'gap_render_admin_page'
    );
});
