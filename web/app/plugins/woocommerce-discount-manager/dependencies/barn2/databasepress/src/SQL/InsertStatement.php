<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\databasepress
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\SQL;

class InsertStatement
{
    /** @var  SQLStatement */
    protected $sql;
    /**
     * InsertStatement constructor.
     *
     * @param SQLStatement|null $statement
     */
    public function __construct(SQLStatement $statement = null)
    {
        if ($statement === null) {
            $statement = new SQLStatement();
        }
        $this->sql = $statement;
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
     * @param array $values
     * @return InsertStatement
     */
    public function insert(array $values) : self
    {
        foreach ($values as $column => $value) {
            $this->sql->addColumn($column);
            $this->sql->addValue($value);
        }
        return $this;
    }
    /**
     * @param   string $table
     */
    public function into(string $table)
    {
        $this->sql->addTables([$table]);
    }
    /**
     * @inheritDoc
     */
    public function __clone()
    {
        $this->sql = clone $this->sql;
    }
}
