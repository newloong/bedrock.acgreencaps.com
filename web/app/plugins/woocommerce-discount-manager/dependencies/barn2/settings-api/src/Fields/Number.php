<?php

/**
 * @package   Barn2\barn2-settings-api
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Fields;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Traits\With_Min_Max;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Traits\With_Size;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Traits\With_Suffix;
/**
 * Number field class.
 */
class Number extends Field
{
    use With_Min_Max;
    use With_Size;
    use With_Suffix;
    /** {@inheritDoc} */
    protected $type = 'number';
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $field = parent::jsonSerialize();
        $field['min'] = $this->get_min();
        $field['max'] = $this->get_max();
        $field['size'] = $this->get_size();
        $field['suffix'] = $this->get_suffix();
        return $field;
    }
}
