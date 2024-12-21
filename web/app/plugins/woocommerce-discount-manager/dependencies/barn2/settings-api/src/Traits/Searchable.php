<?php

/**
 * @package   Barn2\barn2-settings-api
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Traits;

trait Searchable
{
    /**
     * Whether the field is searchable.
     *
     * @var bool
     */
    protected $searchable = \false;
    /**
     * Set whether the field is searchable.
     *
     * @param bool $searchable Whether the field is searchable.
     * @return self
     */
    public function set_searchable(bool $searchable) : self
    {
        $this->searchable = $searchable;
        return $this;
    }
    /**
     * Get whether the field is searchable.
     *
     * @return bool
     */
    public function is_searchable() : bool
    {
        return $this->searchable;
    }
}
