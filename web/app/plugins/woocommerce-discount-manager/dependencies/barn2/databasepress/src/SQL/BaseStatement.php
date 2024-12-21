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
class BaseStatement extends WhereStatement
{
    /**
     * @param   string|string[] $table
     * @param   Closure         $closure
     *
     * @return  Delete|Select|BaseStatement
     */
    public function join($table, Closure $closure)
    {
        $this->sql->addJoinClause('INNER', $table, $closure);
        return $this;
    }
    /**
     * @param   string|string[] $table
     * @param   Closure         $closure
     *
     * @return  Delete|Select|BaseStatement
     */
    public function leftJoin($table, Closure $closure)
    {
        $this->sql->addJoinClause('LEFT', $table, $closure);
        return $this;
    }
    /**
     * @param   string|string[] $table
     * @param   Closure         $closure
     *
     * @return  Delete|Select|BaseStatement
     */
    public function rightJoin($table, Closure $closure)
    {
        $this->sql->addJoinClause('RIGHT', $table, $closure);
        return $this;
    }
    /**
     * @param   string|string[] $table
     * @param   Closure         $closure
     *
     * @return  Delete|Select|BaseStatement
     */
    public function fullJoin($table, Closure $closure)
    {
        $this->sql->addJoinClause('FULL', $table, $closure);
        return $this;
    }
    /**
     * @param   string|string[] $table
     *
     * @return  Delete|Select|BaseStatement
     */
    public function crossJoin($table)
    {
        $this->sql->addJoinClause('CROSS', $table, null);
        return $this;
    }
}
