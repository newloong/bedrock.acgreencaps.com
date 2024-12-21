<?php

namespace Barn2\Plugin\Discount_Manager\Entities;

use Barn2\Plugin\Discount_Manager\Database\Discount_Settings;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Entity;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\IEntityMapper;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\IMappableEntity;
use Barn2\Plugin\Discount_Manager\Traits\Entity_ID;
use JsonSerializable;

/**
 * Represents a setting of a discount.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Discount_Setting extends Entity implements IMappableEntity, JsonSerializable {

	use Entity_ID;

	/**
	 * Retrieve ID of the associated discount id.
	 *
	 * @return int
	 */
	public function discount_id(): int {
		return $this->orm()->getColumn( 'discount_id' );
	}

	/**
	 * Set the discount id to which this setting is associated to.
	 *
	 * @param int $discount_id
	 * @return self
	 */
	public function set_discount_id( int $discount_id ): self {
		$this->orm()->setColumn( 'discount_id', $discount_id );
		return $this;
	}

	/**
	 * Get the key of the setting.
	 *
	 * @return string
	 */
	public function key(): string {
		return $this->orm()->getColumn( 'key' );
	}

	/**
	 * Set the key of the setting.
	 *
	 * @param string $key
	 * @return self
	 */
	public function set_key( string $key ): self {
		$this->orm()->setColumn( 'key', $key );
		return $this;
	}

	/**
	 * Get the value of the setting.
	 *
	 * @return mixed
	 */
	public function value() {
		return maybe_unserialize( $this->orm()->getColumn( 'value' ) );
	}

	/**
	 * Set the value of the setting
	 *
	 * @param string $value
	 * @return self
	 */
	public function set_value( string $value ): self {
		$this->orm()->setColumn( 'value', $value );
		return $this;
	}

	/**
	 * Get associated discount entity.
	 *
	 * @return Discount
	 */
	public function discount(): Discount {
		return $this->orm()->getRelated( 'discount' );
	}

	/**
	 * Associate a discount to this setting.
	 *
	 * @param Discount $discount
	 * @return self
	 */
	public function set_discount( Discount $discount ): self {
		$this->orm()->setRelated( 'discount', $discount );
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public static function mapEntity( IEntityMapper $mapper ) {
		global $wpdb;

		$mapper->table( "{$wpdb->prefix}" . Discount_Settings::NAME );

		$mapper->cast(
			[
				'id'          => 'integer',
				'discount_id' => 'integer',
			]
		);

		$mapper->relation( 'discount' )->belongsTo( Discount::class );
	}

	/**
	 * @inheritdoc
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return $this->value();
	}
}
