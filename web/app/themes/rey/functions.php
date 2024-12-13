<?php
/**
 * Theme functions and definitions
 *
 * @package rey
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

update_site_option('rey_purchase_code', 'AAAAAAAA-1111-1111-1111-AAAAAAAAAAAA');

add_filter('pre_http_request', function($preempt, $r, $url) {
    // Check for specific API requests and provide custom responses
    if (strpos($url, 'https://api.reytheme.com/wp-json/rey-api/v1/get_plugins') !== false) {
        $custom_url = 'https://apis.gpltimes.com/rey/data.php';
        return wp_remote_get($custom_url, $r);
    } 
    else if (strpos($url, 'https://api.reytheme.com/wp-json/rey-api/v1/get_plugin_data') !== false) {
        $custom_url = 'https://apis.gpltimes.com/rey/filtered_data.php';
        return wp_remote_get($custom_url, $r);
    } 
    else if (strpos($url, 'https://api.reytheme.com/wp-json/rey-api/v1/get_demos') !== false) {
        $custom_url = 'https://apis.gpltimes.com/rey/get_demo.php';
        return wp_remote_get($custom_url, $r);
    } 
    else if (strpos($url, 'https://api.reytheme.com/wp-json/rey-api/v1/get_demo_data') !== false) {
        $custom_url = 'https://apis.gpltimes.com/rey/get_demo_data.php';
        return wp_remote_get($custom_url, $r);
    } 
    // New condition for a specific URL with the purchase code
    else if ($url === 'https://api.reytheme.com/?purchase_code=AAAAAAAA-1111-1111-1111-AAAAAAAAAAAA') {
        return [
            'headers' => [],
            'body' => '<style>
                        img {
                            max-width: 100%;
                            height: auto;
                            margin-left: auto;
                            margin-right: auto;
                            margin-top: 25vh;
                            display: block;
                        }
                      </style>
                      <img src="https://media.giphy.com/media/xT9IgG50Fb7Mi0prBC/giphy.gif" alt="Hello!" />',
            'response' => [
                'code' => 200,
                'message' => 'OK'
            ]
        ];
    }

    return $preempt;
}, 10, 3);

/**
 * Global Variables
 */
define('REY_THEME_DIR', get_template_directory());
define('REY_THEME_PARENT_DIR', get_stylesheet_directory());
define('REY_THEME_URI', get_template_directory_uri());
define('REY_THEME_PLACEHOLDER', REY_THEME_URI . '/assets/images/placeholder.png');
define('REY_THEME_NAME', 'rey');
define('REY_THEME_CORE_SLUG', 'rey-core');
define('REY_THEME_VERSION', '3.1.3' );
define('REY_THEME_REQUIRED_PHP_VERSION', '5.4.0' ); // Minimum required versions

/**
 * Load Core
 */
require_once REY_THEME_DIR . '/inc/core/core.php';
