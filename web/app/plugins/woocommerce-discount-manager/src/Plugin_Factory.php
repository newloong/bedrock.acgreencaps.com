<?php

namespace Barn2\Plugin\Discount_Manager;

/**
 * Factory to create/return the shared plugin instance.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 * @codeCoverageIgnore
 */
class Plugin_Factory {

	private static $plugin = null;

	/**
	 * Create/return the shared plugin instance.
	 *
	 * @param string $file
	 * @param string $version
	 * @return Plugin
	 */
	public static function create( $file, $version ) {
		if ( null === self::$plugin ) {
			self::$plugin = new Plugin( $file, $version );
		}
		return self::$plugin;
	}
}
