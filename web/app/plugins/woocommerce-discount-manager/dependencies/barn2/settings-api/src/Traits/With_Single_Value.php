<?php

/**
 * @package   Barn2\barn2-settings-api
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Traits;

/**
 * Trait With_Single_Value
 * Adds supports_single_value to the field.
 *
 * @package Barn2\Settings_API\Traits
 */
trait With_Single_Value
{
    /**
     * Whether the field supports a single value.
     *
     * @var mixed
     */
    protected $supports_single_value = \false;
    /**
     * Set whether the field supports a single value.
     *
     * @param bool $supports_single_value Whether the field supports a single value.
     * @return self
     */
    public function set_supports_single_value(bool $supports_single_value) : self
    {
        $this->supports_single_value = $supports_single_value;
        return $this;
    }
    /**
     * Get whether the field supports a single value.
     *
     * @return bool
     */
    public function supports_single_value() : bool
    {
        return $this->supports_single_value;
    }
}
