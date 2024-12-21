<?php

namespace Barn2\Plugin\Discount_Manager\Integrations;

use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Registerable;
use WC_Product;
use Barn2\Plugin\Discount_Manager\Products;
use Barn2\Plugin\Discount_Manager\Types\Bulk;

use function Barn2\Plugin\Discount_Manager\wdm;

/**
 * Provides functionality for the Restaurant Ordering plugin.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Restaurant_Ordering implements Registerable {

	/**
	 * @inheritdoc
	 */
	public function register() {
		if ( ! function_exists( 'Barn2\Plugin\WC_Restaurant_Ordering\wro' ) ) {
			return;
		}

		add_filter( 'wc_restaurant_ordering_modal_description', [ $this, 'render_discount' ], 10, 2 );
		add_filter( 'wc_restaurant_ordering_product_display_price', [ $this, 'render_product_display_price' ], 10, 2 );
		add_filter( 'wc_restaurant_ordering_modal_data', [ $this, 'adjust_price_parameter' ], 10, 2 );
		add_filter( 'wc_restaurant_ordering_ordering_modal_response_headers', [ $this, 'uncache_response' ] );
	}

	/**
	 * Render the discounts list in the modal.
	 *
	 * @param string $description The description.
	 * @param \WC_Product $product The product.
	 * @return string
	 */
	public function render_discount( string $description, \WC_Product $product ): string {
		/** @var \Barn2\Plugin\Discount_Manager\Products $products_service */
		$products_service = wdm()->get_service( 'products' );
		$discount         = $products_service->get_discount_for_product( $product->get_id() );

		if ( empty( $discount ) ) {
			return $description;
		}

		$type = $discount->get_type();

		ob_start();

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

		$output = ob_get_clean();

		return $description . $output;
	}

	/**
	 * Render the product display price discounted.
	 *
	 * @param string $price The price.
	 * @param \WC_Product $product The product.
	 * @return string
	 */
	public function render_product_display_price( string $price, WC_Product $product ): string {
		/** @var Products $products_service */
		$products_service = wdm()->get_service( 'products' );

		return $products_service->set_simple_sale_price( $price, $product );
	}

	/**
	 * Adjust the price parameter for the modal.
	 *
	 * @param array $data The data.
	 * @param \WC_Product $product The product.
	 * @return array
	 */
	public function adjust_price_parameter( array $data, WC_Product $product ): array {
		$data['price'] = Products::get_product_price( $product );

		return $data;
	}

	/**
	 * Uncache the response.
	 *
	 * @param array $headers The headers.
	 * @return array
	 */
	public function uncache_response( array $headers ): array {
		$headers['Cache-Control'] = 'no-cache, no-store, must-revalidate';

		return $headers;
	}
}
