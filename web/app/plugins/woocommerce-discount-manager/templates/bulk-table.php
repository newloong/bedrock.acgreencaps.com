<?php
/**
 * Displays the list of tiers for the current product.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/discounts/bulk-table.php
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */

defined( 'ABSPATH' ) || exit;

use Barn2\Plugin\Discount_Manager\Entities\Discount;

/** @var Discount $discount */
if ( ! $discount ) {
	return;
}

$tiers = $discount->get_type()->get_tiers_for_product( $product_id );

if ( empty( $tiers ) ) {
	return;
}

?>

<div class="wdm-bulk-table">
	<table>
		<thead>
			<tr>
				<th><?php esc_html_e( 'Quantity', 'woocommerce-discount-manager' ); ?></th>
				<th><?php esc_html_e( 'Price per item', 'woocommerce-discount-manager' ); ?></th>
				<th><?php esc_html_e( 'Discount', 'woocommerce-discount-manager' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $tiers as $tier ) : ?>
				<tr>
					<td><?php echo esc_html( $tier->get_quantity_range() ); ?></td>
					<td><?php echo wp_kses_post( $tier->get_price_per_item() ); ?></td>
					<td><?php echo wp_kses_post( $tier->get_formatted_discount_amount() ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
