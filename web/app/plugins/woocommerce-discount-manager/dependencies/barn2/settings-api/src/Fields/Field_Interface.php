<?php

/**
 * @package   Barn2\barn2-settings-api
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Fields;

/**
 * Interface Field_Interface
 * Represents a field.
 *
 * @package Barn2\Settings_API\Fields
 */
interface Field_Interface
{
    /**
     * Set the field name.
     *
     * @param string $name The field name.
     * @return self
     */
    public function set_name(string $name) : self;
    /**
     * Get the field name.
     *
     * @return string
     */
    public function get_name() : string;
    /**
     * Set the field label.
     *
     * @param string $label The field label.
     * @return self
     */
    public function set_label(string $label) : self;
    /**
     * Get the field label.
     *
     * @return string
     */
    public function get_label() : string;
    /**
     * Set the field type.
     *
     * @param string $type The field type.
     * @return self
     */
    public function set_type(string $type) : self;
    /**
     * Get the field type.
     *
     * @return string
     */
    public function get_type() : string;
    /**
     * Set the field default value.
     *
     * @param mixed $default The field default value.
     * @return self
     */
    public function set_default($default) : self;
    /**
     * Get the field default value.
     *
     * @return mixed
     */
    public function get_default();
    /**
     * Set the field description.
     *
     * @param string $description The field description.
     * @return self
     */
    public function set_description(string $description) : self;
    /**
     * Get the field description.
     *
     * @return string
     */
    public function get_description() : string;
    /**
     * Set the field attributes.
     *
     * @param array $attributes The field attributes.
     * @return self
     */
    public function set_attributes(array $attributes) : self;
    /**
     * Get the field attributes.
     *
     * @return array
     */
    public function get_attributes() : array;
    /**
     * Set the field tooltip.
     *
     * @param string $tooltip The field tooltip.
     * @return self
     */
    public function set_tooltip(string $tooltip) : self;
    /**
     * Get the field tooltip.
     *
     * @return string
     */
    public function get_tooltip() : string;
    /**
     * Set the field's visibility conditions.
     *
     * @param array $conditions The field conditions.
     * @return self
     */
    public function set_conditions(array $conditions) : self;
    /**
     * Get the field's visibility conditions.
     *
     * @return array
     */
    public function get_conditions() : array;
    /**
     * Sanitize the field value.
     *
     * @param mixed $value The field value.
     * @return self
     */
    public function sanitize($value);
}
