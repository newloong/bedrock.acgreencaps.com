<?php

/**
 * @package   Barn2\barn2-settings-api
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API;

use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Plugin\Plugin;
/**
 * REST API interface.
 */
interface Rest_Api_Interface
{
    /**
     * Get the plugin instance.
     *
     * @return Plugin
     */
    public function get_plugin() : Plugin;
    /**
     * Get the api namespace.
     *
     * @return string
     */
    public function get_api_namespace() : string;
    /**
     * Check if a given request has admin access.
     *
     * @param  \WP_REST_Request $request Full data about the request.
     * @return \WP_Error|bool
     */
    public function check_permissions(\WP_REST_Request $request);
    /**
     * Hook into the REST API.
     */
    public function register_routes() : void;
    /**
     * Register the API routes.
     */
    public function register_api_routes() : void;
    /**
     * Get the settings.
     *
     * @param \WP_REST_Request $request The request.
     * @return \WP_REST_Response
     */
    public function get_settings(\WP_REST_Request $request) : \WP_REST_Response;
    /**
     * Save the settings.
     *
     * @param \WP_REST_Request $request The request.
     * @return \WP_REST_Response
     */
    public function save_settings(\WP_REST_Request $request) : \WP_REST_Response;
}
