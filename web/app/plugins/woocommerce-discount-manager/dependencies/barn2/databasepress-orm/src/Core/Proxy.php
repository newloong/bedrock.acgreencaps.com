<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\DatabasepressORM
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core;

use ReflectionClass;
use ReflectionProperty;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Entity;
class Proxy
{
    /** @var ReflectionProperty */
    private $dataMapperArgs;
    /** @var \ReflectionMethod */
    private $ormMethod;
    /**
     * Proxy constructor.
     *
     * @throws \ReflectionException
     */
    private function __construct()
    {
        $entityReflection = new ReflectionClass(Entity::class);
        $this->dataMapperArgs = $entityReflection->getProperty('dataMapperArgs');
        $this->ormMethod = $entityReflection->getMethod('orm');
        $this->dataMapperArgs->setAccessible(\true);
        $this->ormMethod->setAccessible(\true);
    }
    /**
     * @param Entity $entity
     * @return DataMapper
     */
    public function getDataMapper(Entity $entity) : DataMapper
    {
        return $this->ormMethod->invoke($entity);
    }
    /**
     * @param Entity $entity
     * @return array
     */
    public function getEntityColumns(Entity $entity) : array
    {
        if (null !== ($value = $this->dataMapperArgs->getValue($entity))) {
            return $value[2];
        }
        return $this->getDataMapper($entity)->getRawColumns();
    }
    /**
     * @return Proxy
     */
    public static function instance() : Proxy
    {
        static $proxy;
        if ($proxy === null) {
            try {
                $proxy = new self();
            } catch (\ReflectionException $exception) {
            }
        }
        return $proxy;
    }
}
