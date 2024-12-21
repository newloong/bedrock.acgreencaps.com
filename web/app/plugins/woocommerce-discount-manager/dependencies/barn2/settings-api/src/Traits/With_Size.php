<?php

/**
 * @package   Barn2\barn2-settings-api
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Traits;

/**
 * Trait With_Size
 * Adds size to the field.
 *
 * @package Barn2\Settings_API\Traits
 */
trait With_Size
{
    /**
     * The size of the field.
     *
     * @var string
     */
    protected $size = 'regular';
    /**
     * Set the size of the field.
     *
     * @param string $size The size of the field.
     * @return self
     */
    public function set_size(string $size) : self
    {
        $this->size = $size;
        return $this;
    }
    /**
     * Get the size of the field.
     *
     * @return string
     */
    public function get_size() : string
    {
        return $this->size;
    }
}
