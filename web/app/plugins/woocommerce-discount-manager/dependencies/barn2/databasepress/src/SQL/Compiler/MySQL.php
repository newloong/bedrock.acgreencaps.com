<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\databasepress
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\SQL\Compiler;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\SQL\Compiler;
class MySQL extends Compiler
{
    /** @var string Wrapper used to escape table and column names. */
    protected $wrapper = '`%s`';
    /**
     * @param   array $func
     *
     * @return  string
     */
    protected function sqlFunctionROUND(array $func)
    {
        return 'FORMAT(' . $this->wrap($func['column']) . ', ' . $this->param($func['decimals']) . ')';
    }
    /**
     * @param   array $func
     *
     * @return  string
     */
    protected function sqlFunctionLEN(array $func)
    {
        return 'LENGTH(' . $this->wrap($func['column']) . ')';
    }
}
