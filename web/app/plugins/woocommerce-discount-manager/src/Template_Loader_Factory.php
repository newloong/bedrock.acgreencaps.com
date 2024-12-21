<?php

namespace Barn2\Plugin\Discount_Manager;

use Barn2\Plugin\Discount_Manager\Dependencies\Lib\WooCommerce\Templates;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Template_Loader;

/**
 * A factory for creating the shared template loader instance.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 *
 * @codeCoverageIgnore
 */
class Template_Loader_Factory {

	private static $template_loader = null;

	/**
	 * Get the shared template loader instance.
	 *
	 * @return Template_Loader The template loader.
	 */
	public static function create() {
		if ( null === self::$template_loader ) {
			self::$template_loader = new Templates( 'discounts', wdm()->get_dir_path() . 'templates/' );
		}
		return self::$template_loader;
	}
}
