<?php

/**
 * @package   Barn2\barn2-settings-api
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Fields;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Traits\With_Callable;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Traits\With_Options;
/**
 * Radio field class.
 * Represents a radio field.
 */
class Radio extends Field
{
    /** {@inheritDoc} */
    protected $type = 'radio';
    use With_Options;
    use With_Callable;
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $field = parent::jsonSerialize();
        $field['options'] = $this->get_options();
        $field['hasCallable'] = $this->has_callable();
        return $field;
    }
}
