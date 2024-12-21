<?php

namespace Barn2\Plugin\Discount_Manager\Traits;

/**
 * Provides access to 2 methods that retrieve
 * the products and categories assigned to a condition.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
trait Condition_Has_Items {

	/**
	 * Get products assigned.
	 *
	 * @return array
	 */
	public function get_products(): array {
		return $this->settings->has( 'products' ) ? array_map( 'absint', $this->settings->get( 'products' ) ) : [];
	}

	/**
	 * Get categories assigned.
	 * Includes all child categories.
	 *
	 * @return array
	 */
	public function get_categories(): array {
		$categories = $this->settings->has( 'categories' ) ? array_map( 'absint', $this->settings->get( 'categories' ) ) : [];

		foreach ( $categories as $category ) {
			$categories = array_merge( $categories, get_term_children( $category, 'product_cat' ) );
		}

		return $categories;
	}
}
