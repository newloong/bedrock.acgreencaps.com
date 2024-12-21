<?php

namespace Barn2\Plugin\Discount_Manager;

use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Registerable;
use Barn2\Plugin\Discount_Manager\Entities\Discount;
use Barn2\Plugin\Discount_Manager\Types\Actionable;
use WC_Order;
use WC_Meta_Data;
use WC_Order_Item_Product;

/**
 * Handles integration with WooCommerce orders
 * when manually creating an order from the admin.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Orders implements Registerable {

	/**
	 * @inheritdoc
	 */
	public function register(): void {
		add_filter( 'woocommerce_order_actions', [ $this, 'register_custom_order_action' ], 10, 2 );
		add_action( 'woocommerce_order_action_wdm_calculate_discount', [ $this, 'calculate_order_discount' ] );
		add_action( 'woocommerce_checkout_create_order_line_item', [ $this, 'save_discount_metadata' ], 10, 4 );
		add_action( 'woocommerce_checkout_order_created', [ $this, 'save_discounts_list_metadata' ], 10 );
		add_filter( 'woocommerce_hidden_order_itemmeta', [ $this, 'hide_metadata' ], 10 );
		add_filter( 'woocommerce_order_item_display_meta_key', [ $this, 'format_item_key' ], 10, 3 );
		add_filter( 'woocommerce_order_item_display_meta_value', [ $this, 'format_item_data' ], 10, 3 );
		add_action( 'woocommerce_admin_order_totals_after_tax', [ $this, 'display_total_savings' ], 10 );
		add_filter( 'woocommerce_get_order_item_totals', [ $this, 'add_order_item_totals' ], 10, 2 );
	}

	/**
	 * Save the "_wdm_discounted", "_wdm_discounted_by"
	 * and "_wdm" keys as a meta data.
	 *
	 * @param \WC_Order_Item_Product $item          The order item.
	 * @param string                 $cart_item_key The cart item key.
	 * @param array                  $values        The values.
	 * @param \WC_Order              $order         The order.
	 */
	public function save_discount_metadata( $item, $cart_item_key, $values, $order ) {
		$discounted    = $values['_wdm_discounted'] ?? false;
		$discounted_by = $values['_wdm_discounted_by'] ?? '';
		$prices        = $values['_wdm'] ?? [];

		if ( ! $discounted ) {
			return;
		}

		$item->add_meta_data( '_wdm_discounted', true );
		$item->add_meta_data( '_wdm_discounted_by', $discounted_by );
		$item->add_meta_data( '_wdm', wp_json_encode( $prices ) );
	}

	/**
	 * Save the list of active discounts as a meta data.
	 *
	 * @param \WC_Order $order The order.
	 */
	public function save_discounts_list_metadata( WC_Order $order ): void {
		$active_discounts = wdm()->cache()->get_active_discounts();

		if ( empty( $active_discounts ) ) {
			return;
		}

		$discount_ids = array_map(
			function ( $discount ) {
				return $discount->id();
			},
			$active_discounts
		);

		// Make sure values are unique.
		$discount_ids = array_unique( $discount_ids );

		$order->update_meta_data( '_wdm_discounts_list', $discount_ids );

		$order->save();
	}

	/**
	 * Hide the "_wdm_discounted" keys from the order item meta data.
	 *
	 * @param array $hidden The hidden keys.
	 * @return array
	 */
	public function hide_metadata( $hidden ) {
		$hidden[] = '_wdm_discounted';

		return $hidden;
	}

	/**
	 * Format the metadata key so that it is displayed
	 * in a human-readable format.
	 *
	 * @param string $key The key.
	 * @param WC_Meta_Data $meta The meta data.
	 * @param WC_Order_Item_Product $item The order item.
	 * @return string
	 */
	public function format_item_key( $key, $meta, $item ) {
		if ( '_wdm_discounted' === $key ) {
			return esc_html__( 'Discounted', 'woocommerce-discount-manager' );
		}

		if ( '_wdm_discounted_by' === $key ) {
			return esc_html__( 'Discount', 'woocommerce-discount-manager' );
		}

		if ( '_wdm' === $key ) {
			return esc_html__( 'Discounted Prices', 'woocommerce-discount-manager' );
		}

		return $key;
	}

	/**
	 * Format the metadata value so that it is displayed
	 * in a human-readable format.
	 *
	 * @param string $value The value.
	 * @param WC_Meta_Data $meta The meta data.
	 * @param WC_Order_Item_Product $item The order item.
	 * @return string
	 */
	public function format_item_data( $value, $meta, $item ) {
		if ( '_wdm_discounted' === $meta->key ) {
			return $value ? esc_html__( 'Yes', 'woocommerce-discount-manager' ) : esc_html__( 'No', 'woocommerce-discount-manager' );
		}

		if ( '_wdm_discounted_by' === $meta->key ) {
			$discount_id     = $meta->value;
			$discount_exists = Util::discount_exists( $discount_id );

			if ( $discount_exists ) {
				$orm = wdm()->orm();

				/** @var Discount $discount */
				$discount = $orm( Discount::class )->find( $discount_id );

				return $discount->get_name();
			} else {
				return esc_html__( 'Discount no longer exists', 'woocommerce-discount-manager' );
			}
		}

		if ( '_wdm' === $meta->key ) {
			$prices = json_decode( $meta->value, true );

			if ( ! is_array( $prices ) ) {
				return '';
			}

			$original_price = $prices['original_price'] ?? 0;
			$new_price      = $prices['new_price'] ?? 0;

			$original_price = wc_price( $original_price, [ 'currency' => $item->get_order()->get_currency() ] );
			$new_price      = wc_price( $new_price, [ 'currency' => $item->get_order()->get_currency() ] );

			return sprintf(
				'%s &rarr; %s',
				$original_price,
				$new_price
			);
		}

		return $value;
	}

	/**
	 * Display the total savings in the order totals.
	 *
	 * @param int $order_id The order ID.
	 * @return void
	 */
	public function display_total_savings( $order_id ) {
		/**
		 * Filter: determine if the total savings should be displayed in the order totals.
		 *
		 * @param bool $should_display True if the total savings should be displayed, false otherwise.
		 * @param int $order_id The order ID.
		 * @return bool
		 */
		$should_display = apply_filters( 'wdm_display_total_savings', false, $order_id );

		// Temporarily hidden.
		if ( ! $should_display ) {
			return;
		}

		$order = wc_get_order( $order_id );

		if ( ! $order instanceof \WC_Order ) {
			return;
		}

		$total_savings = $this->get_total_savings( $order );

		if ( $total_savings <= 0 ) {
			return;
		}

		?>
		<tr>
			<td class="label"><?php esc_html_e( 'Discounts Total', 'woocommerce-discount-manager' ); ?>:</td>
			<td width="1%"></td>
			<td class="total">
				<?php
					echo wc_price( $total_savings, [ 'currency' => $order->get_currency() ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Register the custom order action.
	 *
	 * @param array    $actions The list of actions.
	 * @param \WC_Order $order  The order.
	 * @return array
	 */
	public function register_custom_order_action( array $actions, $order ): array {
		if ( ! $order instanceof \WC_Order ) {
			return $actions;
		}

		if ( ! $order->is_editable() ) {
			return $actions;
		}

		$actions['wdm_calculate_discount'] = esc_html__( 'Calculate discount', 'woocommerce-discount-manager' );

		return $actions;
	}

	/**
	 * Calculate the discount for the given order.
	 *
	 * @param \WC_Order $order
	 */
	public function calculate_order_discount( $order ): void {
		if ( ! $order instanceof \WC_Order ) {
			return;
		}

		if ( ! $order->is_editable() ) {
			return;
		}

		$products = self::get_products_ids( $order );

		if ( empty( $products ) ) {
			return;
		}

		$discounts = $this->get_applicable_discounts( $order );

		if ( empty( $discounts ) ) {
			return;
		}

		// Always purge coupons before applying new ones.
		$this->maybe_purge_coupons( $order );

		// Purge the discounts meta data.
		$this->purge_discounts( $order );

		/** @var Discount $discount */
		foreach ( $discounts as $discount ) {
			$type = $discount->get_type();

			if ( $type instanceof Actionable ) {
				$type->run_order_actions( $order );
			}
		}

		$order->update_meta_data( 'wdm_discount_added', true );
	}

	/**
	 * Get all discounts that should be applied to the given order.
	 *
	 * @param \WC_Order $order
	 * @return array
	 */
	public function get_applicable_discounts( \WC_Order $order ): array {
		$discounts = Cache::get_published_discounts();

		$discounts = array_filter(
			$discounts,
			function ( Discount $discount ) use ( $order ) {
				return $discount->is_applicable_to_order( $order );
			}
		);

		return $discounts;
	}

	/**
	 * Get the products ids in the order.
	 *
	 * @param \WC_Order $order
	 * @return array
	 */
	public static function get_products_ids( \WC_Order $order ): array {
		$items = $order->get_items();

		return array_map(
			function ( $item ) {
				return $item->get_product_id();
			},
			array_filter(
				$items,
				function ( $item ) {
					return $item instanceof \WC_Order_Item_Product;
				}
			)
		);
	}

	/**
	 * Purge any coupons that start with 'wdm-discount'.
	 *
	 * @param \WC_Order $order
	 * @return void
	 */
	private function maybe_purge_coupons( \WC_Order $order ): void {
		$coupons = $order->get_coupons();

		if ( empty( $coupons ) ) {
			return;
		}

		foreach ( $coupons as $coupon ) {
			if ( Util::string_starts_with( $coupon->get_code(), 'wdm-discount' ) ) {
				$order->remove_coupon( $coupon->get_code() );
			}
		}
	}

	/**
	 * Check if the given order has any of the given products.
	 *
	 * @param \WC_Order $order   The order to check.
	 * @param array     $products The products to check.
	 * @return bool True if the order has any of the given products, false otherwise.
	 */
	public static function has_specific_products( \WC_Order $order, array $products ): bool {
		$products_in_order = self::get_products_ids( $order );

		if ( empty( $products_in_order ) ) {
			return false;
		}

		$products_in_order = array_map( 'intval', $products_in_order );

		$intersect = array_intersect( $products_in_order, $products );

		return ! empty( $intersect );
	}

	/**
	 * Determine if the given order has any products in the given categories.
	 *
	 * @param \WC_Order $order    The order to check.
	 * @param array $categories The categories to check.
	 * @return boolean True if the order has any products in the given categories, false otherwise.
	 */
	public static function has_products_in_categories( \WC_Order $order, array $categories ): bool {
		$products_in_order = self::get_products_ids( $order );

		if ( empty( $products_in_order ) ) {
			return false;
		}

		$products_in_order = array_map( 'intval', $products_in_order );

		$intersect = array_filter(
			$products_in_order,
			function ( $product_id ) use ( $categories ) {
				$product_cats = wp_get_post_terms( $product_id, 'product_cat', [ 'fields' => 'ids' ] );

				if ( ! empty( $product_cats ) ) {
					$intersect = array_intersect( $product_cats, $categories );

					if ( ! empty( $intersect ) ) {
						return true;
					}
				}

				return false;
			}
		);

		return ! empty( $intersect );
	}

	/**
	 * Get the specific products in the order with the class of \WC_Order_Item_Product but
	 * only if they are in the given products array.
	 *
	 * If the product is a variation, the parent product is checked.
	 *
	 * @param \WC_Order $order   The order to check.
	 * @param array $products The products to find.
	 * @return array The products found in the order.
	 */
	public static function get_specific_products( \WC_Order $order, array $products ): array {
		$items = array_filter(
			$order->get_items(),
			function ( $item ) {
				return $item instanceof \WC_Order_Item_Product;
			}
		);

		if ( empty( $items ) ) {
			return [];
		}

		$items = array_filter(
			$items,
			function ( $item ) use ( $products ) {
				$product_id = $item->get_product_id();

				$product = wc_get_product( $product_id );

				if ( $product->is_type( 'variation' ) ) {
					$product_id = $product->get_parent_id();
				}

				return in_array( $product_id, $products, true );
			}
		);

		return $items;
	}

	/**
	 * Get the products with a class of WC_Order_Item_Product in the order
	 * but only if they are in the given categories.
	 *
	 * If the product is a variation, the parent product is checked.
	 *
	 * @param \WC_Order $order    The order to check.
	 * @param array $categories The categories to find.
	 * @return array The products found in the order.
	 */
	public static function get_products_in_categories( \WC_Order $order, array $categories ): array {
		$items = array_filter(
			$order->get_items(),
			function ( $item ) {
				return $item instanceof \WC_Order_Item_Product;
			}
		);

		if ( empty( $items ) ) {
			return [];
		}

		$items = array_filter(
			$items,
			function ( $item ) use ( $categories ) {
				$product_id = $item->get_product_id();

				$product = wc_get_product( $product_id );

				if ( $product->is_type( 'variation' ) ) {
					$product_id = $product->get_parent_id();
				}

				$product_categories = get_the_terms( $product_id, 'product_cat' );

				if ( ! $product_categories ) {
					return false;
				}

				$product_categories = array_map(
					function ( $category ) {
						return $category->term_id;
					},
					$product_categories
				);

				$intersect = array_intersect( $product_categories, $categories );

				return ! empty( $intersect );
			}
		);

		return $items;
	}

	/**
	 * Get the discounted items in the order.
	 *
	 * @param \WC_Order $order The order.
	 * @return array
	 */
	public function get_discounted_items( \WC_Order $order ): array {
		$items = array_filter(
			$order->get_items(),
			function ( $item ) {
				return $item instanceof \WC_Order_Item_Product;
			}
		);

		if ( empty( $items ) ) {
			return [];
		}

		$items = array_filter(
			$items,
			function ( $item ) {
				$discounted = $item->get_meta( '_wdm', true );

				return $discounted;
			}
		);

		return $items;
	}

	/**
	 * Get the total savings of the order.
	 *
	 * @param \WC_Order $order The order.
	 * @return float
	 */
	public function get_total_savings( \WC_Order $order ) {
		$discounted_products = $this->get_discounted_items( $order );

		if ( empty( $discounted_products ) ) {
			return 0;
		}

		$total_savings = array_reduce(
			$discounted_products,
			function ( $total, $item ) {
				$qty = $item->get_quantity();
				$metadata = json_decode( $item->get_meta( '_wdm', true ), true );

				$price_before = $metadata['original_price'] ?? 0;
				$price_after  = $metadata['new_price'] ?? 0;

				if ( $price_before <= 0 || $price_after <= 0 ) {
					return $total;
				}

				$savings = ( $price_before - $price_after ) * $qty;

				return $total + $savings;
			},
			0
		);

		return $total_savings;
	}

	/**
	 * Purge the discounts meta data from the order.
	 *
	 * This is used when the order is updated and recalculation is needed.
	 * This also removes the metadata from each order item.
	 *
	 * @param \WC_Order $order The order.
	 * @return void
	 */
	public function purge_discounts( \WC_Order $order ): void {
		$order->delete_meta_data( '_wdm_discounts_list' );

		$items = array_filter(
			$order->get_items(),
			function ( $item ) {
				return $item instanceof \WC_Order_Item_Product;
			}
		);

		if ( empty( $items ) ) {
			return;
		}

		foreach ( $items as $item ) {
			$item->delete_meta_data( '_wdm_discounted' );
			$item->delete_meta_data( '_wdm_discounted_by' );
			$item->delete_meta_data( '_wdm' );

			$item->save();
		}
	}

	/**
	 * Add the total savings to the order item totals.
	 *
	 * @param array    $total_rows The total rows.
	 * @param \WC_Order $order     The order.
	 * @return array
	 */
	public function add_order_item_totals( array $total_rows, \WC_Order $order ): array {
		/**
		 * Filter: determine if the total savings should be displayed in the order item totals.
		 * This is injected into the order item totals via the 'woocommerce_get_order_item_totals' filter.
		 *
		 * The content is then displayed on pages and in emails.
		 *
		 * @param bool $should_display True if the total savings should be displayed, false otherwise.
		 * @param array $total_rows The total rows.
		 * @param int $order_id The order ID.
		 * @return bool
		 */
		$should_display = apply_filters( 'wdm_display_total_savings_in_order_totals', true, $total_rows, $order->get_id() );

		if ( ! $should_display ) {
			return $total_rows;
		}

		$total_savings = $this->get_total_savings( $order );

		if ( $total_savings <= 0 ) {
			return $total_rows;
		}

		$total_rows['wdm_total_savings'] = [
			'label' => esc_html__( 'Total savings:', 'woocommerce-discount-manager' ),
			'value' => wc_price( $total_savings, [ 'currency' => $order->get_currency() ] ),
		];

		// Now move the total savings to the top of the array, keeping the keys.
		$total_rows = array_merge( [ 'wdm_total_savings' => $total_rows['wdm_total_savings'] ], $total_rows );

		return $total_rows;
	}
}
