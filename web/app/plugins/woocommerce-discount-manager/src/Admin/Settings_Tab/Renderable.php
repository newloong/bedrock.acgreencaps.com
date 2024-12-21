<?php

namespace Barn2\Plugin\Discount_Manager\Admin\Settings_Tab;

/**
 * Defines the interface for a settings tab that can be rendered.
 *
 * @package   Barn2/woocommerce-discount-manager
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
interface Renderable {
	/**
	 * Output the settings tab.
	 * This should be used to output the HTML for the settings tab.
	 *
	 * @return void
	 */
	public function output();
}
