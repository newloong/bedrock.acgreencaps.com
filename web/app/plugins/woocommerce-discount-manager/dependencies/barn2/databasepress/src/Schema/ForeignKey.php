<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\databasepress
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\Schema;

class ForeignKey
{
    /** @var string */
    protected $refTable;
    /** @var string[] */
    protected $refColumns;
    /** @var array */
    protected $actions = [];
    /** @var string[] */
    protected $columns;
    /**
     * ForeignKey constructor.
     *
     * @param string[] $columns
     */
    public function __construct(array $columns)
    {
        $this->columns = $columns;
    }
    /**
     * @param string $on
     * @param string $action
     * @return $this
     */
    protected function addAction(string $on, string $action) : self
    {
        $action = \strtoupper($action);
        if (!\in_array($action, ['RESTRICT', 'CASCADE', 'NO ACTION', 'SET NULL'])) {
            return $this;
        }
        $this->actions[$on] = $action;
        return $this;
    }
    /**
     * @return string
     */
    public function getReferencedTable() : string
    {
        return $this->refTable;
    }
    /**
     * @return string[]
     */
    public function getReferencedColumns() : array
    {
        return $this->refColumns;
    }
    /**
     * @return string[]
     */
    public function getColumns() : array
    {
        return $this->columns;
    }
    /**
     * @return array
     */
    public function getActions() : array
    {
        return $this->actions;
    }
    /**
     * @param string   $table
     * @param string[] $columns
     * @return $this
     */
    public function references(string $table, string ...$columns) : self
    {
        $this->refTable = $table;
        $this->refColumns = $columns;
        return $this;
    }
    /**
     * @param string $action
     * @return $this
     */
    public function onDelete(string $action) : self
    {
        return $this->addAction('ON DELETE', $action);
    }
    /**
     * @param string $action
     * @return $this
     */
    public function onUpdate(string $action) : self
    {
        return $this->addAction('ON UPDATE', $action);
    }
}
