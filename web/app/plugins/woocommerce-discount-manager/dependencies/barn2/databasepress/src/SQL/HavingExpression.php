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
class HavingExpression
{
    /** @var  SQLStatement */
    protected $sql;
    /** @var    Having */
    protected $having;
    /** @var    string|Expression */
    protected $column;
    /** @var    string */
    protected $separator;
    /**
     * AggregateExpression constructor.
     *
     * @param SQLStatement $statement
     */
    public function __construct(SQLStatement $statement)
    {
        $this->sql = $statement;
        $this->having = new Having($statement);
    }
    /**
     * @param string $column
     * @param string $separator
     * @return HavingExpression
     */
    public function init($column, string $separator) : self
    {
        if ($column instanceof Closure) {
            $column = Expression::fromClosure($column);
        }
        $this->column = $column;
        $this->separator = $separator;
        return $this;
    }
    /**
     * @param bool $distinct
     * @return Having
     */
    public function count(bool $distinct = \false) : Having
    {
        $value = (new Expression())->count($this->column, $distinct);
        return $this->having->init($value, $this->separator);
    }
    /**
     * @param bool $distinct
     * @return Having
     */
    public function avg(bool $distinct = \false) : Having
    {
        $value = (new Expression())->avg($this->column, $distinct);
        return $this->having->init($value, $this->separator);
    }
    /**
     * @param bool $distinct
     * @return Having
     */
    public function sum(bool $distinct = \false) : Having
    {
        $value = (new Expression())->sum($this->column, $distinct);
        return $this->having->init($value, $this->separator);
    }
    /**
     * @param bool $distinct
     * @return Having
     */
    public function min(bool $distinct = \false) : Having
    {
        $value = (new Expression())->min($this->column, $distinct);
        return $this->having->init($value, $this->separator);
    }
    /**
     * @param bool $distinct
     * @return Having
     */
    public function max(bool $distinct = \false) : Having
    {
        $value = (new Expression())->max($this->column, $distinct);
        return $this->having->init($value, $this->separator);
    }
    /**
     * @inheritDoc
     */
    public function __clone()
    {
        if ($this->column instanceof Expression) {
            $this->column = clone $this->column;
        }
        $this->sql = clone $this->sql;
        $this->having = new Having($this->sql);
    }
}
