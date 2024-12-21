<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\DatabasepressORM
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM;

interface IMappableEntity
{
    /**
     * @param IEntityMapper $mapper
     * @return mixed
     */
    public static function mapEntity(IEntityMapper $mapper);
}
