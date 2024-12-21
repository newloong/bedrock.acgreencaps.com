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
class Delete extends DeleteStatement
{
    use WPDBHelpers;
    /** @var Database */
    protected $connection;
    /**
     * Delete constructor.
     *
     * @param Connection        $connection
     * @param string|array      $from
     * @param SQLStatement|null $statement
     */
    public function __construct(Database $connection, $from, SQLStatement $statement = null)
    {
        parent::__construct($from, $statement);
        $this->connection = $connection;
    }
    /**
     * Delete records.
     *
     * @param  string|array $tables (optional)
     * @throws WPDBException When wpdb throws an error.
     * @return int Number of deleted records.
     */
    public function delete($tables = [])
    {
        parent::delete($tables);
        $compiler = $this->connection->getCompiler();
        $command = $this->connection->command($compiler->delete($this->sql), $compiler->getParams());
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
        return $query;
    }
}
