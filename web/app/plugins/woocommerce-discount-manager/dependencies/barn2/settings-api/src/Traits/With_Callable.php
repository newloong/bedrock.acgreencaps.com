<?php

/**
 * @package   Barn2\barn2-settings-api
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Traits;

/**
 * Trait With_Callable
 *
 * @package Barn2\Settings_API\Traits
 */
trait With_Callable
{
    /**
     * @var callable
     */
    protected $callable;
    /**
     * @param callable $func
     */
    public function set_callable(callable $func)
    {
        $this->callable = $func;
        return $this;
    }
    /**
     * @return callable
     */
    public function get_callable()
    {
        return $this->callable;
    }
    /**
     * @return bool
     */
    public function has_callable()
    {
        return !empty($this->callable);
    }
}
