<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\DatabasepressORM
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Traits;

use Closure;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\SQL\ColumnExpression;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\SQL\HavingStatement;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\SQL\SQLStatement;
trait SelectTrait
{
    /**
     * @return SQLStatement
     */
    protected abstract function getSQLStatement() : SQLStatement;
    /**
     * @return HavingStatement
     */
    protected abstract function getHavingStatement() : HavingStatement;
    /**
     * @param   string|array|Closure $columns
     */
    public function select($columns = [])
    {
        $expr = new ColumnExpression($this->getSQLStatement());
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
     * @param bool $value
     * @return self|mixed
     */
    public function distinct(bool $value = \true) : self
    {
        $this->getSQLStatement()->setDistinct($value);
        return $this;
    }
    /**
     * @param string|array $columns
     * @return self|mixed
     */
    public function groupBy($columns) : self
    {
        if (!\is_array($columns)) {
            $columns = [$columns];
        }
        $this->getSQLStatement()->addGroupBy($columns);
        return $this;
    }
    /**
     * @param   string  $column
     * @param   Closure $value (optional)
     *
     * @return  self|mixed
     */
    public function having($column, Closure $value = null) : self
    {
        $this->getHavingStatement()->having($column, $value);
        return $this;
    }
    /**
     * @param   string  $column
     * @param   Closure $value
     *
     * @return  self|mixed
     */
    public function andHaving($column, Closure $value = null) : self
    {
        $this->getHavingStatement()->andHaving($column, $value);
        return $this;
    }
    /**
     * @param   string  $column
     * @param   Closure $value
     *
     * @return  self|mixed
     */
    public function orHaving($column, Closure $value = null) : self
    {
        $this->getHavingStatement()->orHaving($column, $value);
        return $this;
    }
    /**
     * @param   string|array $columns
     * @param   string       $order (optional)
     * @param   string       $nulls (optional)
     *
     * @return  self|mixed
     */
    public function orderBy($columns, string $order = 'ASC', string $nulls = null) : self
    {
        if (!\is_array($columns)) {
            $columns = [$columns];
        }
        $this->getSQLStatement()->addOrder($columns, $order, $nulls);
        return $this;
    }
    /**
     * @param   int $value
     *
     * @return  self|mixed
     */
    public function limit(int $value) : self
    {
        $this->getSQLStatement()->setLimit($value);
        return $this;
    }
    /**
     * @param   int $value
     *
     * @return  self|mixed
     */
    public function offset(int $value) : self
    {
        $this->getSQLStatement()->setOffset($value);
        return $this;
    }
}
