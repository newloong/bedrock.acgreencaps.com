<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\DatabasepressORM
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core;

use Closure;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Relations\BelongsTo;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Relations\HasOneOrMany;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Relations\ShareOneOrMany;
class RelationFactory
{
    /** @var  string */
    protected $name;
    /** @var  Closure */
    protected $callback;
    /**
     * RelationFactory constructor.
     *
     * @param string  $name
     * @param Closure $callback
     */
    public function __construct(string $name, Closure $callback)
    {
        $this->name = $name;
        $this->callback = $callback;
    }
    /**
     * @param string          $entityClass
     * @param ForeignKey|null $foreignKey
     * @return Relation
     */
    public function hasOne(string $entityClass, ForeignKey $foreignKey = null) : Relation
    {
        return ($this->callback)($this->name, new HasOneOrMany($entityClass, $foreignKey));
    }
    /**
     * @param string          $entityClass
     * @param ForeignKey|null $foreignKey
     * @return Relation
     */
    public function hasMany(string $entityClass, ForeignKey $foreignKey = null) : Relation
    {
        return ($this->callback)($this->name, new HasOneOrMany($entityClass, $foreignKey, \true));
    }
    /**
     * @param string          $entityClass
     * @param ForeignKey|null $foreignKey
     * @return Relation
     */
    public function belongsTo(string $entityClass, ForeignKey $foreignKey = null) : Relation
    {
        return ($this->callback)($this->name, new BelongsTo($entityClass, $foreignKey));
    }
    /**
     * @param string          $entityClass
     * @param ForeignKey|null $foreignKey
     * @param Junction|null   $junction
     * @return Relation
     */
    public function shareOne(string $entityClass, ForeignKey $foreignKey = null, Junction $junction = null) : Relation
    {
        return ($this->callback)($this->name, new ShareOneOrMany($entityClass, $foreignKey, $junction));
    }
    /**
     * @param string          $entityClass
     * @param ForeignKey|null $foreignKey
     * @param Junction|null   $junction
     * @return Relation
     */
    public function shareMany(string $entityClass, ForeignKey $foreignKey = null, Junction $junction = null) : Relation
    {
        return ($this->callback)($this->name, new ShareOneOrMany($entityClass, $foreignKey, $junction, \true));
    }
}
