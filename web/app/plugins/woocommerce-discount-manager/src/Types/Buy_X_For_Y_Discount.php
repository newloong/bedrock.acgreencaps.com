<?php

namespace Barn2\Plugin\Discount_Manager\Types;

use Barn2\Plugin\Discount_Manager\Products;
use Barn2\Plugin\Discount_Manager\Reductions\Percentage;
use Barn2\Plugin\Discount_Manager\Traits\Discount_With_Calculated_Subtotal;
use Barn2\Plugin\Discount_Manager\Traits\Discount_With_Reductions;
use Barn2\Plugin\Discount_Manager\Traits\Discount_With_Required_Qty;
use Barn2\Plugin\Discount_Manager\Util;
use WC_Cart;
use WC_Order;

use function Barn2\Plugin\Discount_Manager\wdm;

/**
 * Buy X for Y discount type.
 *
 * Give a percentage or fixed discount when the customer buys a certain quantity, e.g.
 * buy products and get 10% off, or buy 4 t-shirts and get $5 off.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Buy_X_For_Y_Discount extends Type implements Actionable, Applicable {

	use Discount_With_Reductions;
	use Discount_With_Required_Qty;
	use Discount_With_Calculated_Subtotal;

	const ORDER = 5;

	/**
	 * @inheritdoc
	 */
	public static function get_slug(): string {
		return 'buy_x_for_y_discount';
	}

	/**
	 * @inheritdoc
	 */
	public static function get_name(): string {
		return __( 'Buy X products for Y discount', 'woocommerce-discount-manager' );
	}

	/**
	 * @inheritdoc
	 */
	public static function get_tooltip(): string {
		ob_start();
		?>
		<ul>
			<li><?php esc_html_e( 'Buy 5 products and get 10% off', 'woocommerce-discount-manager' ); ?></li>
			<li><?php esc_html_e( 'Buy 4 t-shirts and get $5 off', 'woocommerce-discount-manager' ); ?></li>
		</ul>
		<p><?php esc_html_e( 'Give a percentage or fixed discount when the customer buys a certain quantity.', 'woocommerce-discount-manager' ); ?></p>
		<?php
		return ob_get_clean();
	}

	/**
	 * @inheritdoc
	 */
	public static function get_settings(): array {
		return [
			'required_qty'   => [
				'type'    => 'number',
				'label'   => __( 'Quantity required for discount', 'woocommerce-discount-manager' ),
				'default' => 1,
			],
			'amount_type'    => [
				'type'  => 'discount',
				'label' => __( 'Discount', 'woocommerce-discount-manager' ),
			],
			'apply_to'       => [
				'type'    => 'radio',
				'label'   => __( 'Apply to all products or additional products above the required quantity?', 'woocommerce-discount-manager' ),
				'options' => [
					'any'      => __( 'All products', 'woocommerce-discount-manager' ),
					'addition' => __( 'Additional products only', 'woocommerce-discount-manager' ),
				],
			],
			'additional_qty' => [
				'type'       => 'number',
				'label'      => __( 'Number of additional products', 'woocommerce-discount-manager' ),
				'default'    => 1,
				'conditions' => [
					'rules' => [
						'apply_to' => [
							'op'    => 'eq',
							'value' => 'addition',
						],
						'type'     => [
							'op'    => 'eq',
							'value' => self::get_slug(),
						],
					],
				],
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public static function get_default_settings_values(): array {
		return [
			'required_qty'        => 1,
			'amount_type'         => 'percentage',
			'fixed_discount'      => 0,
			'percentage_discount' => 0,
			'apply_to'            => 'any',
			'additional_qty'      => 1,
		];
	}

	/**
	 * Determine whether the discount is applicable to all products or
	 * additional products above the required quantity.
	 *
	 * @return string 'any' or 'addition'
	 */
	public function get_apply_to(): string {
		return $this->discount->settings()->get( 'apply_to' ) ? $this->discount->settings()->get( 'apply_to' )->value() : 'any';
	}

	/**
	 * @inheritdoc
	 */
	public function is_applicable_to_cart( WC_Cart $cart ): bool {
		$apply_to = $this->get_apply_to();

		// If the discount is not applicable to additional products, then we only need to check
		// that the required quantity of products is in the cart.
		if ( $apply_to === 'any' ) {
			return $this->cart_meets_products_selection_and_quantity( $cart, $this->discount );
		}

		return $this->cart_meets_products_selection_and_quantity( $cart, $this->discount );
	}

	/**
	 * @inheritdoc
	 */
	public function is_applicable_to_order( WC_Order $order ): bool {
		$apply_to = $this->get_apply_to();

		// If the discount is not applicable to additional products, then we only need to check
		// that the required quantity of products is in the cart.
		if ( $apply_to === 'any' ) {
			return $this->order_meets_products_selection_and_quantity( $order, $this->discount );
		}

		return $this->order_meets_products_selection_and_quantity( $order, $this->discount );
	}

	/**
	 * Apply the discount to the cart or order.
	 *
	 * The idea with "All products" is to offer a discount that loops
	 * each time a line item has been found with the required quantity.
	 *
	 * Alternatively, when there's a sufficient quantity of products in the cart/order (total line items),
	 * the discount loops.
	 *
	 * @param array $products Array of products to apply the discount to (cart items or order items).
	 * @param boolean|WC_Cart $cart Whether we're handling a cart or an order.
	 * @param boolean|WC_Order $order Whether the products are order items or cart items.
	 * @return void
	 */
	public function apply_discount_to_all_products( array $products, $cart = false, $order = false ): void {
		$required_qty               = $this->get_min_required_qty();
		$total_line_items           = $cart ? count( $cart->get_cart() ) : count( $order->get_items() );
		$apply_to                   = $this->get_apply_to();
		$should_skip_additional_qty = $apply_to === 'any' ? true : false;

		// Check if any of the products in the cart or order has the required quantity.
		$has_product_with_required_qty = $cart ? $this->cart_has_product_with_required_qty( $products, false, $should_skip_additional_qty ) : $this->order_has_product_with_required_qty( $products, false, $should_skip_additional_qty );

		if ( $has_product_with_required_qty ) {

			// Get the cheapest product with the required quantity.
			$product = $this->get_cheapest_product_with_required_qty( $products, $required_qty, $order instanceof WC_Order );

			if ( $product === null ) {
				return;
			}

			$qty = $cart ? $product['quantity'] : $product->get_quantity();

			$this->discount_cheapest_product_with_the_required_qty(
				[ $product ],
				$required_qty,
				$required_qty,
				$order instanceof WC_Order,
				intval( $qty / $required_qty )
			);

		} elseif ( ! $has_product_with_required_qty && $total_line_items >= $required_qty ) {
			$this->discount_cart_level(
				$products,
				$cart,
				$order,
				$total_line_items,
				intval( $total_line_items / $required_qty )
			);
		}
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
		// Bail out if no items available for discount
		if ( empty( $items ) ) {
			return;
		}

		$original_subtotal = $order ? $this->get_order_subtotal( $order ) : $this->get_cart_subtotal( $cart );

		// Define a products array as an associative array with product IDs as keys, and quantities and prices as values.
		$products = [];

		foreach ( $items as $cart_item_key => $cart_item ) {
			$product_id = $order ? $cart_item->get_product_id() : $cart_item['product_id'];
			$quantity   = $order ? $cart_item->get_quantity() : $cart_item['quantity'];
			$price      = $order ? $cart_item->get_total() : $cart_item['line_total'];

			// Handle variations
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

		$min_required_qty = $this->get_min_required_qty();
		$discount_type    = $this->get_discount_amount_type();
		$discount_value   = $this->get_reduction()->get_amount();

		$total_discounted_amount = 0;

		foreach ( $products as $product_id => $product ) {
			$product_price    = $product['price'];
			$product_quantity = $product['quantity'];

			$discount_apply_count = floor( $product_quantity / $min_required_qty );
			$discounted_quantity  = $discount_apply_count * $min_required_qty;
			$regular_quantity     = $product_quantity - $discounted_quantity;

			$discounted_subtotal = $product_price * $discounted_quantity;
			$regular_subtotal    = $product_price * $regular_quantity;

			if ( $discount_type === 'percentage' && $discount_value > 0 && $discount_value <= 100 ) {
				$discount_amount = $discounted_subtotal * ( $discount_value / 100 );
			} elseif ( $discount_type === 'fixed' && $discount_value > 0 ) {
				$discount_amount = $discount_apply_count * $discount_value;
			} else {
				$discount_amount = 0;
			}

			$total_discounted_amount += $discount_amount;
		}

		$fixed_price         = $original_subtotal - $total_discounted_amount;
		$percentage_discount = ( $total_discounted_amount / $original_subtotal ) * 100;

		if ( $fixed_price < 0 ) {
			$fixed_price = 0;
		}

		if ( $fixed_price > $original_subtotal ) {
			return;
		}

		if ( $percentage_discount <= 0 ) {
			return;
		}

		if ( $this->is_processing_order() && $order ) {
			$this->increase_total_order_discount( $total_discounted_amount );
		} else {
			$reduction = new Percentage( $percentage_discount, null, $this->get_discount() );

			foreach ( $items as $cart_item_key => $cart_item ) {
				$reduction->apply_reduction( $cart_item['data'], $cart_item );
			}
		}

		wdm()->cache()->mark_discount_as_active( $this->get_discount() );
	}

	/**
	 * Apply the discount to the cart or order subtotal
	 * by looping through the products and applying the discount to each product
	 * so that the final discount is applied to the cart or order subtotal.
	 *
	 * @param array $products Array of products to apply the discount to (cart items or order items).
	 * @param boolean|WC_Cart $cart Whether we're handling a cart or an order.
	 * @param boolean|WC_Order $order Whether the products are order items or cart items.
	 * @param int $total_line_items The total number of line items in the cart or order.
	 * @param int $discount_applications The number of times the discount should be applied.
	 * @return void
	 */
	private function discount_cart_level( array $products, $cart = false, $order = false, int $total_line_items = 0, int $discount_applications = 0 ): void {
		$total_discount_applications = 0;
		$original_subtotal           = $cart ? $cart->get_subtotal() : $order->get_subtotal();
		$subtotal                    = $cart ? $cart->get_subtotal() : $order->get_subtotal();
		$reduction                   = $this->get_reduction();
		$reduction_type              = $this->get_discount_amount_type();
		$reduction_amount            = $reduction->get_amount();

		// Define the discount type and amount
		$discount_type   = $reduction_type; // or 'fixed'
		$discount_amount = $reduction_amount; // 10% or $10, depending on the discount type

		// Calculate the discount amount based on the discount type and amount
		if ( $discount_type === 'percentage' ) {
			$discount = $subtotal * ( $discount_amount / 100 );
		} else {
			$discount = $discount_amount;
		}

		// Apply a reduction to the subtotal for each application.
		while ( $total_discount_applications < $discount_applications ) {
			$subtotal -= $discount;
			++$total_discount_applications;
		}

		$fixed_price         = $subtotal;
		$percentage_discount = ( ( $original_subtotal - $fixed_price ) / $original_subtotal ) * 100;

		if ( empty( $products ) ) {
			return;
		}

		if ( $fixed_price < 0 ) {
			return;
		}

		if ( $fixed_price > $original_subtotal ) {
			return;
		}

		if ( $percentage_discount <= 0 ) {
			return;
		}

		$reduction = new Percentage( $percentage_discount, null, $this->get_discount() );

		foreach ( $products as $item_key => $item ) {
			if ( $cart ) {
				$reduction->apply_reduction( $item['data'], $item );
			} else {
				$reduction->apply_reduction_to_order_item( $item );
			}
		}

		wdm()->cache()->mark_discount_as_active( $this->get_discount() );
	}

	/**
	 * The idea with "Additional products only" is to offer this kind of discount:
	 *
	 * "Buy 2, get a 3rd half price."
	 *
	 * Here's an example where the product costs $10.
	 *
	 * Cart calculation for 2 products in cart:
	 * 2 at full price, 2 x $10 = $20
	 * Cart total = $20
	 *
	 * Cart calculation for 3 products in cart:
	 * 2 at full price, 2 x $10 = $20
	 * 1 at half price, 1 X $5 = $5
	 * Cart total = $25
	 *
	 * Cart calculation for 6 products in cart:
	 * 4 at full price, 4 x $10 = $40
	 * 2 at half price, 2 X $5 = $10
	 * Cart total = $50
	 * So the discount "loops" when there is sufficient quantity in the cart.
	 *
	 * @param array $products
	 * @param boolean|WC_Cart $cart Whether we're handling a cart or an order.
	 * @param boolean|WC_Order $order Whether we're handling a cart or an order.
	 * @return void
	 */
	public function apply_discount_to_additional_products( array $products, $cart = false, $order = false ): void {
		$additional_qty   = absint( $this->discount->settings()->get( 'additional_qty' )->value() );
		$total_line_items = $cart ? count( $cart->get_cart() ) : count( $order->get_items() );

		// If the additional quantity is 0, then we don't need to apply the discount to additional products.
		if ( $additional_qty === 0 ) {
			return;
		}

		$total_qty_in_cart = $cart ? $cart->get_cart_contents_count() : $order->get_item_count();

		// The number of products to discount.
		$number_of_products_to_discount = $additional_qty;

		// Check if any of the products in the cart or order has the required quantity.
		$has_product_with_required_qty = $cart ? $this->cart_has_product_with_required_qty( $products ) : $this->order_has_product_with_required_qty( $products );

		if ( $has_product_with_required_qty ) {
			$this->discount_cheapest_product_with_the_required_qty( $products, $this->get_min_required_qty(), $number_of_products_to_discount, $order instanceof WC_Order );
			return;
		} elseif ( ! $has_product_with_required_qty && $total_line_items > 1 && $total_qty_in_cart >= $number_of_products_to_discount ) {
			$this->discount_cheapest_in_cart_order( $products, $number_of_products_to_discount, $cart, $order );
		}
	}

	/**
	 * This method is used to discount products at a cart level instead of per line item.
	 *
	 * For example, a user of the plugin might offer the discount “Buy 2 get 1 half price” on a specific product only.
	 * But they might offer it on a whole category of products, which would lead to this scenario.
	 *
	 * Based on the number of products to discount, find the cheapest product and determine if the product's quantity is greater than or equal to
	 * the number of products to discount.
	 *
	 * - If a product is found, then apply the discount to the product.
	 * - If a product is not found, then keep looking for the cheapest product until the number of products to discount is reached.
	 *
	 * In the latter case, the products to discount will have at least a quantity of 1.
	 *
	 * @param array $products Array of products in the cart or order.
	 * @param integer $number_of_products_to_discount The number of products to discount.
	 * @param WC_Cart|boolean $cart Whether we're handling a cart or an order.
	 * @param WC_Order|boolean $order Whether we're handling a cart or an order.
	 * @return void
	 */
	private function discount_cheapest_in_cart_order( array $products, int $number_of_products_to_discount, $cart = false, $order = false ): void {

		$total_discounted_products  = 0;
		$ids_of_discounted_products = [];
		$relevant_products          = $this->exclude_the_required_products( $products );

		// Loop through the products until the number of products to discount is reached.
		while ( $total_discounted_products < $number_of_products_to_discount ) {

			// Get the cheapest product from the relevant products array. This is usually the first product in the array.
			// The array is updated on each iteration.
			// Each iteration removes the product that was just processed.
			$temp             = array_values( $relevant_products );
			$cheapest_product = array_shift( $temp );

			// If no product was found, then we need to stop the loop.
			if ( $cheapest_product === null ) {
				break;
			}

			// The key of the cheapest product in the products array.
			$key = array_search( $cheapest_product, $products );

			// The quantity of the cheapest product.
			$cheapest_product_qty = $cart ? $cheapest_product['quantity'] : $cheapest_product->get_quantity();

			$this->discount_cheapest_product_with_the_required_qty(
				[ $cheapest_product ],
				$cheapest_product_qty,
				$cheapest_product_qty,
				$order
			);

			// Update the number of products to discount.
			$number_of_products_to_discount -= $cheapest_product_qty;

			// Update the total number of discounted products.
			$total_discounted_products += $cheapest_product_qty;

			// Update the array of relevant products by removing the item that was just processed.
			unset( $relevant_products[ $key ] );

		}
	}

	/**
	 * Filter the array of relevant products, we need to exclude the products that match the requirements
	 * because we're only discounting the additional products.
	 *
	 * The criteria for excluding the products are is based on their quantity in the order/cart.
	 * Filter the array of products by tracking their quantity into the $number_of_excluded_qty variable.
	 *
	 * When the variable reaches the required quantity, then we stop filtering the array.
	 *
	 * @param array $products
	 * @param boolean $is_order Whether the products are order items or cart items.
	 * @return array
	 */
	private function exclude_the_required_products( array $products, bool $is_order = false ): array {
		// The number of minimum required products.
		$min_required_qty       = $this->get_min_required_qty();
		$number_of_excluded_qty = 0;

		$filtered_products = array_filter(
			$products,
			function ( $product ) use ( &$number_of_excluded_qty, $min_required_qty, $is_order ) {
				// If the number of excluded quantity is greater than or equal to the required quantity, then we need to stop filtering.
				if ( $number_of_excluded_qty >= $min_required_qty ) {
					return false;
				}

				// The quantity of the product.
				$qty = $is_order ? $product->get_quantity() : $product['quantity'];

				// Update the number of excluded quantity.
				$number_of_excluded_qty += $qty;

				return true;
			}
		);

		return $filtered_products;
	}

	/**
	 * Discount the cheapest product with the required quantity in the cart or order.
	 * based on the number of items to discount.
	 *
	 * @param array $products Array of products in the cart or order.
	 * @param int $required_qty The required quantity.
	 * @param int $number_of_items_to_discount The number of items to discount.
	 * @param boolean $is_order Whether the products are order items or cart items.
	 * @param boolean $forced_applications Set to integer to force the number of applications.
	 * @return void
	 */
	private function discount_cheapest_product_with_the_required_qty( array $products, int $required_qty, int $number_of_items_to_discount, bool $is_order = false, $forced_applications = false ): void {
		// Get the cheapest product with the required quantity.
		$product = $this->get_cheapest_product_with_required_qty( $products, $required_qty, $is_order );

		if ( $product === null ) {
			return;
		}

		$reduction        = $this->get_reduction();
		$total            = $is_order ? floatval( $product->get_total() ) : floatval( $product['line_total'] );
		$reduction_type   = $this->get_discount_amount_type();
		$reduction_amount = $reduction->get_amount();
		$quantity         = $product['quantity'];

		// Merge tax if applicable.
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

		// Define the discount type: 'percentage' or 'fixed'
		$discount_type = $reduction_type; // Change this to 'fixed' if needed

		// Define the discount value (either percentage or fixed amount)
		$discount_value = $reduction_amount; // 50% discount or $50 fixed discount

		// Define the number of items to apply the discount to
		$discount_apply_count = $this->calculate_number_of_items_to_buy_with_discount( $quantity, $required_qty, $number_of_items_to_discount );

		if ( $discount_apply_count < 1 ) {
			$discount_apply_count = 1;
		}

		// Calculate the total price for all items without discount
		$total_price = $product_price * $product_quantity;

		// Calculate the number of times the discount should be applied
		$discount_applications = min( $discount_apply_count, $product_quantity );

		// If the number of applications is forced, then we need to use the forced value.
		if ( $forced_applications !== false ) {
			$discount_applications = $forced_applications;
		}

		// Adjust the discount to make the product free if necessary
		if ( $discount_type === 'fixed' && $discount_value > $product_price ) {
			$discount_value = $product_price;
		}

		// Apply the discount to the specified subset of items based on the specified type
		if ( $discount_applications > 0 ) {
			if ( $discount_type === 'percentage' && $discount_value > 0 && $discount_value <= 100 ) {
				// Calculate the percentage discount for each application
				$discount = ( $product_price * $discount_value / 100 ) * $discount_applications;
			} elseif ( $discount_type === 'fixed' && $discount_value > 0 ) {
				// Calculate the fixed amount discount for each application
				$discount = $discount_value * $discount_applications;
			} else {
				// No valid discount type or value provided
				$discount = 0;
			}

			// Subtract the discount from the total price
			$total_price -= $discount;
		}

		$fixed_price         = $total_price;
		$percentage_discount = ( ( $total - $fixed_price ) / $total ) * 100;

		if ( $this->is_processing_order() && $is_order ) {
			$this->increase_total_order_discount( $total - $fixed_price );
		} else {
			$reduction = new Percentage( $percentage_discount, null, $this->get_discount() );
			$reduction->apply_reduction( $product['data'], $product );
		}

		wdm()->cache()->mark_discount_as_active( $this->get_discount() );
	}

	/**
	 * Calculate the number of items to buy with discount.
	 *
	 * @param int $quantity_in_cart The quantity of the product in the cart.
	 * @param int $full_price_buy The number of items to buy at full price.
	 * @param int $discounted_buy The number of items to buy with discount.
	 * @return int The number of items to buy with discount.
	 */
	private function calculate_number_of_items_to_buy_with_discount( int $quantity_in_cart, int $full_price_buy, int $discounted_buy ) {
		$elegible_for_discount = 0;

		while ( $quantity_in_cart >= ( $full_price_buy + $discounted_buy ) ) {
			// Calculate how many items can be discounted in this loop
			$elegible_for_discount += $discounted_buy;

			// Deduct the products used in this loop from the total
			$quantity_in_cart -= ( $full_price_buy + $discounted_buy );
		}

		return $elegible_for_discount;
	}

	/**
	 * Get the cheapest product with the required quantity.
	 *
	 * @param array $products Array of products in the cart or order.
	 * @param int $required_qty The required quantity.
	 * @param boolean $is_order Whether the products are order items or cart items.
	 * @return array|null|\WC_Order_Item_Product The cheapest product with the required quantity, or null if no product was found.
	 */
	private function get_cheapest_product_with_required_qty( array $products, int $required_qty, bool $is_order = false ) {
		$cheapest_product_with_required_qty = null;
		$cheapest_product_price             = null;

		foreach ( $products as $product ) {
			$qty = $is_order ? $product->get_quantity() : $product['quantity'];

			if ( $qty >= $required_qty ) {
				$product_price = $is_order ? floatval( $product->get_total() ) : floatval( Products::get_product_price( $product['data'] ) );

				if ( $cheapest_product_price === null || $product_price < $cheapest_product_price ) {
					$cheapest_product_price             = $product_price;
					$cheapest_product_with_required_qty = $product;
				}
			}
		}

		return $cheapest_product_with_required_qty;
	}

	/**
	 * @inheritdoc
	 */
	public function run_cart_actions( WC_Cart &$cart ): void {
		$apply_to = $this->get_apply_to();

		// If the discount applicable to all products, then we need to apply the discount to all products in the cart.
		if ( $apply_to === 'any' ) {
			$this->maybe_discount_all_items( $this->discount->get_relevant_products( $cart ), $cart );
		} else {
			$this->apply_discount_to_additional_products( $this->discount->get_relevant_products( $cart ), $cart );
		}
	}

	/**
	 * @inheritdoc
	 */
	public function run_order_actions( WC_Order &$order ): void {
		// Flag that we're processing an order.
		$this->set_proccesing_order( true );

		// Application method.
		$apply_to = $this->get_apply_to();

		// If the discount applicable to all products, then we need to apply the discount to all products in the order.
		if ( $apply_to === 'any' ) {
			$this->maybe_discount_all_items( $this->discount->get_relevant_products( false, $order ), false, $order );
		} else {
			$this->apply_discount_to_additional_products( $this->discount->get_relevant_products( false, $order ), false, $order );
		}

		if ( wdm()->cache()->is_discount_active( $this->get_discount() ) && $this->get_total_order_discount() > 0 ) {
			$this->generate_virtual_coupon_for_order( $order );
		}

		// Flag that we're no longer processing an order.
		$this->set_proccesing_order( false );
	}
}
