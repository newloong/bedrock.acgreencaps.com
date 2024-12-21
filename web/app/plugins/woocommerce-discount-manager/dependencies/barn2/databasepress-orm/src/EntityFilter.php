<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\DatabasepressORM
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core\Query;
/**
 * Adds the ability to filter entities by a given query.
 *
 * Used as a replacement for the previous callback method
 * that was not compatible with Object Caching plugins like Redis.
 */
abstract class EntityFilter implements IEntityFilter
{
    /**
     * The name of the filter.
     *
     * @var string
     */
    public $name;
    /**
     * Constructor.
     *
     * @param string $name The name of the filter.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }
    /**
     * Get the name of the filter.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * Filter the entities via the query.
     *
     * @param Query $query The query to filter.
     * @param mixed $data  The data to filter.
     * @return void
     */
    public abstract function filter(Query $query, $data = null);
}
