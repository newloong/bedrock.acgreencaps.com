<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\databasepress
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\SQL;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\Database;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\WPDBException;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\WPDBHelpers;
class Insert extends InsertStatement
{
    use WPDBHelpers;
    /**
     * Database instance.
     *
     * @var Database
     */
    protected $connection;
    /**
     * Insert constructor.
     *
     * @param Database          $connection
     * @param SQLStatement|null $statement
     */
    public function __construct(Database $connection, SQLStatement $statement = null)
    {
        parent::__construct($statement);
        $this->connection = $connection;
    }
    /**
     * @param  string $table
     * @throws WPDBException When wpdb throws an error.
     * @return int|true
     */
    public function into(string $table)
    {
        parent::into($table);
        $compiler = $this->connection->getCompiler();
        $command = $this->connection->command($compiler->insert($this->sql), $compiler->getParams());
        if ($this->connection->isDebug()) {
            return $command;
        }
        // Execute the query.
        $query = $this->getWPDBQuery($command);
        global $wpdb;
        $errors = $wpdb->last_error;
        if (!empty($errors)) {
            throw new WPDBException($wpdb->last_error);
        }
        return $wpdb->insert_id !== 0 ? $wpdb->insert_id : \true;
    }
}
