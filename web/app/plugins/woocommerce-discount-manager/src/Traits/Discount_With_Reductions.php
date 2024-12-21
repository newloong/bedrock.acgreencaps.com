<?php

namespace Barn2\Plugin\Discount_Manager\Traits;

use Barn2\Plugin\Discount_Manager\Reductions\Fixed;
use Barn2\Plugin\Discount_Manager\Reductions\Percentage;
use Barn2\Plugin\Discount_Manager\Reductions\Reduction;

/**
 * Trait for discount types that apply a reduction to products.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
trait Discount_With_Reductions {

	/**
	 * Get the type of discount amount.
	 * Possible values: 'percentage', 'fixed'.
	 *
	 * @return string The type of discount amount.
	 */
	public function get_discount_amount_type(): string {
		return $this->discount->settings()->get( 'amount_type' )->value();
	}

	/**
	 * Get the reduction to apply to products.
	 *
	 * @return Reduction The reduction to apply to products.
	 */
	public function get_reduction(): Reduction {
		$type = $this->get_discount_amount_type();

		if ( $type === 'percentage' ) {
			return new Percentage( $this->discount->settings()->get( 'percentage_discount' )->value(), null, $this->get_discount() );
		}

		return new Fixed( $this->discount->settings()->get( 'fixed_discount' )->value(), null, $this->get_discount() );
	}
}
