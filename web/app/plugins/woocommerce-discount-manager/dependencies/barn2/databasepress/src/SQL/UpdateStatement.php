<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\databasepress
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\SQL;

class UpdateStatement extends BaseStatement
{
    /**
     * UpdateStatement constructor.
     *
     * @param string|array      $table
     * @param SQLStatement|null $statement
     */
    public function __construct($table, SQLStatement $statement = null)
    {
        if (!\is_array($table)) {
            $table = [$table];
        }
        parent::__construct($statement);
        $this->sql->addTables($table);
    }
    /**
     * @param   array $columns
     */
    public function set(array $columns)
    {
        $this->sql->addUpdateColumns($columns);
    }
}
