<?php

namespace Barn2\Plugin\Discount_Manager\Integrations;

use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Registerable;
use WC_Product;
use Barn2\Plugin\Discount_Manager\Products;
use Barn2\Plugin\Discount_Manager\Cart;
use Barn2\Plugin\Discount_Manager\Reductions\Fixed;
use Barn2\Plugin\Discount_Manager\Reductions\Percentage;
use Barn2\Plugin\Discount_Manager\Types\Simple;
use Barn2\Plugin\Discount_Manager\Entities\Discount;
use WC_Product_Variable;

use function Barn2\Plugin\Discount_Manager\wdm;

/**
 * Provides integration with the Wholesale Pro plugin.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Wholesale_Pro implements Registerable {
	/**
	 * The products handler.
	 *
	 * @var Products
	 */
	protected $products_handler;

	/**
	 * The cart handler.
	 *
	 * @var Cart
	 */
	protected $cart_handler;

	/**
	 * The price handler from WWP.
	 *
	 * @var \Barn2\Plugin\WC_Wholesale_Pro\Price_Handler
	 */
	protected $price_handler;

	/**
	 * Whether the cart has discounts.
	 *
	 * @var bool
	 */
	protected $has_discounts = false;

	/**
	 * @inheritdoc
	 */
	public function register() {
		if ( ! function_exists( 'Barn2\Plugin\WC_Wholesale_Pro\woocommerce_wholesale_pro' ) ) {
			return;
		}

		$this->products_handler = wdm()->get_service( 'products' );
		$this->cart_handler     = wdm()->get_service( 'cart' );
		$this->price_handler    = \Barn2\Plugin\WC_Wholesale_Pro\woocommerce_wholesale_pro()->get_service( 'price_handler' );

		// Setup the admin panel description.
		add_filter( 'wcwp_admin_roles_description', [ $this, 'set_admin_panel_description' ] );

		// Whenever a discount is updated - we need to clear the cache in WWP.
		add_action( 'wdm_discounts_reordered', [ $this, 'clear_wholesale_pro_cache' ] );
		add_action( 'wdm_discount_status_toggled', [ $this, 'clear_wholesale_pro_cache' ] );
		add_action( 'wdm_discount_deleted', [ $this, 'clear_wholesale_pro_cache' ] );
		add_action( 'wdm_discount_created', [ $this, 'clear_wholesale_pro_cache' ] );
		add_action( 'wdm_discount_duplicated', [ $this, 'clear_wholesale_pro_cache' ] );
		add_action( 'wdm_discount_updated', [ $this, 'clear_wholesale_pro_cache' ] );

		// Disable the WWP hooks that we don't need.
		add_action( 'wp_loaded', [ $this, 'disable_wwp_hooks' ] );

		// Remove the simple discount sale price from the products.
		remove_filter( 'woocommerce_get_price_html', [ $this->products_handler, 'set_simple_sale_price' ], 20 );

		// Adjust the price display.
		add_filter( 'woocommerce_get_price_html', [ $this, 'get_price_html' ], 30, 2 );

		// Disable the WWP price calculation if the user has discounts.
		add_filter( 'wcwp_calculate_wholesale_price_in_cart', [ $this, 'disable_if_user_has_discounts' ], 10, 3 );

		add_filter( 'wcwp_calculate_wholesale_price_in_cart', [ $this, 'disable_multiple_triggers' ], 10, 3 );

		// Disable the WWP price calculation if the user has discounts.
		add_filter( 'wcwp_enable_wholesale_price_calculation', [ $this, 'enable_wholesale_price_calculation' ], 10, 5 );

		// Set the is_on_sale flag for the product.
		add_filter( 'wcwp_product_is_on_sale', [ $this, 'set_is_on_sale' ], 10, 5 );
	}

	/**
	 * Disable the WWP hooks that we don't need.
	 *
	 * @return void
	 */
	public function disable_wwp_hooks(): void {
		remove_filter( 'woocommerce_get_price_html', [ $this->price_handler, 'get_price_html' ], 999, 2 );
	}

	/**
	 * Clear the Wholesale Pro cache because a discount has been updated.
	 * Which means the prices may have changed for the wholesale roles and
	 * we need to clear the cache so the new prices are displayed.
	 *
	 * @return void
	 */
	public function clear_wholesale_pro_cache(): void {
		$this->price_handler->clear_all_transients();
	}

	/**
	 * Get the price HTML for the product.
	 *
	 * We need to check whether the product has a simple discount and/or a wholesale discount.
	 * If there's only a simple discount then we need to fall back to the WDM price.
	 * If there's only a wholesale discount then we need to fall back to the WWP price.
	 * If there's both a simple discount and a wholesale discount then we need to calculate the final price.
	 *
	 * @param string $price_html The current price HTML.
	 * @param WC_Product $product The product.
	 * @return string The new price HTML.
	 */
	public function get_price_html( string $price_html, WC_Product $product ): string {
		if ( is_admin() ) {
			return $price_html;
		}

		$role = \Barn2\Plugin\WC_Wholesale_Pro\Util::get_current_user_wholesale_role_object();

		$has_discount           = $this->product_has_simple_discount( $product );
		$has_wholesale_discount = $role ? $this->product_has_wholesale_discount( $product, $role ) : false;
		$user_has_discount      = $role ? $this->user_has_discount_relevant_to_product( get_current_user_id(), $product->get_id() ) : false;

		if ( $product instanceof WC_Product_Variable && ! $has_discount && ( $has_wholesale_discount || $user_has_discount ) ) {
			return $this->price_handler->get_price_html( $price_html, $product );
		}

		// If there's no discount and no wholesale discount then return.
		if ( ! $has_discount && ! $has_wholesale_discount ) {
			return $price_html;
		}

		$has_only_simple_discount    = $has_discount && ! $has_wholesale_discount;
		$has_only_wholesale_discount = ! $has_discount && $has_wholesale_discount;
		$has_both_discounts          = $has_discount && $has_wholesale_discount;

		// If the user has a discount relevant to the product then we disable the wholesale pricing.
		if ( $user_has_discount && $has_wholesale_discount ) {
			return $this->products_handler->set_simple_sale_price( $price_html, $product );
		}

		// If there's only a simple discount then fall back to the WDM price.
		if ( $has_only_simple_discount ) {
			return $this->products_handler->set_simple_sale_price( $price_html, $product );
		}

		// If there's only a wholesale discount then fall back to the WWP price.
		if ( $has_only_wholesale_discount ) {
			return $this->price_handler->get_price_html( $price_html, $product );
		}

		// If there's both a simple discount and a wholesale discount then calculate the final price.
		if ( $has_both_discounts ) {
			$discounted_price = $this->calculate_price_with_both_discounts( $product, $role );
			$discount         = $this->products_handler->get_discount_for_product( $product->get_id() );

			if ( $product->is_type( 'grouped' ) ) {
				return $this->products_handler->generate_grouped_sale_price( $price_html, $product, $discount );
			}

			if ( $product->is_type( 'variable' ) ) {
				return $this->products_handler->generate_grouped_sale_price( $price_html, $product, $discount );
			}

			$price_html = wc_format_sale_price(
				wc_get_price_to_display( $product, [ 'price' => $product->get_regular_price() ] ),
				wc_get_price_to_display( $product, [ 'price' => $discounted_price ] )
			) . $product->get_price_suffix();
		}

		return $price_html;
	}

	/**
	 * Check whether the product has a simple discount.
	 * This is a discount powered by WDM.
	 *
	 * @param WC_Product $product The product.
	 * @return bool
	 */
	private function product_has_simple_discount( WC_Product $product ): bool {
		$discount = $this->products_handler->get_discount_for_product( $product->get_id() );

		if ( empty( $discount ) ) {
			return false;
		}

		if ( ! $discount->get_type() instanceof Simple ) {
			return false;
		}

		return true;
	}

	/**
	 * Check whether the user has a discount relevant to the product.
	 *
	 * @param int $user_id The user ID.
	 * @param int $product_id The product ID.
	 * @return bool
	 */
	private function user_has_discount_relevant_to_product( $user_id, $product_id ): bool {
		$discount = $this->products_handler->get_discount_for_product( $product_id );

		if ( empty( $discount ) ) {
			return false;
		}

		if ( ! $discount->applies_to_users() ) {
			return false;
		}

		$elegible_users = array_map( 'absint', $discount->get_applicable_users() );

		return in_array( $user_id, $elegible_users, true );
	}

	/**
	 * Determine whether the product has a wholesale pricing.
	 *
	 * @param WC_Product $product The product.
	 * @param object $role The wholesale role.
	 * @return boolean Whether the product has a wholesale pricing.
	 */
	private function product_has_wholesale_pricing( WC_Product $product, $role ): bool {
		if ( $role->get_product_pricing() !== 'yes' ) {
			return false;
		}

		// Check if any of the variations have a wholesale price.
		if ( $product instanceof WC_Product_Variable ) {
			$variations = $product->get_available_variations( 'objects' );

			foreach ( $variations as $variation ) {
				if ( is_array( $variation ) ) {
					continue;
				}

				$variation_price = $variation->get_meta( $role->get_name() );

				if ( is_numeric( $variation_price ) ) {
					return true;
				}
			}
		}

		$product_price = $product->get_meta( $role->get_name() );

		if ( ! is_numeric( $product_price ) ) {
			return false;
		}

		return $product_price;
	}

	/**
	 * Get the category wholesale discount amount for the product.
	 *
	 * @param WC_Product $product The product.
	 * @param object $role The wholesale role.
	 * @return float|bool
	 */
	private function get_category_wholesale_discount_amount( WC_Product $product, $role ) {
		$id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();

		$categories = get_the_terms( $id, 'product_cat' );

		if ( ! $categories || empty( $categories ) ) {
			return false;
		}

		$discounts = [];

		foreach ( $categories as $category ) {
			$category_discount = get_term_meta( $category->term_id, $role->get_name(), true );

			if ( $category_discount ) {
				$discounts[ $category->name ] = $category_discount;
				continue;
			}

			$ancestors = get_ancestors( $category->term_id, 'product_cat', 'taxonomy' );

			foreach ( $ancestors as $ancestor_id ) {
				$category_discount = get_term_meta( $ancestor_id, $role->get_name(), true );

				if ( $category_discount ) {
					$ancestor                     = get_term( $ancestor_id, 'product_cat' );
					$discounts[ $ancestor->name ] = $category_discount;
					continue;
				}
			}
		}

		if ( empty( $discounts ) ) {
			return false;
		}

		$discount = max( $discounts );

		return $discount;
	}

	/**
	 * Get the global wholesale discount amount.
	 *
	 * @param object $role The wholesale role.
	 * @return float
	 */
	private function get_global_wholesale_discount_amount( $role ): float {
		return (float) $role->get_global_discount();
	}

	/**
	 * Check whether the product has a wholesale discount,
	 * this only checks whether the product has a category or global discount.
	 *
	 * If the product has a wholesale pricing then this will return false.
	 *
	 * @param WC_Product $product The product.
	 * @param object $role The wholesale role.
	 * @return bool
	 */
	private function product_has_wholesale_discount( WC_Product $product, $role ): bool {
		return $this->product_has_wholesale_pricing( $product, $role ) || $this->get_category_wholesale_discount_amount( $product, $role ) || $this->get_global_wholesale_discount_amount( $role );
	}

	/**
	 * Calculate the price with both the simple discount and the wholesale discount.
	 *
	 * @param WC_Product $product The product.
	 * @param object $role The wholesale role.
	 * @return float
	 */
	private function calculate_price_with_both_discounts( WC_Product $product, $role ) {
		$original_product_price = $product->get_price( 'edit' );
		$price                  = $original_product_price;

		if ( $this->product_has_wholesale_pricing( $product, $role ) ) {
			$price = $product->get_price();
		}

		// Simple discount amount.
		$discount        = $this->products_handler->get_discount_for_product( $product->get_id() );
		$reduction       = $discount->get_type()->get_reduction(); // @php-stan-ignore-line
		$discount_amount = $reduction->get_amount();

		// Wholesale discount amount.
		$wholesale_discount_amount = 0;

		if ( $this->get_global_wholesale_discount_amount( $role ) ) {
			$wholesale_discount_amount = $this->get_global_wholesale_discount_amount( $role );
		} elseif ( $this->get_category_wholesale_discount_amount( $product, $role ) ) {
			$wholesale_discount_amount = $this->get_category_wholesale_discount_amount( $product, $role );
		}

		if ( $this->product_has_wholesale_pricing( $product, $role ) ) {
			$wholesale_discount_amount = 0;
		}

		// If there's a wholesale discount then we need to subtract it from the price. This is always a percentage.
		if ( $wholesale_discount_amount && $price > 0 ) {
			$price = $price - ( $price * ( $wholesale_discount_amount / 100 ) );
		}

		// If there's a simple discount then we need to subtract it from the price. This can be a percentage or a fixed amount.
		if ( $discount_amount && $price > 0 ) {
			if ( $reduction instanceof Percentage ) {
				$price = $price - ( $price * ( $discount_amount / 100 ) );
			} elseif ( $reduction instanceof Fixed ) {
				$price = $price - $discount_amount;
			}
		}

		return $price;
	}

	/**
	 * Set the description for the Wholesale Pro admin panel.
	 *
	 * @param string $description The current description.
	 * @return string The new description.
	 */
	public function set_admin_panel_description( $description ): string {
		$settings_url = admin_url( 'admin.php?page=wdm_options' );

		// Translators: %s is a link to the settings page.
		$additional_text = sprintf( __( 'Set bulk and quantity-based discounts for your wholesale roles in the WooCommerce Discount Manager <a href="%s">settings</a>.', 'woocommerce-discount-manager' ), esc_url( $settings_url ) );

		return $description . ' ' . $additional_text;
	}

	/**
	 * Disable the WWP price calculation if the user has user specific discounts.
	 *
	 * @param bool $enabled Whether the WWP price calculation is enabled.
	 * @param \WC_Cart $cart The cart.
	 * @param object $role The wholesale role.
	 * @return bool
	 */
	public function disable_if_user_has_discounts( $enabled, $cart, $role ): bool {
		/** @var Discount[] $wdm_discounts  */
		$wdm_discounts = $this->cart_handler->get_applicable_discounts( $cart, false );

		// If there's no WDM discounts then return.
		if ( empty( $wdm_discounts ) ) {
			return $enabled;
		}

		// If the user has a private discount then disable the WWP price calculation.
		foreach ( $wdm_discounts as $discount ) {
			if ( $discount->applies_to_users() ) {
				$elegible_users = array_map( 'absint', $discount->get_applicable_users() );
				$current_user   = absint( get_current_user_id() );
				$relevant       = in_array( $current_user, $elegible_users, true );

				if ( $relevant ) {
					$enabled = false;
				}
			}
		}

		return $enabled;
	}

	/**
	 * Disable multiple triggers for the wholesale price calculation.
	 *
	 * @param bool $enabled Whether the WWP price calculation is enabled.
	 * @param \WC_Cart $cart The cart.
	 * @param object $role The wholesale role.
	 * @return bool
	 */
	public function disable_multiple_triggers( $enabled, $cart, $role ): bool {
		if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 ) {
			return false;
		}

		return $enabled;
	}

	/**
	 * Disable the WWP price calculation if the user has a discount relevant to the product
	 * while having a wholesale discount at the same time.
	 *
	 * @param bool $enabled Whether the WWP price calculation is enabled.
	 * @param WC_Product $product The product.
	 * @param \WP_User|bool $user The user.
	 * @param float $price The price.
	 * @param array|null $cart_item The cart item.
	 * @return bool
	 */
	public function enable_wholesale_price_calculation( $enabled, WC_Product $product, $user, $price, $cart_item ): bool {
		$user_id                = $user ? $user->ID : false;
		$role                   = \Barn2\Plugin\WC_Wholesale_Pro\Util::get_current_user_wholesale_role_object();
		$has_wholesale_discount = $role ? $this->product_has_wholesale_discount( $product, $role ) : false;

		if ( ! $user_id ) {
			return $enabled;
		}

		$should_check = is_shop() || is_product_taxonomy() || is_product();

		if ( ! $should_check ) {
			return $enabled;
		}

		if ( $this->user_has_discount_relevant_to_product( $user_id, $product->get_id() ) && $has_wholesale_discount ) {
			return false;
		}

		return $enabled;
	}

	/**
	 * Set the is_on_sale flag for the product.
	 *
	 * @param bool $is_on_sale The current is_on_sale flag.
	 * @param object $handler The handler.
	 * @param string $role_name The role name.
	 * @param WC_Product $product The product.
	 * @return bool
	 */
	public function set_is_on_sale( $is_on_sale, $handler, $role_name, $product ) {
		$has_discount = $this->product_has_simple_discount( $product );

		if ( $has_discount ) {
			return true;
		}

		return $is_on_sale;
	}
}
