<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\DatabasepressORM
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core;

class Junction
{
    /** @var string */
    private $table;
    /** @var string[] */
    private $columns;
    /**
     * Junction constructor.
     *
     * @param string $table
     * @param array  $columns
     */
    public function __construct(string $table, array $columns)
    {
        $this->table = $table;
        $this->columns = $columns;
    }
    /**
     * @return string
     */
    public function table() : string
    {
        return $this->table;
    }
    /**
     * @return array
     */
    public function columns() : array
    {
        return $this->columns;
    }
}
