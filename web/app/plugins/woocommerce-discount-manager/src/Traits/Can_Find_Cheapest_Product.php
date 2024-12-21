<?php

namespace Barn2\Plugin\Discount_Manager\Traits;

use Barn2\Plugin\Discount_Manager\Products;

/**
 * Trait for finding the cheapest nth product.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
trait Can_Find_Cheapest_Product {
	/**
	 * This function will find the cheapest nth product based on numeric value of the
	 * $n_product parameter.
	 *
	 * $n_product = 1 will return the cheapest product.
	 * $n_product = 2 will return the second cheapest product.
	 * $n_product = 3 will return the third cheapest product.
	 * etc...
	 *
	 * @param array $relevant_products The relevant products.
	 * @param int $nth The nth cheapest product to find.
	 * @return \WC_Product|\WC_Order_Item_Product The nth cheapest product.
	 */
	public function find_cheapest_nth_product( array $relevant_products, int $nth ) {
		// Sort the products by price.
		usort(
			$relevant_products,
			function( $a, $b ) {
				// If the products are order items, then we need to get the product from the order item.
				if ( $a instanceof \WC_Order_Item_Product ) {
					$a = $a->get_product();
				}

				if ( $b instanceof \WC_Order_Item_Product ) {
					$b = $b->get_product();
				}

				// If the products are arrays, then we need to get the product from the array.
				if ( is_array( $a ) ) {
					$a = $a['data'];
				}

				if ( is_array( $b ) ) {
					$b = $b['data'];
				}

				return Products::get_product_price( $a ) <=> Products::get_product_price( $b );
			}
		);

		// Return the nth cheapest product.
		return $relevant_products[ $nth - 1 ];
	}
}
