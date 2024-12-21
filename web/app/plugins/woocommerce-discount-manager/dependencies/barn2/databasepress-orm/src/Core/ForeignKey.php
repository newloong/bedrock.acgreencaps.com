<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\DatabasepressORM
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core;

class ForeignKey
{
    /** @var bool */
    private $composite;
    /** @var null|string[] */
    private $columns;
    /**
     * ForeignKey constructor.
     *
     * @param string[]        $columns
     * @param null|PrimaryKey $primaryKey
     * @param null|string     $prefix
     */
    public function __construct(array $columns = [], $primaryKey = null, $prefix = null)
    {
        $this->columns = $columns;
        if ($primaryKey instanceof PrimaryKey) {
            $columns = [];
            foreach ($primaryKey->columns() as $column) {
                $columns[$column] = $prefix . '_' . $column;
            }
            $this->columns = $columns;
        }
        $this->composite = \count($columns) > 1;
    }
    /**
     * @return bool
     */
    public function isComposite() : bool
    {
        return $this->composite;
    }
    /**
     * @return string[]
     */
    public function columns() : array
    {
        return $this->columns;
    }
    /**
     * @param array $columns
     * @param bool  $map
     * @return array|mixed|null
     */
    public function getValue(array $columns, bool $map = \false)
    {
        if (!$map && !$this->composite) {
            return $columns[\array_keys($this->columns)[0]] ?? null;
        }
        $value = [];
        foreach ($this->columns as $candidate => $column) {
            $value[$column] = $columns[$candidate] ?? null;
        }
        return $value;
    }
    /**
     * @param array $columns
     * @param bool  $map
     * @return array|mixed|null
     */
    public function getInverseValue(array $columns, bool $map = \false)
    {
        if (!$map && !$this->composite) {
            return $columns[\array_values($this->columns)[0]] ?? null;
        }
        $value = [];
        foreach ($this->columns as $candidate => $column) {
            $value[$candidate] = $columns[$column] ?? null;
        }
        return $value;
    }
    /**
     * @param array $columns
     * @param bool  $map
     * @return array|mixed|null
     */
    public function extractValue(array $columns, bool $map = \false)
    {
        if (!$map && !$this->composite) {
            return $columns[\array_values($this->columns)[0]] ?? null;
        }
        $value = [];
        foreach ($this->columns as $column) {
            $value[$column] = $columns[$column];
        }
        return $value;
    }
    /**
     * @return string
     */
    public function __toString()
    {
        return \implode(', ', $this->columns);
    }
}
