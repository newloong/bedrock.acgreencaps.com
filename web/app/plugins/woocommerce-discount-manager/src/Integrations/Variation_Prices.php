<?php

namespace Barn2\Plugin\Discount_Manager\Integrations;

use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Registerable;
use Barn2\Plugin\Discount_Manager\Entities\Discount;

use function Barn2\Plugin\Discount_Manager\wdm;

/**
 * Provides functionality for the Variation Prices plugin.
 */
class Variation_Prices implements Registerable {

	/**
	 * The price range handler.
	 *
	 * @var \Barn2\Plugin\WC_Variation_Prices\Handlers\Price_Range
	 */
	private $price_range_handler;

	/**
	 * The products handler.
	 *
	 * @var Products
	 */
	private $products_handler;

	/**
	 * @inheritdoc
	 */
	public function register() {
		if ( ! function_exists( 'Barn2\Plugin\WC_Variation_Prices\wvp' ) ) {
			return;
		}

		$this->price_range_handler = \Barn2\Plugin\WC_Variation_Prices\wvp()->get_service( 'handlers/price_range' );
		$this->products_handler    = wdm()->get_service( 'products' );

		add_filter( 'wdm_get_simple_sale_price_html', [ $this, 'get_sale_price_html' ], 10, 4 );
	}

	/**
	 * Get the sale price HTML for a variable product in the list format.
	 *
	 * @param string $html The sale price HTML.
	 * @param \WC_Product $product The product.
	 * @param Discount $discount The discount.
	 * @param \WC_Product $reduced_product The reduced product.
	 * @return string The sale price HTML.
	 */
	public function get_sale_price_html( $html, $product, Discount $discount, $reduced_product ) {
		if ( ! $product->is_type( 'variable' ) ) {
			return $html;
		}

		if ( ! $this->price_range_handler->is_product_affected() ) {
			return $html;
		}

		$format = get_option( 'variation_prices_settings_range_format', 'default' );

		// Only provide support for the list format.
		if ( $format !== 'list' ) {
			return $html;
		}

		return $this->get_list_format_html( $product, $discount, $reduced_product );
	}

	/**
	 * Render the sale price HTML for a variable product in the list format.
	 *
	 * @param \WC_Product $product The product.
	 * @param Discount $discount The discount.
	 * @param \WC_Product $reduced_product The reduced product.
	 * @return string The sale price HTML.
	 */
	private function get_list_format_html( $product, Discount $discount, $reduced_product ) {
		$variations       = $product->get_available_variations();
		$formatted_prices = [];

		foreach ( $variations as $variation ) {
			$variation_id      = $variation['variation_id'];
			$variation_product = wc_get_product( $variation_id );
			$name              = wc_get_formatted_variation( $variation_product, true, false );

			/** @var Discount $discount */
			$reduction                = $discount->get_type()->get_reduction(); // @php-stan-ignore-line
			$variable_reduced_product = $variation_product;
			$reduction->apply_reduction( $variable_reduced_product );

			$price_html = wc_format_sale_price(
				wc_get_price_to_display( $variable_reduced_product, [ 'price' => $variable_reduced_product->get_regular_price() ] ),
				wc_get_price_to_display( $variable_reduced_product, [ 'price' => $variable_reduced_product->get_sale_price() ] )
			) . $variable_reduced_product->get_price_suffix();

			$formatted_prices[] = [
				'name'          => $name,
				'regular_price' => $variation_product->get_regular_price(),
				'sale_price'    => $variation_product->get_sale_price(),
				'formatted'     => $price_html,
			];
		}

		ob_start();

		echo wdm()->templates() // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			->get_template(
				'wvp-price-list.php',
				[ 'prices' => $formatted_prices ] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			);

		return ob_get_clean();
	}
}
