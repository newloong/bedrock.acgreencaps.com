<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\databasepress
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\Schema;

class AlterColumn extends BaseColumn
{
    /** @var string */
    protected $table;
    /**
     * AlterColumn constructor.
     *
     * @param AlterTable  $table
     * @param string      $name
     * @param string|null $type
     */
    public function __construct(AlterTable $table, string $name, string $type = null)
    {
        $this->table = $table;
        parent::__construct($name, $type);
    }
    /**
     * @return  string
     */
    public function getTable() : string
    {
        return $this->table;
    }
    /**
     * @inheritdoc
     */
    public function defaultValue($value) : BaseColumn
    {
        if ($this->get('handleDefault', \true)) {
            return parent::defaultValue($value);
        }
        return $this;
    }
    /**
     * @return $this
     */
    public function autoincrement() : self
    {
        return $this->set('autoincrement', \true);
    }
}
