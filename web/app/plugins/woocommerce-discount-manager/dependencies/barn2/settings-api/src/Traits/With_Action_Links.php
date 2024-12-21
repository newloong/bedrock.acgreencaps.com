<?php

/**
 * @package   Barn2\barn2-settings-api
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Traits;

/**
 * Trait With_Action_Links
 * Adds action links to the settings page.
 *
 * @package Barn2\Settings_API\Traits
 */
trait With_Action_Links
{
    /**
     * The action links.
     *
     * @var array
     */
    protected $action_links = [];
    /**
     * Setup action links.
     * Links will be displayed on the settings page under the 1st tab.
     *
     * Array format:
     *
     * [
     *    [
     *      'text' => 'Link text',
     *      'url'  => 'Link URL',
     *   ],
     *  ...
     * ]
     *
     * @param array $links The links to add.
     * @return self
     */
    public function set_action_links(array $links) : self
    {
        $this->action_links = $links;
        return $this;
    }
    /**
     * Get the action links.
     *
     * @return array
     */
    public function get_action_links() : array
    {
        return $this->action_links;
    }
}
