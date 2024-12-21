<?php

/**
 * @package   Barn2\barn2-settings-api
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Traits;

/**
 * Trait Clearable
 * Adds clearable to the field.
 *
 * @package Barn2\Settings_API\Traits
 */
trait Clearable
{
    /**
     * Whether the field is clearable.
     *
     * @var bool
     */
    protected $clearable = \false;
    /**
     * Set whether the field is clearable.
     *
     * @param bool $clearable Whether the field is clearable.
     * @return self
     */
    public function set_clearable(bool $clearable) : self
    {
        $this->clearable = $clearable;
        return $this;
    }
    /**
     * Get whether the field is clearable.
     *
     * @return bool
     */
    public function is_clearable() : bool
    {
        return $this->clearable;
    }
}
