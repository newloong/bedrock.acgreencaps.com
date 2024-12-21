<?php

/**
 * @package   Barn2\barn2-settings-api
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Tabs;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Sections\Section_Interface;
/**
 * Interface Tab_Interface
 * Represents a tab.
 */
interface Tab_Interface
{
    /**
     * Get the tab slug.
     *
     * @return string
     */
    public function get_slug() : string;
    /**
     * Get the tab name.
     *
     * @return string
     */
    public function get_name() : string;
    /**
     * Get the tab sections.
     *
     * @return array
     */
    public function get_sections() : array;
    /**
     * Add a section to the tab.
     *
     * @param Section_Interface $section The section to add.
     * @return self
     */
    public function add_section(Section_Interface $section) : self;
}
