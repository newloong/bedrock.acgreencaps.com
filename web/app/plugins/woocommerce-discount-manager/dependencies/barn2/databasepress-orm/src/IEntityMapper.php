<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\DatabasepressORM
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core\RelationFactory;
interface IEntityMapper
{
    /**
     * @param string $name
     * @return IEntityMapper
     */
    public function entityName(string $name) : self;
    /**
     * @param string $table
     * @return IEntityMapper
     */
    public function table(string $table) : self;
    /**
     * @param string ...$primaryKey
     * @return IEntityMapper
     */
    public function primaryKey(string ...$primaryKey) : self;
    /**
     * @param callable $callback
     * @return IEntityMapper
     */
    public function primaryKeyGenerator(callable $callback) : self;
    /**
     * @param string $sequence
     * @return IEntityMapper
     */
    public function sequence(string $sequence) : self;
    /**
     * @param string   $column
     * @param callable $callback
     * @return IEntityMapper
     */
    public function getter(string $column, callable $callback) : self;
    /**
     * @param string   $column
     * @param callable $callback
     * @return IEntityMapper
     */
    public function setter(string $column, callable $callback) : self;
    /**
     * @param string $name
     * @return RelationFactory
     */
    public function relation(string $name) : RelationFactory;
    /**
     * @param array $casts
     * @return IEntityMapper
     */
    public function cast(array $casts) : self;
    /**
     * @param bool        $value
     * @param string|null $column
     * @return IEntityMapper
     */
    public function useSoftDelete(bool $value = \true, string $column = null) : self;
    /**
     * @param bool        $value
     * @param string|null $created_at
     * @param string|null $updated_at
     * @return IEntityMapper
     */
    public function useTimestamp(bool $value = \true, string $created_at = null, string $updated_at = null) : self;
    /**
     * @param string[] $columns
     * @return IEntityMapper
     */
    public function assignable(array $columns) : self;
    /**
     * @param string[] $columns
     * @return IEntityMapper
     */
    public function guarded(array $columns) : self;
    /**
     * @param string   $name
     * @param callable $callback
     * @return IEntityMapper
     */
    //public function filter( string $name, callable $callback): self;
    /**
     * @param string   $event
     * @param callable $callback
     * @return IEntityMapper
     */
    public function on(string $event, callable $callback) : self;
}
