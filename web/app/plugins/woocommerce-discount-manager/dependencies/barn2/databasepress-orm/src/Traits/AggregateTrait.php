<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\DatabasepressORM
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Traits;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\SQL\ColumnExpression;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\SQL\SQLStatement;
trait AggregateTrait
{
    /**
     * @return SQLStatement
     */
    protected abstract function getSQLStatement() : SQLStatement;
    /**
     * @return mixed
     */
    protected abstract function executeAggregate();
    /**
     * @param   string $name
     * @return mixed
     */
    public function column(string $name)
    {
        (new ColumnExpression($this->getSQLStatement()))->column($name);
        return $this->executeAggregate();
    }
    /**
     * @param   string $column (optional)
     * @param   bool   $distinct (optional)
     * @return mixed
     */
    public function count($column = '*', bool $distinct = \false)
    {
        (new ColumnExpression($this->getSQLStatement()))->count($column, null, $distinct);
        return $this->executeAggregate();
    }
    /**
     * @param   string $column
     * @param   bool   $distinct (optional)
     * @return mixed
     */
    public function avg(string $column, bool $distinct = \false)
    {
        (new ColumnExpression($this->getSQLStatement()))->avg($column, null, $distinct);
        return $this->executeAggregate();
    }
    /**
     * @param   string $column
     * @param   bool   $distinct (optional)
     * @return mixed
     */
    public function sum(string $column, bool $distinct = \false)
    {
        (new ColumnExpression($this->getSQLStatement()))->sum($column, null, $distinct);
        return $this->executeAggregate();
    }
    /**
     * @param   string $column
     * @param   bool   $distinct (optional)
     * @return mixed
     */
    public function min(string $column, bool $distinct = \false)
    {
        (new ColumnExpression($this->getSQLStatement()))->min($column, null, $distinct);
        return $this->executeAggregate();
    }
    /**
     * @param   string $column
     * @param   bool   $distinct (optional)
     * @return mixed
     */
    public function max(string $column, bool $distinct = \false)
    {
        (new ColumnExpression($this->getSQLStatement()))->max($column, null, $distinct);
        return $this->executeAggregate();
    }
}
