<?php
namespace ReyCore\WooCommerce\LoopComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class SoldOutBadge extends Component {

	public function status(){
		return self::stock_display() !== 'hide';
	}

	public function get_id(){
		return 'sold_out_badge';
	}

	public function get_name(){
		return 'Sold Out Badge';
	}

	public function scheme(){

		if( in_array(self::stock_display(), ['badge_so', 'badge_is'], true) ){
			return [
				'type'          => 'action',
				'tag'           => 'reycore/loop_inside_thumbnail/top-right',
				'priority'      => 10,
			];
		}
		else if ( 'text' === self::stock_display() ){
			return [
				'type'          => 'action',
				'tag'           => 'woocommerce_shop_loop_item_title',
				'priority'      => 60,
			];
		}

	}

	public static function stock_display(){
		 return get_theme_mod('loop_stock_display', 'badge_so');
	}

	/**
	 * Item Component - NEW badge to product entry for any product added in the last 30 days.
	*
	* @since 1.0.0
	*/
	public function render() {

		if( ! $this->maybe_render() ){
			return;
		}

		if( ! ($product = reycore_wc__get_product()) ){
			return;
		}

		if( $product->get_type() === 'variable' ){
			// if product already set as out of stock, stop;
			// this includes all variations being out of stock
			// or the parent product inventory is set to 0
			// because there's no point since the badge will be displayed anyway
			if( $product->is_in_stock() ){
				foreach ($product->get_children() as $product_id) {
					$variation = wc_get_product($product_id);
					self::__render($variation, true);
				}
			}
			else {
				self::__render($product);
			}
		}
		else {
			self::__render($product);
		}

	}

	public static function __render($product, $is_variation = false){

		if( in_array(self::stock_display(), ['badge_so', 'badge_is'], true) ){
			self::render_badge($product, $is_variation);
		}
		else if ( 'text' === self::stock_display() ){
			self::render_text($product, $is_variation);
		}

	}

	public static function render_text($product, $is_variation = false){

		if( ! $product ){
			return;
		}

		$status = $product->get_stock_status();

		if( ($hide_statuses = get_theme_mod('loop_stock_hide_statuses', [])) && in_array($status, $hide_statuses, true) ){
			return;
		}

		$availability = $product->get_availability();
		$text = '';
		$css_class = $availability['class'];

		switch( $status ):
			case "instock":
				$text = $availability['availability'] ? $availability['availability'] : esc_html__( 'In stock', 'rey-core' );
				break;
			case "outofstock":
				$text = $availability['availability'] ? $availability['availability'] : esc_html__( 'Out of stock', 'rey-core' );
				break;
			case "onbackorder":
				$text = $availability['availability'] ? $availability['availability'] : esc_html__( 'On Backorder', 'rey-core' );
				break;
		endswitch;

		$attributes = [
			'class' => 'rey-loopStock ' . $css_class,
			'style' => self::get_css(),
			'data-status' => $status,
		];

		if( $is_variation ){
			$attributes['data-variation-id'] = $product->get_id();
		}

		printf('<div %2$s>%1$s</div>', $text, reycore__implode_html_attributes($attributes) );

	}

	public static function render_badge($product, $is_variation = false){

		if( ! $product ){
			return;
		}

		$status = $product->get_stock_status();
		$badge = '';
		$text = '';

		if( $custom_text = get_theme_mod('loop_sold_out_badge_text', '') ){
			$text = $custom_text;
		}

		if( $product->is_in_stock() ){
			if( 'onbackorder' === $status && apply_filters('reycore/woocommerce/loop/stock/onbackorder', false) ){
				$badge = apply_filters('reycore/woocommerce/loop/in_stock_text', esc_html__( 'ON BACKORDER', 'rey-core' ) );
				$css_class = 'rey-backorder-badge';
			}
			else if( self::stock_display() === 'badge_is' ){
				$badge = $text ? $text : apply_filters('reycore/woocommerce/loop/in_stock_text', esc_html__( 'IN STOCK', 'rey-core' ) );
				$css_class = 'rey-instock-badge';
			}
		}
		else {
			if( self::stock_display() === 'badge_so' ) {
				$badge = $text ? $text : apply_filters('reycore/woocommerce/loop/sold_out_text', esc_html__( 'SOLD OUT', 'rey-core' ) );
				$css_class = 'rey-soldout-badge';
			}
		}

		if( empty($badge) ){
			return;
		}

		$attributes = [
			'style' => self::get_css(),
			'data-status' => $status,
		];

		if( $is_variation ){
			$attributes['data-variation-id'] = $product->get_id();
		}

		printf('<div class="rey-itemBadge rey-stock-badge %2$s" %3$s>%1$s</div>', $badge, $css_class, reycore__implode_html_attributes($attributes) );

	}

	public static function get_css(){

		if( $custom_css = get_theme_mod('loop_sold_out_badge_css', '') ){
			return esc_attr($custom_css);
		}

		return '';
	}

}
