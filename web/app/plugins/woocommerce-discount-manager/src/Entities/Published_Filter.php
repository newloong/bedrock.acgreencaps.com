<?php

namespace Barn2\Plugin\Discount_Manager\Entities;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core\Query;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\EntityFilter;

/**
 * Filters the discounts by published status.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Published_Filter extends EntityFilter {
	/**
	 * Filters the query by published status.
	 *
	 * @param  Query $query The query to filter.
	 * @param  null  $data  The data to filter.
	 * @return void
	 */
	public function filter( Query $query, $data = null ) {
		$query->where( 'enabled' )->is( true );
	}
}
