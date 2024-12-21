<?php

namespace Barn2\Plugin\Discount_Manager\Database;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\Schema\CreateTable;

/**
 * Handles creation of the database table containing settings
 * of the discounts.
 *
 * @codeCoverageIgnore
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Discount_Settings extends Table {
	/**
	 * @inheritdoc
	 */
	const NAME = 'wdm_discount_settings';

	/**
	 * @inheritdoc
	 */
	public function create(): void {
		$this->db->schema()->create(
			$this->get_table_name(),
			function ( CreateTable $table ) {
				$table->integer( 'id' )
					->autoincrement()
					->index()
					->unsigned();

				$table->integer( 'discount_id' )
					->index();

				$table->foreign( 'discount_id' )
					->references( "{$this->get_prefix()}" . Discounts::NAME, 'id' )
					->onDelete( 'cascade' );

				$table->string( 'key' )
					->index();

				$table->text( 'value' )
					->size( 'big' );
			}
		);
	}
}
