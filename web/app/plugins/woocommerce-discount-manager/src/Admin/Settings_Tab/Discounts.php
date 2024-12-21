<?php

namespace Barn2\Plugin\Discount_Manager\Admin\Settings_Tab;

use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Plugin\Licensed_Plugin;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Registerable;
use Barn2\Plugin\Discount_Manager\Util;
use JsonSerializable;

use function Barn2\Plugin\Discount_Manager\wdm;

/**
 * Registers the discounts tab.
 *
 * @package   Barn2/woocommerce-discount-manager
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 * @codeCoverageIgnore
 */
class Discounts implements Registerable, JsonSerializable, Renderable {

	const TAB_ID    = 'discounts';
	const MENU_SLUG = 'wdm_options';

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
		$this->title  = __( 'Discounts', 'woocommerce-discount-manager' );
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Enqueue assets on the setting page.
	 *
	 * @param string $hook
	 * @return void
	 */
	public function enqueue_assets( $hook ): void {
		// Check that the $hook ends with "wdm_options".
		if ( ! Util::string_ends_with( $hook, 'wdm_options' ) ) {
			return;
		}

		wp_enqueue_script( 'wdm-settings-page' );
		wp_add_inline_script( 'wdm-settings-page', 'const WDM_Admin = ' . wp_json_encode( $this ), 'before' );
	}

	/**
	 * Register the settings.
	 */
	public function output() {
		print( '<div id="barn2-wdm-discounts-root"></div>' );
	}

	/**
	 * Get the tab title.
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Determine if WPML is installed.
	 *
	 * @return boolean
	 */
	private function is_wpml_installed() {
		return class_exists( 'SitePress' );
	}

	/**
	 * {@inheritdoc}
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return [
			'currency'         => Util::get_currency_data(),
			'types'            => wdm()->types(),
			'userRoles'        => Util::get_roles( true, true ),
			'contentLocations' => Util::convert_array_to_dropdown_options( Util::get_bulk_table_locations() ),
			'wpml'             => $this->is_wpml_installed(),
		];
	}
}
