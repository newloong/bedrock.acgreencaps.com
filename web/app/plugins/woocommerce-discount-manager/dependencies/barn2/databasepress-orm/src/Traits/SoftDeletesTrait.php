<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\DatabasepressORM
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Traits;

trait SoftDeletesTrait
{
    /** @var bool */
    protected $withSoftDeleted = \false;
    /** @var bool */
    protected $onlySoftDeleted = \false;
    /**
     * @param bool $value
     * @return mixed|SoftDeletesTrait
     */
    public function withSoftDeleted(bool $value = \true) : self
    {
        $this->withSoftDeleted = $value;
        return $this;
    }
    /**
     * @param bool $value
     * @return mixed|SoftDeletesTrait
     */
    public function onlySoftDeleted(bool $value = \true) : self
    {
        $this->onlySoftDeleted = $this->withSoftDeleted = $value;
        return $this;
    }
}
