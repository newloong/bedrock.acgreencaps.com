<?php

/**
 * @package   Barn2\barn2-settings-api
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Traits;

/**
 * Trait for fields that have a minimum and maximum value.
 */
trait With_Min_Max
{
    /**
     * The minimum value.
     *
     * @var int
     */
    protected $min = 0;
    /**
     * The maximum value.
     *
     * @var int
     */
    protected $max = 100;
    /**
     * Set the minimum value.
     *
     * @param integer $min
     * @return self
     */
    public function set_min(int $min) : self
    {
        $this->min = $min;
        return $this;
    }
    /**
     * Get the minimum value.
     *
     * @return integer
     */
    public function get_min() : int
    {
        return $this->min;
    }
    /**
     * Set the maximum value.
     *
     * @param integer $max
     * @return self
     */
    public function set_max(int $max) : self
    {
        $this->max = $max;
        return $this;
    }
    /**
     * Get the maximum value.
     *
     * @return integer
     */
    public function get_max() : int
    {
        return $this->max;
    }
}
