<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\databasepress
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\Schema;

class CreateColumn extends BaseColumn
{
    /** @var string */
    protected $table;
    /**
     * CreateColumn constructor.
     *
     * @param CreateTable $table
     * @param string      $name
     * @param string      $type
     */
    public function __construct(CreateTable $table, string $name, string $type)
    {
        $this->table = $table;
        parent::__construct($name, $type);
    }
    /**
     * @return string
     */
    public function getTable() : string
    {
        return $this->table;
    }
    /**
     * @param string|null $name
     * @return $this
     */
    public function autoincrement(string $name = null) : self
    {
        $this->table->autoincrement($this, $name);
        return $this;
    }
    /**
     * @param string|null $name
     * @return $this
     */
    public function primary(string $name = null) : self
    {
        $this->table->primary($this->name, $name);
        return $this;
    }
    /**
     * @param string|null $name
     * @return $this
     */
    public function unique(string $name = null) : self
    {
        $this->table->unique($this->name, $name);
        return $this;
    }
    /**
     * @param string|null $name
     * @return $this
     */
    public function index(string $name = null) : self
    {
        $this->table->index($this->name, $name);
        return $this;
    }
}
