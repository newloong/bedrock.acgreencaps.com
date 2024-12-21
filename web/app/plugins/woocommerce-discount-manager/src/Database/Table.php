<?php

namespace Barn2\Plugin\Discount_Manager\Database;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\Database;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Registerable;

/**
 * Base `Table` class used to handle the registration
 * of custom database tables during plugin activation.
 *
 * @codeCoverageIgnore
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
abstract class Table implements Registerable {
	/**
	 * Name of the database table without the wpdb prefix.
	 *
	 * @var string
	 */
	const NAME = '';

	/**
	 * Database connection
	 *
	 * @var Database
	 */
	protected $db;

	/**
	 * Initialize the database class.
	 */
	public function __construct() {
		$this->db = new Database();
	}

	/**
	 * Get the prefix of the database.
	 *
	 * @return string
	 */
	public function get_prefix(): string {
		global $wpdb;

		return $wpdb->prefix;
	}

	/**
	 * Returns the name of this table + the wpdb prefix.
	 *
	 * @return string
	 */
	public function get_table_name(): string {
		return "{$this->get_prefix()}" . $this::NAME;
	}

	/**
	 * Maybe create the database table if it doesn't exist.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( empty( $this::NAME ) ) {
			return;
		}

		if ( ! $this->db->schema()->hasTable( $this->get_table_name() ) ) {
			$this->create();
		}
	}

	/**
	 * Creates the database table.
	 *
	 * @return void
	 */
	abstract public function create(): void;
}
