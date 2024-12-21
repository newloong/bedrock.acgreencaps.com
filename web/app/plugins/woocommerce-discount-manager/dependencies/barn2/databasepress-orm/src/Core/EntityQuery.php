<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\DatabasepressORM
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\Database;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\SQL\Delete;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\SQL\SQLStatement;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\SQL\Update;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\EntityManager;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Traits\AggregateTrait;
class EntityQuery extends Query
{
    use AggregateTrait;
    /** @var EntityManager */
    protected $manager;
    /** @var EntityMapper */
    protected $mapper;
    /** @var bool */
    protected $locked = \false;
    /**
     * EntityQuery constructor.
     *
     * @param EntityManager     $entityManager
     * @param EntityMapper      $entityMapper
     * @param SQLStatement|null $statement
     */
    public function __construct(EntityManager $entityManager, EntityMapper $entityMapper, SQLStatement $statement = null)
    {
        parent::__construct($statement);
        $this->mapper = $entityMapper;
        $this->manager = $entityManager;
    }
    /**
     * @param string|string[]|mixed[] $names
     * @return EntityQuery
     */
    public function filter($names) : self
    {
        if (!\is_array($names)) {
            $names = [$names];
        }
        $query = new Query($this->sql);
        $filters = $this->mapper->getFilters();
        foreach ($names as $name => $data) {
            if (\is_int($name)) {
                $name = $data;
                $data = null;
            }
            if (isset($filters[$name])) {
                $filter = $filters[$name];
                $filter->filter($query, $data);
            }
        }
        return $this;
    }
    /**
     * @param array $columns
     * @return null
     */
    public function get(array $columns = [])
    {
        $result = $this->query($columns)->first();
        if ($result === \false || $result === null) {
            return null;
        }
        $class = $this->mapper->getClass();
        return new $class($this->manager, $this->mapper, \get_object_vars($result), [], $this->isReadOnly(), \false);
    }
    /**
     * @param array $columns
     * @return array
     */
    public function all(array $columns = []) : array
    {
        $results = $this->query($columns)->all();
        $entities = [];
        $class = $this->mapper->getClass();
        $isReadOnly = $this->isReadOnly();
        $loaders = $this->getLazyLoaders($results);
        foreach ($results as $result) {
            $entities[] = new $class($this->manager, $this->mapper, \get_object_vars($result), $loaders, $isReadOnly, \false);
        }
        return $entities;
    }
    /**
     * @param bool  $force
     * @param array $tables
     * @return int
     * @throws \Exception
     */
    public function delete(bool $force = \false, array $tables = [])
    {
        return $this->transaction(function (Database $connection) use($tables, $force) {
            if (!$force && $this->mapper->supportsSoftDelete()) {
                return (new Update($connection, $this->mapper->getTable(), $this->sql))->set([$this->mapper->getSoftDeleteColumn() => \date($this->manager->getDateFormat())]);
            }
            return (new Delete($connection, $this->mapper->getTable(), $this->sql))->delete($tables);
        });
    }
    /**
     * @param array $columns
     * @return int
     */
    public function update(array $columns = [])
    {
        return $this->transaction(function (Database $connection) use($columns) {
            if ($this->mapper->supportsTimestamp()) {
                $columns[$this->mapper->getTimestampColumns()[1]] = \date($this->manager->getDateFormat());
            }
            return (new Update($connection, $this->mapper->getTable(), $this->sql))->set($columns);
        });
    }
    /**
     * @param string[]|string $column
     * @param int             $value
     * @return int
     */
    public function increment($column, $value = 1)
    {
        return $this->transaction(function (Database $connection) use($column, $value) {
            if ($this->mapper->supportsTimestamp()) {
                $this->sql->addUpdateColumns([$this->mapper->getTimestampColumns()[1] => \date($this->manager->getDateFormat())]);
            }
            return (new Update($connection, $this->mapper->getTable(), $this->sql))->increment($column, $value);
        });
    }
    /**
     * @param string[]|string $column
     * @param int             $value
     * @return int
     */
    public function decrement($column, $value = 1)
    {
        return $this->transaction(function (Database $connection) use($column, $value) {
            if ($this->mapper->supportsTimestamp()) {
                $this->sql->addUpdateColumns([$this->mapper->getTimestampColumns()[1] => \date($this->manager->getDateFormat())]);
            }
            return (new Update($connection, $this->mapper->getTable(), $this->sql))->decrement($column, $value);
        });
    }
    /**
     * @param $id
     * @return mixed|null
     */
    public function find($id)
    {
        if (\is_array($id)) {
            foreach ($id as $pk_column => $pk_value) {
                $this->where($pk_column)->is($pk_value);
            }
        } else {
            $this->where($this->mapper->getPrimaryKey()->columns()[0])->is($id);
        }
        return $this->get();
    }
    /**
     * @param array|string ...$ids
     * @return array
     */
    public function findAll(...$ids) : array
    {
        if (\is_array($ids[0])) {
            $keys = \array_keys($ids[0]);
            $values = [];
            foreach ($ids as $pk_value) {
                foreach ($keys as $pk_column) {
                    $values[$pk_column][] = $pk_value[$pk_column];
                }
            }
            foreach ($values as $pk_column => $pk_values) {
                $this->where($pk_column)->in($pk_values);
            }
        } else {
            $this->where($this->mapper->getPrimaryKey()->columns()[0])->in($ids);
        }
        return $this->all();
    }
    /**
     * @param \Closure $callback
     * @param int      $default
     * @return int
     */
    protected function transaction(\Closure $callback, $default = 0)
    {
        return $this->manager->getConnection()->transaction($callback, null, $default);
    }
    /**
     * @return EntityQuery
     */
    protected function buildQuery() : self
    {
        $this->sql->addTables([$this->mapper->getTable()]);
        return $this;
    }
    /**
     * @param array $columns
     * @return \Opis\Database\ResultSet;
     */
    protected function query(array $columns = [])
    {
        if (!$this->buildQuery()->locked && !empty($columns)) {
            foreach ((array) $this->mapper->getPrimaryKey()->columns() as $pk_column) {
                $columns[] = $pk_column;
            }
        }
        if ($this->mapper->supportsSoftDelete()) {
            if (!$this->withSoftDeleted) {
                $this->where($this->mapper->getSoftDeleteColumn())->isNull();
            } elseif ($this->onlySoftDeleted) {
                $this->where($this->mapper->getSoftDeleteColumn())->notNull();
            }
        }
        $this->select($columns);
        $connection = $this->manager->getConnection();
        $compiler = $connection->getCompiler();
        return $connection->query($compiler->select($this->sql), $compiler->getParams());
    }
    /**
     * @return mixed
     */
    protected function executeAggregate()
    {
        $this->sql->addTables([$this->mapper->getTable()]);
        if ($this->mapper->supportsSoftDelete()) {
            if (!$this->withSoftDeleted) {
                $this->where($this->mapper->getSoftDeleteColumn())->isNull();
            } elseif ($this->onlySoftDeleted) {
                $this->where($this->mapper->getSoftDeleteColumn())->notNull();
            }
        }
        $connection = $this->manager->getConnection();
        $compiler = $connection->getCompiler();
        return $connection->column($compiler->select($this->sql), $compiler->getParams());
    }
    /**
     * @return bool
     */
    protected function isReadOnly() : bool
    {
        return !empty($this->sql->getJoins());
    }
    /**
     * @param array $results
     * @return array
     */
    protected function getLazyLoaders(array $results) : array
    {
        if (empty($this->with) || empty($results)) {
            return [];
        }
        $loaders = [];
        $attr = $this->getWithAttributes();
        $relations = $this->mapper->getRelations();
        foreach ($attr['with'] as $with => $callback) {
            if (!isset($relations[$with])) {
                continue;
            }
            $loader = $relations[$with]->getLazyLoader($this->manager, $this->mapper, ['results' => $results, 'callback' => $callback, 'with' => $attr[$with]['extra'] ?? [], 'immediate' => $this->immediate]);
            if (null === $loader) {
                continue;
            }
            $loaders[$with] = $loader;
        }
        return $loaders;
    }
}
