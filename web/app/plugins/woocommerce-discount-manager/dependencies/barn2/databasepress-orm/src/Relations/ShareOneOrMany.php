<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\DatabasepressORM
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Relations;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Entity;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\EntityManager;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\SQL\Delete;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\SQL\Insert;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\SQL\Join;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\SQL\SQLStatement;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core\DataMapper;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core\EntityMapper;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core\EntityQuery;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core\ForeignKey;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core\Junction;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core\LazyLoader;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core\Proxy;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core\Query;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core\Relation;
class ShareOneOrMany extends Relation
{
    /** @var null|Junction */
    protected $junction;
    /** @var  bool */
    protected $hasMany;
    public function __construct(string $entityClass, ForeignKey $foreignKey = null, Junction $junction = null, bool $hasMany = \false)
    {
        parent::__construct($entityClass, $foreignKey);
        $this->hasMany = $hasMany;
        $this->junction = $junction;
    }
    /**
     * @param DataMapper $data
     * @param Entity     $entity
     * @return bool
     */
    public function link(DataMapper $data, Entity $entity) : bool
    {
        $manager = $data->getEntityManager();
        $owner = $data->getEntityMapper();
        $related = $manager->resolveEntityMapper($this->entityClass);
        if ($this->junction === null) {
            $this->junction = $this->buildJunction($owner, $related);
        }
        if ($this->foreignKey === null) {
            $this->foreignKey = $owner->getForeignKey();
        }
        $values = [];
        foreach ($this->foreignKey->getValue($data->getRawColumns(), \true) as $fk_column => $fk_value) {
            $values[$fk_column] = $fk_value;
        }
        $columns = Proxy::instance()->getDataMapper($entity)->getRawColumns();
        foreach ($this->junction->columns() as $pk_column => $fk_column) {
            $values[$fk_column] = $columns[$pk_column];
        }
        $cmd = new Insert($manager->getConnection());
        $cmd->insert($values);
        return (bool) $cmd->into($this->junction->table());
    }
    /**
     * @param DataMapper $data
     * @param Entity     $entity
     * @return bool
     */
    public function unlink(DataMapper $data, Entity $entity) : bool
    {
        $manager = $data->getEntityManager();
        $owner = $data->getEntityMapper();
        $related = $manager->resolveEntityMapper($this->entityClass);
        if ($this->junction === null) {
            $this->junction = $this->buildJunction($owner, $related);
        }
        if ($this->foreignKey === null) {
            $this->foreignKey = $owner->getForeignKey();
        }
        $values = [];
        foreach ($this->foreignKey->getValue($data->getRawColumns(), \true) as $fk_column => $fk_value) {
            $values[$fk_column] = $fk_value;
        }
        $columns = Proxy::instance()->getDataMapper($entity)->getRawColumns();
        foreach ($this->junction->columns() as $pk_column => $fk_column) {
            $values[$fk_column] = $columns[$pk_column];
        }
        $cmd = new Delete($manager->getConnection(), $this->junction->table());
        foreach ($values as $column => $value) {
            $cmd->where($column)->is($value);
        }
        return (bool) $cmd->delete();
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
        if ($this->junction === null) {
            $this->junction = $this->buildJunction($owner, $related);
        }
        if ($this->foreignKey === null) {
            $this->foreignKey = $owner->getForeignKey();
        }
        $junctionTable = $this->junction->table();
        $joinTable = $related->getTable();
        $ids = [];
        foreach ($options['results'] as $result) {
            $result = \is_object($result) ? \get_object_vars($result) : $result;
            foreach ($owner->getPrimaryKey()->getValue($result, \true) as $pk_col => $pk_val) {
                $ids[$pk_col][] = $pk_val;
            }
        }
        $statement = new SQLStatement();
        $select = new class($manager, $related, $statement, $junctionTable) extends EntityQuery
        {
            protected $junctionTable;
            public function __construct(EntityManager $entityManager, EntityMapper $entityMapper, $statement, $table)
            {
                parent::__construct($entityManager, $entityMapper, $statement);
                $this->junctionTable = $table;
            }
            protected function buildQuery() : EntityQuery
            {
                $this->locked = \true;
                $this->sql->addTables([$this->junctionTable]);
                return $this;
            }
            protected function isReadOnly() : bool
            {
                return \count($this->sql->getJoins()) > 1;
            }
        };
        $linkKey = new ForeignKey(\array_map(function ($value) use($junctionTable) {
            return 'hidden_' . $junctionTable . '_' . $value;
        }, $this->foreignKey->columns()));
        $select->join($joinTable, function (Join $join) use($junctionTable, $joinTable) {
            foreach ($this->junction->columns() as $pk_column => $fk_column) {
                $join->on($junctionTable . '.' . $fk_column, $joinTable . '.' . $pk_column);
            }
        });
        foreach ($this->foreignKey->getValue($ids, \true) as $fk_col => $fk_val) {
            $select->where($junctionTable . '.' . $fk_col)->in($fk_val);
        }
        $statement->addColumn($joinTable . '.*');
        $linkKeyCols = $linkKey->columns();
        foreach ($this->foreignKey->columns() as $pk_col => $fk_col) {
            $statement->addColumn($junctionTable . '.' . $fk_col, $linkKeyCols[$pk_col]);
        }
        if ($options['callback'] !== null) {
            $options['callback'](new Query($statement));
        }
        $select->with($options['with'], $options['immediate']);
        return new LazyLoader($select, $linkKey, \false, $this->hasMany, $options['immediate']);
    }
    public function getResult(DataMapper $data, callable $callback = null)
    {
        $manager = $data->getEntityManager();
        $owner = $data->getEntityMapper();
        $related = $manager->resolveEntityMapper($this->entityClass);
        if ($this->junction === null) {
            $this->junction = $this->buildJunction($owner, $related);
        }
        if ($this->foreignKey === null) {
            $this->foreignKey = $owner->getForeignKey();
        }
        $junctionTable = $this->junction->table();
        $joinTable = $related->getTable();
        $statement = new SQLStatement();
        $select = new class($manager, $related, $statement, $junctionTable) extends EntityQuery
        {
            protected $junctionTable;
            public function __construct(EntityManager $entityManager, EntityMapper $entityMapper, $statement, $table)
            {
                parent::__construct($entityManager, $entityMapper, $statement);
                $this->junctionTable = $table;
            }
            protected function buildQuery() : EntityQuery
            {
                $this->locked = \true;
                $this->sql->addTables([$this->junctionTable]);
                return $this;
            }
            protected function isReadOnly() : bool
            {
                return \count($this->sql->getJoins()) > 1;
            }
        };
        $select->join($joinTable, function (Join $join) use($junctionTable, $joinTable) {
            foreach ($this->junction->columns() as $pk_column => $fk_column) {
                $join->on($junctionTable . '.' . $fk_column, $joinTable . '.' . $pk_column);
            }
        });
        foreach ($this->foreignKey->getValue($data->getRawColumns(), \true) as $fk_column => $value) {
            $select->where($junctionTable . '.' . $fk_column)->is($value);
        }
        $statement->addColumn($joinTable . '.*');
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
    /**
     * @param EntityMapper $owner
     * @param EntityMapper $related
     * @return Junction
     */
    protected function buildJunction(EntityMapper $owner, EntityMapper $related) : Junction
    {
        return new class($owner, $related) extends Junction
        {
            public function __construct(EntityMapper $owner, EntityMapper $related)
            {
                $table = [$owner->getTable(), $related->getTable()];
                \sort($table);
                parent::__construct(\implode('_', $table), $related->getForeignKey()->columns());
            }
        };
    }
}
