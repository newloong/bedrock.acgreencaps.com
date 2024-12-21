<?php

namespace Barn2\Plugin\Discount_Manager\Types;

/**
 * Indicates that a discount type needs to be verified as applicable to the cart as a whole.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
interface Applicable {

	/**
	 * Determine if the discount is applicable to the cart as a whole.
	 *
	 * @param \WC_Cart $cart The cart object.
	 * @return boolean True if the discount is applicable to the cart as a whole.
	 */
	public function is_applicable_to_cart( \WC_Cart $cart ): bool;

	/**
	 * Determine if the discount is applicable to the order as a whole.
	 *
	 * @param \WC_Order $order The order object.
	 * @return boolean True if the discount is applicable to the order as a whole.
	 */
	public function is_applicable_to_order( \WC_Order $order ): bool;
}
