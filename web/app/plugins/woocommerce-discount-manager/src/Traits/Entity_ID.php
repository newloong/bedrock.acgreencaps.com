<?php

namespace Barn2\Plugin\Discount_Manager\Traits;

/**
 * Provides access to the ID of the entity.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
trait Entity_ID {

	/**
	 * Retrieve ID of the Entity
	 *
	 * @return int
	 */
	public function id(): int {
		return $this->orm()->getColumn( 'id' );
	}

	/**
	 * Set ID of the Entity
	 *
	 * @param int|string|null $id ID of the Entity
	 */
	public function set_id( $id ): void {
		$this->orm()->setColumn( 'id', $id );
	}
}
