<?php

namespace Barn2\Plugin\Discount_Manager;

use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Registerable;
use Barn2\Plugin\Discount_Manager\Entities\Discount;
use Barn2\Plugin\Discount_Manager\Types\Actionable;

/**
 * Provides integration with the cart.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Cart implements Registerable {

	/**
	 * @inheritdoc
	 */
	public function register(): void {
		add_action( 'woocommerce_before_calculate_totals', [ $this, 'register_discounts' ], 999 );
		add_filter( 'woocommerce_cart_item_price', [ $this, 'display_discounted_price_in_cart' ], 30, 3 );
		add_action( 'woocommerce_before_mini_cart', [ $this, 'force_mini_cart_calculation' ], 1 );
		add_action( 'woocommerce_cart_totals_before_order_total', [ $this, 'show_total_discount_cart_checkout' ], 9999 );
		add_action( 'woocommerce_review_order_before_order_total', [ $this, 'show_total_discount_cart_checkout' ], 9999 );
		add_action( 'woocommerce_before_cart', [ $this, 'display_cart_notice' ] );
		add_action( 'woocommerce_before_calculate_totals', [ $this, 'preserve_shipping_methods' ], 5 );
	}

	/**
	 * Workaround - https://github.com/woocommerce/woocommerce/issues/26422
	 */
	public function force_mini_cart_calculation(): void {
		if ( is_cart() || is_checkout() || ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
			return;
		}

		WC()->cart->calculate_totals();
	}

	/**
	 * Display the discounted price in the cart.
	 *
	 * @param string $price The price.
	 * @param array $values The cart item values.
	 * @param string $cart_item_key The cart item key.
	 * @return string
	 */
	public function display_discounted_price_in_cart( $price, $values, $cart_item_key ): string {
		$is_on_sale = $values['data']->is_on_sale();

		if ( $is_on_sale ) {
			$original_price = $values['data']->get_regular_price();
			$price          = wc_format_sale_price( $original_price, $price );
		}

		return $price;
	}

	/**
	 * Get applicable discounts and register the first one.
	 *
	 * @param \WC_Cart $cart
	 * @return void
	 */
	public function register_discounts( \WC_Cart $cart ): void {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

		if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 ) {
			return;
		}

		wdm()->cache()->clear_discounted_products();
		wdm()->cache()->clear_active_discounts();

		$discounts = $this->get_applicable_discounts( $cart );

		/**
		 * Action triggered when applicable discounts are registered.
		 *
		 * @param Discount[] $discounts The discounts that were applied to the cart (empty if none).
		 * @param \WC_Cart $cart The cart object.
		 */
		do_action( 'wdm_applicable_discounts_registered', $discounts, $cart );

		if ( empty( $discounts ) ) {
			return;
		}

		foreach ( $discounts as $discount ) {
			if ( ! $discount instanceof Discount ) {
				continue;
			}

			$type = $discount->get_type();

			if ( $type instanceof Actionable ) {
				$type->run_cart_actions( $cart );
			}
		}

		/**
		 * Action triggered when all discounts are added to the cart.
		 *
		 * @param Discount[] $discounts The discounts that were applied to the cart.
		 * @param \WC_Cart $cart The cart object.
		 */
		do_action( 'wdm_cart_discount_added', $discounts, $cart );

		$this->mark_discounted_products( $cart );
	}

	/**
	 * Mark the discounted products in the cart.
	 * This adds a flag to the cart items to indicate that they have been discounted.
	 *
	 * @param \WC_Cart $cart
	 * @return void
	 */
	private function mark_discounted_products( &$cart ): void {
		$discounted_products = wdm()->cache()->get_discounted_products();

		if ( empty( $discounted_products ) ) {
			return;
		}

		foreach ( $cart->get_cart() as $cart_item_key => $values ) {
			$product = $values['data'];

			if ( $product->is_type( 'variation' ) ) {
				$parent_product_id = $product->get_parent_id();
				$product           = wc_get_product( $parent_product_id );
			}

			if ( in_array( $product->get_id(), $discounted_products, true ) ) {
				$cart->cart_contents[ $cart_item_key ]['_wdm_discounted'] = true;

				$discount_id = wdm()->cache()->get_tracked_discount_id_by_product( $product->get_id() );

				if ( $discount_id > 0 ) {
					$cart->cart_contents[ $cart_item_key ]['_wdm_discounted_by'] = $discount_id;
				}
			}
		}
	}

	/**
	 * Display the cart notice.
	 *
	 * @return void
	 */
	public function display_cart_notice(): void {
		$active_discounts = wdm()->cache()->get_active_discounts();

		if ( empty( $active_discounts ) ) {
			return;
		}

		$messages = [];

		foreach ( $active_discounts as $discount ) {
			$notice = $discount->get_notice();

			if ( ! $notice ) {
				continue;
			}

			$messages[] = $notice;
		}

		if ( empty( $messages ) ) {
			return;
		}

		$content = '<ul class="wdm-cart-notice"><li>' . implode( '</li><li>', $messages ) . '</li></ul>';

		// If there is only one message, remove the list.
		if ( count( $messages ) === 1 ) {
			$content = str_replace( [ '<ul class="wdm-cart-notice"><li>', '</li></ul>' ], '', $content );
		}

		/**
		 * Filter the cart notice message.
		 *
		 * @param string $notice The notice message.
		 * @param array $messages The messages.
		 * @return string
		 */
		$notice = apply_filters( 'wdm_cart_notice', $content, $messages );

		wc_print_notice( $notice, 'notice' );
	}

	/**
	 * Returns a list of discounts matching the conditions
	 * and content of the cart.
	 *
	 * @param \WC_Cart $cart
	 * @param bool $should_calculate Whether to calculate the totals.
	 * @return Discount[]
	 */
	public function get_applicable_discounts( \WC_Cart $cart, bool $should_calculate = true ): array {
		$discounts = Cache::get_published_discounts();

		$discounts = array_filter(
			$discounts,
			function ( Discount $discount ) use ( $cart, $should_calculate ) {
				if ( $should_calculate ) {
					WC()->cart->calculate_totals();
				}
				return $discount->is_applicable_to_cart( $cart );
			}
		);

		/**
		 * Filter the applicable discounts.
		 *
		 * @param Discount[] $discounts The discounts.
		 * @param \WC_Cart $cart The cart object.
		 * @return Discount[] The discounts.
		 */
		return apply_filters( 'wdm_applicable_discounts', $discounts, $cart );
	}

	/**
	 * Displays the total amount of savings in the cart.
	 *
	 * @return void
	 */
	public function show_total_discount_cart_checkout(): void {
		$discount_total = WC()->cart->get_discount_total();

		foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
			$product = $values['data'];
			if ( $product->is_on_sale() ) {
				$regular_price   = $product->get_regular_price();
				$sale_price      = $product->get_sale_price();
				$discount        = ( (float) $regular_price - (float) $sale_price ) * (int) $values['quantity'];
				$discount_total += $discount;
			}
		}

		/**
		 * Filters the total discount amount calculated for the cart.
		 *
		 * @param float $discount_total The total discount amount.
		 * @return float The total discount amount.
		 */
		$discount_total = apply_filters( 'wdm_cart_total_discount_amount', $discount_total );

		$output = '';

		if ( $discount_total > 0 ) {
			$label = __( 'Total savings', 'woocommerce-discount-manager' );

			$output = '<tr><th>' . $label . '</th><td data-title="' . $label . '">' . wp_kses_post( wc_price( $discount_total ) ) . '</td></tr>';
			/**
			 * Filter the output of the total discount amount.
			 *
			 * @param string $output The output.
			 * @param float $discount_total The total discount amount.
			 */
			$output = apply_filters( 'wdm_cart_total_discount_amount_output', $output, $discount_total );

			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Determines if the cart has specific products.
	 *
	 * @param \WC_Cart $cart The cart object.
	 * @param array $products The products to check for.
	 * @return boolean True if the cart has the products, false otherwise.
	 */
	public static function has_specific_products( \WC_Cart $cart, array $products ): bool {
		$cart_products = $cart->get_cart();

		foreach ( $cart_products as $cart_item ) {
			$product = $cart_item['data'];

			if ( $product->is_type( 'variation' ) ) {
				$parent_product_id = $product->get_parent_id();
				$product           = wc_get_product( $parent_product_id );
			}

			if ( in_array( $product->get_id(), $products, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determines if the cart has products in specific categories.
	 *
	 * @param \WC_Cart $cart The cart object.
	 * @param array $categories The categories to check for.
	 * @return boolean True if the cart has the products, false otherwise.
	 */
	public static function has_products_in_categories( \WC_Cart $cart, array $categories ): bool {
		$cart_products = $cart->get_cart();

		foreach ( $cart_products as $cart_item ) {
			$product            = $cart_item['data'];
			$product_categories = $product->get_category_ids();

			if ( $product->is_type( 'variation' ) ) {
				$parent_product_id  = $product->get_parent_id();
				$parent_product     = wc_get_product( $parent_product_id );
				$product_categories = $parent_product->get_category_ids();
			}

			foreach ( $product_categories as $product_category ) {
				if ( in_array( $product_category, $categories, true ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Get the specific products in the cart.
	 *
	 * @param \WC_Cart $cart The cart object.
	 * @param array $products The products to get.
	 * @return array The products.
	 */
	public static function get_specific_products( \WC_Cart $cart, array $products ): array {
		$cart_products = $cart->get_cart();

		$specific_products = [];

		foreach ( $cart_products as $index => $cart_item ) {
			$product = $cart_item['data'];

			if ( $product->is_type( 'variation' ) ) {
				$parent_product_id = $product->get_parent_id();
				$product           = wc_get_product( $parent_product_id );
			}

			if ( in_array( $product->get_id(), $products, true ) ) {
				$specific_products[ $index ] = $cart_item;
			}
		}

		return $specific_products;
	}

	/**
	 * Get the products in specific categories in the cart but maintain the cart structure.
	 *
	 * @param \WC_Cart $cart The cart object.
	 * @param array $categories The categories to get.
	 * @return array The products.
	 */
	public static function get_products_in_categories( \WC_Cart $cart, array $categories ): array {
		$cart_products = $cart->get_cart();

		$products_in_categories = [];

		foreach ( $cart_products as $index => $cart_item ) {
			$product            = $cart_item['data'];
			$product_categories = $product->get_category_ids();

			if ( $product->is_type( 'variation' ) ) {
				$parent_product_id  = $product->get_parent_id();
				$parent_product     = wc_get_product( $parent_product_id );
				$product_categories = $parent_product->get_category_ids();
			}

			foreach ( $product_categories as $product_category ) {
				if ( in_array( $product_category, $categories, true ) ) {
					$products_in_categories[ $index ] = $cart_item;
				}
			}
		}

		return $products_in_categories;
	}

	/**
	 * Preserves the chosen shipping methods during cart calculations
	 * to prevent them from being reset. This is needed because WooCommerce
	 * sometimes resets shipping methods when recalculating cart totals,
	 * which can cause issues with shipping costs and available methods.
	 *
	 * This method stores the currently chosen shipping methods before calculations
	 * and restores them afterwards if they have changed.
	 *
	 * @return void
	 */
	public function preserve_shipping_methods(): void {
		/**
		 * Filter to determine if the shipping methods should be preserved.
		 *
		 * @param bool $should_preserve Whether the shipping methods should be preserved.
		 * @return bool
		 */
		$should_preserve = apply_filters( 'wdm_preserve_shipping_methods', true );

		if ( ! $should_preserve ) {
			return;
		}

		if ( ! isset( WC()->session ) ) {
			return;
		}

		$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );

		if ( empty( $chosen_methods ) ) {
			return;
		}

		add_action(
			'woocommerce_after_calculate_totals',
			function () use ( $chosen_methods ) {
				$current_methods = WC()->session->get( 'chosen_shipping_methods' );

				if ( $current_methods !== $chosen_methods ) {
					WC()->session->set( 'chosen_shipping_methods', $chosen_methods );

					$packages      = WC()->shipping()->get_packages();
					$method_counts = [];
					foreach ( $packages as $key => $package ) {
						$method_counts[ $key ] = count( $package['rates'] );
					}
					WC()->session->set( 'shipping_method_counts', $method_counts );
				}
			},
			10
		);
	}
}
