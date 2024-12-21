<?php

/**
 * @package   Barn2\barn2-settings-api
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API;

use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Plugin\Plugin;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Plugin\Premium_Plugin;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Plugin\Licensed_Plugin;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Fields\License;
use WP_REST_Response;
/**
 * REST API class.
 */
class Rest_Api implements Rest_Api_Interface
{
    const API_NAMESPACE = 'barn2-settings-api/v1';
    /**
     * The plugin instance.
     *
     * @var Plugin
     */
    protected $plugin;
    /**
     * The settings manager.
     *
     * @var Settings_Manager
     */
    protected $manager;
    /**
     * Constructor.
     *
     * @param Plugin $plugin The plugin instance.
     * @param Settings_Manager $settings_manager The settings manager.
     */
    public function __construct(Plugin $plugin, Settings_Manager $settings_manager)
    {
        $this->plugin = $plugin;
        $this->manager = $settings_manager;
    }
    /**
     * Get the plugin instance.
     *
     * @return Plugin
     */
    public function get_plugin() : Plugin
    {
        return $this->plugin;
    }
    /**
     * Get the api namespace for the steps.
     *
     * @return string
     */
    public function get_api_namespace() : string
    {
        return self::API_NAMESPACE . '/' . $this->get_plugin()->get_slug();
    }
    /**
     * Check if a given request has admin access.
     *
     * @param  \WP_REST_Request $request Full data about the request.
     * @return \WP_Error|bool
     */
    public function check_permissions(\WP_REST_Request $request)
    {
        return \wp_verify_nonce($request->get_header('x-wp-nonce'), 'wp_rest') && \current_user_can('manage_options');
    }
    /**
     * Register the REST API routes.
     *
     * @return void
     */
    public function register_routes() : void
    {
        \add_action('rest_api_init', [$this, 'register_api_routes']);
    }
    /**
     * Register the REST API routes.
     *
     * @return void
     */
    public function register_api_routes() : void
    {
        $namespace = $this->get_api_namespace();
        \register_rest_route($namespace, '/settings', ['methods' => 'GET', 'callback' => [$this, 'get_settings'], 'permission_callback' => [$this, 'check_permissions']]);
        \register_rest_route($namespace, '/settings', ['methods' => 'POST', 'callback' => [$this, 'save_settings'], 'permission_callback' => [$this, 'check_permissions']]);
        if ($this->get_plugin() instanceof Premium_Plugin) {
            $this->register_license_routes();
        }
        \register_rest_route($namespace, '/callable', ['methods' => 'POST', 'callback' => [$this, 'get_callable'], 'permission_callback' => [$this, 'check_permissions']]);
    }
    /**
     * Register the license REST API routes.
     *
     * @return void
     */
    private function register_license_routes() : void
    {
        $namespace = $this->get_api_namespace();
        \register_rest_route($namespace, '/license', ['methods' => 'GET', 'callback' => [$this, 'get_license'], 'permission_callback' => [$this, 'check_permissions']]);
        \register_rest_route($namespace, '/license', ['methods' => 'POST', 'callback' => [$this, 'save_license'], 'permission_callback' => [$this, 'check_permissions']]);
    }
    /**
     * Get the settings.
     *
     * @param  \WP_REST_Request $request Full data about the request.
     * @return \WP_REST_Response
     */
    public function get_settings(\WP_REST_Request $request) : \WP_REST_Response
    {
        return new WP_REST_Response([], 200);
    }
    /**
     * Save the settings.
     *
     * @param  \WP_REST_Request $request Full data about the request.
     * @return \WP_REST_Response
     */
    public function save_settings(\WP_REST_Request $request) : \WP_REST_Response
    {
        $option_name = $this->manager->get_helper()->get_option_name();
        $settings = $request->get_param('settings');
        $override = $request->get_param('override') ?? \false;
        $should_refresh_license = \false;
        if (!\is_array($settings)) {
            return new WP_REST_Response(['message' => __('Something went wrong. Please try again.', 'woocommerce-discount-manager')], 400);
        }
        // Handle the license change.
        $this->handle_license_change($settings, $override, $should_refresh_license);
        // Sanitize the settings.
        $sanitized_settings = [];
        foreach ($settings as $key => $value) {
            $field = $this->manager->get_field_by_name($key);
            if (!$field) {
                continue;
            }
            $sanitized_settings[$key] = $field->sanitize($value);
            // Now transform the value.
            if ($field instanceof Fields\Transformable) {
                $sanitized_settings[$key] = $field->transform($sanitized_settings[$key]);
            }
        }
        // Validate the settings.
        $has_validation = $this->manager->has_validation();
        if ($has_validation) {
            $validation_callback = $this->manager->get_validation();
            $validated = $validation_callback($sanitized_settings);
            if (!$validated) {
                return new WP_REST_Response(['message' => __('Something went wrong during validation. Please try again.', 'woocommerce-discount-manager')], 400);
            }
            if ($validated instanceof \WP_Error) {
                return new WP_REST_Response(['message' => $validated->get_error_message()], 400);
            }
        }
        /**
         * Filter: barn2_settings_api_{plugin_slug}_save_settings
         * Filter the settings before saving.
         *
         * @param array $sanitized_settings The settings after sanitization.
         * @param \WP_REST_Request $request Full data about the request.
         * @param Settings_Manager $manager The settings manager.
         * @param string $option_name The option name.
         * @return array
         */
        $settings = \apply_filters("barn2_settings_api_{$this->get_plugin()->get_slug()}_save_settings", $sanitized_settings, $request, $this->manager, $option_name);
        foreach ($settings as $key => $value) {
            $this->manager->get_helper()->update_option($key, $value);
        }
        /**
         * Action: barn2_settings_api_{plugin_slug}_settings_saved
         * Fires after the settings are saved.
         *
         * @param array $settings The settings.
         * @param \WP_REST_Request $request Full data about the request.
         * @param Settings_Manager $manager The settings manager.
         * @param string $option_name The option name.
         * @return void
         */
        \do_action("barn2_settings_api_{$this->get_plugin()->get_slug()}_settings_saved", $settings, $request, $this->manager, $option_name);
        return new WP_REST_Response(['settings' => $settings, 'should_refresh_license' => $should_refresh_license, 'success' => \true], 200);
    }
    /**
     * Get the license.
     *
     * @param  \WP_REST_Request $request Full data about the request.
     * @return \WP_REST_Response
     */
    public function get_license(\WP_REST_Request $request) : \WP_REST_Response
    {
        /** @var Licensed_Plugin $license_handler */
        $license_handler = $this->get_plugin()->get_license();
        $license_data = ['status' => $license_handler->get_status(), 'exists' => $license_handler->exists(), 'key' => $license_handler->get_license_key(), 'status_help_text' => $license_handler->get_status_help_text(), 'error_message' => $license_handler->get_error_message(), 'product_id' => $this->get_plugin()->get_id(), 'overridden' => $license_handler->is_license_overridden()];
        return new WP_REST_Response($license_data, 200);
    }
    /**
     * Save the license.
     *
     * @param  \WP_REST_Request $request Full data about the request.
     * @return \WP_REST_Response
     */
    public function save_license(\WP_REST_Request $request) : \WP_REST_Response
    {
        /** @var Licensed_Plugin $license_handler */
        $license_handler = $this->get_plugin()->get_license();
        $action = $request->get_param('action') ?? 'check';
        $key = $request->get_param('key') ?? '';
        $override = $request->get_param('override') ?? \false;
        if ('activate' === $action) {
            if ($override) {
                $license_handler->override($key, 'active');
            } else {
                $license_handler->activate($key);
            }
        } elseif ('deactivate' === $action) {
            $license_handler->deactivate();
        } else {
            $license_handler->refresh();
        }
        $updated_license_data = ['status' => $license_handler->get_status(), 'exists' => $license_handler->exists(), 'key' => $license_handler->get_license_key(), 'status_help_text' => $license_handler->get_status_help_text(), 'error_message' => $license_handler->get_error_message(), 'product_id' => $this->get_plugin()->get_id(), 'overridden' => $license_handler->is_license_overridden()];
        return new WP_REST_Response($updated_license_data, 200);
    }
    /**
     * Get the callable.
     * This is used to dynamically populate fields.
     *
     * @param  \WP_REST_Request $request Full data about the request.
     * @return \WP_REST_Response
     */
    public function get_callable(\WP_REST_Request $request) : \WP_REST_Response
    {
        $field = $request->get_param('field');
        $value = $request->get_param('value');
        $field_name = $field['name'];
        $field_instance = $this->manager->get_field_by_name($field_name);
        $callable = $field_instance->get_callable();
        if (!$callable) {
            return new WP_REST_Response([], 200);
        }
        $return = $callable($value);
        return new WP_REST_Response($return, 200);
    }
    /**
     * Determine if the license has changed while submitting the settings.
     * If so, handle the license change. If no license change, remove the license key from the settings.
     *
     * @param array $all_settings
     * @param bool $override
     * @param bool $should_refresh_license
     * @return void
     */
    private function handle_license_change(array &$all_settings, bool $override, bool &$should_refresh_license) : void
    {
        $license_field = $this->manager->get_license_field();
        if (!$license_field instanceof License) {
            return;
        }
        $license_field_name = $license_field->get_name();
        // If the plugin is not licensed, return.
        if (!$this->get_plugin() instanceof Licensed_Plugin) {
            return;
        }
        $license_handler = $this->get_plugin()->get_license();
        $submitted_key = $all_settings[$license_field_name] ?? '';
        // Remove it from the settings.
        unset($all_settings[$license_field_name]);
        if ($override) {
            $license_handler->override($submitted_key, 'active');
            $should_refresh_license = \true;
            return;
        }
        // If the status is deactivated, return.
        if ('inactive' === $license_handler->get_status()) {
            // Activate the license.
            $license_handler->activate($submitted_key);
            $should_refresh_license = \true;
            return;
        }
        // If the license key has not changed, return.
        if ($submitted_key === $license_handler->get_license_key()) {
            return;
        }
        // If the license key is empty, deactivate the license.
        if (empty($submitted_key)) {
            $license_handler->deactivate();
            return;
        }
        // Activate the license.
        $license_handler->activate($submitted_key);
        $should_refresh_license = \true;
    }
}
