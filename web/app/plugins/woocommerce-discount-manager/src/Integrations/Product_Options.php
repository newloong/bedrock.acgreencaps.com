<?php

namespace Barn2\Plugin\Discount_Manager\Integrations;

use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Registerable;
use Barn2\Plugin\Discount_Manager\Products;
use Barn2\Plugin\Discount_Manager\Cart;
use Barn2\Plugin\Discount_Manager\Types\Simple;
use Barn2\Plugin\Discount_Manager\Util;
use WC_Product;

use function Barn2\Plugin\Discount_Manager\wdm;

/**
 * Integrates with Product Options.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Product_Options implements Registerable {

	/**
	 * Array of cart discounted products.
	 *
	 * @var array
	 */
	public $cart_discounted_products = [];

	/**
	 * @inheritdoc
	 */
	public function register() {
		if ( ! function_exists( 'Barn2\Plugin\WC_Product_Options\wpo' ) ) {
			return;
		}

		/** @var Products $products_service */
		$products_service = wdm()->get_service( 'products' );

		/** @var Cart $cart_service */
		$cart_service = wdm()->get_service( 'cart' );

		// Update the product price with the discounted price.
		add_filter( 'wc_product_options_product_price_container', [ $this, 'retrieve_discounted_price' ], 10, 2 );

		// Remove the actions that display the total amount saved.
		remove_action( 'woocommerce_cart_totals_before_order_total', [ $cart_service, 'show_total_discount_cart_checkout' ], 9999 );
		remove_action( 'woocommerce_review_order_before_order_total', [ $cart_service, 'show_total_discount_cart_checkout' ], 9999 );

		// Add new actions so we can override the logic.
		add_filter( 'woocommerce_cart_item_price', [ $this, 'display_discounted_price_in_cart' ], 50, 3 );
		add_action( 'wdm_after_set_cart_item_data', [ $this, 'track_cart_item_data' ], 10, 3 );

		add_action( 'woocommerce_cart_totals_before_order_total', [ $this, 'show_total_discount_cart_checkout' ], 9999 );
		add_action( 'woocommerce_review_order_before_order_total', [ $this, 'show_total_discount_cart_checkout' ], 9999 );
	}

	/**
	 * Retrieve the discounted price for the product in the WPO container.
	 *
	 * @param string $price The price.
	 * @param WC_Product $product The product.
	 * @return float
	 */
	public function retrieve_discounted_price( $price, WC_Product $product ) {
		/** @var Products $products_service */
		$products_service = wdm()->get_service( 'products' );

		$should_show = Util::should_show_sale_price( $product );

		if ( ! $should_show ) {
			return $price;
		}

		$discount = $products_service->get_discount_for_product( $product->get_id() );

		if ( empty( $discount ) ) {
			return $price;
		}

		if ( ! $discount->get_type() instanceof Simple ) {
			return $price;
		}

		if ( empty( $discount ) ) {
			return $price;
		}

		/** @var Discount $discount */
		$reduction       = $discount->get_type()->get_reduction(); // @php-stan-ignore-line
		$reduced_product = $product;

		$reduction->apply_reduction( $reduced_product );

		$price = floatval( $reduced_product->get_sale_price() );

		return $price;
	}

	/**
	 * Track discounted products in the cart.
	 * This is needed because the price of a product is modified by Product Options.
	 *
	 * But we need to know the original price of the product to display the appropriate
	 * crossed out price in the cart.
	 *
	 * @param string $cart_item_key The cart item key.
	 * @param array $cart_item_data The cart item data.
	 * @return self
	 */
	public function track_discounted_cart_products( $cart_item_key, $cart_item_data ) {
		$this->cart_discounted_products[ $cart_item_key ] = $cart_item_data;

		return $this;
	}

	/**
	 * Get the tracked cart item data.
	 *
	 * @param string $cart_item_key The cart item key.
	 * @return array
	 */
	public function get_tracked_cart_item_data( $cart_item_key ): array {
		return isset( $this->cart_discounted_products[ $cart_item_key ] ) ? $this->cart_discounted_products[ $cart_item_key ] : [];
	}

	/**
	 * Check if a cart item is discounted.
	 *
	 * @param string $cart_item_key The cart item key.
	 * @return bool
	 */
	public function has_cart_item_discounted( $cart_item_key ) {
		return isset( $this->cart_discounted_products[ $cart_item_key ] );
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
		if ( $this->has_cart_item_discounted( $cart_item_key ) ) {
			$cart_item_data = $this->get_tracked_cart_item_data( $cart_item_key );

			$original_price = $cart_item_data['_wdm']['original_price'];
			$sale_price     = $cart_item_data['_wdm']['new_price'];
			$price          = wc_format_sale_price( $original_price, $sale_price );
		}

		return $price;
	}

	/**
	 * Track cart item data.
	 *
	 * @param array $cart_item The cart item.
	 * @param float $original_price The original price.
	 * @param float $new_price The new price.
	 * @return void
	 */
	public function track_cart_item_data( $cart_item, $original_price, $new_price ) {
		$this->track_discounted_cart_products( $cart_item['key'], $cart_item );
	}

	/**
	 * Show the total discount amount in the cart and checkout.
	 *
	 * This is needed because the total discount amount is not calculated correctly
	 * when Product Options is active.
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
			} elseif ( $this->has_cart_item_discounted( $cart_item_key ) ) {
				$cart_item_data = $this->get_tracked_cart_item_data( $cart_item_key );

				$original_price  = $cart_item_data['_wdm']['original_price'];
				$sale_price      = $cart_item_data['_wdm']['new_price'];
				$discount        = ( (float) $original_price - (float) $sale_price ) * (int) $values['quantity'];
				$discount_total += $discount;
			}
		}

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
}
