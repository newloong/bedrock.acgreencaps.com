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
class HavingStatement
{
    /** @var    SQLStatement */
    protected $sql;
    /** @var    HavingExpression */
    protected $expression;
    /**
     * HavingStatement constructor.
     *
     * @param SQLStatement|null $statement
     */
    public function __construct(SQLStatement $statement = null)
    {
        if ($statement === null) {
            $statement = new SQLStatement();
        }
        $this->sql = $statement;
        $this->expression = new HavingExpression($statement);
    }
    /**
     * @param   string|Expression|Closure $column
     * @param   Closure                   $value
     * @param   string                    $separator
     *
     * @return  $this
     */
    protected function addCondition($column, Closure $value = null, $separator = 'AND') : self
    {
        if ($column instanceof Closure && $value === null) {
            $this->sql->addHavingGroupCondition($column, $separator);
        } else {
            $expr = $this->expression->init($column, $separator);
            if ($value) {
                $value($expr);
            }
        }
        return $this;
    }
    /**
     * @internal
     * @return SQLStatement
     */
    public function getSQLStatement() : SQLStatement
    {
        return $this->sql;
    }
    /**
     * @param   string|Expression|Closure $column
     * @param   Closure                   $value (optional)
     *
     * @return  $this
     */
    public function having($column, Closure $value = null) : self
    {
        return $this->addCondition($column, $value, 'AND');
    }
    /**
     * @param   string|Expression $column
     * @param   Closure           $value (optional)
     *
     * @return  $this
     */
    public function andHaving($column, Closure $value = null) : self
    {
        return $this->addCondition($column, $value, 'AND');
    }
    /**
     * @param   string|Expression $column
     * @param   Closure           $value (optional)
     *
     * @return  $this
     */
    public function orHaving($column, Closure $value = null) : self
    {
        return $this->addCondition($column, $value, 'OR');
    }
    /**
     * @inheritDoc
     */
    public function __clone()
    {
        $this->sql = clone $this->sql;
        $this->expression = new HavingExpression($this->sql);
    }
}
