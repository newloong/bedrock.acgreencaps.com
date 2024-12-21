<?php

namespace Barn2\Plugin\Discount_Manager\Admin\Settings_Tab;

use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Admin\Settings_API_Helper;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Plugin\Licensed_Plugin;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Registerable;

/**
 * Registers the general settings tab.
 *
 * @package   Barn2/woocommerce-discount-manager
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 * @codeCoverageIgnore
 */
class General implements Registerable {

	const TAB_ID       = 'general';
	const OPTION_GROUP = 'wc_discount_manager_settings';
	const MENU_SLUG    = 'wdm_options';

	private $title;

	/**
	 * @var Licensed_Plugin
	 */
	private $plugin; // @phpstan-ignore-line

	/**
	 * Get things started.
	 *
	 * @param Licensed_Plugin $plugin
	 */
	public function __construct( Licensed_Plugin $plugin ) {
		$this->plugin = $plugin;
		$this->title  = __( 'Settings', 'woocommerce-discount-manager' );
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {}

	/**
	 * Get the tab title.
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}
}
