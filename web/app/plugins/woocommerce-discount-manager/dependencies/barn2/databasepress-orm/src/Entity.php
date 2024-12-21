<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\DatabasepressORM
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core\DataMapper;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core\EntityMapper;
abstract class Entity
{
    /** @var array */
    private $dataMapperArgs;
    /** @var  DataMapper|null */
    private $dataMapper;
    /**
     * Entity constructor.
     *
     * @param EntityManager $entityManager
     * @param EntityMapper  $entityMapper
     * @param array         $columns
     * @param array         $loaders
     * @param bool          $isReadOnly
     * @param bool          $isNew
     */
    public final function __construct(EntityManager $entityManager, EntityMapper $entityMapper, array $columns = [], array $loaders = [], bool $isReadOnly = \false, bool $isNew = \false)
    {
        $this->dataMapperArgs = [$entityManager, $entityMapper, $columns, $loaders, $isReadOnly, $isNew];
    }
    /**
     * @return IDataMapper
     */
    protected final function orm() : IDataMapper
    {
        if ($this->dataMapper === null) {
            $this->dataMapper = new DataMapper(...$this->dataMapperArgs);
            unset($this->dataMapperArgs);
        }
        return $this->dataMapper;
    }
}
