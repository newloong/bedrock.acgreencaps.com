<?php

/**
 * @package   Barn2\barn2-settings-api
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Fields;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Traits\With_Callable;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Traits\With_Help;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Traits\With_Options;
/**
 * Checkboxes field class.
 * Represents a checkboxes field.
 */
class Checkboxes extends Field implements Transformable
{
    /** {@inheritDoc} */
    protected $type = 'checkboxes';
    use With_Help;
    use With_Options;
    use With_Callable;
    /**
     * {@inheritDoc}
     */
    public function transform($value)
    {
        return \array_keys(\array_filter($value));
    }
    /**
     * {@inheritDoc}
     */
    public function untransform($value)
    {
        // If the value is empty and a default is set, use the default.
        if (empty($value) && !empty($this->get_default())) {
            $value = $this->get_default();
            if (\is_array($value)) {
                $value = \array_combine($value, $value);
            }
        }
        if (!\is_array($value)) {
            $value = [];
        }
        return \array_combine($value, $value);
    }
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $field = parent::jsonSerialize();
        $field['options'] = $this->get_options();
        $field['help'] = $this->get_help();
        $field['hasCallable'] = $this->has_callable();
        return $field;
    }
}
