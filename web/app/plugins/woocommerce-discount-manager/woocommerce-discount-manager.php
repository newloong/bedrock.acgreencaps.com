<?php
/**
 * The main plugin file for WooCommerce Discount Manager
 *
 * This file is included during the WordPress bootstrap process if the plugin is active.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce Discount Manager
 * Plugin URI:        https://barn2.com/wordpress-plugins/woocommerce-discount-manager/
 * Description:       Add advanced pricing rules and discounts to WooCommerce.
 * Version:           1.2.2
 * Author:            Barn2 Plugins
 * Author URI:        https://barn2.com
 * Update URI:        https://barn2.com/wordpress-plugins/woocommerce-discount-manager/
 * Text Domain:       woocommerce-discount-manager
 * Domain Path:       /languages
 * Requires at least: 6.1
 * Requires PHP:      7.4
 * Tested up to:      6.6.2
 * Requires Plugins:  woocommerce
 *
 * WC requires at least: 7.2
 * WC tested up to: 9.3.3
 *
 * Copyright:       Barn2 Media Ltd
 * License:         GNU General Public License v3.0
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Barn2\Plugin\Discount_Manager;

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const PLUGIN_FILE    = __FILE__;
const PLUGIN_VERSION = '1.2.2';

// Include autoloader.
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Helper function to access the shared plugin instance.
 *
 * @return Plugin
 */
function wdm() {
	return Plugin_Factory::create( PLUGIN_FILE, PLUGIN_VERSION );
}

wdm()->register();
