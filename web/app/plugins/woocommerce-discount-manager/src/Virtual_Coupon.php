<?php

namespace Barn2\Plugin\Discount_Manager;

use Barn2\Plugin\Discount_Manager\Entities\Discount;
use WC_Coupon;
use WC_Order;

/**
 * Responsible for creating a virtual coupon based on a discount.
 * This is used to apply the discount to admin orders.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Virtual_Coupon {
	/**
	 * The discount that this coupon is based on.
	 *
	 * @var Discount
	 */
	public $discount;

	/**
	 * The coupon object.
	 *
	 * @var WC_Coupon
	 */
	public $coupon;

	/**
	 * The amount of the discount.
	 *
	 * @var float
	 */
	public $discount_amount = 0;

	/**
	 * The order that this coupon is being applied to.
	 *
	 * @var WC_Order|boolean
	 */
	protected $order = false;

	/**
	 * Constructor.
	 *
	 * @param Discount  $discount The discount that this coupon is based on.
	 * @param WC_Coupon $coupon   The coupon object.
	 * @param float     $discount_amount The amount of the discount.
	 */
	public function __construct( Discount $discount, WC_Coupon $coupon, float $discount_amount ) {
		$this->discount        = $discount;
		$this->coupon          = $coupon;
		$this->discount_amount = $discount_amount;
	}

	/**
	 * Get the discount that this coupon is based on.
	 *
	 * @return Discount
	 */
	public function get_discount(): Discount {
		return $this->discount;
	}

	/**
	 * Get the reduction amount.
	 *
	 * @return float
	 */
	public function get_amount(): float {
		return $this->discount_amount;
	}

	/**
	 * Set the order that this coupon is being applied to.
	 *
	 * @param WC_Order $order The order.
	 * @return self
	 */
	public function set_order( WC_Order $order ): self {
		$this->order = $order;

		return $this;
	}

	/**
	 * Get the order that this coupon is being applied to.
	 *
	 * @return WC_Order|boolean
	 */
	public function get_order() {
		return $this->order;
	}

	/**
	 * Setup and return the coupon object.
	 *
	 * @return WC_Coupon
	 */
	public function setup_coupon(): WC_Coupon {
		$args = [
			'discount_type' => 'fixed_cart',
			'amount'        => $this->get_amount(),
		];

		$applicable_to_specific_products   = $this->discount->is_applicable_to_specific_products();
		$applicable_to_specific_categories = $this->discount->is_applicable_to_specific_categories();

		if ( $applicable_to_specific_products || $applicable_to_specific_categories ) {
			$order_items = $this->discount->get_relevant_products( false, $this->get_order() );
			$product_ids = [];

			// Convert the order items to product IDs.
			foreach ( $order_items as $item ) {
				$product_ids[] = $item->get_product_id();
			}

			if ( ! empty( $product_ids ) ) {
				$args['product_ids']   = $product_ids;
				$args['discount_type'] = 'fixed_product';
			}
		}

		$args['discount_type'] = 'wdm';

		$this->coupon->read_manual_coupon(
			Util::get_coupon_identifier( $this->discount ),
			$args
		);

		return $this->coupon;
	}
}
