<?php

namespace Barn2\Plugin\Discount_Manager\Integrations;

use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Registerable;
use Barn2\Plugin\Discount_Manager\Entities\Discount;
use Barn2\Plugin\Discount_Manager\Types\Bulk;

use function Barn2\Plugin\Discount_Manager\wdm;

/**
 * Integrates with WooCommerce Quick View Pro.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Quick_View_Pro implements Registerable {

	/**
	 * @inheritdoc
	 */
	public function register() {
		if ( ! function_exists( 'Barn2\Plugin\WC_Quick_View_Pro\wqv' ) ) {
			return;
		}

		add_action( 'wc_quick_view_pro_quick_view_product_details', [ $this, 'render_discounts_list' ], 30 );
	}

	/**
	 * Check if Quick View Pro has any display locations enabled.
	 * This only checks if the cart or meta are enabled, as these are the only locations that are relevant to us.
	 *
	 * @return bool
	 */
	private function has_quick_view_pro_display_locations() {
		$settings = \Barn2\Plugin\WC_Quick_View_Pro\Util\Settings::get_plugin_settings();

		return ( isset( $settings['show_cart'] ) && $settings['show_cart'] ) || ( isset( $settings['show_meta'] ) && $settings['show_meta'] );
	}

	/**
	 * Check if the discount has a relevant display location.
	 * We only check for the 'woocommerce_before_add_to_cart_form' and 'woocommerce_product_meta_start' locations,
	 * as these are the only locations that are relevant to us.
	 *
	 * @param Discount $discount The discount.
	 * @return bool
	 */
	private function discount_has_relevant_display_locations( Discount $discount ) {
		$relevant_locations = [ 'woocommerce_before_add_to_cart_form', 'woocommerce_product_meta_start' ];
		$discount_location  = $discount->get_frontend_text_location();

		return in_array( $discount_location, $relevant_locations, true );
	}

	/**
	 * Render the discounts list for the product.
	 *
	 * @param \WC_Product $product The product.
	 * @return void
	 */
	public function render_discounts_list( $product ): void {
		/** @var \Barn2\Plugin\Discount_Manager\Products $products_service */
		$products_service = wdm()->get_service( 'products' );
		$discount         = $products_service->get_discount_for_product( $product->get_id() );

		if ( empty( $discount ) ) {
			return;
		}

		// Don't render the discounts because Quick View Pro will render its own.
		if ( $this->has_quick_view_pro_display_locations() && $this->discount_has_relevant_display_locations( $discount ) ) {
			return;
		}

		$type = $discount->get_type();

		echo wdm()->templates()->get_template( 'product-discounts.php', [ 'discounts' => [ $discount ] ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		if ( $type instanceof Bulk ) {
			$should_display_table = $type->should_display_bulk_table();

			if ( $should_display_table ) {
				echo wdm()->templates()->get_template( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'bulk-table.php',
					[
						'discount'   => $discount, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'product_id' => $product->get_id(), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					]
				); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}
	}
}
