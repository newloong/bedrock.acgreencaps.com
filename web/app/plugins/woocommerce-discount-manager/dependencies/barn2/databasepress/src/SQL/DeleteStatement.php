<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\databasepress
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\SQL;

class DeleteStatement extends BaseStatement
{
    /**
     * DeleteStatement constructor.
     *
     * @param string|array      $from
     * @param SQLStatement|null $statement
     */
    public function __construct($from, SQLStatement $statement = null)
    {
        parent::__construct($statement);
        if (!\is_array($from)) {
            $from = [$from];
        }
        $this->sql->setFrom($from);
    }
    /**
     * Delete records
     *
     * @param   string|array $tables
     */
    public function delete($tables = [])
    {
        if (!\is_array($tables)) {
            $tables = [$tables];
        }
        $this->sql->addTables($tables);
    }
}
