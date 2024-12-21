<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\databasepress
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress;

class Record
{
    use WPDBHelpers;
    /**
     * SQL Statement.
     *
     * @var string
     */
    protected $statement;
    /**
     * Initialize a record.
     *
     * @param string $statement
     */
    public function __construct(string $statement)
    {
        $this->statement = $statement;
    }
    /**
     * Get all results.
     *
     * @return array
     */
    public function all()
    {
        return $this->getWPDBResults($this->statement);
    }
    /**
     * Get the 1st result.
     *
     * @return self
     */
    public function first()
    {
        return $this->getWPDBRow($this->statement);
    }
    /**
     * Get a specific column.
     *
     * @param integer $col
     * @return mixed
     */
    public function column($col = 0)
    {
        return $this->getWPDBColumn($this->statement, $col);
    }
}
