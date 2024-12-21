<?php

namespace Barn2\Plugin\Discount_Manager\Traits;

use Barn2\Plugin\Discount_Manager\Entities\Discount;
use WC_Cart;
use WC_Order;

/**
 * Provides a method to retrieve the minimum required quantity for the discount to apply.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
trait Discount_With_Required_Qty {

	/**
	 * Retrieve the minimum required quantity for the discount to apply.
	 *
	 * @return integer
	 */
	public function get_min_required_qty(): int {
		return (int) $this->discount->settings()->get( 'required_qty' )->value();
	}

	/**
	 * Check whether the cart meets the minimum required quantity for the discount to apply.
	 *
	 * - If the discount is applicable to all products, the cart must contain the minimum required quantity of any product.
	 * - If the discount is applicable to specific products, the cart must contain the minimum required quantity of any of the selected products.
	 * - If the discount is applicable to specific categories, the cart must contain the minimum required quantity of any product in the selected categories.
	 *
	 * @param WC_Cart $cart
	 * @param Discount $discount
	 * @return boolean
	 */
	public function cart_meets_products_selection_and_quantity( WC_Cart $cart, Discount $discount ): bool {
		$applies_to_all_products        = $discount->is_applicable_to_all_products();
		$applies_to_specific_products   = $discount->is_applicable_to_specific_products();
		$applies_to_specific_categories = $discount->is_applicable_to_specific_categories();

		$min_required_qty = $this->get_min_required_qty();

		if ( $applies_to_all_products ) {
			$cart_qty = $cart->get_cart_contents_count();
			return $cart_qty >= $min_required_qty;
		}

		if ( $applies_to_specific_products ) {
			$selected_products = $discount->get_elegible_products();
			$cart_qty          = 0;

			foreach ( $cart->get_cart() as $cart_item ) {
				$product_id = $cart_item['product_id'];
				if ( in_array( $product_id, $selected_products ) ) {
					$cart_qty += $cart_item['quantity'];
				}
			}

			return $cart_qty >= $min_required_qty;
		}

		if ( $applies_to_specific_categories ) {
			$selected_categories = $discount->get_elegible_categories();
			$cart_qty            = 0;

			foreach ( $cart->get_cart() as $cart_item ) {
				$product_id         = $cart_item['product_id'];
				$product            = wc_get_product( $product_id );
				$product_categories = $product->get_category_ids();

				foreach ( $product_categories as $product_category ) {
					if ( in_array( $product_category, $selected_categories ) ) {
						$cart_qty += $cart_item['quantity'];
						break;
					}
				}
			}

			return $cart_qty >= $min_required_qty;
		}

		return false;
	}

	/**
	 * Check whether the order meets the minimum required quantity for the discount to apply.
	 *
	 * - If the discount is applicable to all products, the order must contain the minimum required quantity of any product.
	 * - If the discount is applicable to specific products, the order must contain the minimum required quantity of any of the selected products.
	 * - If the discount is applicable to specific categories, the order must contain the minimum required quantity of any product in the selected categories.
	 *
	 * @param WC_Order $order
	 * @param Discount $discount
	 * @return boolean
	 */
	public function order_meets_products_selection_and_quantity( WC_Order $order, Discount $discount ): bool {
		$applies_to_all_products        = $discount->is_applicable_to_all_products();
		$applies_to_specific_products   = $discount->is_applicable_to_specific_products();
		$applies_to_specific_categories = $discount->is_applicable_to_specific_categories();

		$min_required_qty = $this->get_min_required_qty();

		if ( $applies_to_all_products ) {
			$order_qty = $order->get_item_count();
			return $order_qty >= $min_required_qty;
		}

		if ( $applies_to_specific_products ) {
			$selected_products = $discount->get_elegible_products();
			$order_qty         = 0;

			foreach ( $order->get_items() as $order_item ) {
				$product_id = $order_item->get_product_id();
				if ( in_array( $product_id, $selected_products ) ) {
					$order_qty += $order_item->get_quantity();
				}
			}

			return $order_qty >= $min_required_qty;
		}

		if ( $applies_to_specific_categories ) {
			$selected_categories = $discount->get_elegible_categories();
			$order_qty           = 0;

			foreach ( $order->get_items() as $order_item ) {
				$product_id         = $order_item->get_product_id();
				$product            = wc_get_product( $product_id );
				$product_categories = $product->get_category_ids();

				foreach ( $product_categories as $product_category ) {
					if ( in_array( $product_category, $selected_categories ) ) {
						$order_qty += $order_item->get_quantity();
						break;
					}
				}
			}

			return $order_qty >= $min_required_qty;
		}

		return false;
	}

	/**
	 * Check if the cart has a product with the required quantity.
	 *
	 * @param array $products Array of products in the cart.
	 * @param boolean|int $custom_additional_qty Whether to use the custom additional quantity or not.
	 * @param boolean $skip_additional_qty Whether to skip the additional quantity or not.
	 * @return boolean True if the cart has a product with the required quantity, false otherwise.
	 */
	private function cart_has_product_with_required_qty( array $products, $custom_additional_qty = false, bool $skip_additional_qty = false ): bool {
		$min_required_qty = $this->get_min_required_qty();
		$additional_qty   = $custom_additional_qty ? absint( $custom_additional_qty ) : absint( $this->discount->settings()->get( 'additional_qty' )->value() );
		$required_qty     = $min_required_qty + $additional_qty;

		if ( $skip_additional_qty ) {
			$required_qty = $min_required_qty;
		}

		if ( $additional_qty > $min_required_qty ) {
			$required_qty = $min_required_qty;
		}

		foreach ( $products as $cart_item ) {
			$qty = $cart_item['quantity'];

			if ( $qty >= $required_qty ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if the order has a product with the required quantity.
	 *
	 * @param array $products Array of products in the order.
	 * @param boolean|int $custom_additional_qty Whether to use the custom additional quantity or not.
	 * @return boolean True if the order has a product with the required quantity, false otherwise.
	 */
	private function order_has_product_with_required_qty( array $products, $custom_additional_qty = false, bool $skip_additional_qty = false ): bool {
		$min_required_qty = $this->get_min_required_qty();
		$additional_qty   = $custom_additional_qty ? absint( $custom_additional_qty ) : absint( $this->discount->settings()->get( 'additional_qty' )->value() );
		$required_qty     = $min_required_qty + $additional_qty;

		if ( $skip_additional_qty ) {
			$required_qty = $min_required_qty;
		}

		if ( $additional_qty > $min_required_qty ) {
			$required_qty = $min_required_qty;
		}

		foreach ( $products as $order_item ) {
			$qty = $order_item->get_quantity();

			if ( $qty >= $required_qty ) {
				return true;
			}
		}

		return false;
	}
}
