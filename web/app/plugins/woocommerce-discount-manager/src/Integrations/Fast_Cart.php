<?php

namespace Barn2\Plugin\Discount_Manager\Integrations;

use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Registerable;

use function Barn2\Plugin\Discount_Manager\wdm;

/**
 * Integrates with Fast Cart.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Fast_Cart implements Registerable {

	/**
	 * @inheritdoc
	 */
	public function register() {
		if ( ! function_exists( 'Barn2\Plugin\WC_Fast_Cart\wfc' ) ) {
			return;
		}

		add_action( 'wfc_before_cart_table', [ $this, 'display_discount_notice' ] );
		add_action( 'wfc_cart_totals_before_order_total', [ $this, 'display_total_savings' ] );
	}

	/**
	 * Displays the discount notice in the cart.
	 *
	 * @return void
	 */
	public function display_discount_notice() {
		/** @var \Barn2\Plugin\Discount_Manager\Cart $cart_service */
		$cart_service = wdm()->get_service( 'cart' );
		$cart_service->display_cart_notice();
	}

	/**
	 * Displays the total savings in the cart.
	 *
	 * @return void
	 */
	public function display_total_savings(): void {
		/** @var \Barn2\Plugin\Discount_Manager\Cart $cart_service */
		$cart_service = wdm()->get_service( 'cart' );
		$cart_service->show_total_discount_cart_checkout();
	}
}
