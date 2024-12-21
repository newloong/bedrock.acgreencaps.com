<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\databasepress
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\SQL;

use Closure;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\Database;
class Query extends BaseStatement
{
    /**
     * Database connection handler.
     *
     * @var Database
     */
    protected $connection;
    /**
     * Tables to query from.
     *
     * @var string|array
     */
    protected $tables;
    /**
     * Query constructor.
     *
     * @param Database          $connection
     * @param string|array      $tables
     * @param SQLStatement|null $statement
     */
    public function __construct(Database $connection, $tables, SQLStatement $statement = null)
    {
        parent::__construct($statement);
        $this->tables = $tables;
        $this->connection = $connection;
    }
    /**
     * @return  Select
     */
    protected function buildSelect() : Select
    {
        return new Select($this->connection, $this->tables, $this->sql);
    }
    /**
     * @return  Delete
     */
    protected function buildDelete() : Delete
    {
        return new Delete($this->connection, $this->tables, $this->sql);
    }
    /**
     * @param   bool $value (optional)
     *
     * @return  Select|SelectStatement
     */
    public function distinct($value = \true)
    {
        return $this->buildSelect()->distinct($value);
    }
    /**
     * @param   string|Closure|Expression|array $columns
     *
     * @return  Select
     */
    public function groupBy($columns)
    {
        return $this->buildSelect()->groupBy($columns);
    }
    /**
     * @param   string  $column
     * @param   Closure $value (optional)
     *
     * @return  Select
     */
    public function having($column, Closure $value = null)
    {
        return $this->buildSelect()->having($column, $value);
    }
    /**
     * @param   string  $column
     * @param   Closure $value (optional)
     *
     * @return  Select
     */
    public function andHaving($column, Closure $value = null)
    {
        return $this->buildSelect()->andHaving($column, $value);
    }
    /**
     * @param   string|Closure|Expression $column
     * @param   Closure                   $value (optional)
     *
     * @return  Select
     */
    public function orHaving($column, Closure $value = null)
    {
        return $this->buildSelect()->orHaving($column, $value);
    }
    /**
     * @param   string|Closure|Expression|array $columns
     * @param   string                          $order (optional)
     * @param   string                          $nulls (optional)
     *
     * @return  Select|SelectStatement
     */
    public function orderBy($columns, $order = 'ASC', $nulls = null)
    {
        return $this->buildSelect()->orderBy($columns, $order, $nulls);
    }
    /**
     * @param   int $value
     *
     * @return  Select|SelectStatement
     */
    public function limit($value)
    {
        return $this->buildSelect()->limit($value);
    }
    /**
     * @param   int $value
     *
     * @return  Select|SelectStatement
     */
    public function offset($value)
    {
        return $this->buildSelect()->offset($value);
    }
    /**
     * @param   string $table
     * @param   string $database (optional)
     *
     * @return  Select|SelectStatement
     */
    public function into($table, $database = null)
    {
        return $this->buildSelect()->into($table, $database);
    }
    /**
     * @param   array $columns (optional)
     *
     * @return  \Barn2\Databasepress\ResultSet
     */
    public function select($columns = [])
    {
        return $this->buildSelect()->select($columns);
    }
    /**
     * @param   string|Closure|Expression $name
     *
     * @return  mixed|false
     */
    public function column($name)
    {
        return $this->buildSelect()->column($name);
    }
    /**
     * @param   string|Closure|Expression $column (optional)
     * @param   bool                      $distinct (optional)
     *
     * @return  int
     */
    public function count($column = '*', $distinct = \false)
    {
        return $this->buildSelect()->count($column, $distinct);
    }
    /**
     * @param   string|Closure|Expression $column
     * @param   bool                      $distinct (optional)
     *
     * @return  int|float
     */
    public function avg($column, $distinct = \false)
    {
        return $this->buildSelect()->avg($column, $distinct);
    }
    /**
     * @param   string|Closure|Expression $column
     * @param   bool                      $distinct (optional)
     *
     * @return  int|float
     */
    public function sum($column, $distinct = \false)
    {
        return $this->buildSelect()->sum($column, $distinct);
    }
    /**
     * @param   string|Closure|Expression $column
     * @param   bool                      $distinct (optional)
     *
     * @return  int|float
     */
    public function min($column, $distinct = \false)
    {
        return $this->buildSelect()->min($column, $distinct);
    }
    /**
     * @param   string|Closure|Expression $column
     * @param   bool                      $distinct (optional)
     *
     * @return  int|float
     */
    public function max($column, $distinct = \false)
    {
        return $this->buildSelect()->max($column, $distinct);
    }
    /**
     * @param   string[] $tables (optional)
     *
     * @return  int
     */
    public function delete($tables = [])
    {
        return $this->buildDelete()->delete($tables);
    }
}
