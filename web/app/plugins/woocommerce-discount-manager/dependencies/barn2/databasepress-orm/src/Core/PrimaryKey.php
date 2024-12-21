<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\DatabasepressORM
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Entity;
class PrimaryKey
{
    /** @var string */
    private $columns;
    /** @var bool */
    private $composite;
    /**
     * PrimaryKey constructor.
     *
     * @param string ...$columns
     */
    public function __construct(string ...$columns)
    {
        $this->columns = $columns;
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
        if (!$this->composite && !$map) {
            return $columns[$this->columns[0]] ?? null;
        }
        $value = [];
        foreach ($this->columns as $column) {
            $value[$column] = $columns[$column] ?? null;
        }
        return $value;
    }
    /**
     * @param DataMapper $data
     * @param bool       $map
     * @return array|mixed|null
     */
    public function valueFromDataMapper(DataMapper $data, bool $map = \false)
    {
        return $this->getValue($data->getRawColumns(), $map);
    }
    /**
     * @param Entity $entity
     * @param bool   $map
     * @return array|mixed|null
     */
    public function valueFromEntity(Entity $entity, bool $map = \false)
    {
        return $this->getValue(Proxy::instance()->getEntityColumns($entity), $map);
    }
    /**
     * @return string
     */
    public function __toString()
    {
        return \implode(', ', $this->columns);
    }
}
