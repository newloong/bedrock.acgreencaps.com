<?php

namespace Barn2\Plugin\Discount_Manager;

use Barn2\Plugin\Discount_Manager\Entities\Discount;
use WC_Product;

/**
 * Utility class with static methods used throughout the plugin.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Util {

	/**
	 * Get currency data about the store currency.
	 * For use in JS.
	 *
	 * @return array
	 */
	public static function get_currency_data(): array {
		$currency = get_woocommerce_currency();

		return [
			'code'              => $currency,
			'precision'         => wc_get_price_decimals(),
			'symbol'            => html_entity_decode( get_woocommerce_currency_symbol( $currency ) ),
			'symbolPosition'    => get_option( 'woocommerce_currency_pos' ),
			'decimalSeparator'  => wc_get_price_decimal_separator(),
			'thousandSeparator' => wc_get_price_thousand_separator(),
			'priceFormat'       => html_entity_decode( get_woocommerce_price_format() ),
		];
	}

	/**
	 * Returns a list of all registered user roles on the site.
	 *
	 * @param bool $dropdown Whether to format the list for use in a dropdown.
	 * @param bool $with_guest_role Whether to include the guest role in the list.
	 * @return array The list of roles.
	 */
	public static function get_roles( bool $dropdown = false, bool $with_guest_role = false ): array {
		$formatted   = [];
		$roles       = wp_roles()->get_names();
		$count_users = count_users();

		foreach ( $roles as $role => $name ) {
			$formatted[ $role ] = $name;
		}

		if ( $with_guest_role ) {
			$formatted['guest'] = __( 'Guest', 'woocommerce-discount-manager' );
		}

		if ( $dropdown ) {
			$formatted = [];

			foreach ( $roles as $role => $name ) {
				if ( ! isset( $count_users['avail_roles'][ $role ] ) || $count_users['avail_roles'][ $role ] <= 0 ) {
					continue;
				}

				$formatted[] = [
					'value' => $role,
					'label' => $name,
				];
			}

			if ( $with_guest_role ) {
				$formatted[] = [
					'value' => 'guest',
					'label' => __( 'Guest', 'woocommerce-discount-manager' ),
				];
			}
		}

		return $formatted;
	}

	/**
	 * Get the 1st role of the currently logged in user.
	 *
	 * If a user ID is provided, get the 1st role of that user.
	 * If the user is a guest, return 'guest'.
	 *
	 * If $user_id === 0 and $check_guest_role is true, return 'guest'.
	 * If $user_id === 0 and $check_guest_role is false, return an empty string.
	 *
	 * @param int|bool $user_id The user ID. If not provided, the current user is used. Default false. If 0 and $check_guest_role is true, return 'guest' but if 0 and $check_guest_role is false, return an empty string.
	 * @param bool $check_guest_role Whether to check if the user is a guest. Default false.
	 * @return string The user role or 'guest' if the user is a guest or an empty string if $user_id === 0.
	 */
	public static function get_current_user_role( $user_id = false, bool $check_guest_role = false ): string {
		$user = $user_id ? get_user_by( 'id', $user_id ) : wp_get_current_user();

		if ( $user_id === 0 && $check_guest_role === true ) {
			return 'guest';
		}

		if ( $user_id === 0 ) {
			return '';
		}

		if ( $user->ID === 0 && $check_guest_role === true ) {
			return 'guest';
		}

		if ( $user->ID === 0 ) {
			return '';
		}

		$roles = (array) $user->roles;

		return array_values( $roles )[0];
	}

	/**
	 * Determine if a string starts with a given prefix.
	 *
	 * @param string $string
	 * @param string $prefix
	 * @return boolean
	 */
	public static function string_starts_with( $string, $prefix ): bool {
		return substr( $string, 0, strlen( $prefix ) ) === $prefix;
	}

	/**
	 * Get the list of product IDs in the cart.
	 *
	 * @param \WC_Cart $cart The cart object.
	 * @return array The list of product IDs.
	 */
	public static function get_cart_products_ids( \WC_Cart $cart ): array {
		$products = [];

		foreach ( $cart->get_cart() as $cart_item ) {
			$products[] = $cart_item['product_id'];
		}

		return $products;
	}

	/**
	 * Get flatten array of id from specific product specific variations
	 *
	 * @param array $data
	 * @return array
	 */
	public static function get_selected_variations_id_flatten( $data ) {
		$variation_ids = [];

		// Loop through the parent array
		foreach ( $data as $item ) {
			if ( isset( $item['variations'] ) && is_array( $item['variations'] ) ) {
				// Loop through the variations array and collect the IDs
				foreach ( $item['variations'] as $variation ) {
					if ( isset( $variation['id'] ) ) {
						$variation_ids[] = $variation['id'];
					}
				}
			}
		}

		return $variation_ids;
	}

	/**
	 * Get selected variations by specific product id
	 *
	 * @param int $product_id
	 * @param array $variations Product variations
	 *
	 * @return array $variations_id
	 */
	public static function get_selected_variations_by_specific_product( $product_id, $variations ): array {
		$variations_id = [];
		if ( empty( $variations ) ) {
			return [];
		}
		foreach ( $variations as $variable ) {
			if ( intval( $product_id ) === intval( $variable['parent_product'] ) ) {
				foreach ( $variable['variations'] as $variation ) {
					if ( isset( $variation['id'] ) ) {
						$variations_id[] = (int) $variation['id'];
					}
				}
			}
		}

		return $variations_id;
	}

	/**
	 * Generate an identifier slug used for setting up coupon data.
	 *
	 * @param Discount $discount
	 * @return string
	 */
	public static function get_coupon_identifier( Discount $discount ): string {
		return 'wdm-discount-' . $discount->id();
	}

	/**
	 * Determine if a discount exists.
	 *
	 * @param int $discount_id The discount ID.
	 * @return bool
	 */
	public static function discount_exists( $discount_id ): bool {
		$orm = wdm()->orm();
		return $orm( Discount::class )->find( $discount_id ) !== null;
	}

	/**
	 * Determine if a product has at least one of the given categories.
	 *
	 * @param int $product_id The product ID.
	 * @param array $categories The list of category IDs.
	 * @return bool
	 */
	public static function product_has_categories( $product_id, $categories ): bool {
		// Get the product and check if it's a variable product. If it is, get the parent product ID.
		$product = wc_get_product( $product_id );

		if ( $product->is_type( 'variation' ) ) {
			$product_id = $product->get_parent_id();
		}

		$product_categories = get_the_terms( $product_id, 'product_cat' );

		if ( ! $product_categories ) {
			return false;
		}

		foreach ( $product_categories as $product_category ) {
			if ( in_array( $product_category->term_id, $categories, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if the discount text should be escaped.
	 *
	 * @return bool
	 */
	public static function should_escape_discount_text(): bool {
		/**
		 * Filter whether to escape the discount text.
		 *
		 * @param bool $should_escape Whether to escape the discount text.
		 * @return bool
		 */
		return apply_filters( 'wdm_escape_discount_text', true );
	}

	/**
	 * Get the list of allowed HTML tags for the discount text.
	 * Default to the list of allowed HTML tags for posts.
	 *
	 * @return array
	 */
	public static function get_product_text_allowed_html(): array {
		/**
		 * Filter the list of allowed HTML tags for the discount text.
		 *
		 * @param array $allowed_html The list of allowed HTML tags.
		 * @return array
		 */
		return apply_filters( 'wdm_product_text_allowed_html', wp_kses_allowed_html( 'post' ) );
	}

	/**
	 * Helper function to determine if the sale price should be shown.
	 *
	 * @param bool|WC_Product $product The product.
	 * @return bool Whether to show the sale price.
	 */
	public static function should_show_sale_price( $product = false ): bool {
		$should_show = true;

		/**
		 * Filter whether to show the sale price for a product when a simple discount is applicable.
		 *
		 * @param bool $should_show Whether to show the sale price.
		 * @param bool|WC_Product $product The product.
		 * @return bool Whether to show the sale price.
		 */
		$should_show = apply_filters( 'wdm_should_show_sale_price', $should_show, $product );

		return $should_show;
	}

	/**
	 * Get the list of locations where the bulk table can be displayed.
	 *
	 * @return array
	 */
	public static function get_bulk_table_locations(): array {
		$locations = [
			'woocommerce_before_single_product'        => esc_html__( 'Before product page content', 'woocommerce-discount-manager' ),
			'woocommerce_single_product_title'         => esc_html__( 'Before product title', 'woocommerce-discount-manager' ),
			'woocommerce_single_product_summary'       => esc_html__( 'Before short description', 'woocommerce-discount-manager' ),
			'woocommerce_before_add_to_cart_form'      => esc_html__( 'Before add to cart button', 'woocommerce-discount-manager' ),
			'woocommerce_product_meta_start'           => esc_html__( 'Before product meta information', 'woocommerce-discount-manager' ),
			'woocommerce_after_single_product_summary' => esc_html__( 'Before product tabs', 'woocommerce-discount-manager' ),
		];

		/**
		 * Filter the list of locations where the bulk table can be displayed.
		 *
		 * @param array $locations The list of locations.
		 * @return array
		 */
		return apply_filters( 'wdm_bulk_table_locations', $locations );
	}

	/**
	 * Convert an array of options to a format suitable for a dropdown.
	 *
	 * @param array $options The list of options.
	 * @return array The formatted list of options.
	 */
	public static function convert_array_to_dropdown_options( array $options ): array {

		$formatted = [];

		foreach ( $options as $value => $label ) {
			$formatted[] = [
				'value' => $value,
				'label' => $label,
			];
		}

		return $formatted;
	}

	/**
	 * Determine whether prices are entered including tax.
	 *
	 * @return bool
	 */
	public static function prices_include_tax(): bool {
		return get_option( 'woocommerce_prices_include_tax' ) === 'yes';
	}

	/**
	 * Determine if a string ends with a given suffix.
	 *
	 * @param string $haystack
	 * @param string $needle
	 * @return bool
	 */
	public static function string_ends_with( $haystack, $needle ): bool {
		$length = strlen( $needle );
		if ( ! $length ) {
			return true;
		}
		return substr( $haystack, -$length ) === $needle;
	}
}
