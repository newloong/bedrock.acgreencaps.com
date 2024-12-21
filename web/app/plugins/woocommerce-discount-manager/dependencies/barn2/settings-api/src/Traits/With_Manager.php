<?php

/**
 * @package   Barn2\barn2-settings-api
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Traits;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Settings_Manager;
/**
 * Trait for fields that have a settings manager.
 */
trait With_Manager
{
    /**
     * The settings manager.
     *
     * @var Settings_Manager
     */
    protected $manager;
    /**
     * Set the settings manager.
     *
     * @param Settings_Manager $manager The settings manager.
     * @return self
     */
    public function set_manager(Settings_Manager $manager) : self
    {
        $this->manager = $manager;
        return $this;
    }
    /**
     * Get the settings manager.
     *
     * @return Settings_Manager
     */
    public function get_manager() : Settings_Manager
    {
        return $this->manager;
    }
}
