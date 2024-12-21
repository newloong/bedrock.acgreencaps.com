<?php

/**
 * @package   Barn2\barn2-settings-api
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Traits;

/**
 * Trait With_Help
 * Adds help text to the field.
 *
 * @package Barn2\Settings_API\Traits
 */
trait With_Help
{
    /**
     * The help text.
     *
     * @var string
     */
    protected $help = '';
    /**
     * Set the help text.
     *
     * @param string $help The help text.
     * @return self
     */
    public function set_help(string $help) : self
    {
        $this->help = $help;
        return $this;
    }
    /**
     * Get the help text.
     *
     * @return string
     */
    public function get_help() : string
    {
        return $this->help;
    }
}
