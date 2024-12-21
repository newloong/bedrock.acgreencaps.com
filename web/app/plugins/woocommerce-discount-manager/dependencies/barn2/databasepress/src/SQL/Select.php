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
class Select extends SelectStatement
{
    /** @var Database */
    protected $connection;
    /**
     * Select constructor.
     *
     * @param Database          $connection
     * @param array|string      $tables
     * @param SQLStatement|null $statement
     */
    public function __construct(Database $connection, $tables, SQLStatement $statement = null)
    {
        parent::__construct($tables, $statement);
        $this->connection = $connection;
    }
    /**
     * @param   string|Closure|Expression|string[]|Closure[]|Expression[] $columns (optional)
     *
     * @return  ResultSet
     */
    public function select($columns = [])
    {
        parent::select($columns);
        $compiler = $this->connection->getCompiler();
        return $this->connection->query($compiler->select($this->sql), $compiler->getParams());
    }
    /**
     * @param   string|Closure|Expression $name
     *
     * @return  mixed|false
     */
    public function column($name)
    {
        parent::column($name);
        return $this->getColumnResult();
    }
    /**
     * @param   string|Closure|Expression|string[]|Closure[]|Expression[] $column (optional)
     * @param   bool                                                      $distinct (optional)
     * @return  int|bool
     */
    public function count($column = '*', bool $distinct = \false)
    {
        parent::count($column, $distinct);
        return $this->getColumnResult();
    }
    /**
     * @param   string|Closure|Expression $column
     * @param   bool                      $distinct (optional)
     *
     * @return  int|float
     */
    public function avg($column, bool $distinct = \false)
    {
        parent::avg($column, $distinct);
        return $this->getColumnResult();
    }
    /**
     * @param   string|Closure|Expression $column
     * @param   bool                      $distinct (optional)
     *
     * @return  int|float
     */
    public function sum($column, bool $distinct = \false)
    {
        parent::sum($column, $distinct);
        return $this->getColumnResult();
    }
    /**
     * @param   string|Closure|Expression $column
     * @param   bool                      $distinct (optional)
     *
     * @return  int|float
     */
    public function min($column, bool $distinct = \false)
    {
        parent::min($column, $distinct);
        return $this->getColumnResult();
    }
    /**
     * @param   string|Closure|Expression $column
     * @param   bool                      $distinct (optional)
     *
     * @return  int|float
     */
    public function max($column, bool $distinct = \false)
    {
        parent::max($column, $distinct);
        return $this->getColumnResult();
    }
    protected function getColumnResult()
    {
        $compiler = $this->connection->getCompiler();
        return $this->connection->column($compiler->select($this->sql), $compiler->getParams());
    }
}
