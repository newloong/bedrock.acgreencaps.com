<?php

/**
 * @package   Barn2\barn2-settings-api
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Tabs;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Sections\Section_Interface;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Traits\With_Action_Links;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Traits\With_Manager;
use JsonSerializable;
/**
 * Class Tab
 * Represents a tab.
 */
class Tab implements Tab_Interface, JsonSerializable
{
    use With_Action_Links;
    use With_Manager;
    /**
     * The tab name.
     *
     * @var string
     */
    protected $name;
    /**
     * The tab slug.
     *
     * @var string
     */
    protected $slug;
    /**
     * The tab sections.
     *
     * @var array
     */
    protected $sections = [];
    /**
     * Constructor.
     *
     * @param string $slug the tab slug.
     * @param string $name the tab name.
     * @param array $sections the tab sections.
     * @return void
     */
    public function __construct(string $slug, string $name, $sections = [])
    {
        $slug = \str_replace('-', '_', $slug);
        $this->slug = $slug;
        $this->name = $name;
        $this->sections = $sections;
    }
    /**
     * {@inheritDoc}
     */
    public function get_slug() : string
    {
        return $this->slug;
    }
    /**
     * {@inheritDoc}
     */
    public function get_name() : string
    {
        return $this->name;
    }
    /**
     * {@inheritDoc}
     */
    public function get_sections() : array
    {
        return $this->sections;
    }
    /**
     * Add a section.
     *
     * @param Section_Interface $section the section.
     * @return self
     */
    public function add_section(Section_Interface $section) : self
    {
        $this->sections[] = $section;
        return $this;
    }
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return ['id' => $this->get_slug(), 'name' => $this->get_name(), 'sections' => $this->get_sections(), 'links' => $this->get_action_links()];
    }
}
