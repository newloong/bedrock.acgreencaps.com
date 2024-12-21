<?php
/**
 * Displays the list of discounts for the current product.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/discounts/product-discounts.php
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! $discounts ) {
	return;
}

$discounts_with_text = array_filter(
	$discounts,
	function ( $discount ) {
		return ! empty( $discount->get_frontend_text() );
	}
);

?>

<?php foreach ( $discounts_with_text as $discount ) : ?>
<div class="wdm-discount" id="wdm-discount-<?php echo esc_attr( $discount->id() ); ?>">
	<?php echo $discount->get_frontend_text(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>
<?php endforeach; ?>
