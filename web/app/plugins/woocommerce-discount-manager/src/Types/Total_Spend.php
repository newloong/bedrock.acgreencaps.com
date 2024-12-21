<?php

namespace Barn2\Plugin\Discount_Manager\Types;

use Barn2\Plugin\Discount_Manager\Reductions\Percentage;
use Barn2\Plugin\Discount_Manager\Traits\Discount_With_Reductions;
use WC_Cart;
use WC_Order;

use function Barn2\Plugin\Discount_Manager\wdm;

/**
 * Total spend discount type.
 *
 * Give a discount based on the amount spent, e.g. 10% off if you spend over $100, or $5 off t-shirts when you spend $100 in that category.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Total_Spend extends Type implements Actionable, Applicable {

	use Discount_With_Reductions;

	const ORDER = 2;

	/**
	 * @inheritdoc
	 */
	public static function get_slug(): string {
		return 'total_spend';
	}

	/**
	 * @inheritdoc
	 */
	public static function get_name(): string {
		return __( 'Based on total spend', 'woocommerce-discount-manager' );
	}

	/**
	 * @inheritdoc
	 */
	public static function get_tooltip(): string {
		ob_start();
		?>
		<ul>
			<li><?php esc_html_e( '10% off if you spend over $100', 'woocommerce-discount-manager' ); ?></li>
			<li><?php esc_html_e( '$5 off t-shirts when you spend $100 in that category', 'woocommerce-discount-manager' ); ?></li>
		</ul>
		<p><?php esc_html_e( 'Give a discount based on the amount spent.', 'woocommerce-discount-manager' ); ?></p>
		<?php
		return ob_get_clean();
	}

	/**
	 * @inheritdoc
	 */
	public static function get_settings(): array {
		return [
			'total_spend' => [
				'type'  => 'price',
				'label' => __( 'Total spend required for discount', 'woocommerce-discount-manager' ),
			],
			'amount_type' => [
				'type'  => 'discount',
				'label' => __( 'Discount', 'woocommerce-discount-manager' ),
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public static function get_default_settings_values(): array {
		return [
			'total_spend'         => 0,
			'amount_type'         => 'percentage',
			'fixed_discount'      => 0,
			'percentage_discount' => 0,
		];
	}

	/**
	 * Retrieve the total cart value for the relevant products.
	 *
	 * @param WC_Cart $cart
	 * @return float
	 */
	private function get_cart_total_for_relevant_products( WC_Cart $cart ): float {
		$cart_total          = 0;
		$relevant_products   = $this->discount->get_relevant_products( $cart );
		$is_inclusive_of_tax = wc_prices_include_tax();

		foreach ( $relevant_products as $cart_item ) {
			$cart_total += $is_inclusive_of_tax ? $cart_item['line_subtotal'] + $cart_item['line_subtotal_tax'] : $cart_item['line_subtotal'];
		}

		return $cart_total;
	}

	/**
	 * Retrieve the total order value for the relevant products.
	 *
	 * @param WC_Order $order
	 * @return float
	 */
	private function get_order_total_for_relevant_products( WC_Order $order ): float {
		$order_total         = 0;
		$relevant_products   = $this->discount->get_relevant_products( false, $order );
		$is_inclusive_of_tax = wc_prices_include_tax();

		foreach ( $relevant_products as $order_item ) {
			$order_total += $is_inclusive_of_tax ? $order_item->get_subtotal() + $order_item->get_subtotal_tax() : $order_item->get_subtotal();
		}

		return $order_total;
	}

	/**
	 * @inheritdoc
	 */
	public function is_applicable_to_cart( WC_Cart $cart ): bool {
		$cart_total          = $this->get_cart_total_for_relevant_products( $cart );
		$required_cart_total = $this->discount->settings()->get( 'total_spend' )->value();

		return $cart_total >= $required_cart_total;
	}

	/**
	 * Determine if the discount is applicable to the cart or order based on the products it applies to.
	 *
	 * When the discount applies to all products, it is always applicable.
	 * When the discount applies to specific products, it is only applicable if the cart/order contains ONLY those specific products.
	 * When the discount applies to specific categories, it is only applicable if the cart/order contains ONLY products in those categories.
	 *
	 * @param bool|WC_Cart $cart The cart. False when checking an order.
	 * @param bool|WC_Order $order The order. False when checking a cart.
	 * @return boolean
	 */
	private function products_selection_is_applicable( $cart, $order = false ): bool {
		$applies_to_all_products        = $this->discount->is_applicable_to_all_products();
		$applies_to_specific_products   = $this->discount->is_applicable_to_specific_products();
		$applies_to_specific_categories = $this->discount->is_applicable_to_specific_categories();

		if ( $applies_to_all_products ) {
			return true;
		}

		// Check that the cart/order contains only the relevant products.
		if ( $applies_to_specific_products ) {
			$relevant_products = $this->discount->get_relevant_products( $cart, $order );
			$relevant_products = array_column( $relevant_products, 'product_id' );

			if ( $cart ) {
				$cart_products = array_column( $cart->get_cart(), 'product_id' );
				return array_diff( $cart_products, $relevant_products ) === [];
			} elseif ( $order ) {
				$order_products = array_column( $order->get_items(), 'product_id' );
				return array_diff( $order_products, $relevant_products ) === [];
			}
		}

		// Check that the cart/order contains only products in the relevant categories.
		if ( $applies_to_specific_categories ) {
			$relevant_categories = $this->discount->get_elegible_categories();
			$categories_in_cart  = $this->get_all_categories_of_all_products( $cart, $order );

			return array_diff( $categories_in_cart, $relevant_categories ) === [];
		}

		return false;
	}

	/**
	 * Get all categories of all products in the cart/order.
	 *
	 * @param boolean|WC_Cart $cart
	 * @param boolean|WC_Order $order
	 * @return array
	 */
	private function get_all_categories_of_all_products( $cart, $order = false ): array {
		$categories = [];

		if ( $cart ) {
			$cart_items = $cart->get_cart();
			foreach ( $cart_items as $cart_item ) {
				$product    = wc_get_product( $cart_item['product_id'] );
				$categories = array_merge( $categories, $product->get_category_ids() );
			}
		} elseif ( $order ) {
			$order_items = $order->get_items();
			foreach ( $order_items as $order_item ) {
				$product    = wc_get_product( $order_item->get_product_id() );
				$categories = array_merge( $categories, $product->get_category_ids() );
			}
		}

		return $categories;
	}

	/**
	 * @inheritdoc
	 */
	public function is_applicable_to_order( WC_Order $order ): bool {
		$order_total          = $this->get_order_total_for_relevant_products( $order );
		$required_order_total = $this->discount->settings()->get( 'total_spend' )->value();

		return $order_total >= $required_order_total;
	}

	/**
	 * If the discount is applicable, apply it to the cart so that the total of the cart is reduced.
	 *
	 * To achieve this, we need to:
	 * - Retrieve the total cart value.
	 * - Check if the discount type is a percentage or a fixed amount.
	 * - Calculate the new total cart value by reducing the total cart value by the discount amount.
	 * - Use the new total cart value to dynamically apply a reduction to each cart item so that the total cart value now matches the new total cart value.
	 *
	 * @param WC_Cart $cart
	 * @return void
	 */
	public function run_cart_actions( WC_Cart &$cart ): void {
		$subtotal      = floatval( $this->get_cart_total_for_relevant_products( $cart ) );
		$discount_type = $this->get_discount_amount_type();
		$reduction     = $this->get_reduction()->get_amount();

		if ( $subtotal <= 0 ) {
			return;
		}

		if ( $discount_type === 'percentage' ) {
			$discount_amount = $subtotal * ( $reduction / 100 );
		} else {
			$discount_amount = $reduction;
		}

		$new_subtotal = $subtotal - $discount_amount;

		if ( $new_subtotal < 0 ) {
			$new_subtotal = 0;
		}

		$fixed_price         = $new_subtotal;
		$percentage_discount = ( ( $subtotal - $fixed_price ) / $subtotal ) * 100;

		if ( $fixed_price < 0 ) {
			return;
		}

		if ( $fixed_price > $subtotal ) {
			return;
		}

		if ( $percentage_discount <= 0 ) {
			return;
		}

		$reduction            = new Percentage( $percentage_discount, null, $this->get_discount() );
		$products_to_discount = $this->discount->get_relevant_products( $cart );

		foreach ( $products_to_discount as $cart_item_key => $cart_item ) {
			$reduction->apply_reduction( $cart_item['data'], $cart_item );
		}

		wdm()->cache()->mark_discount_as_active( $this->get_discount() );
	}

	/**
	 * If the discount is applicable, apply it to the order so that the total of the order is reduced.
	 *
	 * To achieve this, we need to:
	 * - Retrieve the total order value.
	 * - Check if the discount type is a percentage or a fixed amount.
	 * - Calculate the new total order value by reducing the total order value by the discount amount.
	 * - Use the new total order value to dynamically apply a reduction to each order item so that the total order value now matches the new total order value.
	 *
	 * @param WC_Order $order
	 * @return void
	 */
	public function run_order_actions( WC_Order &$order ): void {
		// Flag that we're processing an order.
		$this->set_proccesing_order( true );

		$subtotal      = floatval( $this->get_order_total_for_relevant_products( $order ) );
		$discount_type = $this->get_discount_amount_type();
		$reduction     = $this->get_reduction()->get_amount();

		if ( $subtotal <= 0 ) {
			// Flag that we're no longer processing an order.
			$this->set_proccesing_order( false );
			return;
		}

		if ( $discount_type === 'percentage' ) {
			$discount_amount = $subtotal * ( $reduction / 100 );
		} else {
			$discount_amount = $reduction;
		}

		$new_subtotal = $subtotal - $discount_amount;

		// Get the amount that needs to be discounted for the virtual coupon.
		$total_discounted_amount = $subtotal - $new_subtotal;

		if ( $new_subtotal < 0 ) {
			return;
		}

		// Track the total amount to discount from the order.
		$this->increase_total_order_discount( $total_discounted_amount );

		// Generate the virtual coupon for the order.
		if ( $this->get_total_order_discount() > 0 ) {
			$this->generate_virtual_coupon_for_order( $order );
		}

		// Flag that we're no longer processing an order.
		$this->set_proccesing_order( false );
	}
}
