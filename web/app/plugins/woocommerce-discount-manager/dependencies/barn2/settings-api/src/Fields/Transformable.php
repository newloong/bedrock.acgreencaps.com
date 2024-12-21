<?php

/**
 * @package   Barn2\barn2-settings-api
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Fields;

/**
 * Interface for fields that can transform their value before it is saved or displayed.
 */
interface Transformable
{
    /**
     * Transform the field value before it is saved.
     *
     * @param mixed $value
     * @return mixed
     */
    public function transform($value);
    /**
     * Transform the field value before it is displayed.
     *
     * @param mixed $value
     * @return mixed
     */
    public function untransform($value);
}
