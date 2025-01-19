<?php

namespace Barn2\Plugin\Discount_Manager\Reductions;

use Barn2\Plugin\Discount_Manager\Products;

use function Barn2\Plugin\Discount_Manager\wdm;

/**
 * Applies fixed pricing reductions on products/cart.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Fixed extends Reduction {

	/**
	 * Apply the reduction to the given product.
	 *
	 * @param \WC_Product $product
	 * @param array $cart_item Optional. The cart item.
	 */
	public function apply_reduction( \WC_Product &$product, array &$cart_item = [] ): void {
		$product_id = $product->get_parent_id() ?: $product->get_id();

		// If a sale price is set, use that instead of calculating it.
		if ( $this->has_sale_price() ) {
			$product->set_price( $this->get_sale_price() );
			$product->set_sale_price( $this->get_sale_price() );

			if ( ! empty( $cart_item ) ) {
				$this->set_cart_item_data( $cart_item, floatval( Products::get_product_price( $product ) ), $this->get_sale_price() );
			}

			wdm()->cache()->add_discounted_product( $product_id );

			if ( $this->has_discount() ) {
				wdm()->cache()->track_discount(
					$this->get_discount()->id(),
					$product_id
				);
			}

			return;
		}

		$price            = floatval( Products::get_product_price( $product ) );
		$discounted_price = $price - $this->get_amount();

		if ( $discounted_price < 0 ) {
			$discounted_price = 0;
		}

		$product->set_price( $discounted_price );
		$product->set_sale_price( $discounted_price );

		if ( ! empty( $cart_item ) ) {
			$this->set_cart_item_data( $cart_item, $price, $discounted_price );
		}

		wdm()->cache()->add_discounted_product( $product_id );

		if ( $this->has_discount() ) {
			wdm()->cache()->track_discount(
				$this->get_discount()->id(),
				$product_id
			);
		}
	}

	/**
	 * Apply the reduction to the given order item.
	 *
	 * @param \WC_Order_Item_Product $item
	 */
	public function apply_reduction_to_order_item( \WC_Order_Item_Product &$item ): void {
		$product          = $item->get_product();
		$product_id       = $product->get_parent_id() ?: $product->get_id();
		$product_quantity = $item->get_quantity();
		$price            = floatval( Products::get_product_price( $product ) );

		// If a sale price is set, use that instead of calculating it.
		if ( $this->has_sale_price() ) {
			if ( $product_quantity > 1 ) {
				$item->set_subtotal( $price * $product_quantity );
				$item->set_total( $this->get_sale_price() * $product_quantity );
			} else {
				$item->set_subtotal( $this->get_sale_price() );
				$item->set_total( $this->get_sale_price() );
			}
			wdm()->cache()->add_discounted_product( $product_id );

			if ( $this->has_discount() ) {
				wdm()->cache()->track_discount(
					$this->get_discount()->id(),
					$product_id
				);
			}
			return;
		}

		$discounted_price = $price - $this->get_amount();

		if ( $discounted_price < 0 ) {
			$discounted_price = 0;
		}

		if ( $product_quantity > 1 ) {
			$item->set_subtotal( $price * $product_quantity );
			$item->set_total( $discounted_price * $product_quantity );
		} else {
			$item->set_subtotal( $price );
			$item->set_total( $discounted_price );
		}

		wdm()->cache()->add_discounted_product( $product_id );

		if ( $this->has_discount() ) {
			wdm()->cache()->track_discount(
				$this->get_discount()->id(),
				$product_id
			);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function get_total_reduction_for_order( array $order_items ): float {
		$total_reduction = 0;
		foreach ( $order_items as $item ) {
			$product          = $item->get_product();
			$price            = floatval( Products::get_product_price( $product ) );
			$discounted_price = $price - $this->get_amount();
			if ( $discounted_price < 0 ) {
				$discounted_price = 0;
			}
			$total_reduction += ( $price - $discounted_price ) * $item->get_quantity();
		}
		return $total_reduction;
	}
}
