<?php
/**
 * Displays the list of variable product prices and discounts.
 * This is the list format for the variation prices.
 *
 * Only used when the Variation Prices plugin is active and the list format is selected.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/discounts/wvp-price-list.php
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */

defined( 'ABSPATH' ) || exit;

?>

<span class="wvp-price-range wdm-price-range">
	<span class="wdm-wvp-list wvp-list">
		<?php foreach ( $prices as $price ) : ?>
			<span class="wvp-list-item">
				<span class="wvp-list-item-name"><?php echo esc_html( $price['name'] ); ?></span>
				<span class="price wvp-list-item-price"><?php echo wp_kses_post( $price['formatted'] ); ?></span>
			</span>
		<?php endforeach; ?>
	</span>
</span>
