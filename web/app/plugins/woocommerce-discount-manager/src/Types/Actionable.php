<?php

namespace Barn2\Plugin\Discount_Manager\Types;

use WC_Cart;
use WC_Order;

/**
 * Interface for discount types that have cart actions.
 * These are applied to the cart when the discount is applied
 * but before the totals are calculated.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
interface Actionable {

	/**
	 * Run the cart actions for the discount type.
	 *
	 * @param WC_Cart $cart
	 */
	public function run_cart_actions( WC_Cart &$cart ): void;

	/**
	 * Run the order actions for the discount type.
	 *
	 * @param WC_Order $order
	 */
	public function run_order_actions( WC_Order &$order ): void;

}
