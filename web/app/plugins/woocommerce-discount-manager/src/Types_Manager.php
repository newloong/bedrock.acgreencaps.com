<?php

namespace Barn2\Plugin\Discount_Manager;

use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Registerable;
use Barn2\Plugin\Discount_Manager\Types\Bulk;
use Barn2\Plugin\Discount_Manager\Types\Buy_X_For_Y_Discount;
use Barn2\Plugin\Discount_Manager\Types\Buy_X_For_Y_Fixed;
use Barn2\Plugin\Discount_Manager\Types\Free_Products;
use Barn2\Plugin\Discount_Manager\Types\Simple;
use Barn2\Plugin\Discount_Manager\Types\Total_Spend;
use Barn2\Plugin\Discount_Manager\Types\Type;
use JsonSerializable;

/**
 * Manages discount types.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Types_Manager implements Registerable, JsonSerializable {

	/**
	 * All registered types.
	 *
	 * @var array
	 */
	protected $types = [];

	/**
	 * Register types of discounts.
	 *
	 * @return void
	 */
	public function register(): void {
		$available_types = [
			Bulk::class,
			Buy_X_For_Y_Discount::class,
			Buy_X_For_Y_Fixed::class,
			Free_Products::class,
			Simple::class,
			Total_Spend::class,
		];

		foreach ( $available_types as $type_class ) {
			$this->add_type( $type_class );
		}
	}

	/**
	 * Add a type of discount.
	 *
	 * @param string $type_class The class name of the type.
	 * @throws \InvalidArgumentException If the type is not an instance of Type.
	 * @return self
	 */
	public function add_type( string $type_class ): self {
		$type = new $type_class();

		if ( ! $type instanceof Type ) {
			throw new \InvalidArgumentException( 'Type must be an instance of ' . Type::class );
		}

		$this->types[ $type_class ] = [
			'name'     => $type::get_name(),
			'tooltip'  => $type::get_tooltip(),
			'slug'     => $type::get_slug(),
			'class'    => $type_class,
			'order'    => $type::ORDER,
			'settings' => $type::get_settings(),
			'defaults' => $type::get_default_settings_values(),
		];

		return $this;
	}

	/**
	 * Get all registered types.
	 *
	 * @return array
	 */
	public function all(): array {
		return $this->types;
	}

	/**
	 * Get a type by its slug.
	 *
	 * @param string $slug The slug of the type.
	 * @return array|null
	 */
	public function get_by_slug( string $slug ): ?array {
		foreach ( $this->types as $type_class => $type ) {
			if ( $type['slug'] === $slug ) {
				return $type;
			}
		}

		return null;
	}

	/**
	 * Get a type by its class name.
	 *
	 * @param string $type_class The class name of the type.
	 * @return array|null
	 */
	public function get_by_class( string $type_class ): ?array {
		return $this->types[ $type_class ] ?? null;
	}

	/**
	 * Returns the json object for the types.
	 * This is used in the admin settings page.
	 *
	 * @return array
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {

		$types = $this->all();

		// Sort the by the order.
		uasort(
			$types,
			function ( $a, $b ) {
				return $a['order'] <=> $b['order'];
			}
		);

		// Remove the order and class from the array.
		foreach ( $types as &$type ) {
			unset( $type['order'], $type['class'] );
		}

		// Reset the array keys.
		$types = array_values( $types );

		return $types;
	}
}
