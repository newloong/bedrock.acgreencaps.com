<?php

/**
 * @package   Barn2\barn2-settings-api
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Sections;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Fields\Field;
/**
 * Interface Section_Interface
 * Represents a section.
 */
interface Section_Interface
{
    /**
     * Get the section slug.
     *
     * @return string
     */
    public function get_slug() : string;
    /**
     * Get the section name.
     *
     * @return string
     */
    public function get_name() : string;
    /**
     * Add a field to the section.
     *
     * @param Field $field The field to add.
     * @return self
     */
    public function add_field(Field $field) : self;
    /**
     * Add fields to the section.
     *
     * @param Field[] $fields The fields to add.
     * @return self
     */
    public function add_fields(array $fields) : self;
    /**
     * Get the fields in the section.
     *
     * @return Field[]
     */
    public function get_fields() : array;
    /**
     * Get the section description.
     *
     * @return string
     */
    public function get_description() : string;
}
