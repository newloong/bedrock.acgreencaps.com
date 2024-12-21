<?php

namespace Barn2\Plugin\Discount_Manager\Traits;

/**
 * Provides access to an `assign` method for entities.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
trait Entity_Assignable {

	/**
	 * Assign mass data to entity.
	 *
	 * @param array $data
	 * @return self
	 */
	public function assign( array $data ): self {
		$this->orm()->assign( $data );
		return $this;
	}
}
