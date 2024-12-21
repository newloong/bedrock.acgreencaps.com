<?php

namespace Barn2\Plugin\Discount_Manager\Types;

use Barn2\Plugin\Discount_Manager\Products;

/**
 * Class Bulk_Tier
 * Holds the information for a bulk tier.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Bulk_Tier {

	/**
	 * @var \WC_Product
	 */
	protected $product;

	/**
	 * @var int
	 */
	protected $min_quantity;

	/**
	 * @var int
	 */
	protected $max_quantity;

	/**
	 * @var string
	 */
	protected $discount_type;

	/**
	 * @var float
	 */
	protected $discount_amount;

	/**
	 * Bulk_Tier constructor.
	 *
	 * @param \WC_Product $product     The product.
	 * @param int         $min_quantity The minimum quantity.
	 * @param int         $max_quantity The maximum quantity.
	 * @param string      $discount_type The discount type.
	 * @param string      $discount_amount The discount amount.
	 */
	public function __construct( \WC_Product $product, int $min_quantity, int $max_quantity, string $discount_type, string $discount_amount ) {
		$this->product         = $product;
		$this->min_quantity    = $min_quantity;
		$this->max_quantity    = $max_quantity === 0 ? PHP_INT_MAX : $max_quantity;
		$this->discount_type   = $discount_type;
		$this->discount_amount = floatval( $discount_amount );
	}

	/**
	 * Get the product.
	 *
	 * @return \WC_Product
	 */
	public function get_product(): \WC_Product {
		return $this->product;
	}

	/**
	 * Get the minimum quantity.
	 *
	 * @return int
	 */
	public function get_min_quantity(): int {
		return $this->min_quantity;
	}

	/**
	 * Get the maximum quantity.
	 *
	 * @return int
	 */
	public function get_max_quantity(): int {
		return $this->max_quantity;
	}

	/**
	 * Get the discount type.
	 *
	 * @return string
	 */
	public function get_discount_type(): string {
		return $this->discount_type;
	}

	/**
	 * Get the discount amount.
	 *
	 * @return float
	 */
	public function get_discount_amount(): float {
		return $this->discount_amount;
	}

	/**
	 * Get the price per item based on the discount type and the tier quantity.
	 *
	 * @return string
	 */
	public function get_price_per_item(): string {
		$price = floatval( Products::get_product_price( $this->product ) );

		if ( 'percentage' === $this->get_discount_type() ) {
			$price = $price - ( $price * ( $this->get_discount_amount() / 100 ) );
		} else {
			$price = $price - $this->get_discount_amount();
		}

		if ( $price < 0 ) {
			$price = 0;
		}

		return wc_price( $price );
	}

	/**
	 * Get the discount amount formatted.
	 *
	 * @return string
	 */
	public function get_formatted_discount_amount(): string {
		$type = $this->get_discount_type();

		if ( 'percentage' === $type ) {
			return sprintf( '%s%%', $this->get_discount_amount() );
		}

		return sprintf( '%s', wc_price( $this->get_discount_amount() ) );
	}

	/**
	 * Get the quantity range.
	 *
	 * @return string|int
	 */
	public function get_quantity_range() {
		if ( $this->min_quantity === $this->max_quantity ) {
			return $this->min_quantity;
		}

		if ( $this->max_quantity === PHP_INT_MAX ) {
			return sprintf( '%d+', $this->min_quantity );
		}

		return sprintf( '%d-%d', $this->min_quantity, $this->max_quantity );
	}
}
