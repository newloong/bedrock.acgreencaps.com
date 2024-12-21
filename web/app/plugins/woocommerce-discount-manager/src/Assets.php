<?php

namespace Barn2\Plugin\Discount_Manager;

use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Registerable;

/**
 * Handles the loading of plugin assets.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Assets implements Registerable {

	/**
	 * @inheritdoc
	 */
	public function register(): void {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Enqueues the plugin's frontend scripts and styles.
	 *
	 * @return void
	 */
	public function enqueue_scripts(): void {
		wp_register_style(
			'wc-discount-manager',
			wdm()->get_dir_url() . 'assets/css/frontend/wdm-frontend.css',
			[],
			wdm()->get_version()
		);

		wp_enqueue_style( 'wc-discount-manager' );
	}
}
