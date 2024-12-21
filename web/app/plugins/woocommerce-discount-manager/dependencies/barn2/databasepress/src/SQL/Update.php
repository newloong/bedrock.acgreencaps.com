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
class Update extends UpdateStatement
{
    use WPDBHelpers;
    /**
     * Database connection.
     *
     * @var Database
     */
    protected $connection;
    /**
     * Update constructor.
     *
     * @param Database          $connection
     * @param string|array      $table
     * @param SQLStatement|null $statement
     */
    public function __construct(Database $connection, $table, SQLStatement $statement = null)
    {
        parent::__construct($table, $statement);
        $this->connection = $connection;
    }
    /**
     * @param   string       $sign
     * @param   string|array $columns
     * @param   int          $value
     *
     * @return  int
     */
    protected function incrementOrDecrement(string $sign, $columns, $value)
    {
        if (!\is_array($columns)) {
            $columns = [$columns];
        }
        $values = [];
        foreach ($columns as $k => $v) {
            if (\is_numeric($k)) {
                $values[$v] = function (Expression $expr) use($sign, $v, $value) {
                    $expr->column($v)->{$sign}->value($value);
                };
            } else {
                $values[$k] = function (Expression $expr) use($sign, $k, $v) {
                    $expr->column($k)->{$sign}->value($v);
                };
            }
        }
        return $this->set($values);
    }
    /**
     * @param   string|array $column
     * @param   int          $value (optional)
     *
     * @return  int
     */
    public function increment($column, $value = 1)
    {
        return $this->incrementOrDecrement('+', $column, $value);
    }
    /**
     * @param string|array $column
     * @param int          $value (optional)
     * @return int
     */
    public function decrement($column, $value = 1)
    {
        return $this->incrementOrDecrement('-', $column, $value);
    }
    /**
     * @param  array $columns
     * @throws WPDBException When wpdb throws an error.
     * @return int Count of affected rows.
     */
    public function set(array $columns)
    {
        parent::set($columns);
        $compiler = $this->connection->getCompiler();
        $command = $this->connection->command($compiler->update($this->sql), $compiler->getParams());
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
