<?php

namespace Barn2\Plugin\Discount_Manager\Types;

use Barn2\Plugin\Discount_Manager\Products;
use Barn2\Plugin\Discount_Manager\Reductions\Percentage;
use Barn2\Plugin\Discount_Manager\Traits\Discount_With_Calculated_Subtotal;
use Barn2\Plugin\Discount_Manager\Traits\Discount_With_Required_Qty;
use Barn2\Plugin\Discount_Manager\Util;
use WC_Cart;
use WC_Order;

use function Barn2\Plugin\Discount_Manager\wdm;

/**
 * Buy X for Y fixed discount type.
 *
 * Charge a fixed price for buying multiple quantities, e.g. 2 t-shirts for $25.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Buy_X_For_Y_Fixed extends Type implements Applicable, Actionable {

	use Discount_With_Required_Qty;
	use Discount_With_Calculated_Subtotal;

	const ORDER = 4;

	/**
	 * @inheritdoc
	 */
	public static function get_slug(): string {
		return 'buy_x_for_y_fixed';
	}

	/**
	 * @inheritdoc
	 */
	public static function get_name(): string {
		return __( 'Buy X products for a fixed price', 'woocommerce-discount-manager' );
	}

	/**
	 * @inheritdoc
	 */
	public static function get_tooltip(): string {
		ob_start();
		?>
		<ul>
			<li><?php esc_html_e( 'Any 10 products for $100', 'woocommerce-discount-manager' ); ?></li>
			<li><?php esc_html_e( '2 t-shirts for $25', 'woocommerce-discount-manager' ); ?></li>
			<li><?php esc_html_e( '5 of the ‘Barn2 Hoodie’ product for $150', 'woocommerce-discount-manager' ); ?></li>
		</ul>
		<p><?php esc_html_e( 'Charge a fixed price for buying multiple quantities.', 'woocommerce-discount-manager' ); ?></p>
		<?php
		return ob_get_clean();
	}

	/**
	 * @inheritdoc
	 */
	public static function get_settings(): array {
		return [
			'required_qty' => [
				'type'    => 'number',
				'label'   => __( 'Quantity required for discount', 'woocommerce-discount-manager' ),
				'default' => 1,
			],
			'fixed_price'  => [
				'type'  => 'price',
				'label' => __( 'Fixed price', 'woocommerce-discount-manager' ),
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public static function get_default_settings_values(): array {
		return [
			'required_qty' => 1,
			'fixed_price'  => 0,
		];
	}

	/**
	 * @inheritdoc
	 */
	public function is_applicable_to_cart( WC_Cart $cart ): bool {
		return $this->cart_meets_products_selection_and_quantity( $cart, $this->discount );
	}

	/**
	 * @inheritdoc
	 */
	public function is_applicable_to_order( WC_Order $order ): bool {
		return $this->order_meets_products_selection_and_quantity( $order, $this->discount );
	}

	/**
	 * Get the fixed price for the discount.
	 *
	 * @return float
	 */
	public function get_fixed_price(): float {
		$settings = $this->discount->settings();

		if ( $settings->has( 'fixed_price' ) ) {
			return floatval( $settings->get( 'fixed_price' )->value() );
		}

		return 0.0;
	}

	/**
	 * Forces products to be discounted and match the fixed price.
	 * This is used to ensure that the total price of the cart/order is equal
	 * to the fixed price as set in the discount settings.
	 *
	 * - If the discount applies to all products, then all products will be discounted so that the subtotal of the cart is equal to the fixed price.
	 * - If the discount applies to specific products, then only those products will be discounted so that the subtotal of those products combined is equal to the fixed price.
	 * - If the discount applies to specific categories, then only products in those categories will be discounted so that the subtotal of those products combined is equal to the fixed price.
	 *
	 * @param WC_Cart $cart The cart.
	 * @return void
	 */
	public function run_cart_actions( WC_Cart &$cart ): void {
		$required_qty = $this->get_min_required_qty();
		$products     = $this->discount->get_relevant_products( $cart );

		$total_relevant_products_qty = $this->get_cart_quantity_of_relevant_products( $cart );

		if ( $total_relevant_products_qty >= $required_qty ) {
			$this->maybe_discount_all_items( $products, $cart, false );
		}
	}

	/**
	 * Maybe discount an item.
	 *
	 * @param mixed $item The cart item or order item.
	 * @param boolean|WC_Cart $cart The cart.
	 * @param boolean|WC_Order $order The order.
	 * @return void
	 */
	private function maybe_discount_item( $item, $cart = false, $order = false ): void {
		// Determine if the item has the required quantity.
		$item_has_required_qty = $order ? $item->get_quantity() >= $this->get_min_required_qty() : $item['quantity'] >= $this->get_min_required_qty();

		// Skip if no required quantity.
		if ( ! $item_has_required_qty ) {
			return;
		}

		// Get the quantity of the item.
		$item_quantity = $order ? $item->get_quantity() : $item['quantity'];

		$original_subtotal = $order ? $item->get_total() : $item['line_total'];
		$reduction_amount  = $this->get_fixed_price();

		// Merge taxes with the original subtotal.
		if ( Util::prices_include_tax() ) {
			if ( $order ) {
				$original_subtotal += $item->get_total_tax();
			} else {
				$original_subtotal += $item['line_tax'];
			}
		}

		// Define the discount amount.
		$discount_amount = $reduction_amount; // 10% or $10, depending on the discount type. Forced to be $10 (example).

		$quantity          = $item_quantity;
		$unit_price        = $original_subtotal / $quantity;
		$discount_quantity = $this->get_min_required_qty();
		$discount_price    = $discount_amount;

		// Calculate the total cost with the discount
		$total = 0;
		while ( $quantity >= $discount_quantity ) {
			$total    += $discount_price;
			$quantity -= $discount_quantity;
		}

		// Add the remaining items at the regular price
		$total += $quantity * $unit_price;

		$fixed_price         = $total;
		$percentage_discount = ( ( $original_subtotal - $fixed_price ) / $original_subtotal ) * 100;

		if ( $fixed_price < 0 ) {
			return;
		}

		if ( $fixed_price > $original_subtotal ) {
			return;
		}

		if ( $percentage_discount <= 0 ) {
			return;
		}

		if ( $this->is_processing_order() && $order ) {
			$this->increase_total_order_discount( $original_subtotal - $fixed_price );
		} else {
			$reduction = new Percentage( $percentage_discount, null, $this->get_discount() );
			$reduction->apply_reduction( $item['data'], $item );
		}

		wdm()->cache()->mark_discount_as_active( $this->get_discount() );
	}

	/**
	 * Maybe discount all items.
	 *
	 * @param array $items Array of cart items or order items.
	 * @param boolean|WC_Cart $cart The cart.
	 * @param boolean|WC_Order $order The order.
	 * @return void
	 */
	private function maybe_discount_all_items( array $items, $cart = false, $order = false ): void {
		$original_subtotal = $order ? $this->get_order_subtotal( $order ) : $this->get_cart_subtotal( $cart );

		// Define a products array as an associative array with product IDs as keys, and quantities and prices as values. Taking into consideration that items might be coming from an order.
		$products = [];

		foreach ( $items as $cart_item_key => $cart_item ) {
			$product_id = $order ? $cart_item->get_product_id() : $cart_item['product_id'];
			$quantity   = $order ? $cart_item->get_quantity() : $cart_item['quantity'];
			$price      = $order ? $cart_item->get_total() : $cart_item['line_total'];

			// If the item is a variation, then we need to override the $product_id with the variation id.
			if ( $order && $cart_item->get_product_type() === 'variation' ) {
				$product_id = $cart_item->get_variation_id();
			} elseif ( $cart && $cart_item['variation_id'] ) {
				$product_id = $cart_item['variation_id'];
			}

			// Merge taxes with the original subtotal.
			if ( Util::prices_include_tax() ) {
				if ( $order ) {
					$price += $cart_item->get_total_tax();
				} else {
					$price += $cart_item['line_tax'];
				}
			}

			$price /= $quantity;

			if ( ! isset( $products[ $product_id ] ) ) {
				$products[ $product_id ] = [
					'quantity' => 0,
					'price'    => 0,
				];
			}

			$products[ $product_id ]['quantity'] += $quantity;
			$products[ $product_id ]['price']    += $price;
		}

		$discount_quantity = $this->get_min_required_qty();
		$discount_price    = $this->get_fixed_price();

		// Calculate the total quantity of all products in the cart.
		$total_quantity = 0;

		foreach ( $products as $product ) {
			$total_quantity += $product['quantity'];
		}

		// Calculate how many times the discount can be applied based on the total quantity.
		$discount_applications = floor( $total_quantity / $discount_quantity );

		// Calculate the total cost with the discount applied.
		$total = $discount_applications * $discount_price;

		// Calculate the remaining items at the regular price.
		$remaining_quantity = $total_quantity % $discount_quantity;

		foreach ( $products as $product ) {
			$price    = $product['price'];
			$quantity = $product['quantity'];

			// If there are remaining items for this product, add them at the regular price.
			if ( $remaining_quantity > 0 ) {
				$items_to_apply_discount = min( $remaining_quantity, $quantity );
				$total                  += $items_to_apply_discount * $price;
				$remaining_quantity     -= $items_to_apply_discount;
			}
		}

		$fixed_price         = $total;
		$percentage_discount = ( ( $original_subtotal - $fixed_price ) / $original_subtotal ) * 100;

		if ( $fixed_price < 0 ) {
			return;
		}

		if ( $fixed_price > $original_subtotal ) {
			return;
		}

		if ( $percentage_discount <= 0 ) {
			return;
		}

		if ( $this->is_processing_order() && $order ) {
			$this->increase_total_order_discount( $original_subtotal - $fixed_price );
		} else {
			$reduction = new Percentage( $percentage_discount, null, $this->get_discount() );

			foreach ( $items as $cart_item_key => $cart_item ) {
				$reduction->apply_reduction( $cart_item['data'], $cart_item );
			}
		}

		wdm()->cache()->mark_discount_as_active( $this->get_discount() );
	}

	/**
	 * Get the cheapest product with the required quantity.
	 * The $products array is expected to be sorted by price ascending.
	 *
	 * Additionally the $products array may contain cart items or order items.
	 *
	 * @param array $products Array of cart items or order items.
	 * @param integer $required_qty The required quantity.
	 * @param boolean|WC_Cart $cart The cart.
	 * @param boolean|WC_Order $order The order.
	 * @return mixed
	 */
	private function get_cart_order_cheapest_product_with_required_qty( array $products, int $required_qty, $cart = false, $order = false ) {
		// Find the cheapest product with the required quantity from the products array.
		foreach ( $products as $product ) {
			$product_qty = $order ? $product->get_quantity() : $product['quantity'];

			if ( $product_qty >= $required_qty ) {
				return $product;
			}
		}

		return false;
	}

	/**
	 * Apply a percentage discount to all order items in the order so that the
	 * total price is equal to the fixed price as set in the discount settings.
	 *
	 * @param WC_Order $order The order.
	 * @return void
	 */
	public function run_order_actions( WC_Order &$order ): void {
		$this->set_proccesing_order( true );

		$required_qty = $this->get_min_required_qty();
		$products     = $this->discount->get_relevant_products( false, $order );

		$total_relevant_products_qty = $this->get_order_quantity_of_relevant_products( $order );

		if ( $total_relevant_products_qty >= $required_qty ) {
			$this->maybe_discount_all_items( $products, false, $order );
		}

		if ( wdm()->cache()->is_discount_active( $this->get_discount() ) && $this->get_total_order_discount() > 0 ) {
			$this->generate_virtual_coupon_for_order( $order );
		}

		$this->set_proccesing_order( false );
	}
}
