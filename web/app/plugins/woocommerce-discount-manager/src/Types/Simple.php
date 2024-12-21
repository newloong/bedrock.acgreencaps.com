<?php

namespace Barn2\Plugin\Discount_Manager\Types;

use Barn2\Plugin\Discount_Manager\Traits\Discount_With_Reductions;
use WC_Cart;
use WC_Order;
use WC_Order_Item_Product;

use function Barn2\Plugin\Discount_Manager\wdm;

/**
 * Simple discount type.
 *
 * Create either a percentage or fixed discount, e.g. 10% off everything, or $5 off all t-shirts.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Simple extends Type implements Actionable, Applicable {

	use Discount_With_Reductions;

	const ORDER = 1;

	/**
	 * @inheritdoc
	 */
	public static function get_slug(): string {
		return 'simple';
	}

	/**
	 * @inheritdoc
	 */
	public static function get_name(): string {
		return __( 'Simple', 'woocommerce-discount-manager' );
	}

	/**
	 * Determines if the discount has a sale badge enabled.
	 *
	 * @return boolean
	 */
	public function has_sale_badge(): bool {
		return $this->discount->settings()->get( 'sale_badge' ) && $this->discount->settings()->get( 'sale_badge' )->value() === '1';
	}

	/**
	 * @inheritdoc
	 */
	public static function get_tooltip(): string {
		ob_start();
		?>
		<ul>
			<li><?php esc_html_e( '10% off everything', 'woocommerce-discount-manager' ); ?></li>
			<li><?php esc_html_e( '$5 off all t-shirts', 'woocommerce-discount-manager' ); ?></li>
			<li><?php esc_html_e( '20% off "Barn2 Hoodie"', 'woocommerce-discount-manager' ); ?></li>
		</ul>
		<p><?php esc_html_e( 'Create either a percentage or fixed discount.', 'woocommerce-discount-manager' ); ?></p>
		<?php
		return ob_get_clean();
	}

	/**
	 * @inheritdoc
	 */
	public static function get_settings(): array {
		return [
			'amount_type' => [
				'type'  => 'discount',
				'label' => __( 'Discount', 'woocommerce-discount-manager' ),
			],
			'sale_badge'  => [
				'type'    => 'checkbox',
				'label'   => __( 'Sale badge', 'woocommerce-discount-manager' ),
				'title'   => __( 'Display a sale badge on products eligible for this discount', 'woocommerce-discount-manager' ),
				'default' => true,
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public static function get_default_settings_values(): array {
		return [
			'amount_type'         => 'percentage',
			'fixed_discount'      => 0,
			'percentage_discount' => 0,
			'sale_badge'          => true,
		];
	}

	/**
	 * @inheritdoc
	 */
	public function is_applicable_to_cart( WC_Cart $cart ): bool {
		return ! empty( $this->discount->get_relevant_products( $cart ) );
	}

	/**
	 * @inheritdoc
	 */
	public function is_applicable_to_order( WC_Order $order ): bool {
		return ! empty( $this->discount->get_relevant_products( false, $order ) );
	}

	/**
	 * @inheritdoc
	 */
	public function run_cart_actions( WC_Cart &$cart ): void {
		$cart_products = $this->discount->get_relevant_products( $cart );
		$reduction     = $this->get_reduction();

		if ( empty( $cart_products ) ) {
			return;
		}

		foreach ( $cart_products as $cart_item ) {
			$product = is_array( $cart_item ) ? $cart_item['data'] : $cart_item;
			$reduction->apply_reduction( $product, $cart_item );
		}

		wdm()->cache()->mark_discount_as_active( $this->discount );
	}

	/**
	 * @inheritdoc
	 */
	public function run_order_actions( WC_Order &$order ): void {
		$items = array_filter(
			$order->get_items(),
			function ( $item ) {
				return $item instanceof WC_Order_Item_Product;
			}
		);

		if ( empty( $items ) ) {
			return;
		}

		$this->set_proccesing_order( true );
		$items           = $this->discount->get_relevant_products( false, $order );
		$reduction       = $this->get_reduction();
		$reduction_total = $reduction->get_total_reduction_for_order( $items );
		$reduction_total = $reduction_total;

		if ( $reduction_total <= 0 ) {
			$this->set_proccesing_order( false );
			return;
		}

		$this->increase_total_order_discount( $reduction_total );

		if ( $this->get_total_order_discount() > 0 ) {
			$this->generate_virtual_coupon_for_order( $order );
		}

		$this->set_proccesing_order( false );
	}
}
