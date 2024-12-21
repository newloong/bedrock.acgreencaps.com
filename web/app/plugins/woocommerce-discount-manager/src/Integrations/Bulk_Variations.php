<?php
namespace Barn2\Plugin\Discount_Manager\Integrations;

use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Registerable;
use Barn2\Plugin\Discount_Manager\Settings;
use Barn2\Plugin\Discount_Manager\Types\Bulk;

use function Barn2\Plugin\Discount_Manager\wdm;

/**
 * Integrates with the Bulk Variations plugin.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Bulk_Variations implements Registerable {

	/**
	 * @inheritdoc
	 */
	public function register() {
		if ( ! function_exists( 'Barn2\Plugin\WC_Bulk_Variations\wbv' ) ) {
			return;
		}

		add_action( 'wc_bulk_variations_before_totals_container', [ $this, 'render_discounts_list' ], 10, 2 );
	}

	/**
	 * Render the discount and bulk table for the product in the grid.
	 * But only if the discount is set to display before the add to cart form.
	 *
	 * @param int $product_id The product ID.
	 * @param \Barn2\Plugin\WC_Bulk_Variations\Grid $grid The grid object.
	 * @return void
	 */
	public function render_discounts_list( $product_id, $grid ): void {
		/** @var \Barn2\Plugin\Discount_Manager\Products $products_service */
		$products_service = wdm()->get_service( 'products' );
		$discount         = $products_service->get_discount_for_product( $product_id );

		if ( empty( $discount ) ) {
			return;
		}

		$frontend_text_location = $discount->get_frontend_text_location();

		if ( $frontend_text_location !== 'woocommerce_before_add_to_cart_form' ) {
			return;
		}

		echo wdm()->templates()->get_template( 'product-discounts.php', [ 'discounts' => [ $discount ] ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		$type = $discount->get_type();

		if ( $type instanceof Bulk ) {
			$should_display_table = $type->should_display_bulk_table();

			if ( $should_display_table ) {
				echo wdm()->templates()->get_template( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'bulk-table.php',
					[
						'discount'   => $discount, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'product_id' => $product_id, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					]
				); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}
	}
}
