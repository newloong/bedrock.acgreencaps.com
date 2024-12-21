<?php

/**
 * @package   Barn2\barn2-settings-api
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Sections;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Fields\Field;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Sections\Section_Interface;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Traits\With_Action_Links;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Traits\With_Manager;
use JsonSerializable;
/**
 * Section class.
 * Represents a section of settings.
 */
class Section implements Section_Interface, JsonSerializable
{
    use With_Action_Links;
    use With_Manager;
    /**
     * The section name.
     *
     * @var string
     */
    protected $name;
    /**
     * The section slug.
     *
     * @var string
     */
    protected $slug;
    /**
     * The section description.
     *
     * @var string
     */
    protected $description = '';
    /**
     * The section fields.
     *
     * @var array
     */
    protected $fields = [];
    /**
     * Constructor.
     *
     * @param string $slug The section slug.
     * @param string $name The section name.
     * @param string $description The section description.
     * @return void
     */
    public function __construct(string $slug, string $name, string $description = '')
    {
        $slug = \str_replace('-', '_', $slug);
        $this->slug = $slug;
        $this->name = $name;
        $this->description = $description;
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
    public function get_description() : string
    {
        return $this->description;
    }
    /**
     * {@inheritDoc}
     */
    public function add_field(Field $field) : self
    {
        $this->fields[] = $field;
        return $this;
    }
    /**
     * {@inheritDoc}
     */
    public function add_fields(array $fields) : self
    {
        foreach ($fields as $field) {
            $this->add_field($field);
        }
        return $this;
    }
    /**
     * {@inheritDoc}
     */
    public function get_fields() : array
    {
        return $this->fields;
    }
    /**
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize() : array
    {
        return ['id' => $this->get_slug(), 'name' => $this->get_name(), 'description' => $this->get_description(), 'links' => $this->get_action_links(), 'fields' => $this->get_fields()];
    }
}
