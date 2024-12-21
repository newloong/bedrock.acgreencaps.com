<?php

namespace Barn2\Plugin\Discount_Manager\Admin;

use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Plugin\Admin\Admin_Links;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Plugin\Plugin;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Registerable;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Util;
use Barn2\Plugin\Discount_Manager\Util as Discount_ManagerUtil;

/**
 * General Admin Functions
 *
 * @package   Barn2/woocommerce-discount-manager
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Admin_Controller implements Registerable {

	/**
	 * Instance of the plugin.
	 *
	 * @var Plugin
	 */
	private $plugin;

	private $license_setting;

	/**
	 * Constructor.
	 *
	 * @param Plugin $plugin
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin          = $plugin;
		$this->license_setting = $plugin->get_license_setting();
	}

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		$services = [
			'admin_links'   => new Admin_Links( $this->plugin ),
			'settings_page' => new Settings_Page( $this->plugin ),
		];

		Util::register_services( $services );

		// Load admin scripts.
		add_action( 'admin_enqueue_scripts', [ $this, 'register_assets' ], 5 );
	}

	/**
	 * Load admin assets.
	 *
	 * @param string $hook
	 */
	public function register_assets( $hook ): void {
		$screen = get_current_screen();

		wp_register_script(
			'wdm-settings-page',
			plugins_url( 'assets/js/admin/discounts-editor.js', $this->plugin->get_file() ),
			array_merge(
				[],
				Util::get_script_dependencies( $this->plugin, 'admin/discounts-editor.js' )['dependencies']
			),
			$this->plugin->get_version(),
			true
		);

		wp_register_style(
			'wdm-settings-page',
			plugins_url( 'assets/css/admin/wdm-settings-page.css', $this->plugin->get_file() ),
			[ 'wp-components', 'wc-components' ],
			$this->plugin->get_version()
		);

		if ( Discount_ManagerUtil::string_ends_with( $screen->id, 'wdm_options' ) ) {
			wp_enqueue_style( 'wdm-settings-page' );
		}

		wp_register_script(
			'wdm-order-admin',
			plugins_url( 'assets/js/admin/order-admin.js', $this->plugin->get_file() ),
			array_merge(
				[],
				Util::get_script_dependencies( $this->plugin, 'admin/order-admin.js' )['dependencies']
			),
			$this->plugin->get_version(),
			true
		);

		// Enqueue wdm-order-admin if screen id is shop_order.
		if ( $screen && 'shop_order' === $screen->id ) {
			wp_enqueue_script( 'wdm-order-admin' );
		}
	}
}
