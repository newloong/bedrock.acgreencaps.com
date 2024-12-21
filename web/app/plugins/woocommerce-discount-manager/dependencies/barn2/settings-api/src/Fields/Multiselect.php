<?php

/**
 * @package   Barn2\barn2-settings-api
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Fields;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Traits\Clearable;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Traits\Searchable;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Traits\With_Callable;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Traits\With_Single_Value;
/**
 * Multiselect field class.
 * Represents a multiselect field.
 */
class Multiselect extends Select implements Transformable
{
    use Clearable;
    use Searchable;
    use With_Single_Value;
    use With_Callable;
    /** {@inheritDoc} */
    protected $type = 'multiselect';
    /**
     * {@inheritDoc}
     */
    public function transform($value)
    {
        $values = [];
        if ($this->supports_single_value()) {
            $values = isset($value['value']) ? $value['value'] : $value;
        } else {
            foreach ($value as $item) {
                $values[] = isset($item['value']) ? $item['value'] : $item;
            }
        }
        return $values;
    }
    /**
     * {@inheritDoc}
     */
    public function untransform($value)
    {
        return $value;
    }
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $field = parent::jsonSerialize();
        $field['options'] = $this->get_options();
        $field['clearable'] = $this->is_clearable();
        $field['searchable'] = $this->is_searchable();
        $field['single'] = $this->supports_single_value();
        $field['hasCallable'] = $this->has_callable();
        return $field;
    }
}
