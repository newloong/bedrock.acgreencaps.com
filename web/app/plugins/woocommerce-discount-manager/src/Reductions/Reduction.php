<?php

namespace Barn2\Plugin\Discount_Manager\Reductions;

use WC_Order_Item_Product;

/**
 * Base reduction class that represents the pricing adjustments
 * that a discount can make.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
abstract class Reduction {

	/**
	 * The amount of the reduction.
	 *
	 * @var float
	 */
	protected $amount;

	/**
	 * Force a specific sale price instead of calculating it.
	 *
	 * @var float
	 */
	protected $sale_price;

	/**
	 * The discount responsible for the reduction.
	 *
	 * @var \Barn2\Plugin\Discount_Manager\Entities\Discount
	 */
	protected $discount;

	/**
	 * Reduction constructor.
	 *
	 * @param float $amount The amount of the reduction.
	 * @param float|null $sale_price Optional. Force a specific sale price instead of calculating it.
	 * @param mixed $discount Optional. The discount responsible for the reduction.
	 */
	public function __construct( float $amount, float $sale_price = null, $discount = null ) {
		$this->amount     = $amount;
		$this->sale_price = $sale_price;
		$this->discount   = $discount;
	}

	/**
	 * Get the amount of the reduction.
	 *
	 * @return float
	 */
	public function get_amount(): float {
		return $this->amount;
	}

	/**
	 * Set the amount of the reduction.
	 *
	 * @param float $amount
	 * @return self
	 */
	public function set_amount( float $amount ): Reduction {
		$this->amount = $amount;

		return $this;
	}

	/**
	 * Determine if the reduction has a forced sale price.
	 *
	 * @return boolean
	 */
	public function has_sale_price(): bool {
		return ! empty( $this->sale_price ) && is_numeric( $this->sale_price );
	}

	/**
	 * Set the forced sale price.
	 *
	 * @param float $sale_price
	 * @return self
	 */
	public function set_sale_price( float $sale_price ): Reduction {
		$this->sale_price = $sale_price;

		return $this;
	}

	/**
	 * Get the forced sale price.
	 *
	 * @return float
	 */
	public function get_sale_price(): float {
		return $this->sale_price;
	}

	/**
	 * Set the discount responsible for the reduction.
	 *
	 * @param \Barn2\Plugin\Discount_Manager\Entities\Discount $discount
	 * @return self
	 */
	public function set_discount( $discount ): Reduction {
		$this->discount = $discount;

		return $this;
	}

	/**
	 * Get the discount responsible for the reduction.
	 *
	 * @return \Barn2\Plugin\Discount_Manager\Entities\Discount|null
	 */
	public function get_discount() {
		return $this->discount;
	}

	/**
	 * Determine if the reduction has a discount entity.
	 *
	 * @return boolean
	 */
	public function has_discount(): bool {
		return ! empty( $this->discount );
	}

	/**
	 * Apply the reduction to the given product.
	 *
	 * @param \WC_Product $product
	 * @param array $cart_item Optional. The cart item.
	 */
	abstract public function apply_reduction( \WC_Product &$product, array &$cart_item = [] ): void;

	/**
	 * Apply the reduction to the given order item.
	 *
	 * @param \WC_Order_Item_Product $item
	 */
	abstract public function apply_reduction_to_order_item( \WC_Order_Item_Product &$item ): void;

	/**
	 * Set the cart item data. This is used to store the original and new prices of the cart item.
	 *
	 * @param array $cart_item The cart item.
	 * @param float $original_price The original price.
	 * @param float $new_price The new price.
	 * @return void
	 */
	public function set_cart_item_data( array &$cart_item, $original_price, $new_price ): void {
		/**
		 * Hook: wdm_before_set_cart_item_data.
		 * Fires before the cart item data is set.
		 *
		 * @param array $cart_item The cart item.
		 * @param float $original_price The original price.
		 * @param float $new_price The new price.
		 */
		do_action( 'wdm_before_set_cart_item_data', $cart_item, $original_price, $new_price );

		$item_key = $cart_item['key'];

		$cart_item['_wdm'] = [
			'original_price' => $original_price,
			'new_price'      => $new_price,
		];

		/**
		 * Hook: wdm_after_set_cart_item_data.
		 * Fires after the cart item data is set.
		 *
		 * @param array $cart_item The cart item.
		 * @param float $original_price The original price.
		 * @param float $new_price The new price.
		 */
		do_action( 'wdm_after_set_cart_item_data', $cart_item, $original_price, $new_price );

		// Update the cart item in the cart.
		WC()->cart->cart_contents[ $item_key ] = $cart_item;
	}

	/**
	 * Calculate the total reduction for the given order.
	 * The total reduction is the sum of all reductions applied to the items in the order.
	 *
	 * The reduction is then returned as a float.
	 *
	 * This is used to then generate a negative fee for the order.
	 *
	 * @param WC_Order_Item_Product[] $order_items
	 * @return float
	 */
	abstract public function get_total_reduction_for_order( array $order_items ): float;
}
