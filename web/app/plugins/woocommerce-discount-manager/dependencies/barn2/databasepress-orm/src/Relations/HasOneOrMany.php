<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\DatabasepressORM
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Relations;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\SQL\SQLStatement;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Entity;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\EntityManager;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core\DataMapper;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core\EntityMapper;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core\EntityQuery;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core\ForeignKey;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core\LazyLoader;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core\Proxy;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core\Relation;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core\Query;
class HasOneOrMany extends Relation
{
    /** @var bool */
    protected $hasMany;
    /**
     * EntityHasOneOrMany constructor.
     *
     * @param string          $entityClass
     * @param ForeignKey|null $foreignKey
     * @param bool            $hasMany
     */
    public function __construct(string $entityClass, ForeignKey $foreignKey = null, bool $hasMany = \false)
    {
        parent::__construct($entityClass, $foreignKey);
        $this->hasMany = $hasMany;
    }
    /**
     * @param DataMapper $owner
     * @param Entity     $entity
     */
    public function addRelatedEntity(DataMapper $owner, Entity $entity)
    {
        $mapper = $owner->getEntityMapper();
        if ($this->foreignKey === null) {
            $this->foreignKey = $mapper->getForeignKey();
        }
        $related = Proxy::instance()->getDataMapper($entity);
        foreach ($this->foreignKey->getValue($owner->getRawColumns(), \true) as $fk_column => $fk_value) {
            $related->setColumn($fk_column, $fk_value);
        }
    }
    /**
     * @param EntityManager $manager
     * @param EntityMapper  $owner
     * @param array         $options
     * @return LazyLoader
     */
    public function getLazyLoader(EntityManager $manager, EntityMapper $owner, array $options)
    {
        $related = $manager->resolveEntityMapper($this->entityClass);
        if ($this->foreignKey === null) {
            $this->foreignKey = $owner->getForeignKey();
        }
        $ids = [];
        $pk = $owner->getPrimaryKey();
        foreach ($options['results'] as $result) {
            $result = \is_object($result) ? \get_object_vars($result) : $result;
            foreach ($pk->getValue($result, \true) as $pk_col => $pk_val) {
                $ids[$pk_col][] = $pk_val;
            }
        }
        $statement = new SQLStatement();
        $select = new EntityQuery($manager, $related, $statement);
        foreach ($this->foreignKey->getValue($ids, \true) as $fk_col => $fk_val) {
            $select->where($fk_col)->in($fk_val);
        }
        if ($options['callback'] !== null) {
            $options['callback'](new Query($statement));
        }
        $select->with($options['with'], $options['immediate']);
        return new LazyLoader($select, $this->foreignKey, \false, $this->hasMany, $options['immediate']);
    }
    /**
     * @param DataMapper    $data
     * @param callable|null $callback
     * @return mixed
     */
    public function getResult(DataMapper $data, callable $callback = null)
    {
        $manager = $data->getEntityManager();
        $owner = $data->getEntityMapper();
        $related = $manager->resolveEntityMapper($this->entityClass);
        if ($this->foreignKey === null) {
            $this->foreignKey = $owner->getForeignKey();
        }
        $statement = new SQLStatement();
        $select = new EntityQuery($manager, $related, $statement);
        foreach ($this->foreignKey->getValue($data->getRawColumns(), \true) as $fk_column => $fk_value) {
            $select->where($fk_column)->is($fk_value);
        }
        if ($this->queryCallback !== null || $callback !== null) {
            $query = $select;
            // new Query($statement);
            if ($this->queryCallback !== null) {
                ($this->queryCallback)($query);
            }
            if ($callback !== null) {
                $callback($query);
            }
        }
        return $this->hasMany ? $select->all() : $select->get();
    }
}
