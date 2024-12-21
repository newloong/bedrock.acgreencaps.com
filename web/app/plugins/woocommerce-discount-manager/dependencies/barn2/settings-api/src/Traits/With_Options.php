<?php

/**
 * @package   Barn2\barn2-settings-api
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Traits;

/**
 * Trait With_Options
 * Adds options to the field.
 *
 * @package Barn2\Settings_API\Traits
 */
trait With_Options
{
    /**
     * The options.
     *
     * @var array
     */
    protected $options = [];
    /**
     * Setup options.
     *
     * Array format:
     *
     * [
     *    [
     *      'label' => 'Option text',
     *      'value'  => 'Option value',
     *   ],
     *  ...
     * ]
     *
     * @param array $options The options to add.
     * @return self
     */
    public function set_options(array $options) : self
    {
        $this->options = $options;
        return $this;
    }
    /**
     * Get the options.
     *
     * @return array
     */
    public function get_options() : array
    {
        return $this->options;
    }
}
