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
class Join
{
    /** @var    array */
    protected $conditions = [];
    /**
     * @param Closure|Expression $expression
     * @param string             $separator
     *
     * @return $this
     */
    protected function addJoinExpression($expression, string $separator = 'AND')
    {
        if ($expression instanceof Closure) {
            $expression = Expression::fromClosure($expression);
        }
        $this->conditions[] = ['type' => 'joinExpression', 'expression' => $expression, 'separator' => $separator];
        return $this;
    }
    /**
     * @param   string $column1
     * @param   string $column2
     * @param   string $operator
     * @param   string $separator
     *
     * @return $this
     */
    protected function addJoinCondition($column1, $column2, $operator, string $separator = 'AND')
    {
        if ($column1 instanceof Closure) {
            if ($column2 === \true) {
                return $this->addJoinExpression($column1, $separator);
            }
            if ($column2 === null) {
                $join = new Join();
                $column1($join);
                $this->conditions[] = ['type' => 'joinNested', 'join' => $join, 'separator' => $separator];
                return $this;
            }
            $column1 = Expression::fromClosure($column1);
        } elseif ($column1 instanceof Expression && $column2 === \true) {
            return $this->addJoinExpression($column1, $separator);
        }
        if ($column2 instanceof Closure) {
            $column2 = Expression::fromClosure($column2);
        }
        $this->conditions[] = ['type' => 'joinColumn', 'column1' => $column1, 'column2' => $column2, 'operator' => $operator, 'separator' => $separator];
        return $this;
    }
    /**
     * @return  array
     */
    public function getJoinConditions()
    {
        return $this->conditions;
    }
    /**
     * @param   string|Closure $column1
     * @param   string|Closure $column2 (optional)
     * @param   string         $operator (optional)
     *
     * @return  $this
     */
    public function on($column1, $column2 = null, $operator = '=')
    {
        return $this->addJoinCondition($column1, $column2, $operator);
    }
    /**
     * @param   string $column1
     * @param   string $column2 (optional)
     * @param   string $operator (optional)
     *
     * @return  $this
     */
    public function andOn($column1, $column2 = null, $operator = '=')
    {
        return $this->addJoinCondition($column1, $column2, $operator, 'AND');
    }
    /**
     * @param   string $column1
     * @param   string $column2 (optional)
     * @param   string $operator (optional)
     *
     * @return  $this
     */
    public function orOn($column1, $column2 = null, $operator = '=')
    {
        return $this->addJoinCondition($column1, $column2, $operator, 'OR');
    }
}
