<?php



if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Autocomplete_API {
    public function __construct() {
        // Register REST API routes
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register REST API routes for configurations and settings.
     */
    public function register_routes() {
        register_rest_route('gap/v1', '/configs', [
            'methods' => 'GET',
            'callback' => [$this, 'get_configs'],
            'permission_callback' => [$this, 'check_permissions'],
        ]);

        register_rest_route('gap/v1', '/configs', [
            'methods' => 'POST',
            'callback' => [$this, 'save_config'],
            'permission_callback' => [$this, 'check_permissions'],
        ]);

        register_rest_route('gap/v1', '/configs/(?P<id>\d+)', [
            'methods' => 'DELETE',
            'callback' => [$this, 'delete_config'],
            'permission_callback' => [$this, 'check_permissions'],
        ]);

        register_rest_route('gap/v1', '/configs/(?P<id>\d+)', [
            'methods' => 'PUT',
            'callback' => [$this, 'update_config'],
            'permission_callback' => [$this, 'check_permissions'],
        ]);

        register_rest_route('gap/v1', '/settings', [
            'methods' => 'GET',
            'callback' => [$this, 'get_settings'],
            'permission_callback' => [$this, 'check_permissions'],
        ]);

        register_rest_route('gap/v1', '/settings', [
            'methods' => 'POST',
            'callback' => [$this, 'save_settings'],
            'permission_callback' => [$this, 'check_permissions'],
        ]);
    }

    public function get_configs() {
        $configs = get_option('gap_configs', []);
        return rest_ensure_response($configs);
    }

    public function save_config($request) {
        $configs = get_option('gap_configs', []);
        $data = $request->get_json_params();

        $data['config_name'] = isset($data['config_name']) ? sanitize_text_field($data['config_name']) : '';

        if (isset($data['id'])) {
            foreach ($configs as &$config) {
                if ($config['id'] == $data['id']) {
                    $config = array_merge($config, $data);
                    break;
                }
            }
        } else {
            $data['id'] = time();
            $configs[] = $data;
        }

        update_option('gap_configs', $configs);
        return rest_ensure_response(['success' => true, 'config' => $data]);
    }

    public function update_config($request) {
        $id = $request['id'];
        $data = $request->get_json_params();

        $configs = get_option('gap_configs', []);
        foreach ($configs as &$config) {
            if ($config['id'] == $id) {
                $config = array_merge($config, $data);
                break;
            }
        }

        update_option('gap_configs', $configs);
        return rest_ensure_response(['success' => true, 'config' => $data]);
    }

    public function delete_config($request) {
        $id = $request['id'];
        $configs = get_option('gap_configs', []);
        $configs = array_filter($configs, fn($config) => $config['id'] != $id);

        update_option('gap_configs', $configs);
        return rest_ensure_response(['success' => true]);
    }

    public function get_settings() {
        return rest_ensure_response([
            'googleApiKey' => get_option('google_api_key', ''),
            'language' => get_option('gap_language', ''),
            'clearLog' => get_option('gap_clear_log', false),
        ]);
    }

    public function save_settings($request) {
        $params = $request->get_json_params();
        update_option('google_api_key', sanitize_text_field($params['googleApiKey'] ?? ''));
        update_option('gap_language', sanitize_text_field($params['language'] ?? ''));
        update_option('gap_clear_log', (bool)($params['clearLog'] ?? false));
        return $this->get_settings();
    }

    public function check_permissions() {
        return current_user_can('manage_options');
    }
}
new Autocomplete_API();