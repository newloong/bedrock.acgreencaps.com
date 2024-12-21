<?php

namespace Barn2\Plugin\Discount_Manager;

use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Registerable;
use Barn2\Plugin\Discount_Manager\Entities\Discount;
use Barn2\Plugin\Discount_Manager\Types\Bulk;
use WC_Product;
use Barn2\Plugin\Discount_Manager\Types\Type;

/**
 * Provides functionality for the single product page.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Products implements Registerable {

	/**
	 * @inheritdoc
	 */
	public function register(): void {
		add_filter( 'woocommerce_get_price_html', [ $this, 'set_simple_sale_price' ], 20, 2 );
		add_filter( 'woocommerce_product_is_on_sale', [ $this, 'set_is_on_sale' ], 20, 2 );
		add_filter( 'woocommerce_sale_flash', [ $this, 'maybe_hide_sale_badge' ], 10, 3 );

		add_action( 'woocommerce_before_single_product', [ $this, 'render_product_content' ] );
		add_action( 'woocommerce_single_product_summary', [ $this, 'render_product_content' ] );
		add_action( 'woocommerce_before_add_to_cart_form', [ $this, 'render_product_content' ] );
		add_action( 'woocommerce_product_meta_start', [ $this, 'render_product_content' ] );
		add_action( 'woocommerce_after_single_product_summary', [ $this, 'render_product_content' ], 9 );
		add_action( 'woocommerce_single_product_summary', [ $this, 'render_product_content_own_hook' ], 1 );
	}

	/**
	 * Display possible discounts for the current product.
	 *
	 * @return void
	 */
	public function display_possible_discounts(): void {
		if ( ! is_product() ) {
			return;
		}

		global $product;

		$possible_discounts = $this->get_elegible_discounts_for_product( $product->get_id() );

		echo wdm()->templates()->get_template( 'product-discounts.php', [ 'discounts' => $possible_discounts ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Get discounts that are elegible for the product.
	 *
	 * @param int $product_id The product ID.
	 * @return array The discounts.
	 */
	public function get_elegible_discounts_for_product( int $product_id ): array {
		$discounts = Cache::get_published_discounts();

		$discounts = array_filter(
			$discounts,
			function ( Discount $discount ) use ( $product_id ) {
				return $discount->is_relevant_for_product( wc_get_product( $product_id ) );
			}
		);

		/**
		 * Filter the elegible discounts for the product.
		 *
		 * @param array $discounts The discounts.
		 * @param int $product_id The product ID.
		 * @return array The discounts.
		 */
		return apply_filters( 'wdm_get_elegible_discounts_for_product', $discounts, $product_id );
	}

	/**
	 * Get the top relevant discount for the product.
	 *
	 * @param int $product_id The product ID.
	 * @return Discount|null The discount.
	 */
	public function get_discount_for_product( int $product_id ): ?Discount {
		$discounts = $this->get_elegible_discounts_for_product( $product_id );

		if ( empty( $discounts ) ) {
			return null;
		}

		return reset( $discounts );
	}

	/**
	 * Get simple discounts that are elegible for the product.
	 *
	 * @param WC_Product $product The product.
	 * @return array The discounts.
	 */
	public function get_elegible_simple_discounts_for_product( WC_Product $product ): array {
		$discounts = $this->get_elegible_discounts_for_product( $product->get_id() );

		if ( empty( $discounts ) ) {
			return [];
		}

		// Get simple discounts only.
		$discount = array_filter(
			$discounts,
			function ( Discount $discount ) {
				return $discount->get_type() instanceof Types\Simple;
			}
		);

		if ( empty( $discount ) ) {
			return [];
		}

		return $discount;
	}

	/**
	 * Determine whether the current product has multiple discounts.
	 *
	 * @return boolean
	 */
	public function product_has_multiple_discounts( array $discounts ): bool {
		return count( $discounts ) > 1;
	}

	/**
	 * Get the first discount type for the current product.
	 *
	 * @param array $discounts The discounts.
	 * @return Type
	 */
	public function get_first_discount_type_for_product( array $discounts ) {
		$discount = reset( $discounts ); // @php-stan-ignore-line

		return $discount->get_type();
	}

	/**
	 * Set the sale price for products where
	 * a simple discount is applicable.
	 *
	 * @param string $price_html The price HTML.
	 * @param WC_Product $product The product.
	 * @return string The price HTML.
	 */
	public function set_simple_sale_price( string $price_html, WC_Product $product ) {
		if ( ( is_admin() && ! defined( 'DOING_AJAX' ) ) || is_cart() || is_checkout() ) {
			return $price_html;
		}

		$should_show = Util::should_show_sale_price( $product );

		if ( ! $should_show ) {
			return $price_html;
		}

		$discount = $this->get_discount_for_product( $product->get_id() );

		if ( empty( $discount ) ) {
			return $price_html;
		}

		if ( ! $discount->get_type() instanceof Types\Simple ) {
			return $price_html;
		}

		if ( empty( $discount ) ) {
			return $price_html;
		}

		/** @var Discount $discount */
		$reduction       = $discount->get_type()->get_reduction(); // @php-stan-ignore-line
		$reduced_product = $product;

		if ( $product->is_type( 'grouped' ) || $product->is_type( 'variable' ) ) {
			$price_html = $this->generate_grouped_sale_price( $price_html, $product, $discount );
		} else {
			$reduction->apply_reduction( $reduced_product );

			$price_html = wc_format_sale_price(
				wc_get_price_to_display( $reduced_product, [ 'price' => $reduced_product->get_regular_price() ] ),
				wc_get_price_to_display( $reduced_product, [ 'price' => $reduced_product->get_sale_price() ] )
			) . $reduced_product->get_price_suffix();
		}

		/**
		 * Filter the simple sale price HTML when a simple discount is applicable.
		 *
		 * @param string $price_html The price HTML.
		 * @param WC_Product $product The product.
		 * @param Discount $discount The discount.
		 * @param WC_Product $reduced_product The reduced product.
		 * @return string The price HTML.
		 */
		return apply_filters( 'wdm_get_simple_sale_price_html', $price_html, $product, $discount, $reduced_product );
	}

	/**
	 * Generate the sale price for grouped products.
	 *
	 * @param string $price_html The price HTML.
	 * @param WC_Product $product The product.
	 * @param Discount $discount The discount.
	 * @return string
	 */
	public function generate_grouped_sale_price( string $price_html, WC_Product $product, Discount $discount ): string {
		$tax_display_mode      = get_option( 'woocommerce_tax_display_shop' );
		$child_prices          = [];
		$original_child_prices = [];
		$children              = array_filter( array_map( 'wc_get_product', $product->get_children() ), 'wc_products_array_filter_visible_grouped' );
		$reduction             = $discount->get_type()->get_reduction(); // @php-stan-ignore-line

		foreach ( $children as $child ) {
			if ( '' !== self::get_product_price( $child ) ) {
				$regular_price           = wc_get_price_to_display( $child, [ 'price' => $child->get_regular_price() ] );
				$original_child_prices[] = $regular_price;

				$reduced_product = $child;
				$reduction->apply_reduction( $reduced_product );

				$child_prices[] = 'incl' === $tax_display_mode ? wc_get_price_including_tax( $reduced_product ) : wc_get_price_excluding_tax( $reduced_product );
			}
		}

		if ( ! empty( $child_prices ) ) {
			$min_price = min( $child_prices );
			$max_price = max( $child_prices );
		} else {
			$min_price = '';
			$max_price = '';
		}

		if ( ! empty( $original_child_prices ) ) {
			$min_regular_price = min( $original_child_prices );
			$max_regular_price = max( $original_child_prices );
		} else {
			$min_regular_price = '';
			$max_regular_price = '';
		}

		// If the prices are the same, return a single price.
		if ( $min_price === $max_price ) {
			return wc_format_sale_price( $min_regular_price, $min_price ) . $product->get_price_suffix();
		}

		return wc_format_price_range( $min_price, $max_price ) . $product->get_price_suffix();
	}

	/**
	 * Set whether the product is on sale for products where
	 * a simple discount is applicable.
	 *
	 * @param bool $on_sale Whether the product is on sale.
	 * @param WC_Product $product The product.
	 * @return bool Whether the product is on sale.
	 */
	public function set_is_on_sale( $on_sale, $product ): bool {
		if ( is_admin() || is_cart() || is_checkout() ) {
			return $on_sale;
		}

		$discount = $this->get_discount_for_product( $product->get_id() );

		if ( empty( $discount ) ) {
			return $on_sale;
		}

		$type = $discount->get_type();

		if ( ! $type instanceof Types\Simple ) {
			return $on_sale;
		}

		if ( Util::should_show_sale_price( $product ) ) {
			return true;
		}

		return $on_sale;
	}

	/**
	 * Maybe Hide the sale badge for products where
	 * a simple discount is applicable.
	 *
	 * @param string $content The content.
	 * @param string $post The post.
	 * @param WC_Product $product The product.
	 * @return string The post.
	 */
	public function maybe_hide_sale_badge( $content, $post, $product ) {
		if ( is_admin() || is_cart() || is_checkout() ) {
			return $content;
		}

		if ( ! $product instanceof WC_Product ) {
			return $content;
		}

		$discount = $this->get_discount_for_product( $product->get_id() );

		if ( empty( $discount ) ) {
			return $content;
		}

		$type = $discount->get_type();

		if ( ! $type instanceof Types\Simple ) {
			return $content;
		}

		if ( ! $type->has_sale_badge() ) {
			return '';
		}

		return $content;
	}

	/**
	 * Reder the content for the single product page.
	 *
	 * @return void
	 */
	public function render_product_content(): void {
		global $product;

		if ( ! $product instanceof WC_Product ) {
			return;
		}

		$discount = $this->get_discount_for_product( $product->get_id() );

		if ( empty( $discount ) ) {
			return;
		}

		$type = $discount->get_type();

		$frontend_text_location = $discount->get_frontend_text_location();

		if ( $frontend_text_location === current_action() ) {
			echo wdm()->templates()->get_template( 'product-discounts.php', [ 'discounts' => [ $discount ] ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		if ( $type instanceof Bulk ) {
			$should_display_table = $type->should_display_bulk_table();
			$location             = $type->get_bulk_table_display_location();

			if ( $location === current_action() && $should_display_table ) {
				echo wdm()->templates()->get_template( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'bulk-table.php',
					[
						'discount'   => $discount, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'product_id' => $product->get_id(), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					]
				); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}
	}

	/**
	 * Reder the content for the single product page.
	 *
	 * @return void
	 */
	public function render_product_content_own_hook(): void {
		global $product;

		$discount = $this->get_discount_for_product( $product->get_id() );

		if ( empty( $discount ) ) {
			return;
		}

		$type = $discount->get_type();

		$frontend_text_location = $discount->get_frontend_text_location();

		if ( $frontend_text_location !== 'woocommerce_single_product_title' ) {
			return;
		}

		echo wdm()->templates()->get_template( 'product-discounts.php', [ 'discounts' => [ $discount ] ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		if ( $type instanceof Bulk ) {
			$should_display_table = $type->should_display_bulk_table();

			if ( $should_display_table ) {
				echo wdm()->templates()->get_template( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'bulk-table.php',
					[
						'discount'   => $discount, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'product_id' => $product->get_id(), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					]
				); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}
	}

	/**
	 * Wrapper function around the $product->get_price() method
	 * that allows us to override the price for products where
	 * necessary via a filter.
	 *
	 * This is used to ensure that the correct price is retrieved
	 * if we're working with other plugins.
	 *
	 * For example, this is needed when working with WWP.
	 *
	 * @param WC_Product $product The product.
	 * @param string $context The context. Defaults to 'view'. Accepts 'view' and 'edit'.
	 * @return string
	 */
	public static function get_product_price( WC_Product $product, string $context = 'view' ): string {
		$price = $product->get_price( $context );

		/**
		 * Filter the product price.
		 *
		 * @param string $price The price.
		 * @param WC_Product $product The product.
		 * @param string $context The context. Defaults to 'view'. Accepts 'view' and 'edit'.
		 * @return string
		 */
		$price = apply_filters( 'wdm_get_product_price', $price, $product, $context );

		return $price;
	}
}
