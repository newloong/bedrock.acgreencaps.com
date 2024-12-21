<?php

/**
 * @package   Barn2\barn2-settings-api
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Traits;

/**
 * Trait With_Suffix
 * Adds suffix to the field.
 *
 * @package Barn2\Settings_API\Traits
 */
trait With_Suffix
{
    /**
     * The suffix of the field.
     *
     * @var string
     */
    protected $suffix = '';
    /**
     * Set the suffix of the field.
     *
     * @param string $suffix The suffix of the field.
     * @return self
     */
    public function set_suffix(string $suffix) : self
    {
        $this->suffix = $suffix;
        return $this;
    }
    /**
     * Get the suffix of the field.
     *
     * @return string
     */
    public function get_suffix() : string
    {
        return $this->suffix;
    }
}
