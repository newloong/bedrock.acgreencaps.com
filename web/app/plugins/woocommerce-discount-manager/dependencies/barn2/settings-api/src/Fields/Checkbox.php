<?php

/**
 * @package   Barn2\barn2-settings-api
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Fields;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Traits\With_Help;
/**
 * Checkbox field class.
 * Represents a checkbox field.
 */
class Checkbox extends Field
{
    /** {@inheritDoc} */
    protected $type = 'checkbox';
    use With_Help;
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $field = parent::jsonSerialize();
        $field['help'] = $this->get_help();
        return $field;
    }
}
