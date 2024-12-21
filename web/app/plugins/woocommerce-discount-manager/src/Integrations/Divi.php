<?php

namespace Barn2\Plugin\Discount_Manager\Integrations;

use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Registerable;

/**
 * Integrates with Divi.
 *
 * @package   Barn2\woocommerce-discount-manager
 */
class Divi implements Registerable {
	/**
	 * @inheritdoc
	 */
	public function register() {
		if ( ! function_exists( 'et_setup_theme' ) ) {
			return;
		}

		add_filter( 'woocommerce_cart_item_price', [ $this, 'display_discounted_price_in_cart' ], 30, 3 );
		add_filter( 'woocommerce_cart_item_subtotal', [ $this, 'display_discounted_subtotal_in_cart' ], 30, 3 );
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
		$is_on_sale = isset( $values['_wdm'] ) && is_array( $values['_wdm'] ) && isset( $values['_wdm']['original_price'] ) && isset( $values['_wdm']['new_price'] );

		if ( $is_on_sale ) {
			$original_price = $values['_wdm']['original_price'];
			$price          = wc_format_sale_price( $original_price, $values['_wdm']['new_price'] );
		}

		return $price;
	}

	/**
	 * Display the discounted subtotal in the cart.
	 *
	 * @param string $subtotal The subtotal.
	 * @param array $values The cart item values.
	 * @param string $cart_item_key The cart item key.
	 * @return string
	 */
	public function display_discounted_subtotal_in_cart( $subtotal, $values, $cart_item_key ): string {
		$is_on_sale = isset( $values['_wdm'] ) && is_array( $values['_wdm'] ) && isset( $values['_wdm']['original_price'] ) && isset( $values['_wdm']['new_price'] );

		if ( $is_on_sale ) {
			$new_price = $values['_wdm']['new_price'] * $values['quantity'];
			$subtotal  = wc_price( $new_price );
		}

		return $subtotal;
	}
}
