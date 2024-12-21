<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\databasepress
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\SQL;

class Subquery
{
    /** @var    SelectStatement */
    protected $select;
    /**
     * @param   string|array $tables
     *
     * @return  SelectStatement
     */
    public function from($tables)
    {
        return $this->select = new SelectStatement($tables);
    }
    /**
     * @internal
     * @return SQLStatement
     */
    public function getSQLStatement() : SQLStatement
    {
        return $this->select->getSQLStatement();
    }
    /**
     * @inheritDoc
     */
    public function __clone()
    {
        $this->select = clone $this->select;
    }
}
