<?php

namespace Barn2\Plugin\Discount_Manager;

use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Cache as LibCache;
use Barn2\Plugin\Discount_Manager\Entities\Discount;

/**
 * Cache helper methods.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Cache {

	const DISCOUNTS_CACHE_KEY            = 'wdm_discounts_cache';
	const ACTIVE_CART_DISCOUNT_CACHE_KEY = 'wdm_active_cart_discount_cache';
	const VIRTUAL_COUPON_CACHE_KEY       = 'wdm_virtual_coupon_cache';

	/**
	 * The list of discounted products that have
	 * been successfully discounted in the cart
	 * and/or order.
	 *
	 * @var array
	 */
	protected $discounted_products = [];

	/**
	 * The list of discounts that have
	 * been successfully applied to the cart
	 * and/or order.
	 *
	 * @var array
	 */
	protected $active_discounts = [];

	/**
	 * Holds the list of products that have been discounted
	 * in the cart and/or order.
	 *
	 * @var array
	 */
	protected $tracked_discounts = [];

	/**
	 * Mark a discount as active and store it in the list of active discounts.
	 *
	 * This method does not add the discount to the database.
	 * It is used to keep track of which discounts are currently active in the cart
	 * and/or order.
	 *
	 * @param Discount $discount
	 * @return self
	 */
	public function mark_discount_as_active( Discount $discount ): self {
		$this->active_discounts[ $discount->id() ] = $discount;

		return $this;
	}

	/**
	 * Mark a discount as inactive and remove it from the list of active discounts.
	 *
	 * This method does not remove the discount from the database.
	 * It is used to keep track of which discounts are currently active in the cart
	 * and/or order.
	 *
	 * @param Discount $discount
	 * @return self
	 */
	public function mark_discount_as_inactive( Discount $discount ): self {
		unset( $this->active_discounts[ $discount->id() ] );

		return $this;
	}

	/**
	 * Get the list of active discounts that have been successfully applied to the cart
	 * and/or order.
	 *
	 * @return array The list of active discounts.
	 */
	public function get_active_discounts(): array {
		return $this->active_discounts;
	}

	/**
	 * Clear the list of active discounts that have been successfully applied to the cart
	 * and/or order.
	 *
	 * @return self
	 */
	public function clear_active_discounts(): self {
		$this->active_discounts = [];

		return $this;
	}

	/**
	 * Determine whether the list of active discounts is empty or not.
	 *
	 * @return bool Whether the list of active discounts is empty.
	 */
	public function has_active_discounts(): bool {
		return ! empty( $this->active_discounts );
	}

	/**
	 * Determine whether a discount is active.
	 *
	 * @param Discount $discount The discount.
	 * @return bool Whether the discount is active.
	 */
	public function is_discount_active( Discount $discount ): bool {
		return isset( $this->active_discounts[ $discount->id() ] );
	}

	/**
	 * Add a product to the list of discounted products.
	 *
	 * @param int $product_id The product ID.
	 * @return self
	 */
	public function add_discounted_product( int $product_id ): self {
		if ( ! $this->has_discounted_product( $product_id ) ) {
			$this->discounted_products[] = $product_id;
		}

		return $this;
	}

	/**
	 * Get the list of discounted products.
	 *
	 * @return array The list of discounted products.
	 */
	public function get_discounted_products(): array {
		return $this->discounted_products;
	}

	/**
	 * Clear the list of discounted products.
	 *
	 * @return self
	 */
	public function clear_discounted_products(): self {
		$this->discounted_products = [];

		return $this;
	}

	/**
	 * Determine whether the list of discounted products is empty.
	 *
	 * @return bool Whether the list of discounted products is empty.
	 */
	public function has_discounted_products(): bool {
		return ! empty( $this->discounted_products );
	}

	/**
	 * Determine whether a product is in the list of discounted products.
	 *
	 * @param int $product_id The product ID.
	 * @return bool Whether the product is in the list of discounted products.
	 */
	public function has_discounted_product( int $product_id ): bool {
		return in_array( $product_id, $this->discounted_products, true );
	}

	/**
	 * Retrieve a cached list of discounts that are currently enabled
	 * and ordered by priority.
	 *
	 * @return Discount[] The list of discounts.
	 */
	public static function get_published_discounts(): array {
		$discounts = LibCache::remember(
			self::DISCOUNTS_CACHE_KEY,
			function () {
				$orm       = wdm()->orm();
				$discounts = $orm( Discount::class )
					->filter( [ 'published', 'ordered' ] )
					->with( 'settings' )
					->all();

				return $discounts;
			}
		);

		/**
		 * Filter the list of published discounts that are currently enabled
		 * and ordered by priority.
		 *
		 * @param Discount[] $discounts The list of discounts.
		 * @return Discount[] The list of discounts.
		 */
		return apply_filters( 'wdm_get_published_discounts', $discounts );
	}

	/**
	 * Forget the cached list of discounts.
	 *
	 * @return void
	 */
	public static function forget_cached_published_discounts(): void {
		LibCache::forget( self::DISCOUNTS_CACHE_KEY );
	}

	/**
	 * Track a discount that has been successfully applied to a product in the cart
	 * and/or order.
	 *
	 * @param int $discount_id The discount ID.
	 * @param int $product_id The product ID.
	 * @return self
	 */
	public function track_discount( int $discount_id, int $product_id ): self {
		$this->tracked_discounts[ $discount_id ][] = $product_id;

		return $this;
	}

	/**
	 * Get the list of products that have been discounted in the cart and/or order.
	 *
	 * This returns an associative array where the keys are the discount IDs and the
	 * values are the list of product IDs that have been discounted by that discount.
	 *
	 * @return array The list of products that have been discounted.
	 */
	public function get_tracked_discounts(): array {
		return $this->tracked_discounts;
	}

	/**
	 * Retrieve the discount ID that has been successfully applied to a product in the cart
	 * and/or order.
	 *
	 * This method is used to determine which discount has been applied to a product
	 * in the cart and/or order.
	 *
	 * @param int $product_id The product ID.
	 * @return int The discount ID.
	 */
	public function get_tracked_discount_id_by_product( int $product_id ): int {
		$discount_id = 0;

		foreach ( $this->tracked_discounts as $id => $products ) {
			if ( in_array( $product_id, $products, true ) ) {
				$discount_id = $id;
				break;
			}
		}

		return $discount_id;
	}
}
