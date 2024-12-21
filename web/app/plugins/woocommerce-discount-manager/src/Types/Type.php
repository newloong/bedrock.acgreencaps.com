<?php

namespace Barn2\Plugin\Discount_Manager\Types;

use Barn2\Plugin\Discount_Manager\Entities\Discount;
use Barn2\Plugin\Discount_Manager\Virtual_Coupon;
use WC_Cart;
use WC_Coupon;
use WC_Order;

use function Barn2\Plugin\Discount_Manager\wdm;

/**
 * Represents a discount type.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
abstract class Type {

	const ORDER = 0;

	/**
	 * The discount object.
	 *
	 * @var Discount
	 */
	protected $discount;

	/**
	 * Whether the discount type is processing a WooCommerce order.
	 *
	 * This is used so that we prevent applying discounts at a order line level
	 * due to the multitude of issues in how taxes are calculated in WooCommerce.
	 *
	 * @var bool
	 */
	protected $processing_order = false;

	/**
	 * The total discount that needs to be applied to the order.
	 *
	 * @var float
	 */
	protected $total_order_discount = 0;

	/**
	 * The total number of discounted products in the cart|order.
	 *
	 * This is used to track the number of discounted products in the cart|order
	 * but not all types will use this.
	 *
	 * Not all discount types need to track the number of discounted products.
	 *
	 * @var int
	 */
	protected $total_discounted_products = 0;

	/**
	 * Make a new discount type.
	 *
	 * @param Discount $discount The discount object.
	 * @return static The new discount type.
	 */
	public static function make( Discount $discount ) {
		$type = new static(); // @phpstan-ignore-line
		$type->set_discount( $discount );
		return $type;
	}

	/**
	 * Set the discount object.
	 *
	 * @param Discount $discount The discount object.
	 * @return self
	 */
	public function set_discount( Discount $discount ) {
		$this->discount = $discount;

		return $this;
	}

	/**
	 * Get the discount object.
	 *
	 * @return Discount
	 */
	public function get_discount(): Discount {
		return $this->discount;
	}

	/**
	 * Get the slug for this discount type.
	 *
	 * @return string
	 */
	abstract public static function get_slug(): string;

	/**
	 * Get the name for this discount type.
	 *
	 * @return string
	 */
	abstract public static function get_name(): string;

	/**
	 * Get the tooltip for this discount type.
	 *
	 * @return string
	 */
	abstract public static function get_tooltip(): string;

	/**
	 * Get the settings for this discount type.
	 *
	 * @return array
	 */
	abstract public static function get_settings(): array;

	/**
	 * Get the default settings values for this discount type.
	 *
	 * @return array
	 */
	abstract public static function get_default_settings_values(): array;

	/**
	 * Flag whether the discount type is being applied to a WooCommerce order.
	 *
	 * @param boolean $processing_order Whether the discount type is processing a WooCommerce order.
	 * @return self The discount type.
	 */
	public function set_proccesing_order( bool $processing_order ): self {
		$this->processing_order = $processing_order;

		return $this;
	}

	/**
	 * Return whether the discount type is being applied to a WooCommerce order.
	 *
	 * @return bool
	 */
	public function is_processing_order(): bool {
		return $this->processing_order;
	}

	/**
	 * Return the total discount that needs to be applied to the order.
	 *
	 * @return float
	 */
	public function get_total_order_discount(): float {
		return $this->total_order_discount;
	}

	/**
	 * Set the total discount that needs to be applied to the order.
	 *
	 * @param float $total_order_discount The total discount that needs to be applied to the order.
	 * @return self The discount type.
	 */
	public function set_total_order_discount( float $total_order_discount ): self {
		$this->total_order_discount = $total_order_discount;

		return $this;
	}

	/**
	 * Increase the total discount that needs to be applied to the order.
	 *
	 * @param float $amount The amount to increase the total discount by.
	 * @return self The discount type.
	 */
	public function increase_total_order_discount( float $amount ): self {
		$this->total_order_discount += $amount;

		return $this;
	}

	/**
	 * Decrease the total discount that needs to be applied to the order.
	 *
	 * @param float $amount The amount to decrease the total discount by.
	 * @return self The discount type.
	 */
	public function decrease_total_order_discount( float $amount ): self {
		$this->total_order_discount -= $amount;

		return $this;
	}

	/**
	 * Reset the total discount that needs to be applied to the order.
	 *
	 * @return self The discount type.
	 */
	public function reset_total_order_discount(): self {
		$this->total_order_discount = 0;

		return $this;
	}

	/**
	 * Return the total number of discounted products in the cart|order.
	 *
	 * @return int
	 */
	public function get_total_discounted_products(): int {
		return $this->total_discounted_products;
	}

	/**
	 * Set the total number of discounted products in the cart|order.
	 *
	 * @param int $total_discounted_products The total number of discounted products in the cart|order.
	 * @return self The discount type.
	 */
	public function set_total_discounted_products( int $total_discounted_products ): self {
		$this->total_discounted_products = $total_discounted_products;

		return $this;
	}

	/**
	 * Increase the total number of discounted products in the cart|order.
	 *
	 * @param int $amount The amount to increase the total number of discounted products by.
	 * @return self The discount type.
	 */
	public function increase_total_discounted_products( int $amount ): self {
		$this->total_discounted_products += $amount;

		return $this;
	}

	/**
	 * Decrease the total number of discounted products in the cart|order.
	 *
	 * @param int $amount The amount to decrease the total number of discounted products by.
	 * @return self The discount type.
	 */
	public function decrease_total_discounted_products( int $amount ): self {
		$this->total_discounted_products -= $amount;

		return $this;
	}

	/**
	 * Reset the total number of discounted products in the cart|order.
	 *
	 * @return self The discount type.
	 */
	public function reset_total_discounted_products(): self {
		$this->total_discounted_products = 0;

		return $this;
	}

	/**
	 * Generate a virtual coupon for the order.
	 * This is used to apply the discount to the order and is not a real coupon.
	 *
	 * This is mainly used during the creating of manual orders.
	 *
	 * @param WC_Order $order The order object.
	 * @return void
	 */
	public function generate_virtual_coupon_for_order( WC_Order $order ): void {
		$coupon = ( new Virtual_Coupon( $this->get_discount(), new WC_Coupon(), $this->get_total_order_discount() ) )
			->set_order( $order )
			->setup_coupon();

		$order->apply_coupon( $coupon );

		wdm()->cache()->mark_discount_as_active( $this->get_discount() );
	}

	/**
	 * Return the total quantity of relevant products in the cart.
	 *
	 * @param WC_Cart $cart The cart object.
	 * @return int
	 */
	public function get_cart_quantity_of_relevant_products( WC_Cart $cart ): int {
		$relevant_products = $this->discount->get_relevant_products( $cart );
		$quantity          = 0;

		foreach ( $relevant_products as $cart_item ) {
			$quantity += $cart_item['quantity'];
		}

		return $quantity;
	}

	/**
	 * Return the total quantity of relevant products in the order.
	 *
	 * @param WC_Order $order The order object.
	 * @return int
	 */
	public function get_order_quantity_of_relevant_products( WC_Order $order ): int {
		$relevant_products = $this->discount->get_relevant_products( false, $order );
		$quantity          = 0;

		foreach ( $relevant_products as $order_item ) {
			$quantity += $order_item->get_quantity();
		}

		return $quantity;
	}
}
