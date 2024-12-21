<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\DatabasepressORM
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core;

class LazyLoader
{
    /** @var EntityQuery */
    protected $query;
    /** @var bool */
    protected $inverse;
    /** @var ForeignKey */
    protected $foreignKey;
    /** @var bool */
    protected $hasMany;
    /** @var null|Entity[] */
    protected $results;
    /** @var null|array */
    protected $keys;
    /**
     * LazyLoader constructor.
     *
     * @param EntityQuery $query
     * @param ForeignKey  $foreignKey
     * @param bool        $inverse
     * @param bool        $hasMany
     * @param bool        $immediate
     */
    public function __construct(EntityQuery $query, ForeignKey $foreignKey, bool $inverse, bool $hasMany, bool $immediate)
    {
        $this->query = $query;
        $this->foreignKey = $foreignKey;
        $this->inverse = $inverse;
        $this->hasMany = $hasMany;
        if ($immediate) {
            $this->loadResults();
        }
    }
    /**
     * @param DataMapper $data
     * @return null|Entity|Entity[]
     */
    public function getResult(DataMapper $data)
    {
        $results = $this->loadResults();
        if ($this->inverse) {
            $check = $this->foreignKey->extractValue($data->getRawColumns(), \true);
        } else {
            $check = $this->foreignKey->getValue($data->getRawColumns(), \true);
        }
        if ($this->hasMany) {
            $all = [];
            foreach ($this->keys as $index => $item) {
                if ($item === $check) {
                    $all[] = $results[$index];
                }
            }
            return $all;
        }
        foreach ($this->keys as $index => $item) {
            if ($item === $check) {
                return $results[$index];
            }
        }
        return null;
    }
    /**
     * @return Entity[]
     */
    protected function loadResults()
    {
        if ($this->results === null) {
            $this->results = $this->query->all();
            $this->keys = [];
            $proxy = Proxy::instance();
            foreach ($this->results as $result) {
                if ($this->inverse) {
                    $this->keys[] = $this->foreignKey->getValue($proxy->getEntityColumns($result), \true);
                } else {
                    $this->keys[] = $this->foreignKey->extractValue($proxy->getEntityColumns($result), \true);
                }
            }
        }
        return $this->results;
    }
}
