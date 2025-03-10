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
class SelectStatement extends BaseStatement
{
    /** @var HavingStatement */
    protected $have;
    /**
     * SelectStatement constructor.
     *
     * @param string|array      $tables
     * @param SQLStatement|null $statement
     */
    public function __construct($tables, SQLStatement $statement = null)
    {
        parent::__construct($statement);
        if (!\is_array($tables)) {
            $tables = [$tables];
        }
        $this->sql->addTables($tables);
        $this->have = new HavingStatement($this->sql);
    }
    /**
     * @param string      $table
     * @param string|null $database
     * @return SelectStatement
     */
    public function into(string $table, string $database = null) : self
    {
        $this->sql->setInto($table, $database);
        return $this;
    }
    /**
     * @param bool $value
     * @return SelectStatement
     */
    public function distinct(bool $value = \true) : self
    {
        $this->sql->setDistinct($value);
        return $this;
    }
    /**
     * @param   string|Closure|Expression|string[]|Closure[]|Expression[] $columns
     *
     * @return  $this
     */
    public function groupBy($columns) : self
    {
        if (!\is_array($columns)) {
            $columns = [$columns];
        }
        $this->sql->addGroupBy($columns);
        return $this;
    }
    /**
     * @param   string  $column
     * @param   Closure $value (optional)
     *
     * @return  $this
     */
    public function having($column, Closure $value = null) : self
    {
        $this->have->having($column, $value);
        return $this;
    }
    /**
     * @param   string  $column
     * @param   Closure $value (optional)
     *
     * @return  $this
     */
    public function andHaving($column, Closure $value = null) : self
    {
        $this->have->andHaving($column, $value);
        return $this;
    }
    /**
     * @param   string  $column
     * @param   Closure $value (optional)
     *
     * @return  $this
     */
    public function orHaving($column, Closure $value = null) : self
    {
        $this->have->orHaving($column, $value);
        return $this;
    }
    /**
     * @param Closure|Expression|string[]|Closure[]|Expression[] $columns
     * @param string                                             $order
     * @param string|null                                        $nulls
     * @return SelectStatement
     */
    public function orderBy($columns, string $order = 'ASC', string $nulls = null) : self
    {
        if (!\is_array($columns)) {
            $columns = [$columns];
        }
        $this->sql->addOrder($columns, $order, $nulls);
        return $this;
    }
    /**
     * @param int $value
     * @return SelectStatement
     */
    public function limit(int $value) : self
    {
        $this->sql->setLimit($value);
        return $this;
    }
    /**
     * @param int $value
     * @return SelectStatement
     */
    public function offset(int $value) : self
    {
        $this->sql->setOffset($value);
        return $this;
    }
    /**
     * @param string|Closure|Expression|string[]|Closure[]|Expression[] $columns
     */
    public function select($columns = [])
    {
        $expr = new ColumnExpression($this->sql);
        if ($columns instanceof Closure) {
            $columns($expr);
        } else {
            if (!\is_array($columns)) {
                $columns = [$columns];
            }
            $expr->columns($columns);
        }
    }
    /**
     * @param   string|Closure|Expression $name
     */
    public function column($name)
    {
        (new ColumnExpression($this->sql))->column($name);
    }
    /**
     * @param   string|Closure|Expression $column (optional)
     * @param   bool                      $distinct (optional)
     */
    public function count($column = '*', bool $distinct = \false)
    {
        (new ColumnExpression($this->sql))->count($column, null, $distinct);
    }
    /**
     * @param   string|Closure|Expression $column
     * @param   bool                      $distinct (optional)
     */
    public function avg($column, bool $distinct = \false)
    {
        (new ColumnExpression($this->sql))->avg($column, null, $distinct);
    }
    /**
     * @param   string|Closure|Expression $column
     * @param   bool                      $distinct (optional)
     */
    public function sum($column, bool $distinct = \false)
    {
        (new ColumnExpression($this->sql))->sum($column, null, $distinct);
    }
    /**
     * @param   string|Closure|Expression $column
     * @param   bool                      $distinct (optional)
     */
    public function min($column, bool $distinct = \false)
    {
        (new ColumnExpression($this->sql))->min($column, null, $distinct);
    }
    /**
     * @param   string|Closure|Expression $column
     * @param   bool                      $distinct (optional)
     */
    public function max($column, bool $distinct = \false)
    {
        (new ColumnExpression($this->sql))->max($column, null, $distinct);
    }
    /**
     * @inheritDoc
     */
    public function __clone()
    {
        parent::__clone();
        $this->have = new HavingStatement($this->sql);
    }
}
