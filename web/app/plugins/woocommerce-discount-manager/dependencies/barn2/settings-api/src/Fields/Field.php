<?php

/**
 * @package   Barn2\barn2-settings-api
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Fields;

use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Util;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Traits\With_Manager;
use JsonSerializable;
/**
 * Field class.
 * Represents a field.
 */
abstract class Field implements Field_Interface, JsonSerializable
{
    use With_Manager;
    /**
     * The field name.
     *
     * @var string
     */
    protected $name;
    /**
     * The field label.
     *
     * @var string
     */
    protected $label;
    /**
     * The field type.
     *
     * @var string
     */
    protected $type;
    /**
     * The field default value.
     *
     * @var mixed
     */
    protected $default;
    /**
     * The field description.
     *
     * @var string|null
     */
    protected $description;
    /**
     * The field attributes.
     *
     * @var array
     */
    protected $attributes = [];
    /**
     * The field tooltip.
     *
     * @var string|null
     */
    protected $tooltip;
    /**
     * The field visibility conditions.
     *
     * @var array
     */
    protected $conditions = [];
    /**
     * Constructor.
     *
     * @param string $name The field name.
     * @param string $label The field label.
     * @param string $description The field description.
     * @param string $tooltip The field tooltip.
     * @param array $attributes The field attributes.
     * @param mixed $default_value The field default value.
     * @return void
     */
    public function __construct(string $name, string $label, string $description = '', string $tooltip = '', array $attributes = [], $default_value = null, array $conditions = [])
    {
        $this->name = $name;
        $this->label = $label;
        $this->description = $description;
        $this->tooltip = $tooltip;
        $this->attributes = $attributes;
        $this->default = $default_value;
        $this->conditions = $conditions;
    }
    /**
     * {@inheritDoc}
     */
    public function set_name(string $name) : self
    {
        $this->name = $name;
        return $this;
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
    public function set_label(string $label) : self
    {
        $this->label = $label;
        return $this;
    }
    /**
     * {@inheritDoc}
     */
    public function get_label() : string
    {
        return $this->label;
    }
    /**
     * {@inheritDoc}
     */
    public function set_type(string $type) : self
    {
        $this->type = $type;
        return $this;
    }
    /**
     * {@inheritDoc}
     */
    public function get_type() : string
    {
        return $this->type;
    }
    /**
     * {@inheritDoc}
     */
    public function set_default($value) : self
    {
        $this->default = $value;
        return $this;
    }
    /**
     * {@inheritDoc}
     */
    public function get_default()
    {
        return $this->default;
    }
    /**
     * {@inheritDoc}
     */
    public function set_description(string $description) : self
    {
        $this->description = $description;
        return $this;
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
    public function set_attributes(array $attributes) : self
    {
        $this->attributes = $attributes;
        return $this;
    }
    /**
     * {@inheritDoc}
     */
    public function get_attributes() : array
    {
        return $this->attributes;
    }
    /**
     * {@inheritDoc}
     */
    public function set_tooltip(string $tooltip) : self
    {
        $this->tooltip = $tooltip;
        return $this;
    }
    /**
     * {@inheritDoc}
     */
    public function get_tooltip() : string
    {
        return $this->tooltip;
    }
    /**
     * {@inheritDoc}
     */
    public function set_conditions(array $conditions) : self
    {
        $this->conditions = $conditions;
        return $this;
    }
    /**
     * {@inheritDoc}
     */
    public function get_conditions() : array
    {
        return $this->conditions;
    }
    /**
     * {@inheritDoc}
     */
    public function sanitize($value)
    {
        return Util::clean($value);
    }
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return ['name' => $this->get_name(), 'label' => $this->get_label(), 'type' => $this->get_type(), 'default' => $this->get_default(), 'description' => $this->get_description(), 'attributes' => $this->get_attributes(), 'tooltip' => $this->get_tooltip(), 'conditions' => $this->get_conditions()];
    }
}
