<?php



if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Enqueue_Scripts {
    public function __construct() {
        // Hook into WordPress script enqueue actions
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);
    }

    /**
     * Enqueue admin scripts and styles.
     *
     * @param string $hook The current admin page hook.
     */
    public function enqueue_admin_scripts($hook) {
        // Determine the page context
        $pageContext = null;

        if ($hook === 'toplevel_page_google-autocomplete-plugin') {
            $pageContext = 'configurations';
        } elseif ($hook === 'autocomplete_page_google-autocomplete-settings') {
            $pageContext = 'settings';
        }

        // Bail if not a relevant page
        if (!$pageContext) {
            return;
        }

        // Enqueue Admin CSS
        // $css_file = GAP_PLUGIN_DIR . 'assets/admin.css';
        // wp_enqueue_style(
        //     'gap-admin-style',
        //     GAP_PLUGIN_URL . 'assets/admin.css',
        //     [],
        //     file_exists($css_file) ? filemtime($css_file) : null
        // );

        // Enqueue Admin JS
        $js_file = GAP_PLUGIN_DIR . 'assets/admin.js';
        wp_enqueue_script(
            'gap-admin-script',
            GAP_PLUGIN_URL . 'assets/admin.js',
            ['wp-element'], // Requires WordPress React library
            file_exists($js_file) ? filemtime($js_file) : null,
            true
        );

     
        $isPremium = google_autocomplete()->is__premium_only();
        // $isPremium = null;
        $isPlan = google_autocomplete()->is_plan('pro', true);

            add_action('admin_notices', function () {
            // $isPlan = google_autocomplete()->is_plan('pro', true);
            echo '<div class="notice notice-success"><a href="https://www.youtube.com/watch?v=NO1CFjuyWqo" target="_blank" style="font-size:20px;">Watch Tutorial </a><div style="font-size:20px;" id="discount-message"; margin-top:10px; font-size:18px; color:green;">
        Get Discount via Chat
      </div></div> ';
        });
        

        wp_localize_script('gap-admin-script', 'gapData', [
            'restUrl'     => esc_url(rest_url('gap/v1/')),
            'nonce'       => wp_create_nonce('wp_rest'),
            'pageContext' => $pageContext,
            'isPremium'   => $isPremium, // Pass premium status
            'isPlan'   => $isPlan, // Pass premium status
        ]);
        

        
        // Additional settings for Settings Page
        if ($pageContext === 'settings') {
            wp_localize_script('gap-admin-script', 'gapSettingsData', [
                'restUrl' => esc_url(rest_url('gap/v1/settings')),
                'nonce' => wp_create_nonce('wp_rest'),
            ]);
        }
    }

    /**
     * Enqueue frontend scripts.
     */
    public function enqueue_frontend_scripts() {
        if (is_admin()) return;

        // Check if Google API key is provided
        $apikey = get_option('google_api_key', '');
        $lang = get_option('gap_language', '');
        if (!empty($apikey)) {
            // Enqueue the Google Maps API script with `loading=async` and `callback`
            wp_enqueue_script(
                'google-maps',
                'https://maps.googleapis.com/maps/api/js?key=' . esc_attr($apikey) . '&libraries=places&language=' . esc_attr($lang) . '&loading=async&callback=initGoogleMaps',
                [],
                null, // No version for external scripts
                true
            );

            // Add the callback function in the footer
            add_action('wp_footer', function () {
                echo '<script>
                    if (typeof initGoogleMaps === "undefined") {
                        function initGoogleMaps() {
                            console.log("Google Maps API loaded successfully");
                        }
                    }
                </script>';
            });
        }

        // Enqueue the frontend script with Google Maps as a dependency
        $frontend_file = GAP_PLUGIN_DIR . 'frontend/dist/index.js';
        wp_enqueue_script(
            'gap-frontend-script',
            GAP_PLUGIN_URL . 'frontend/dist/index.js',
            !empty($apikey) ? ['google-maps'] : [],
            file_exists($frontend_file) ? filemtime($frontend_file) : null,
            true
        );
    
        // Pass configurations and Google API key to the frontend
        $isPremium = google_autocomplete()->is__premium_only();
        $isPlan = google_autocomplete()->is_plan('pro', true);

        wp_localize_script('gap-frontend-script', 'gapFrontendData', [
            'configs'      => get_option('gap_configs', []),
            'googleApiKey' => $apikey,
            'isPremium'    => $isPremium, // Pass premium status
            'isPlan'    => $isPlan, // Pass premium status
        ]);

    }
}

new Enqueue_Scripts();