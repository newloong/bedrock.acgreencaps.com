<?php

namespace Barn2\Plugin\Discount_Manager;

use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Registerable;
use Barn2\Plugin\Discount_Manager\Entities\Discount;
use Barn2\Plugin\Discount_Manager\Types\Bulk;

/**
 * Handles plugin shortcodes.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Shortcodes implements Registerable {

	/**
	 * @inheritdoc
	 */
	public function register() {
		add_shortcode( 'wdm_discount_content', [ $this, 'discount_content' ] );
		add_shortcode( 'wdm_discount_bulk_table', [ $this, 'discount_bulk_table' ] );
	}

	/**
	 * Display the discount content. The discount ID can be passed as an attribute.
	 * If the discount ID is invalid or the discount has no content, nothing will be displayed.
	 *
	 * Example: [wdm_discount_content id="123"]
	 *
	 * Note: the shortcode does not check if the discount is eligible for the current user or product.
	 *
	 * @param array $atts The shortcode attributes.
	 * @param string $content The shortcode content.
	 * @return string
	 */
	public function discount_content( $atts, $content = '' ) {
		$atts = shortcode_atts(
			[
				'id' => 0,
			],
			$atts,
			'wdm_discount_content'
		);

		$discount_id = absint( $atts['id'] );

		if ( ! $discount_id ) {
			return '';
		}

		$orm      = wdm()->orm();
		$discount = $orm( Discount::class )->find( $discount_id );

		if ( ! $discount ) {
			return '';
		}

		$discount_text = $discount->get_frontend_text();

		if ( ! $discount_text ) {
			return '';
		}

		ob_start();

		echo wdm()->templates()->get_template( 'product-discounts.php', [ 'discounts' => [ $discount ] ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		return ob_get_clean();
	}

	/**
	 * Display the bulk table for the discount. The discount ID can be passed as an attribute.
	 * If the discount ID is invalid or the discount is not a bulk discount, nothing will be displayed.
	 *
	 * Example: [wdm_discount_bulk_table id="123"]
	 *
	 * Note: the shortcode does not check if the discount is eligible for the current user or product.
	 *
	 * @param array $atts The shortcode attributes.
	 * @param string $content The shortcode content.
	 * @return string
	 */
	public function discount_bulk_table( $atts, $content = '' ) {
		$atts = shortcode_atts(
			[
				'id'         => 0,
				'product_id' => 0,
			],
			$atts,
			'wdm_discount_bulk_table'
		);

		$product_id  = $this->get_product_id( $atts );
		$discount_id = $this->get_bulk_discount_id( $atts );

		$orm      = wdm()->orm();
		$discount = $orm( Discount::class )->find( $discount_id );

		if ( ! $discount ) {
			return '';
		}

		$type = $discount->get_type();

		if ( ! $type instanceof Bulk ) {
			return '';
		}

		ob_start();

		echo wdm()->templates()->get_template( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'bulk-table.php',
			[
				'discount'   => $discount, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'product_id' => $product_id, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			]
		); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		return ob_get_clean();
	}

	/**
	 * Get the product ID from the shortcode attributes.
	 * If the product ID is not set, try to get it from the current product.
	 *
	 * @param array $atts The shortcode attributes.
	 * @return int
	 */
	private function get_product_id( $atts ) {
		$product_id = absint( $atts['product_id'] );

		// If the product ID is not set, try to get it from the current product.
		if ( ! $product_id ) {
			global $product;
			$product_id = $product->get_id();
		}

		return $product_id;
	}

	/**
	 * Get the discount ID from the shortcode attributes.
	 * If the discount ID is not set, try to get it from
	 * all the applicable discounts for the current product.
	 *
	 * @param array $atts The shortcode attributes.
	 * @return int
	 */
	private function get_bulk_discount_id( $atts ) {
		$discount_id = absint( $atts['id'] );

		// If the discount ID is not set, try to get it from
		// all the applicable discounts for the current product.
		if ( ! $discount_id || $discount_id < 1 ) {
			$product_id = $this->get_product_id( $atts );
			$discounts  = wdm()->products()->get_elegible_discounts_for_product( $product_id );

			// Return the first eligible discount with a bulk type.
			if ( ! empty( $discounts ) ) {
				$discount = array_filter(
					$discounts,
					function ( $discount ) {
						return $discount->get_type() instanceof Bulk;
					}
				);

				$discount = reset( $discount );

				return $discount->id();
			}
		}

		return $discount_id;
	}
}
