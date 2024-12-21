<?php

namespace Barn2\Plugin\Discount_Manager\Entities;

use Barn2\Plugin\Discount_Manager\Cache;
use Barn2\Plugin\Discount_Manager\Cart;
use Barn2\Plugin\Discount_Manager\Database\Discounts;
use Barn2\Plugin\Discount_Manager\Dependencies\Axiom\Collections\Collection;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Entity;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\IEntityMapper;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\IMappableEntity;
use Barn2\Plugin\Discount_Manager\Orders;
use Barn2\Plugin\Discount_Manager\Products;
use Barn2\Plugin\Discount_Manager\Traits\Entity_Assignable;
use Barn2\Plugin\Discount_Manager\Traits\Entity_ID;
use Barn2\Plugin\Discount_Manager\Types\Applicable;
use Barn2\Plugin\Discount_Manager\Types\Type;
use Barn2\Plugin\Discount_Manager\Util;
use DateTime;
use Exception;
use JsonSerializable;

use function Barn2\Plugin\Discount_Manager\wdm;

/**
 * Represents a discount created via the editor.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Discount extends Entity implements IMappableEntity, JsonSerializable {

	use Entity_ID;
	use Entity_Assignable;

	/**
	 * Create a new discount.
	 *
	 * @param array $args The discount arguments.
	 * @return self The new discount.
	 */
	public static function create( array $args = [] ): self {

		$args = wp_parse_args(
			$args,
			[
				'name'     => '',
				'slug'     => '',
				'priority' => 0,
				'enabled'  => true,
			]
		);

		$orm      = wdm()->orm();
		$discount = $orm->create( self::class );
		$discount->assign( $args );

		$orm->save( $discount );

		Cache::forget_cached_published_discounts();

		/**
		 * Action: fired after a discount is created.
		 *
		 * @param Discount $discount
		 */
		do_action( 'wdm_discount_created', $discount );

		return $discount;
	}

	/**
	 * Get name of the discount.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return $this->orm()->getColumn( 'name' );
	}

	/**
	 * Set name of the discount.
	 *
	 * @param string $name
	 * @return self
	 */
	public function set_name( string $name ): self {
		$this->orm()->setColumn( 'name', $name );
		return $this;
	}

	/**
	 * Get slug of the discount.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return $this->orm()->getColumn( 'slug' );
	}

	/**
	 * Set slug of the discount.
	 *
	 * @param string $slug
	 * @return self
	 */
	public function set_slug( string $slug ): self {
		$this->orm()->setColumn( 'slug', $slug );
		return $this;
	}

	/**
	 * Determine if the discount is enabled.
	 *
	 * @return boolean
	 */
	public function enabled(): bool {
		return $this->orm()->getColumn( 'enabled' );
	}

	/**
	 * Set the discount enabled or disabled.
	 *
	 * @param boolean $enabled
	 * @return self
	 */
	public function set_enabled( bool $enabled ): self {
		$this->orm()->setColumn( 'enabled', $enabled );
		return $this;
	}

	/**
	 * Get the priority order number of the discount.
	 *
	 * @return integer
	 */
	public function priority(): int {
		return $this->orm()->getColumn( 'priority' );
	}

	/**
	 * Set the discount enabled or disabled.
	 *
	 * @param int $priority
	 * @return self
	 */
	public function set_priority( int $priority ): self {
		$this->orm()->setColumn( 'priority', $priority );
		return $this;
	}

	/**
	 * Get settings associated with the discount.
	 *
	 * @return Collection
	 */
	public function settings(): Collection {
		/** @var Discount_Setting[] $settings */
		$settings   = $this->orm()->getRelated( 'settings' );
		$collection = [];

		foreach ( $settings as $setting ) {
			$collection[ $setting->key() ] = $setting;
		}

		return Collection::make( $collection );
	}

	/**
	 * @inheritdoc
	 */
	public static function mapEntity( IEntityMapper $mapper ) {
		global $wpdb;

		$mapper->table( "{$wpdb->prefix}" . Discounts::NAME );

		$mapper->cast(
			[
				'id'         => 'integer',
				'priority'   => 'integer',
				'enabled'    => 'boolean',
				'created_at' => 'date',
				'updated_at' => '?date',
			]
		);

		$mapper->useTimestamp();

		$mapper->relation( 'settings' )->hasMany( Discount_Setting::class );

		$mapper->filter( new Published_Filter( 'published' ) );
		$mapper->filter( new Priority_Filter( 'priority' ) );
	}

	/**
	 * Get the content that may be displayed on the single product page.
	 *
	 * @return string
	 */
	public function get_frontend_text(): string {
		$text          = '';
		$should_escape = Util::should_escape_discount_text();

		if ( $this->settings()->has( 'text' ) ) {
			$text = $this->settings()->get( 'text' )->value();

			if ( $should_escape ) {
				$text = wp_kses( $text, Util::get_product_text_allowed_html() );
			}

			$text = do_shortcode( $text );
		}

		return $text;
	}

	/**
	 * Get the location where the content should be displayed on the single product page.
	 *
	 * @return string
	 */
	public function get_frontend_text_location(): string {
		return $this->settings()->has( 'content_location' ) ? $this->settings()->get( 'content_location' )->value() : 'woocommerce_before_add_to_cart_form';
	}

	/**
	 * Returns the notice that should be displayed when the discount is applied.
	 *
	 * @return string
	 */
	public function get_notice(): string {
		$notice = $this->settings()->get( 'notice' );

		if ( $notice instanceof Discount_Setting ) {
			return $notice->value();
		}

		return '';
	}

	/**
	 * Determine if the discount is applicable to everyone.
	 *
	 * @return boolean
	 */
	public function applies_to_everyone(): bool {
		return $this->settings()->has( 'applies_to' ) && $this->settings()->get( 'applies_to' )->value() === 'all';
	}

	/**
	 * Determine if the discount is applicable to specific user roles.
	 *
	 * @return boolean
	 */
	public function applies_to_roles(): bool {
		return $this->settings()->has( 'applies_to' ) && $this->settings()->get( 'applies_to' )->value() === 'user_roles';
	}

	/**
	 * Determine if the discount is applicable to specific users.
	 *
	 * @return boolean
	 */
	public function applies_to_users(): bool {
		return $this->settings()->has( 'applies_to' ) && $this->settings()->get( 'applies_to' )->value() === 'users';
	}

	/**
	 * Get the list of user roles that the discount is applicable to.
	 *
	 * @return array
	 */
	public function get_applicable_roles(): array {
		$roles = [];

		if ( $this->settings()->has( 'applies_to_roles' ) ) {
			$roles = maybe_unserialize( $this->settings()->get( 'applies_to_roles' )->value() );
		}

		return $roles;
	}

	/**
	 * Get the list of users that the discount is applicable to.
	 *
	 * @return array
	 */
	public function get_applicable_users(): array {
		$users = [];

		if ( $this->settings()->has( 'applies_to_users' ) ) {
			$users = maybe_unserialize( $this->settings()->get( 'applies_to_users' )->value() );
		}

		return $users;
	}

	/**
	 * Determine if the discount is applicable to all products.
	 *
	 * @return boolean
	 */
	public function is_applicable_to_all_products(): bool {
		return $this->settings()->has( 'which_products' ) && $this->settings()->get( 'which_products' )->value() === 'all';
	}

	/**
	 * Determine if the discount is applicable to specific products.
	 *
	 * @return boolean
	 */
	public function is_applicable_to_specific_products(): bool {
		return $this->settings()->has( 'which_products' ) && $this->settings()->get( 'which_products' )->value() === 'products';
	}

	/**
	 * Determine if the discount is applicable to specific categories.
	 *
	 * @return boolean
	 */
	public function is_applicable_to_specific_categories(): bool {
		return $this->settings()->has( 'which_products' ) && $this->settings()->get( 'which_products' )->value() === 'categories';
	}

	/**
	 * Determine if the discount is applicable to specific products with specific variations.
	 *
	 * @return boolean
	 */
	public function is_applicable_to_specific_variations(): bool {
		return $this->settings()->has( 'which_products' ) && $this->settings()->get( 'which_products' )->value() === 'products' && $this->settings()->has( 'selected_variations' ) && ! empty( $this->get_elegible_variations() );
	}

	/**
	 * Get the list of products that the discount is applicable to.
	 * This method does not include the variations.
	 *
	 * @return array
	 */
	public function get_elegible_products(): array {
		$products = [];

		if ( $this->settings()->has( 'selected_products' ) ) {
			$products = maybe_unserialize( $this->settings()->get( 'selected_products' )->value() );
		}

		return array_map( 'absint', $products );
	}

	/**
	 * Get the eligible variation IDs for this discount.
	 *
	 * This method retrieves the variation IDs that are eligible for the discount
	 * when the discount is applicable to specific variations of variable products.
	 *
	 * @return array An array of unique variation IDs that are eligible for the discount.
	 *               Returns an empty array if the discount is not applicable to specific variations
	 *               or if no variations are selected.
	 */
	public function get_elegible_variations(): array {
		if ( ! $this->settings()->has( 'selected_variations' ) ) {
			return [];
		}

		$elegible_variations = $this->settings()->get( 'selected_variations' )->value() ?? [];

		if ( empty( $elegible_variations ) ) {
			return [];
		}

		$variations = [];
		foreach ( $elegible_variations as $product_variations ) {
			if ( isset( $product_variations['variations'] ) && is_array( $product_variations['variations'] ) ) {
				foreach ( $product_variations['variations'] as $variation ) {
					if ( isset( $variation['id'] ) ) {
						$variations[] = $variation['id'];
					}
				}
			}
		}

		return array_map( 'absint', array_unique( $variations ) );
	}

	/**
	 * Get the list of categories that the discount is applicable to.
	 * This includes the children categories.
	 *
	 * @return array
	 */
	public function get_elegible_categories(): array {
		$categories = [];

		if ( $this->settings()->has( 'selected_categories' ) ) {
			$categories = maybe_unserialize( $this->settings()->get( 'selected_categories' )->value() );
		}

		/**
		 * Filter whether the discount is applicable to the child categories.
		 * When true, the discount pulls in the child categories of the categories
		 * that are selected.
		 *
		 * @param boolean $look_for_children True if the discount is applicable to the children of the categories.
		 * @param Discount $discount The discount object.
		 * @return boolean True if the discount is applicable to the children of the categories.
		 */
		$look_for_children = apply_filters( 'wdm_discount_get_elegible_categories_child_terms', true, $this );

		if ( $look_for_children ) {
			// We need to include the children categories.
			foreach ( $categories as $category ) {
				$children = get_term_children( $category, 'product_cat' );

				if ( ! empty( $children ) ) {
					$categories = array_merge( $categories, $children );
				}
			}
		}

		return array_map( 'absint', $categories );
	}

	/**
	 * Return the instance of the type of discount associated with this discount.
	 *
	 * @throws \Exception If the type is not found or does not have a class.
	 * @return Type
	 */
	public function get_type(): Type {
		$type            = $this->settings()->get( 'type' ) ? $this->settings()->get( 'type' )->value() : false;
		$type_definition = wdm()->types()->get_by_slug( $type );

		if ( ! $type_definition ) {
			throw new \Exception( sprintf( 'Type %s not found.', $type ) );
		}

		$type_class = isset( $type_definition['class'] ) ? $type_definition['class'] : false;

		if ( ! $type_class ) {
			throw new \Exception( sprintf( 'Type %s does not have a class.', $type ) );
		}

		return $type_class::make( $this );
	}

	/**
	 * Determine if the discount is always available.
	 *
	 * @return boolean
	 */
	public function is_always_available(): bool {
		return $this->settings()->has( 'availability' ) && $this->settings()->get( 'availability' )->value() === 'always';
	}

	/**
	 * Determine if the discount is available between specific dates.
	 *
	 * @return boolean
	 */
	public function is_available_between_dates(): bool {
		return $this->settings()->has( 'availability' ) && $this->settings()->get( 'availability' )->value() === 'dates';
	}

	/**
	 * Filter products by specific variations.
	 *
	 * This method supports filtering both cart items and order items.
	 *
	 * @param array $items The array of items to filter. Can be cart items or order items.
	 * @return array The filtered array of items.
	 */
	private function filter_products_by_specific_variations( $cart_items ) {
		$products            = [];
		$variations_products = maybe_unserialize( $this->settings()->get( 'selected_variations' )->value() );

		// bail out if there's no variations product saved for discounts
		if ( empty( $variations_products ) ) {
			return $cart_items;
		}

		$variations = Util::get_selected_variations_id_flatten( $variations_products );

		foreach ( $cart_items as $cart_item ) {
			// If the product is a simple product
			if ( empty( $cart_item['variation_id'] ) ) {
				$products[] = $cart_item;
			} elseif ( in_array( $cart_item['variation_id'], $variations ) ) {
				// If the product is a variable product and the variation_id is selected for discounts
				$products[] = $cart_item;
			}
		}

		return $products;
	}

	/**
	 * Get the product name with selected variations appended.
	 *
	 * This method takes a product object and appends the names of selected variations
	 * to the product name, if any variations are selected for this discount.
	 *
	 * @param \WC_Product $product The product object.
	 * @return string The product name, potentially with selected variations appended.
	 */
	public function product_name_with_selected_variations( $product ) {
		$variations_products = $this->settings()->has( 'selected_variations' ) ? maybe_unserialize( $this->settings()->get( 'selected_variations' )->value() ) : [];
		$selected_variations = Util::get_selected_variations_by_specific_product( $product->get_id(), $variations_products );

		// bail out if there's no variations product saved for discounts
		if ( empty( $variations_products ) || empty( $selected_variations ) ) {
			return $product ? $product->get_name() : '';
		}

		$display_name = $product->get_name() . ' (';

		$selected_variations = array_map(
			function ( $product_id ) {
				$product = wc_get_product( $product_id );
				if ( $product ) {
					$variations_name = [];
					$attributes      = $product->get_attributes();
					if ( is_array( $attributes ) ) {
						foreach ( $attributes as $attribute ) {
							if ( $attribute ) {
								$variations_name[] = ucfirst( $attribute );
							}
						}
					}
					return implode( ':', $variations_name );
				}
			},
			$selected_variations
		);

		$display_name .= implode( ', ', $selected_variations );

		$display_name .= ')';

		return $display_name;
	}

	/**
	 * Get the start date of the availability of the discount.
	 *
	 * @return \DateTime|null
	 */
	public function get_availability_start_date(): ?\DateTime {
		$date = null;

		if ( $this->settings()->has( 'start_date' ) ) {
			$date = $this->settings()->get( 'start_date' )->value();
		}

		if ( $date ) {
			$date = DateTime::createFromFormat( 'd-m-Y', $date, wp_timezone() );
		}

		// Set at the beginning of the day.
		if ( $date instanceof \DateTime ) {
			$date->setTime( 0, 0, 0 );
		}

		return $date;
	}

	/**
	 * Get the end date of the availability of the discount.
	 *
	 * @return \DateTime|null
	 */
	public function get_availability_end_date(): ?\DateTime {
		$date = null;

		if ( $this->settings()->has( 'end_date' ) ) {
			$date = $this->settings()->get( 'end_date' )->value();
		}

		if ( $date ) {
			$date = DateTime::createFromFormat( 'd-m-Y', $date, wp_timezone() );
		}

		// Set at the end of the day.
		if ( $date instanceof \DateTime ) {
			$date->setTime( 23, 59, 59 );
		}

		return $date;
	}

	/**
	 * Determine if the discount is available today.
	 *
	 * @return boolean
	 */
	public function is_discount_available_today(): bool {
		$today = new \DateTime();
		$today->setTimezone( wp_timezone() );
		$today->setTime( 0, 0, 0 );

		$start_date = $this->get_availability_start_date();
		$end_date   = $this->get_availability_end_date();

		if ( $start_date && $end_date ) {
			return $today >= $start_date && $today <= $end_date;
		}

		if ( $start_date ) {
			return $today >= $start_date;
		}

		if ( $end_date ) {
			return $today <= $end_date;
		}

		return true;
	}

	/**
	 * Determine if the discount has exclusions.
	 *
	 * @return boolean
	 */
	public function has_exclusions(): bool {
		$has = false;

		if ( $this->is_applicable_to_all_products() ) {
			return ! empty( $this->get_excluded_products() ) || ! empty( $this->get_excluded_categories() );
		} elseif ( $this->is_applicable_to_specific_products() ) {
			return false;
		} elseif ( $this->is_applicable_to_specific_categories() ) {
			return ! empty( $this->get_excluded_products() );
		}

		return $has;
	}

	/**
	 * Determine if the discount can exclude products.
	 *
	 * @return boolean
	 */
	public function can_exclude_products(): bool {
		return $this->is_applicable_to_all_products() || $this->is_applicable_to_specific_categories();
	}

	/**
	 * Determine if the discount can exclude categories.
	 *
	 * @return boolean
	 */
	public function can_exclude_categories(): bool {
		return $this->is_applicable_to_all_products();
	}

	/**
	 * Return the list of products that are excluded from the discount.
	 *
	 * @return array
	 */
	public function get_excluded_products(): array {
		$products = [];

		if ( $this->settings()->has( 'exclude_products' ) ) {
			$products = maybe_unserialize( $this->settings()->get( 'exclude_products' )->value() );
		}

		return $products;
	}

	/**
	 * Return the list of categories that are excluded from the discount.
	 *
	 * @return array
	 */
	public function get_excluded_categories(): array {
		$categories = [];

		if ( $this->settings()->has( 'exclude_categories' ) ) {
			$categories = maybe_unserialize( $this->settings()->get( 'exclude_categories' )->value() );
		}

		if ( ! empty( $categories ) ) {
			$categories = array_map( 'absint', $categories );

			// We need to include the children categories.
			foreach ( $categories as $category ) {
				$children = get_term_children( $category, 'product_cat' );

				if ( ! empty( $children ) ) {
					$categories = array_merge( $categories, $children );
				}
			}
		}

		return $categories;
	}

	/**
	 * Determine if the discount is applicable to the cart as a whole.
	 *
	 * @param \WC_Cart $cart The cart object.
	 * @return boolean True if the discount is applicable to the cart as a whole.
	 */
	public function is_applicable_to_cart( \WC_Cart $cart ): bool {
		$applicable = false;

		// Check if applicable based on products.
		if ( $this->is_applicable_to_all_products() ) {
			$applicable = true;
		} elseif ( $this->is_applicable_to_specific_products() ) {
			$applicable = Cart::has_specific_products( $cart, $this->get_elegible_products() );
		} elseif ( $this->is_applicable_to_specific_categories() ) {
			$applicable = Cart::has_products_in_categories( $cart, $this->get_elegible_categories() );
		}

		// Check if applicable based on currently logged in user.
		if ( $applicable && $this->applies_to_everyone() ) {
			$applicable = true;
		} elseif ( $applicable && $this->applies_to_roles() ) {
			$elegible_roles    = $this->get_applicable_roles();
			$check_for_guest   = in_array( 'guest', $elegible_roles, true );
			$current_user_role = Util::get_current_user_role( get_current_user_id(), $check_for_guest );
			$applicable        = in_array( $current_user_role, $elegible_roles, true );
		} elseif ( $applicable && $this->applies_to_users() ) {
			$elegible_users = array_map( 'absint', $this->get_applicable_users() );
			$current_user   = absint( get_current_user_id() );
			$applicable     = in_array( $current_user, $elegible_users, true );
		}

		// Check if applicable based on availability.
		if ( $applicable && $this->is_always_available() ) {
			$applicable = true;
		} elseif ( $applicable && $this->is_available_between_dates() ) {
			$applicable = $this->is_discount_available_today();
		}

		// Check if applicable based on type.
		$type = $this->get_type();

		if ( $type instanceof Applicable && $applicable ) {
			$applicable = $type->is_applicable_to_cart( $cart );
		}

		/**
		 * Filter whether the discount is applicable to the cart as a whole.
		 *
		 * @param boolean $applicable True if the discount is applicable to the cart as a whole.
		 * @param Discount $discount The discount object.
		 * @param \WC_Cart $cart The cart object.
		 * @return boolean True if the discount is applicable to the cart as a whole.
		 */
		return apply_filters( 'wdm_discount_is_applicable_to_cart', $applicable, $this, $cart );
	}

	/**
	 * Determine if the discount is applicable to the order as a whole.
	 *
	 * @param \WC_Order $order The order object.
	 * @return boolean True if the discount is applicable to the order as a whole.
	 */
	public function is_applicable_to_order( \WC_Order $order ): bool {
		$applicable = false;

		// Check if applicable based on products.
		if ( $this->is_applicable_to_all_products() ) {
			$applicable = true;
		} elseif ( $this->is_applicable_to_specific_products() ) {
			$applicable = Orders::has_specific_products( $order, $this->get_elegible_products() );
		} elseif ( $this->is_applicable_to_specific_categories() ) {
			$applicable = Orders::has_products_in_categories( $order, $this->get_elegible_categories() );
		}

		// Check if applicable based on the customer of the order.
		if ( $applicable && $this->applies_to_everyone() ) {
			$applicable = true;
		} elseif ( $applicable && $this->applies_to_roles() ) {
			$elegible_roles    = $this->get_applicable_roles();
			$check_for_guest   = in_array( 'guest', $elegible_roles, true );
			$current_user_role = Util::get_current_user_role( $order->get_customer_id(), $check_for_guest );
			$applicable        = in_array( $current_user_role, $elegible_roles, true );
		} elseif ( $applicable && $this->applies_to_users() ) {
			$elegible_users = array_map( 'absint', $this->get_applicable_users() );
			$current_user   = absint( $order->get_customer_id() );
			$applicable     = in_array( $current_user, $elegible_users, true );
		}

		// Check if applicable based on availability.
		if ( $applicable && $this->is_always_available() ) {
			$applicable = true;
		} elseif ( $applicable && $this->is_available_between_dates() ) {
			$applicable = $this->is_discount_available_today();
		}

		// Check if applicable based on type.
		$type = $this->get_type();

		if ( $type instanceof Applicable && $applicable ) {
			$applicable = $type->is_applicable_to_order( $order );
		}

		/**
		 * Filter whether the discount is applicable to the order as a whole.
		 *
		 * @param boolean $applicable True if the discount is applicable to the order as a whole.
		 * @param Discount $discount The discount object.
		 * @param \WC_Order $order The order object.
		 * @return boolean True if the discount is applicable to the order as a whole.
		 */
		return apply_filters( 'wdm_discount_is_applicable_to_order', $applicable, $this, $order );
	}

	/**
	 * Get the products that are relevant to the discount from the cart.
	 *
	 * Products are relevant if they are elegible for the discount and not excluded from the discount.
	 * Products are retrieved based on the discount applicability and the discount exclusions.
	 *
	 * If an order is provided, the products will be retrieved from the order instead of the cart.
	 *
	 * @param \WC_Cart|false $cart The cart object.
	 * @param \WC_Order|false $order The order object. If provided, the products will be retrieved from the order instead of the cart.
	 * @param boolean $sort Whether to sort the products by price. Defaults to true.
	 * @throws \Exception If neither a cart nor an order is provided.
	 * @return array The products that are relevant to the discount.
	 */
	public function get_relevant_products( $cart = false, $order = false, $sort = true ): array {
		$products  = [];
		$has_order = $order instanceof \WC_Order;

		if ( ! $cart && ! $has_order ) {
			throw new Exception( 'Either a cart or an order must be provided.' );
		}

		if ( $this->is_applicable_to_all_products() ) {
			$products = $has_order ? $order->get_items() : $cart->get_cart_contents();
		} elseif ( $this->is_applicable_to_specific_products() ) {
			$products = $has_order ? Orders::get_specific_products( $order, $this->get_elegible_products() ) : Cart::get_specific_products( $cart, $this->get_elegible_products() );
			// If applicable to specific variations
			if ( $this->is_applicable_to_specific_variations() ) {
				$products = $this->filter_products_by_specific_variations( $products );
			}
		} elseif ( $this->is_applicable_to_specific_categories() ) {
			$products = $has_order ? Orders::get_products_in_categories( $order, $this->get_elegible_categories() ) : Cart::get_products_in_categories( $cart, $this->get_elegible_categories() );
		}

		if ( $this->has_exclusions() && ( $this->can_exclude_products() || $this->can_exclude_categories() ) ) {
			$excluded_products = $this->get_excluded_products();
			$excluded_products = array_map( 'absint', $excluded_products );
			$excluded_products = array_filter( $excluded_products );

			$excluded_categories = $this->get_excluded_categories();
			$excluded_categories = array_map( 'absint', $excluded_categories );
			$excluded_categories = array_filter( $excluded_categories );

			$products = array_filter(
				$products,
				function ( $product ) use ( $excluded_products, $excluded_categories, $has_order ) {
					$excluded = false;

					if ( $this->can_exclude_products() ) {
						$excluded = in_array( $has_order ? $product->get_product_id() : $product['product_id'], $excluded_products, true );
					}

					if ( ! $excluded && $this->can_exclude_categories() ) {
						$excluded = Util::product_has_categories( $has_order ? $product->get_product_id() : $product['product_id'], $excluded_categories );
					}

					return ! $excluded;
				}
			);
		}

		// Now check if there's any cached products and exclude them.
		$cached_discounted_products = wdm()->cache()->get_discounted_products();

		if ( ! empty( $cached_discounted_products ) ) {
			$products = array_filter(
				$products,
				function ( $product ) use ( $cached_discounted_products, $has_order ) {
					return ! in_array( $has_order ? $product->get_product_id() : $product['product_id'], $cached_discounted_products, true );
				}
			);
		}

		// Sort products by price but keep their array keys.
		if ( $sort ) {
			uasort(
				$products,
				function ( $a, $b ) {
					// Use the product price instead of the line subtotal.
					$a_price = $a instanceof \WC_Order_Item_Product ? Products::get_product_price( $a->get_product() ) : Products::get_product_price( $a['data'] );
					$b_price = $b instanceof \WC_Order_Item_Product ? Products::get_product_price( $b->get_product() ) : Products::get_product_price( $b['data'] );

					return $a_price <=> $b_price;
				}
			);
		}

		return $products;
	}

	/**
	 * Determine if the discount is relevant for a specific product.
	 *
	 * @param string|bool|int|\WC_Product $product The product object.
	 * @return boolean True if the discount is relevant for the product.
	 */
	public function is_relevant_for_product( $product ): bool {
		$relevant = false;

		if ( ! $product instanceof \WC_Product ) {
			return $relevant;
		}

		if ( $this->is_applicable_to_all_products() ) {
			$relevant = true;
		} elseif ( $this->is_applicable_to_specific_products() ) {
			$elegible_products           = $this->get_elegible_products();
			$elegible_variations         = $this->get_elegible_variations();
			$is_applicable_to_variations = $this->is_applicable_to_specific_variations();

			if ( $product->is_type( 'variation' ) ) {
				if ( $is_applicable_to_variations ) {
					// Check if this specific variation is eligible
					$relevant = in_array( $product->get_id(), $elegible_variations, true );
				} else {
					// If not applicable to specific variations, check the parent product
					$relevant = in_array( $product->get_parent_id(), $elegible_products, true );
				}
			} else {
				// For non-variation products, check if the product is in the eligible list
				$relevant = in_array( $product->get_id(), $elegible_products, true );
			}
		} elseif ( $this->is_applicable_to_specific_categories() ) {
			$relevant = Util::product_has_categories( $product->get_id(), $this->get_elegible_categories() );
		}

		if ( $relevant && $this->has_exclusions() && ( $this->can_exclude_products() || $this->can_exclude_categories() ) ) {
			$excluded_products = $this->get_excluded_products();
			$excluded_products = array_map( 'absint', $excluded_products );
			$excluded_products = array_filter( $excluded_products );

			$excluded_categories = $this->get_excluded_categories();
			$excluded_categories = array_map( 'absint', $excluded_categories );
			$excluded_categories = array_filter( $excluded_categories );

			if ( $this->can_exclude_products() ) {
				$relevant = ! in_array( $product->get_id(), $excluded_products, true );
			}

			if ( $relevant && $this->can_exclude_categories() ) {
				$relevant = ! Util::product_has_categories( $product->get_id(), $excluded_categories );
			}
		}

		if ( $relevant && $this->is_available_between_dates() ) {
			$relevant = $this->is_discount_available_today();
		}

		if ( $relevant && $this->applies_to_roles() ) {
			$elegible_roles    = $this->get_applicable_roles();
			$check_for_guest   = in_array( 'guest', $elegible_roles, true );
			$current_user_role = Util::get_current_user_role( get_current_user_id(), $check_for_guest );
			$relevant          = in_array( $current_user_role, $elegible_roles, true );
		} elseif ( $relevant && $this->applies_to_users() ) {
			$elegible_users = array_map( 'absint', $this->get_applicable_users() );
			$current_user   = absint( get_current_user_id() );
			$relevant       = in_array( $current_user, $elegible_users, true );
		}

		return $relevant;
	}

	/**
	 * Get the content of the application column in the admin area.
	 *
	 * @return string The content of the application column.
	 */
	private function get_application_column_content(): string {
		$application = '';

		if ( $this->is_applicable_to_all_products() ) {
			$application = esc_html__( 'All products', 'woocommerce-discount-manager' );
		} elseif ( $this->is_applicable_to_specific_products() ) {
			$products = $this->get_elegible_products();

			if ( ! empty( $products ) ) {
				$products = array_map(
					function ( $product_id ) {
						$product = wc_get_product( $product_id );
						if ( $product->get_type() === 'variable' ) {
							return $this->product_name_with_selected_variations( $product );
						} else {
							return $product ? $product->get_name() : '';
						}
					},
					$products
				);

				$products = array_filter( $products );

				if ( ! empty( $products ) ) {
					$application = '<strong>' . esc_html__( 'Products: ', 'woocommerce-discount-manager' ) . '</strong>' . implode( ', ', $products );
				}
			}

			if ( empty( $application ) ) {
				$application = esc_html__( 'No products selected', 'woocommerce-discount-manager' );
			}
		} elseif ( $this->is_applicable_to_specific_categories() ) {
			$categories = $this->get_elegible_categories();

			// Now filter the categories and remove any duplicates.
			$categories = array_unique( $categories );

			if ( ! empty( $categories ) ) {
				$categories = array_map(
					function ( $category_id ) {
						$category = get_term( $category_id, 'product_cat' );
						return $category ? $category->name : '';
					},
					$categories
				);

				$categories = array_filter( $categories );

				if ( ! empty( $categories ) ) {
					$application = '<strong>' . esc_html__( 'Categories: ', 'woocommerce-discount-manager' ) . '</strong>' . implode( ', ', $categories );
				}
			}

			if ( empty( $application ) ) {
				$application = esc_html__( 'No categories selected', 'woocommerce-discount-manager' );
			}
		}

		// Now check if there's any selected users or roles.
		if ( $this->applies_to_roles() ) {
			$roles = $this->get_applicable_roles();

			if ( ! empty( $roles ) ) {
				$roles = array_map(
					function ( $role ) {
						return ucfirst( $role );
					},
					$roles
				);

				$roles = array_filter( $roles );

				if ( ! empty( $roles ) ) {
					$application .= '<br><strong>' . esc_html__( 'Roles: ', 'woocommerce-discount-manager' ) . '</strong>' . implode( ', ', $roles );
				}
			}
		} elseif ( $this->applies_to_users() ) {
			$users = $this->get_applicable_users();

			if ( ! empty( $users ) ) {
				$users = array_map(
					function ( $user_id ) {
						$user = get_user_by( 'id', $user_id );
						return $user ? $user->display_name : '';
					},
					$users
				);

				$users = array_filter( $users );

				if ( ! empty( $users ) ) {
					$application .= '<br><strong>' . esc_html__( 'Users: ', 'woocommerce-discount-manager' ) . '</strong>' . implode( ', ', $users );
				}
			}
		}

		// Now check if there's any excluded products or categories.
		if ( $this->has_exclusions() ) {
			$excluded_products = $this->get_excluded_products();
			$excluded_products = array_map( 'absint', $excluded_products );
			$excluded_products = array_filter( $excluded_products );

			$excluded_categories = $this->get_excluded_categories();
			$excluded_categories = array_map( 'absint', $excluded_categories );
			$excluded_categories = array_filter( $excluded_categories );

			if ( ! empty( $excluded_products ) && $this->can_exclude_products() ) {
				$excluded_products = array_map(
					function ( $product_id ) {
						$product = wc_get_product( $product_id );
						return $product ? $product->get_name() : '';
					},
					$excluded_products
				);

				$excluded_products = array_filter( $excluded_products );

				if ( ! empty( $excluded_products ) ) {
					$application .= '<br><strong>' . esc_html__( 'Excludes products: ', 'woocommerce-discount-manager' ) . '</strong>' . implode( ', ', $excluded_products );
				}
			}

			if ( ! empty( $excluded_categories ) && $this->can_exclude_categories() ) {
				$excluded_categories = array_map(
					function ( $category_id ) {
						$category = get_term( $category_id, 'product_cat' );
						return $category ? $category->name : '';
					},
					$excluded_categories
				);

				$excluded_categories = array_filter( $excluded_categories );

				if ( ! empty( $excluded_categories ) ) {
					$application .= '<br><strong>' . esc_html__( 'Excludes categories: ', 'woocommerce-discount-manager' ) . '</strong>' . implode( ', ', $excluded_categories );
				}
			}
		}

		if ( ! $this->is_always_available() && $this->is_available_between_dates() ) {
			$start_date = $this->get_availability_start_date();
			$end_date   = $this->get_availability_end_date();

			if ( $start_date && $end_date ) {
				$application .= '<br><strong>' . esc_html__( 'Dates: ', 'woocommerce-discount-manager' ) . '</strong>' . $start_date->format( 'd/m/Y' ) . ' - ' . $end_date->format( 'd/m/Y' );
			}
		}

		return $application;
	}

	/**
	 * List of properties usually used via the rest api.
	 *
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return [
			'id'          => $this->id(),
			'name'        => $this->get_name(),
			'slug'        => $this->get_slug(),
			'enabled'     => $this->enabled(),
			'priority'    => $this->priority(),
			'settings'    => $this->prepare_settings_for_table(),
			'application' => $this->get_application_column_content(),
			'type'        => $this->get_type()->get_name(),
		];
	}

	/**
	 * Prepares the settings for the table.
	 * This is used to display the settings in the admin area
	 * and to prepare the settings for the rest api
	 * which is used by the react app and may require extra data.
	 *
	 * @return array The settings.
	 */
	private function prepare_settings_for_table(): array {
		$settings         = $this->settings()->all();
		$settings['name'] = $this->get_name();

		return $settings;
	}
}
