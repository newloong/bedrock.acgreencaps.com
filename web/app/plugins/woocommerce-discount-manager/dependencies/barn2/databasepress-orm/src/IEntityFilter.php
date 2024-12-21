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
 * Represents a filter that can be applied to an entity.
 */
interface IEntityFilter
{
    /**
     * @return string
     */
    public function getName();
    /**
     * Filter the entities via the query.
     *
     * @param Query $query The query to filter.
     * @param mixed $data  The data to filter.
     * @return void
     */
    public function filter(Query $query, $data = null);
}
