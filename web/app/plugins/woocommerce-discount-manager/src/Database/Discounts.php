<?php

namespace Barn2\Plugin\Discount_Manager\Database;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\Schema\CreateTable;

/**
 * Handles creation of the `discounts` db table.
 *
 * @codeCoverageIgnore
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Discounts extends Table {
	/**
	 * @inheritdoc
	 */
	const NAME = 'wdm_discounts';

	/**
	 * @inheritdoc
	 */
	public function create(): void {
		$this->db->schema()->create(
			$this->get_table_name(),
			function ( CreateTable $table ) {
				$table->integer( 'id' )
					->index()
					->autoincrement();

				$table->string( 'name' )
					->notNull()
					->index();

				$table->string( 'slug' )
					->notNull();

				$table->boolean( 'enabled' )
					->notNull()
					->defaultValue( true );

				$table->integer( 'priority' )
					->notNull()
					->defaultValue( 0 );

				$table->timestamp( 'created_at' );
				$table->timestamp( 'updated_at' );
			}
		);
	}
}
