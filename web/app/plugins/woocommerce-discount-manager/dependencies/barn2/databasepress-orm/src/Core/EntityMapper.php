<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\DatabasepressORM
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\IEntityFilter;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\IEntityMapper;
class EntityMapper implements IEntityMapper
{
    /** @var string */
    protected $entityClass;
    /** @var string|null */
    protected $entityName;
    /** @var string|null */
    protected $table;
    /** @var null|PrimaryKey */
    protected $primaryKey;
    /** @var null|ForeignKey */
    protected $foreignKey;
    /** @var  callable|null */
    protected $primaryKeyGenerator;
    /** @var callable[] */
    protected $getters = [];
    /** @var callable[] */
    protected $setters = [];
    /** @var array */
    protected $casts = [];
    /** @var Relation[] */
    protected $relations = [];
    /** @var  string */
    protected $sequence;
    /** @var bool */
    protected $softDelete = \true;
    /** @var bool */
    protected $timestamp = \true;
    /** @var  null|array */
    protected $assignable;
    /** @var null|array */
    protected $guarded;
    /** @var callable[] */
    protected $filters = [];
    /** @var string */
    protected $softDeleteColumn = 'deleted_at';
    /** @var string[] */
    protected $timestampColumns = ['created_at', 'updated_at'];
    /** @var array */
    protected $eventHandlers = [];
    /**
     * EntityMapper constructor.
     *
     * @param string $entityClass
     */
    public function __construct(string $entityClass)
    {
        $this->entityClass = $entityClass;
    }
    /**
     * @param string $name
     * @return EntityMapper
     */
    public function entityName(string $name) : IEntityMapper
    {
        $this->entityName = $name;
        return $this;
    }
    /**
     * @param string $table
     * @return EntityMapper
     */
    public function table(string $table) : IEntityMapper
    {
        $this->table = $table;
        return $this;
    }
    /**
     * @param string ...$primaryKey
     * @return EntityMapper
     */
    public function primaryKey(string ...$primaryKey) : IEntityMapper
    {
        $this->primaryKey = new PrimaryKey(...$primaryKey);
        return $this;
    }
    /**
     * @param callable $callback
     * @return EntityMapper
     */
    public function primaryKeyGenerator(callable $callback) : IEntityMapper
    {
        $this->primaryKeyGenerator = $callback;
        return $this;
    }
    /**
     * @param string $sequence
     * @return EntityMapper
     */
    public function sequence(string $sequence) : IEntityMapper
    {
        $this->sequence = $sequence;
        return $this;
    }
    /**
     * @param string   $column
     * @param callable $callback
     * @return EntityMapper
     */
    public function getter(string $column, callable $callback) : IEntityMapper
    {
        $this->getters[$column] = $callback;
        return $this;
    }
    /**
     * @param string   $column
     * @param callable $callback
     * @return EntityMapper
     */
    public function setter(string $column, callable $callback) : IEntityMapper
    {
        $this->setters[$column] = $callback;
        return $this;
    }
    /**
     * @param string $name
     * @return RelationFactory
     */
    public function relation(string $name) : RelationFactory
    {
        return new RelationFactory($name, function ($name, Relation $relation) {
            return $this->relations[$name] = $relation;
        });
    }
    /**
     * @param array $casts
     * @return EntityMapper
     */
    public function cast(array $casts) : IEntityMapper
    {
        $this->casts = $casts;
        return $this;
    }
    /**
     * @param bool        $value
     * @param string|null $column
     * @return IEntityMapper
     */
    public function useSoftDelete(bool $value = \true, string $column = null) : IEntityMapper
    {
        $this->softDelete = $value;
        if ($column !== null) {
            $this->softDeleteColumn = $column;
        }
        return $this;
    }
    /**
     * @param bool        $value
     * @param string|null $created_at
     * @param string|null $updated_at
     * @return IEntityMapper
     */
    public function useTimestamp(bool $value = \true, string $created_at = null, string $updated_at = null) : IEntityMapper
    {
        $this->timestamp = $value;
        if ($created_at !== null) {
            $this->timestampColumns[0] = $created_at;
        }
        if ($updated_at !== null) {
            $this->timestampColumns[1] = $updated_at;
        }
        return $this;
    }
    /**
     * @param string[] $columns
     * @return EntityMapper
     */
    public function assignable(array $columns) : IEntityMapper
    {
        $this->assignable = $columns;
        return $this;
    }
    /**
     * @param string[] $columns
     * @return EntityMapper
     */
    public function guarded(array $columns) : IEntityMapper
    {
        $this->guarded = $columns;
        return $this;
    }
    /**
     * @return EntityMapper
     */
    public function filter(IEntityFilter $filter) : IEntityMapper
    {
        $this->filters[$filter->getName()] = $filter;
        return $this;
    }
    /**
     * @inheritDoc
     */
    public function on(string $event, callable $callback) : IEntityMapper
    {
        $this->eventHandlers[$event] = $callback;
        return $this;
    }
    /**
     * @return string
     */
    public function getClass() : string
    {
        return $this->entityClass;
    }
    /**
     * Get the entity's table
     *
     * @return  string
     */
    public function getTable() : string
    {
        if ($this->table === null) {
            $this->table = $this->getEntityName();
        }
        return $this->table;
    }
    /**
     * @return PrimaryKey
     */
    public function getPrimaryKey() : PrimaryKey
    {
        if ($this->primaryKey === null) {
            $this->primaryKey = new PrimaryKey('id');
        }
        return $this->primaryKey;
    }
    /**
     * @return callable|null
     */
    public function getPrimaryKeyGenerator()
    {
        return $this->primaryKeyGenerator;
    }
    /**
     * Get the default foreign key
     *
     * @return ForeignKey
     */
    public function getForeignKey() : ForeignKey
    {
        if ($this->foreignKey === null) {
            $pk = $this->getPrimaryKey();
            $prefix = $this->getEntityName();
            $this->foreignKey = new ForeignKey([], $pk, $prefix);
        }
        return $this->foreignKey;
    }
    /**
     * @return string[]
     */
    public function getTypeCasts() : array
    {
        return $this->casts;
    }
    /**
     * @return callable[]
     */
    public function getGetters() : array
    {
        return $this->getters;
    }
    /**
     * @return callable[]
     */
    public function getSetters() : array
    {
        return $this->setters;
    }
    /**
     * @return Relation[]
     */
    public function getRelations() : array
    {
        return $this->relations;
    }
    /**
     * @return string
     */
    public function getSequence() : string
    {
        if ($this->sequence === null) {
            $this->sequence = $this->getTable() . '_' . $this->getPrimaryKey()->columns()[0] . '_seq';
        }
        return $this->sequence;
    }
    /**
     * @return bool
     */
    public function supportsSoftDelete() : bool
    {
        $deleted_at = $this->softDeleteColumn;
        return $this->softDelete && isset($this->casts[$deleted_at]) && $this->casts[$deleted_at] === '?date';
    }
    /**
     * @return string
     */
    public function getSoftDeleteColumn() : string
    {
        return $this->softDeleteColumn;
    }
    /**
     * @return bool
     */
    public function supportsTimestamp() : bool
    {
        list($created_at, $updated_at) = $this->timestampColumns;
        return $this->timestamp && isset($this->casts[$created_at], $this->casts[$updated_at]) && $this->casts[$created_at] === 'date' && $this->casts[$updated_at] === '?date';
    }
    /**
     * @return string[]
     */
    public function getTimestampColumns() : array
    {
        return $this->timestampColumns;
    }
    /**
     * @return string[]|null
     */
    public function getAssignableColumns()
    {
        return $this->assignable;
    }
    /**
     * @return string[]|null
     */
    public function getGuardedColumns()
    {
        return $this->guarded;
    }
    /**
     * @return IEntityFilter[]
     */
    public function getFilters() : array
    {
        return $this->filters;
    }
    /**
     * @return callable[]
     */
    public function getEventHandlers() : array
    {
        return $this->eventHandlers;
    }
    /**
     * Returns the entity's name
     *
     * @return  string
     */
    protected function getEntityName()
    {
        if ($this->entityName === null) {
            $name = $this->entityClass;
            if (\property_exists($name, 'table')) {
                $this->entityName = $name::$table;
            } elseif (\method_exists($name, 'getTableName')) {
                $this->entityName = $name::getTableName();
            } else {
                if (\false !== ($pos = \strrpos($name, '\\'))) {
                    $name = \substr($name, $pos + 1);
                }
                $name = \strtolower(\preg_replace('/([^A-Z])([A-Z])/', '$1_$2', $name));
                $name = \str_replace('-', '_', $name);
                $this->entityName = $name;
            }
        }
        return $this->entityName;
    }
}
