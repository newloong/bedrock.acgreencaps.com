<?php

namespace Barn2\Plugin\Discount_Manager\Types;

use Barn2\Plugin\Discount_Manager\Reductions\Fixed;
use Barn2\Plugin\Discount_Manager\Reductions\Percentage;
use Barn2\Plugin\Discount_Manager\Virtual_Coupon;
use WC_Cart;
use WC_Order;
use WC_Order_Item_Product;

use function Barn2\Plugin\Discount_Manager\wdm;

/**
 * Bulk discount type.
 *
 * This is a percentage or price discount that applies to a range of quantities.
 * Example: Set up quantity-based discounts, e.g. 1-5: $5.00, 6-10: $4.50, and 11-20: $4.00.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Bulk extends Type implements Actionable, Applicable {

	const ORDER = 6;

	/**
	 * @inheritdoc
	 */
	public static function get_slug(): string {
		return 'bulk';
	}

	/**
	 * @inheritdoc
	 */
	public static function get_name(): string {
		return __( 'Bulk pricing', 'woocommerce-discount-manager' );
	}

	/**
	 * @inheritdoc
	 */
	public static function get_tooltip(): string {
		ob_start();
		?>
		<ul>
			<li><?php esc_html_e( 'Buy 5-9 products and get 10% discount; buy 10+ products and get 20% discount', 'woocommerce-discount-manager' ); ?></li>
			<li><?php esc_html_e( 'Buy 5-9 products and get $10 off each product; buy 10+ products and get $20 discount off each product', 'woocommerce-discount-manager' ); ?></li>
		</ul>
		<p><?php esc_html_e( 'Set up quantity-based discounts with one or more pricing tiers.', 'woocommerce-discount-manager' ); ?></p>
		<?php

		return ob_get_clean();
	}

	/**
	 * @inheritdoc
	 */
	public static function get_settings(): array {
		return [
			'calculation_type' => [
				'type'    => 'radio',
				'label'   => __( 'Calculated based on total cart contents, or individual products?', 'woocommerce-discount-manager' ),
				'options' => [
					'individual' => __( 'Individual products', 'woocommerce-discount-manager' ),
					'cart'       => __( 'Entire cart', 'woocommerce-discount-manager' ),
				],
			],
			'range_type'       => [
				'type'    => 'radio',
				'label'   => __( 'Range type', 'woocommerce-discount-manager' ),
				'options' => [
					'percentage' => __( '% based', 'woocommerce-discount-manager' ),
					'price'      => __( 'Price based', 'woocommerce-discount-manager' ),
				],
			],
			'tiers'            => [
				'type'  => 'tier',
				'label' => __( 'Bulk pricing tiers', 'woocommerce-discount-manager' ),
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public static function get_default_settings_values(): array {
		return [
			'calculation_type' => 'individual',
			'range_type'       => 'percentage',
			'tiers'            => [],
			'bulk_location'    => 'woocommerce_before_add_to_cart_form',
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_applicable_to_cart( WC_Cart $cart ): bool {
		$calculation_type     = $this->discount->settings()->get( 'calculation_type' )->value();
		$products_to_discount = $this->discount->get_relevant_products( $cart );
		$tiers                = $this->set_tier_max_quantity_when_empty( maybe_unserialize( $this->discount->settings()->get( 'tiers' )->value() ) );

		if ( empty( $products_to_discount ) || empty( $tiers ) ) {
			return false;
		}

		// If the calculation type is set to cart, then we need to check the total quantity of all products in the cart.
		if ( $calculation_type === 'cart' ) {
			$total_cart_quantity = $cart->get_cart_contents_count();

			// Find the tier that matches the quantity.
			$tier = $this->find_tier( $tiers, $total_cart_quantity );

			if ( $tier ) {
				return true;
			}
		}

		// If the calculation type is set to individual, then we need to check the quantity of each product in the cart individually and determine if there is a tier that matches the quantity.
		if ( $calculation_type === 'individual' ) {
			foreach ( $products_to_discount as $product ) {
				$quantity = $product['quantity'];

				// Find the tier that matches the quantity.
				$tier = $this->find_tier( $tiers, $quantity );

				if ( $tier ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_applicable_to_order( WC_Order $order ): bool {
		$calculation_type     = $this->discount->settings()->get( 'calculation_type' )->value();
		$products_to_discount = $this->discount->get_relevant_products( false, $order );
		$tiers                = $this->set_tier_max_quantity_when_empty( maybe_unserialize( $this->discount->settings()->get( 'tiers' )->value() ) );

		if ( empty( $products_to_discount ) || empty( $tiers ) ) {
			return false;
		}

		// If the calculation type is set to cart, then we need to check the total quantity of all products in the cart.
		if ( $calculation_type === 'cart' ) {
			$total_cart_quantity = $order->get_item_count();

			// Find the tier that matches the quantity.
			$tier = $this->find_tier( $tiers, $total_cart_quantity );

			if ( $tier ) {
				return true;
			}
		}

		// If the calculation type is set to individual, then we need to check the quantity of each product in the cart individually and determine if there is a tier that matches the quantity.
		if ( $calculation_type === 'individual' ) {
			foreach ( $products_to_discount as $product ) {
				$quantity = $product->get_quantity();

				// Find the tier that matches the quantity.
				$tier = $this->find_tier( $tiers, $quantity );

				if ( $tier ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Apply quantity-based discounts to the cart.
	 *
	 * @param WC_Cart $cart
	 * @return void
	 */
	public function run_cart_actions( WC_Cart &$cart ): void {
		$tier_range_type      = $this->discount->settings()->get( 'range_type' )->value();
		$tiers                = $this->set_tier_max_quantity_when_empty( maybe_unserialize( $this->discount->settings()->get( 'tiers' )->value() ) );
		$products_to_discount = $this->discount->get_relevant_products( $cart );
		$calculation_type     = $this->discount->settings()->get( 'calculation_type' )->value();

		// If the calculation type is set to cart, then we need to apply the discount to the cart subtotal but if the calculation type is set to individual, then we need to apply the discount to each product individually.
		$is_cart_discount = $calculation_type === 'cart';

		if ( empty( $tiers ) || empty( $products_to_discount ) ) {
			return;
		}

		if ( $is_cart_discount ) {

			$total_cart_quantity = $cart->get_cart_contents_count();

			// Find the tier that matches the quantity.
			$tier = $this->find_tier( $tiers, $total_cart_quantity );

			$this->apply_discount_to_cart( $cart, $products_to_discount, $tier, $tier_range_type );

		} else {
			foreach ( $products_to_discount as $cart_item_key => $cart_item ) {
				$quantity = $cart_item['quantity'];

				// Find the tier that matches the quantity.
				$tier = $this->find_tier( $tiers, $quantity );

				if ( $tier ) {
					$this->apply_discount_to_line_item( $cart_item, $tier, $tier_range_type );
				}
			}
		}
	}

	/**
	 * Apply quantity-based discounts to the cart.
	 * The discount here is spread across all products in the cart
	 * so that the final cart subtotal matches the discount amount.
	 *
	 * @param WC_Cart|WC_Order $cart_or_order The cart or order instance.
	 * @param array   $products_to_discount The products to discount.
	 * @param array   $tier The tier.
	 * @param string  $tier_range_type The tier range type.
	 * @param bool    $is_order Whether the discount is being applied to an order.
	 * @return void
	 */
	public function apply_discount_to_cart( &$cart_or_order, array $products_to_discount, array $tier, string $tier_range_type, bool $is_order = false ): void {
		$discount_amount = $tier['for'];
		$subtotal        = $cart_or_order->get_subtotal();

		// If there's tax, then we need to calculate the discount amount based on the subtotal + tax.
		if ( $cart_or_order->get_subtotal_tax() > 0 ) {
			$subtotal += $cart_or_order->get_subtotal_tax();
		}

		// If the range type is set to percentage, then we need to calculate the percentage discount amount based on the subtotal.
		$is_percentage_discount = $tier_range_type === 'percentage';

		$new_cart_subtotal = $is_percentage_discount ? $subtotal - ( $subtotal * ( $discount_amount / 100 ) ) : $subtotal - $discount_amount;
		$fixed_price       = $new_cart_subtotal;

		if ( $fixed_price < 0 ) {
			$fixed_price = 0;
		}

		$percentage_discount = ( ( $subtotal - $fixed_price ) / $subtotal ) * 100;

		if ( empty( $products_to_discount ) ) {
			return;
		}

		if ( $fixed_price > $subtotal ) {
			return;
		}

		if ( $percentage_discount <= 0 ) {
			return;
		}

		$reduction = new Percentage( $percentage_discount, null, $this->get_discount() );

		foreach ( $products_to_discount as $cart_item_key => $cart_item ) {
			$reduction->apply_reduction( $cart_item['data'], $cart_item );
		}

		wdm()->cache()->mark_discount_as_active( $this->get_discount() );
	}

	/**
	 * The discount here is applied individually the line item given.
	 *
	 * @param array|WC_Order_Item_Product $item The cart item.
	 * @param array $tier The tier.
	 * @param string $tier_range_type The tier range type.
	 * @param bool $is_order Whether the discount is being applied to an order.
	 * @return void
	 */
	public function apply_discount_to_line_item( $item, array $tier, string $tier_range_type, bool $is_order = false ): void {
		$discount_amount = $tier['for'];
		$subtotal        = $is_order ? $item->get_subtotal() : $item['line_subtotal'];

		// If the range type is set to percentage, then we need to calculate the percentage discount amount based on the subtotal.
		$is_percentage_discount = $tier_range_type === 'percentage';

		$new_line_subtotal = $is_percentage_discount ? $subtotal - ( $subtotal * ( $discount_amount / 100 ) ) : $subtotal - $discount_amount;

		if ( $new_line_subtotal < 0 ) {
			$new_line_subtotal = 0;
		}

		if ( $new_line_subtotal > $subtotal ) {
			return;
		}

		$reduction = $is_percentage_discount ? new Percentage( $discount_amount, null, $this->get_discount() ) : new Fixed( $discount_amount, null, $this->get_discount() );

		if ( $is_order ) {
			$reduction->apply_reduction_to_order_item( $item );
		} else {
			$reduction->apply_reduction( $item['data'], $item );
		}

		wdm()->cache()->mark_discount_as_active( $this->get_discount() );
	}

	/**
	 * Set the max quantity to PHP_INT_MAX when empty.
	 *
	 * @param array $tiers The tiers.
	 * @return array
	 */
	public function set_tier_max_quantity_when_empty( array $tiers ): array {
		if ( empty( $tiers ) ) {
			return $tiers;
		}

		foreach ( $tiers as &$tier ) {
			if ( empty( $tier['to'] ) ) {
				$tier['to'] = PHP_INT_MAX;
			}
		}

		return $tiers;
	}

	/**
	 * Apply quantity-based discounts to the order.
	 *
	 * The discount here is processed differently to the cart discount.
	 * We do not discount the products directly but instead we
	 * generate a virtual coupon for the discount and apply it to the order.
	 *
	 * In order to apply the virtual coupon to the order, we need to calculate
	 * the total discount amount for the order and then apply the discount to
	 * the order using the virtual coupon.
	 *
	 * @param WC_Order $order
	 * @return void
	 */
	public function run_order_actions( WC_Order &$order ): void {
		$tier_range_type      = $this->discount->settings()->get( 'range_type' )->value();
		$tiers                = $this->set_tier_max_quantity_when_empty( maybe_unserialize( $this->discount->settings()->get( 'tiers' )->value() ) );
		$products_to_discount = $this->discount->get_relevant_products( false, $order );
		$calculation_type     = $this->discount->settings()->get( 'calculation_type' )->value();

		// If the calculation type is set to cart, then we need to apply the discount to the cart subtotal but if the calculation type is set to individual, then we need to apply the discount to each product individually.
		$is_cart_discount = $calculation_type === 'cart';

		if ( empty( $tiers ) || empty( $products_to_discount ) ) {
			return;
		}

		if ( $is_cart_discount ) {
			$total_cart_quantity = $order->get_item_count();

			// Find the tier that matches the quantity.
			$tier = $this->find_tier( $tiers, $total_cart_quantity );

			$this->generate_discount_for_entire_order( $order, $products_to_discount, $tier, $tier_range_type );
		} else {
			$this->generate_discount_for_products_in_order( $order, $products_to_discount, $tiers );
		}
	}

	/**
	 * Generate a virtual coupon for the discount and apply it to the order.
	 * The calculation here is the same as the cart discount.
	 *
	 * Which means the discount is based off the total order quantity instead of the individual product quantity.
	 *
	 * @param WC_Order $order The order.
	 * @param array    $products The products.
	 * @param array    $tier The tier.
	 * @param string   $tier_range_type The tier range type.
	 * @return void
	 */
	private function generate_discount_for_entire_order( WC_Order $order, array $products, array $tier, string $tier_range_type ): void {
		$discount_amount = $tier['for'];
		$subtotal        = $order->get_subtotal();

		// If the range type is set to percentage, then we need to calculate the percentage discount amount based on the subtotal.
		$is_percentage_discount = $tier_range_type === 'percentage';

		$new_cart_subtotal = $is_percentage_discount ? $subtotal - ( $subtotal * ( $discount_amount / 100 ) ) : $subtotal - $discount_amount;

		$reduction_total = $subtotal - $new_cart_subtotal;

		$coupon = ( new Virtual_Coupon( $this->discount, new \WC_Coupon(), $reduction_total ) )
			->set_order( $order )
			->setup_coupon();

		$order->apply_coupon( $coupon );

		wdm()->cache()->mark_discount_as_active( $this->discount );
	}

	/**
	 * Generate a virtual coupon for the discount and apply it to the order.
	 * The calculation here is the same as the cart line item discount.
	 *
	 * Which means the discount is based off the individual product quantity instead of the total order quantity.
	 *
	 * @param WC_Order $order The order.
	 * @param array    $products The products.
	 * @param array    $tiers The tiers.
	 * @return void
	 */
	private function generate_discount_for_products_in_order( WC_Order $order, array $products, array $tiers ): void {
		// The total discount amount is the amount that will be discounted from the entire order.
		$total_discount_amount = 0;

		// For each product in the order, we need to calculate the discount amount and then add it to the total discount amount.
		foreach ( $products as $product ) {
			$quantity = $product->get_quantity();

			$tier = $this->find_tier( $tiers, $quantity );

			if ( $tier ) {
				$discount_amount = $tier['for'];
				$subtotal        = $product->get_subtotal();

				// If the range type is set to percentage, then we need to calculate the percentage discount amount based on the subtotal.
				$is_percentage_discount = $tier['range_type'] === 'percentage';

				$new_line_subtotal = $is_percentage_discount ? $subtotal - ( $subtotal * ( $discount_amount / 100 ) ) : $subtotal - $discount_amount;

				$total_discount_amount += $subtotal - $new_line_subtotal;
			}
		}

		if ( $total_discount_amount <= 0 ) {
			return;
		}

		$coupon = ( new Virtual_Coupon( $this->discount, new \WC_Coupon(), $total_discount_amount ) )
			->set_order( $order )
			->setup_coupon();

		$order->apply_coupon( $coupon );

		wdm()->cache()->mark_discount_as_active( $this->discount );
	}

	/**
	 * Find the tier that matches the quantity.
	 *
	 * @param array $tiers
	 * @param int   $quantity
	 *
	 * @return array|null
	 */
	private function find_tier( array $tiers, int $quantity ): ?array {
		$found_tier = null;

		foreach ( $tiers as $tier ) {
			if ( $quantity >= $tier['from'] && $quantity <= $tier['to'] ) {
				$found_tier = $tier;
				break;
			}
		}

		return $found_tier;
	}

	/**
	 * Get the tiers for a product.
	 *
	 * @param int $product_id The product ID.
	 * @return array
	 */
	public function get_tiers_for_product( int $product_id ): array {
		$available_tiers = maybe_unserialize( $this->discount->settings()->get( 'tiers' )->value() );
		$tier_range_type = $this->discount->settings()->get( 'range_type' )->value();
		$tiers           = [];

		if ( empty( $available_tiers ) ) {
			return [];
		}

		$product = wc_get_product( $product_id );

		if ( ! $product ) {
			return [];
		}

		foreach ( $available_tiers as $tier ) {
			$min_quantity    = absint( $tier['from'] );
			$max_quantity    = absint( $tier['to'] );
			$discount_amount = $tier['for'];

			$tiers[] = new Bulk_Tier( $product, $min_quantity, $max_quantity, $tier_range_type, $discount_amount );
		}

		return $tiers;
	}

	/**
	 * Determine whether to display the bulk table.
	 *
	 * @return bool
	 */
	public function should_display_bulk_table(): bool {
		return $this->discount->settings()->get( 'display_bulk_table' )->value() === '1';
	}

	/**
	 * Get the bulk table display location.
	 *
	 * @return string
	 */
	public function get_bulk_table_display_location(): string {
		return $this->discount->get_frontend_text_location();
	}
}
