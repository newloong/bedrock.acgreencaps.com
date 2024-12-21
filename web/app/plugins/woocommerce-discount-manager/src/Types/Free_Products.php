<?php

namespace Barn2\Plugin\Discount_Manager\Types;

use Barn2\Plugin\Discount_Manager\Reductions\Percentage;
use Barn2\Plugin\Discount_Manager\Traits\Discount_With_Calculated_Subtotal;
use Barn2\Plugin\Discount_Manager\Traits\Discount_With_Required_Qty;
use Barn2\Plugin\Discount_Manager\Util;
use WC_Cart;
use WC_Order;

use function Barn2\Plugin\Discount_Manager\wdm;

/**
 * Free products discount type.
 *
 * Give away one or more products, e.g. buy-one-get-one-free, or buy 3 for the price of 2.
 * The cheapest qualifying product will always be used as the free product.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Free_Products extends Type implements Actionable, Applicable {

	use Discount_With_Required_Qty;
	use Discount_With_Calculated_Subtotal;

	const ORDER = 3;

	/**
	 * @inheritdoc
	 */
	public static function get_slug(): string {
		return 'free_products';
	}

	/**
	 * @inheritdoc
	 */
	public static function get_name(): string {
		return __( 'Free products', 'woocommerce-discount-manager' );
	}

	/**
	 * @inheritdoc
	 */
	public static function get_tooltip(): string {
		ob_start();
		?>
		<ul>
			<li><?php esc_html_e( 'BOGO (buy 1 get 1 free)', 'woocommerce-discount-manager' ); ?></li>
			<li><?php esc_html_e( '3 for the price of 2', 'woocommerce-discount-manager' ); ?></li>
			<li><?php esc_html_e( 'Buy 4 get 1 free', 'woocommerce-discount-manager' ); ?></li>
		</ul>
		<p><?php esc_html_e( 'Give away one or more products. The cheapest qualifying product will always be used as the free product.', 'woocommerce-discount-manager' ); ?></p>
		<?php
		return ob_get_clean();
	}

	/**
	 * @inheritdoc
	 */
	public static function get_settings(): array {
		return [
			'required_qty'       => [
				'type'    => 'number',
				'label'   => __( 'Number of paid products required', 'woocommerce-discount-manager' ),
				'default' => 1,
			],
			'free_qty'           => [
				'type'    => 'number',
				'label'   => __( 'Number of free products given', 'woocommerce-discount-manager' ),
				'default' => 1,
			],
			'application_method' => [
				'type'    => 'radio',
				'label'   => __( 'How should the discount be applied?', 'woocommerce-discount-manager' ),
				'options' => [
					'like' => __( 'Like for like product only', 'woocommerce-discount-manager' ),
					'any'  => __( 'Any product selected above', 'woocommerce-discount-manager' ),
				],
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public static function get_default_settings_values(): array {
		return [
			'required_qty'       => 1,
			'free_qty'           => 1,
			'application_method' => 'like',
		];
	}

	/**
	 * Get the application method for the discount.
	 *
	 * @return string The application method.
	 */
	public function get_application_method(): string {
		return $this->discount->settings()->get( 'application_method' ) ? $this->discount->settings()->get( 'application_method' )->value() : 'like';
	}

	/**
	 * Determine whether the discount should be calculated based on the cart quantity.
	 *
	 * @return boolean
	 */
	public function is_based_on_cart_quantity(): bool {
		return $this->get_application_method() === 'any';
	}

	/**
	 * Determine whether the discount should be calculated based on the line item quantity.
	 *
	 * @return boolean
	 */
	public function is_based_on_line_item_quantity(): bool {
		return $this->get_application_method() === 'like';
	}

	/**
	 * Retrieve the minimum required quantity for the discount to apply.
	 *
	 * @return integer
	 */
	public function get_free_qty(): int {
		return (int) $this->discount->settings()->get( 'free_qty' )->value();
	}

	/**
	 * Get the products elegible for the discount.
	 * These are line items that have a quantity of $total_qty or higher.
	 *
	 * @param array $products The products.
	 * @param boolean $is_order Whether the products are order items.
	 * @return array The elegible products.
	 */
	public function get_elegible_products( array $products, bool $is_order = false ): array {
		$free_qty     = $this->get_free_qty();
		$required_qty = $this->get_min_required_qty();
		$total_qty    = $free_qty + $required_qty;

		// Filter the array and make sure we only have the products we need that have a total or higher quantity of $total_qty.
		$products = array_filter(
			$products,
			function ( $product ) use ( $total_qty, $is_order ) {
				return $is_order ? $product->get_quantity() >= $total_qty : $product['quantity'] >= $total_qty;
			}
		);

		return $products;
	}

	/**
	 * @inheritdoc
	 */
	public function is_applicable_to_cart( WC_Cart $cart ): bool {
		$free_qty     = $this->get_free_qty();
		$required_qty = $this->get_min_required_qty();

		return $cart->get_cart_contents_count() >= $required_qty + $free_qty;
	}

	/**
	 * @inheritdoc
	 */
	public function is_applicable_to_order( WC_Order $order ): bool {
		$free_qty     = $this->get_free_qty();
		$required_qty = $this->get_min_required_qty();

		return $order->get_item_count() >= $required_qty + $free_qty;
	}

	/**
	 * @inheritdoc
	 */
	public function run_cart_actions( WC_Cart &$cart ): void {
		$relevant_products  = $this->discount->get_relevant_products( $cart );
		$is_line_item_based = $this->is_based_on_line_item_quantity();
		$is_cart_based      = $this->is_based_on_cart_quantity();

		if ( empty( $relevant_products ) ) {
			return;
		}

		if ( $is_line_item_based ) {
			$this->discount_products_line_item_basis( $relevant_products );
		} elseif ( $is_cart_based ) {
			$this->discount_products_cart_basis( $relevant_products, $cart );
		}
	}

	/**
	 * Calculate the number of items to buy with discount for a given total quantity
	 * based on the number of items needed for one discount loop.
	 *
	 * @param int $total_quantity The total quantity of the product in the cart.
	 * @param int $full_price_quantity The number of items to buy at full price.
	 * @param int $free_quantity The number of items to buy with discount.
	 * @param boolean $is_line_item_based Whether the discount is based on the line item quantity.
	 * @return int Discount loops.
	 */
	private function calculate_number_of_items_to_buy_with_discount( int $total_quantity, int $full_price_quantity, int $free_quantity, bool $is_line_item_based = false ): int {
		if ( $is_line_item_based ) {
			$elegible_for_discount = 0;

			while ( $total_quantity >= ( $full_price_quantity + $free_quantity ) ) {
				// Calculate how many items can be discounted in this loop
				$elegible_for_discount += $free_quantity;

				// Deduct the products used in this loop from the total
				$total_quantity -= ( $full_price_quantity + $free_quantity );
			}

			return $elegible_for_discount;
		}

		// Calculate the total quantity needed for one discount loop
		$items_needed_for_discount = $full_price_quantity + $free_quantity;

		// Calculate the number of times the discount can be applied
		$discount_loops = intdiv( $total_quantity, $items_needed_for_discount );

		return $discount_loops;
	}

	/**
	 * Discount the products based on the line item quantity.
	 *
	 * @param array $products The products.
	 * @param bool  $is_order Whether the products are order items.
	 * @param bool  $is_cart_basis Whether the discount is based on the cart quantity. (This bypasses the min required qty check)
	 * @param int   $force_apply_count The number of items to apply the discount to.
	 * @return void
	 */
	private function discount_products_line_item_basis( array $products, $is_order = false, bool $is_cart_basis = false, int $force_apply_count = 0 ): void {
		$products = $is_cart_basis ? $products : $this->get_elegible_products( $products, $is_order );

		if ( empty( $products ) ) {
			return;
		}

		// Number of items to give away.
		$number_of_items_to_discount = $this->get_free_qty();

		foreach ( $products as $product ) {

			// Get the quantity of the product.
			$quantity         = $is_order ? $product->get_quantity() : $product['quantity'];
			$total            = $is_order ? floatval( $product->get_total() ) : floatval( $product['line_total'] );
			$reduction_type   = 'percentage';
			$reduction_amount = 100;

			// If there's taxes merge with the total.
			if ( Util::prices_include_tax() ) {
				if ( $is_order ) {
					$total += floatval( $product->get_total_tax() );
				} else {
					$total += floatval( $product['line_tax'] );
				}
			}

			// Cost of a single item of the product.
			$individual_item_price = $total / $quantity;

			// Define the price and quantity for the product
			$product_price    = $individual_item_price; // The regular price per item
			$product_quantity = $quantity; // Quantity of the product in the cart

			// Define the discount type - must be percentage in this case.
			$discount_type = $reduction_type;

			// Define the discount value - must be 100 in this case.
			$discount_value = $reduction_amount;

			// Define the number of items to apply the discount to
			$discount_apply_count = $this->calculate_number_of_items_to_buy_with_discount( $quantity, $this->get_min_required_qty(), $number_of_items_to_discount, ! $is_cart_basis );

			if ( $is_cart_basis && $force_apply_count > 0 ) {
				$discount_apply_count = $force_apply_count;
			}

			if ( $discount_apply_count < 1 ) {
				$discount_apply_count = 1;
			}

			$this->increase_total_discounted_products( $discount_apply_count );

			// Calculate the total price for all items without discount
			$total_price = $product_price * $product_quantity;

			// Calculate the number of times the discount should be applied
			$discount_applications = min( $discount_apply_count, $product_quantity );

			// Apply the discount to the specified subset of items based on the specified type
			if ( $discount_applications > 0 ) {
				if ( $discount_type === 'percentage' && $discount_value > 0 && $discount_value <= 100 ) {
					// Calculate the percentage discount for each application
					$discount = ( $product_price * $discount_value / 100 ) * $discount_applications;
				} else {
					// No valid discount type or value provided
					$discount = 0;
				}

				// Subtract the discount from the total price
				$total_price -= $discount;
			}

			$fixed_price         = $total_price;
			$percentage_discount = ( ( $total - $fixed_price ) / $total ) * 100;

			// For each product in the order, we need to calculate the discount amount and then add it to the total discount amount.
			if ( $is_order && $this->is_processing_order() ) {
				$this->increase_total_order_discount( $total - $fixed_price );
			} else {
				$reduction = new Percentage( $percentage_discount, null, $this->get_discount() );
				$reduction->apply_reduction( $product['data'], $product );
			}
		}

		wdm()->cache()->mark_discount_as_active( $this->get_discount() );
	}

	/**
	 * Discount the additional products based on the cart quantity.
	 *
	 * @param array $products The products.
	 * @param WC_Cart|boolean $cart The cart.
	 * @param WC_Order|boolean $order The order.
	 * @return void
	 */
	private function discount_products_cart_basis( array $products, $cart = false, $order = false ): void {
		// Number of items to give away.
		$number_of_items_to_discount = $this->get_free_qty();

		if ( $number_of_items_to_discount === 0 ) {
			return;
		}

		$this->discount_cheapest_in_cart_order( $products, $cart, $order );
	}

	/**
	 * Discount the products based on the subtotal of the relevant products.
	 * Here we retrieve the total quantity of the relevant products and then calculate how many times the discount should be applied.
	 *
	 * @param array $products The products.
	 * @param WC_Cart|boolean $cart Whether the products are cart items.
	 * @param WC_Order|boolean $order Whether the products are order items.
	 * @return void
	 */
	private function discount_cheapest_in_cart_order( array $products, $cart = false, $order = false ): void {
		$relevant_products    = $products;
		$to_discount_per_loop = $this->get_free_qty();
		$req_qty              = $this->get_min_required_qty();
		$sub_total            = floatval( $cart ? $this->get_cart_subtotal( $cart ) : $this->get_order_subtotal( $order ) );

		// Calculate the quantity of the products in the cart.
		$number_of_items = array_sum( array_column( $relevant_products, 'quantity' ) );

		// Calculate the number of times the discount should be applied.
		$number_of_loops = $this->calculate_number_of_items_to_buy_with_discount( $number_of_items, $req_qty, $to_discount_per_loop );

		// Calculate the total number of products to be discounted.
		$number_of_items_to_discount = $number_of_loops * $to_discount_per_loop;

		// If the number of items to discount is 0, then we don't need to do anything.
		if ( $number_of_items_to_discount === 0 ) {
			return;
		}

		$total_discounted_price = 0;
		$items_discounted       = 0;

		// Loop through the products array
		foreach ( $relevant_products as $product => $details ) {
			$price    = $order ? $details->get_total() : $details['line_total'];
			$quantity = $order ? $details->get_quantity() : $details['quantity'];

			// If there's taxes merge with the total.
			if ( Util::prices_include_tax() ) {
				if ( $order ) {
					$price += $details->get_total_tax();
				} else {
					$price += $details['line_tax'];
				}
			}

			// Calculate the price per item
			$price = $price / $quantity;

			// Calculate the discounted price for the current product
			$discounted_price = min( $number_of_items_to_discount - $items_discounted, $quantity ) * $price;

			// Update counters
			$items_discounted       += min( $number_of_items_to_discount - $items_discounted, $quantity );
			$total_discounted_price += $discounted_price;

			// Check if the required number of items to discount is reached
			if ( $items_discounted >= $number_of_items_to_discount ) {
				break;
			}
		}

		if ( $total_discounted_price <= 0 ) {
			return;
		}

		$fixed_price         = $sub_total - $total_discounted_price;
		$percentage_discount = ( ( $sub_total - $fixed_price ) / $sub_total ) * 100;

		if ( $fixed_price < 0 ) {
			$fixed_price = 0;
		}

		if ( $fixed_price > $sub_total ) {
			return;
		}

		if ( $percentage_discount <= 0 ) {
			return;
		}

		if ( $cart ) {
			foreach ( $products as $product ) {
				$reduction = new Percentage( $percentage_discount, null, $this->get_discount() );
				$reduction->apply_reduction( $product['data'], $product );
			}
		} elseif ( $order && $this->is_processing_order() ) {
			$this->increase_total_order_discount( $sub_total - $fixed_price );
		}

		wdm()->cache()->mark_discount_as_active( $this->get_discount() );
	}

	/**
	 * @inheritdoc
	 */
	public function run_order_actions( WC_Order &$order ): void {
		// Flag the order as processing.
		$this->set_proccesing_order( true );

		$relevant_products  = $this->discount->get_relevant_products( false, $order );
		$is_line_item_based = $this->is_based_on_line_item_quantity();
		$is_cart_based      = $this->is_based_on_cart_quantity();

		if ( empty( $relevant_products ) ) {
			return;
		}

		if ( $is_line_item_based ) {
			$this->discount_products_line_item_basis( $relevant_products, true );
		} elseif ( $is_cart_based ) {
			$this->discount_products_cart_basis( $relevant_products, false, $order );
		}

		if ( $this->get_total_order_discount() > 0 ) {
			$this->generate_virtual_coupon_for_order( $order );
		}

		// Flag the order as not processing.
		$this->set_proccesing_order( false );
	}
}
