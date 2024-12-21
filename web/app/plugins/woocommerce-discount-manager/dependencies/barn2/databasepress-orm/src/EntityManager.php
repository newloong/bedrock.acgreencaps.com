<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\DatabasepressORM
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM;

use RuntimeException;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\Database;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\SQL\Compiler;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\SQL\Insert;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\SQL\Update;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core\EntityMapper;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core\EntityQuery;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core\Proxy;
class EntityManager
{
    /** @var Database */
    protected $connection;
    /** @var  Compiler */
    protected $compiler;
    /** @var  string */
    protected $dateFormat;
    /** @var EntityMapper[] */
    protected $entityMappers = [];
    /** @var callable[] */
    protected $entityMappingCallbacks;
    /**
     * EntityManager constructor.
     *
     * @param Database   $connection
     * @param callable[] $callbacks
     */
    public function __construct(Database $connection, array $callbacks = [])
    {
        $this->connection = $connection;
        $this->entityMappingCallbacks = $callbacks;
    }
    /**
     * @param string $entityClass
     * @return EntityQuery
     */
    public function __invoke(string $entityClass)
    {
        return $this->query($entityClass);
    }
    /**
     * @return Database
     */
    public function getDatabase() : Database
    {
        return $this->connection;
    }
    /**
     * @return Database
     */
    public function getConnection() : Database
    {
        return $this->connection;
    }
    /**
     * @return Compiler
     */
    public function getCompiler() : Compiler
    {
        if ($this->compiler === null) {
            $this->compiler = $this->connection->getCompiler();
        }
        return $this->compiler;
    }
    /**
     * @return string
     */
    public function getDateFormat() : string
    {
        if ($this->dateFormat === null) {
            $this->dateFormat = $this->getCompiler()->getDateFormat();
        }
        return $this->dateFormat;
    }
    /**
     * @param string $entityClass
     * @return EntityQuery
     */
    public function query(string $entityClass) : EntityQuery
    {
        return new EntityQuery($this, $this->resolveEntityMapper($entityClass));
    }
    /**
     * @param Entity $entity
     * @return bool
     */
    public function save(Entity $entity) : bool
    {
        $data = Proxy::instance()->getDataMapper($entity);
        $mapper = $data->getEntityMapper();
        $eventHandlers = $mapper->getEventHandlers();
        if ($data->isDeleted()) {
            throw new RuntimeException('The record is deleted');
        }
        if ($data->isNew()) {
            $id = $this->connection->transaction(function (Database $connection) use($data, $mapper) {
                $columns = $data->getRawColumns();
                if (null !== ($pk_generator = $mapper->getPrimaryKeyGenerator())) {
                    $pk_data = $pk_generator($data);
                    if (\is_array($pk_data)) {
                        foreach ($pk_data as $pk_column => $pk_value) {
                            $columns[$pk_column] = $pk_value;
                        }
                    } else {
                        $columns[$mapper->getPrimaryKey()->columns()[0]] = $pk_data;
                    }
                }
                if ($mapper->supportsTimestamp()) {
                    $timestamp_cols = $mapper->getTimestampColumns();
                    $columns[$timestamp_cols[0]] = \date($this->getDateFormat());
                    $columns[$timestamp_cols[1]] = null;
                }
                (new Insert($connection))->insert($columns)->into($mapper->getTable());
                if ($pk_generator !== null) {
                    return $pk_data ?? \false;
                }
                return $connection->getLastInsertId();
            }, $this->getConnection(), \false);
            if ($id === \false) {
                return \false;
            }
            $data->markAsSaved($id);
            if (isset($eventHandlers['save'])) {
                $eventHandlers['save']($entity, $data);
            }
            return \true;
        }
        if (!$data->wasModified()) {
            return \true;
        }
        $modified = $data->getModifiedColumns();
        if (!empty($modified)) {
            $result = $this->connection->transaction(function (Database $connection) use($data, $mapper, $modified) {
                $columns = \array_intersect_key($data->getRawColumns(), \array_flip($modified));
                $updatedAt = null;
                if ($mapper->supportsTimestamp()) {
                    $columns[$mapper->getTimestampColumns()[1]] = $updatedAt = \date($this->getDateFormat());
                }
                $data->markAsUpdated($updatedAt);
                $update = new Update($connection, $mapper->getTable());
                foreach ($mapper->getPrimaryKey()->getValue($data->getRawColumns(), \true) as $pk_col => $pk_val) {
                    $update->where($pk_col)->is($pk_val);
                }
                return (bool) $update->set($columns);
            }, null, \false);
            if ($result === \false) {
                return \false;
            }
            if (isset($eventHandlers['update'])) {
                $eventHandlers['update']($entity, $data);
            }
            return \true;
        }
        return $this->connection->transaction(function (Database $connection) use($data) {
            $data->executePendingLinkage();
            return \true;
        }, null, \false);
    }
    /**
     * @param string $class
     * @param array  $columns
     * @return Entity
     */
    public function create(string $class, array $columns = []) : Entity
    {
        return new $class($this, $this->resolveEntityMapper($class), $columns, [], \false, \true);
    }
    /**
     * @param Entity $entity
     * @param bool   $force
     * @return bool
     */
    public function delete(Entity $entity, bool $force = \false) : bool
    {
        $data = Proxy::instance()->getDataMapper($entity);
        $mapper = $data->getEntityMapper();
        $eventHandlers = $mapper->getEventHandlers();
        $result = $this->connection->transaction(function () use($data, $mapper, $force) {
            if ($data->isDeleted()) {
                throw new RuntimeException('The record was already deleted');
            }
            if ($data->isNew()) {
                throw new RuntimeException("Can't delete an unsaved entity");
            }
            $data->markAsDeleted();
            $delete = new EntityQuery($this, $mapper);
            foreach ($mapper->getPrimaryKey()->getValue($data->getRawColumns(), \true) as $pk_col => $pk_val) {
                $delete->where($pk_col)->is($pk_val);
            }
            return (bool) $delete->delete($force);
        }, null, \false);
        if ($result === \false) {
            return \false;
        }
        if (isset($eventHandlers['delete'])) {
            $eventHandlers['delete']($entity, $data);
        }
        return \true;
    }
    /**
     * @param string $class
     * @return EntityMapper
     */
    public function resolveEntityMapper(string $class) : EntityMapper
    {
        if (isset($this->entityMappers[$class])) {
            return $this->entityMappers[$class];
        }
        try {
            $reflection = new \ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new RuntimeException("Reflection error for '{$class}'", 0, $e);
        }
        if (!$reflection->isSubclassOf(Entity::class)) {
            throw new RuntimeException("The '{$class}' must extend " . Entity::class);
        }
        if (isset($this->entityMappingCallbacks[$class])) {
            $callback = $this->entityMappingCallbacks[$class];
        } elseif ($reflection->implementsInterface(IMappableEntity::class)) {
            $callback = $class . '::mapEntity';
        } else {
            $callback = null;
        }
        $entityMapper = new EntityMapper($class);
        if ($callback !== null) {
            $callback($entityMapper);
        }
        return $this->entityMappers[$class] = $entityMapper;
    }
    /**
     * @param string   $class
     * @param callable $callback
     * @return EntityManager
     */
    public function registerMappingCallback(string $class, callable $callback) : self
    {
        $this->entityMappingCallbacks[$class] = $callback;
        return $this;
    }
}
