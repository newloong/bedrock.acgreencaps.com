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
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core\LazyLoader;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core\Proxy;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core\Relation;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core\Query;
class BelongsTo extends Relation
{
    /**
     * @param DataMapper  $owner
     * @param Entity|null $entity
     */
    public function addRelatedEntity(DataMapper $owner, Entity $entity = null)
    {
        if ($entity === null) {
            $columns = [];
            $mapper = $owner->getEntityManager()->resolveEntityMapper($this->entityClass);
        } else {
            $related = Proxy::instance()->getDataMapper($entity);
            $mapper = $related->getEntityMapper();
            $columns = $related->getRawColumns();
        }
        if ($this->foreignKey === null) {
            $this->foreignKey = $mapper->getForeignKey();
        }
        foreach ($this->foreignKey->getValue($columns, \true) as $fk_column => $fk_value) {
            $owner->setColumn($fk_column, $fk_value);
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
            $this->foreignKey = $related->getForeignKey();
        }
        $ids = [];
        foreach ($options['results'] as $result) {
            $result = \is_object($result) ? \get_object_vars($result) : $result;
            foreach ($this->foreignKey->getInverseValue($result, \true) as $pk_col => $pk_val) {
                $ids[$pk_col][] = $pk_val;
            }
        }
        $statement = new SQLStatement();
        $select = new EntityQuery($manager, $related, $statement);
        foreach ($ids as $col => $val) {
            $val = \array_unique($val);
            if (\count($val) > 1) {
                $select->where($col)->in($val);
            } else {
                $select->where($col)->is(\reset($val));
            }
        }
        if ($options['callback'] !== null) {
            $options['callback'](new Query($statement));
        }
        $select->with($options['with'], $options['immediate']);
        return new LazyLoader($select, $this->foreignKey, \true, \false, $options['immediate']);
    }
    /**
     * @param DataMapper    $data
     * @param callable|null $callback
     * @return mixed
     */
    public function getResult(DataMapper $data, callable $callback = null)
    {
        $manager = $data->getEntityManager();
        $related = $manager->resolveEntityMapper($this->entityClass);
        if ($this->foreignKey === null) {
            $this->foreignKey = $related->getForeignKey();
        }
        $statement = new SQLStatement();
        $select = new EntityQuery($manager, $related, $statement);
        foreach ($this->foreignKey->getInverseValue($data->getRawColumns(), \true) as $pk_column => $pk_value) {
            $select->where($pk_column)->is($pk_value);
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
        return $select->get();
    }
}
