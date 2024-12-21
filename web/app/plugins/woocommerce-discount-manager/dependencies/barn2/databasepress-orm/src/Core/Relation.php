<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\DatabasepressORM
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\EntityManager;
abstract class Relation
{
    protected $queryCallback;
    protected $entityClass;
    protected $foreignKey;
    /**
     * EntityRelation constructor.
     *
     * @param string          $entityClass
     * @param ForeignKey|null $foreignKey
     */
    public function __construct(string $entityClass, ForeignKey $foreignKey = null)
    {
        $this->entityClass = $entityClass;
        $this->foreignKey = $foreignKey;
    }
    /**
     * @param callable $callback
     * @return Relation
     */
    public function query(callable $callback) : self
    {
        $this->queryCallback = $callback;
        return $this;
    }
    /**
     * @param EntityManager $manager
     * @param EntityMapper  $owner
     * @param array         $options
     * @return mixed
     */
    public abstract function getLazyLoader(EntityManager $manager, EntityMapper $owner, array $options);
    /**
     * @param DataMapper    $data
     * @param callable|null $callback
     * @return mixed
     */
    public abstract function getResult(DataMapper $data, callable $callback = null);
}
