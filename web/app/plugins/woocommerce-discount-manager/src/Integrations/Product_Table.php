<?php

namespace Barn2\Plugin\Discount_Manager\Integrations;

use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Registerable;
use Barn2\Plugin\Discount_Manager\Types\Bulk;
use WC_Product;

use function Barn2\Plugin\Discount_Manager\wdm;

/**
 * Integrates with Product Table.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Product_Table implements Registerable {
	/**
	 * @inheritdoc
	 */
	public function register() {
		if ( ! function_exists( 'Barn2\Plugin\WC_Product_Table\wpt' ) ) {
			return;
		}

		add_filter( 'wc_product_table_column_defaults', [ $this, 'set_default_column_name' ] );
		add_filter( 'wc_product_table_custom_column_discount_content', [ $this, 'render_discount_column' ], 10, 3 );
		add_filter( 'wc_product_table_data_buy', [ $this, 'remove_discount_content_in_buy_col' ], 10, 2 );
	}

	/**
	 * Set the default column name for the discount column.
	 * The default column name is "Discount". This is the column name that will be used if the user does not change it.
	 * The priority of the column is set to the highest priority of the other columns + 1.
	 *
	 * @param array $defaults The default columns.
	 * @return array The default columns.
	 */
	public function set_default_column_name( $defaults ) {
		$highest_priority = 0;

		foreach ( $defaults as $column ) {
			if ( isset( $column['priority'] ) && $column['priority'] > $highest_priority ) {
				$highest_priority = $column['priority'];
			}
		}

		++$highest_priority;

		$defaults['discount_content'] = [
			'heading'  => esc_html__( 'Discount', 'woocommerce-discount-manager' ),
			'priority' => $highest_priority,
		];

		return $defaults;
	}

	/**
	 * Render the discount column.
	 *
	 * @param string $data The column data.
	 * @param WC_Product $product The product.
	 * @param array $args The column args.
	 * @return string The column data.
	 */
	public function render_discount_column( $data, WC_Product $product, $args ) {
		$discount = wdm()->products()->get_discount_for_product( $product->get_id() );

		if ( ! $discount ) {
			return $data;
		}

		if ( empty( $discount ) ) {
			return;
		}

		$type = $discount->get_type();

		ob_start();

		if ( ! empty( $discount->get_frontend_text() ) ) {
			echo wdm()->templates()->get_template( 'product-discounts.php', [ 'discounts' => [ $discount ] ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

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

		$data = ob_get_clean();

		return $data;
	}

	/**
	 * Remove the discount content in the buy column.
	 *
	 * @param string $data The column data.
	 * @param WC_Product $product The product.
	 * @return string The column data.
	 */
	public function remove_discount_content_in_buy_col( $data, WC_Product $product ) {
		// Check if the $data variable contains a div with the class "wdm-bulk-table". If it does, remove the whole div.
		if ( strpos( $data, 'wdm-bulk-table' ) !== false ) {
			$data = preg_replace( '/<div class="wdm-bulk-table.*?<\/div>/s', '', $data );
		}

		// Check if the $data variable contains a div with the class "wdm-discount". If it does, remove the whole div.
		if ( strpos( $data, 'wdm-discount' ) !== false ) {
			$data = preg_replace( '/<div class="wdm-discount.*?<\/div>/s', '', $data );
		}

		return $data;
	}
}
