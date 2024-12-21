<?php

/**
 * @package   Barn2\barn2-settings-api
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Fields;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Traits\With_Size;
/**
 * Textarea field class.
 */
class Textarea extends Field
{
    use With_Size;
    /** {@inheritDoc} */
    protected $type = 'textarea';
    /**
     * The number of rows.
     *
     * @var int
     */
    protected $rows = 5;
    /**
     * The number of columns.
     *
     * @var int
     */
    protected $cols = 10;
    /**
     * Set the number of rows.
     *
     * @param integer $rows
     * @return self
     */
    public function set_rows(int $rows) : self
    {
        $this->rows = $rows;
        return $this;
    }
    /**
     * Get the number of rows.
     *
     * @return integer
     */
    public function get_rows() : int
    {
        return $this->rows;
    }
    /**
     * Set the number of columns.
     *
     * @param integer $cols
     * @return self
     */
    public function set_cols(int $cols) : self
    {
        $this->cols = $cols;
        return $this;
    }
    /**
     * Get the number of columns.
     *
     * @return integer
     */
    public function get_cols() : int
    {
        return $this->cols;
    }
    /**
     * {@inheritDoc}
     */
    public function sanitize($value)
    {
        return \sanitize_textarea_field($value);
    }
    /**
     * {@inheritDoc}
     */
    public function jsonSerialize()
    {
        $field = parent::jsonSerialize();
        $field['rows'] = $this->get_rows();
        $field['cols'] = $this->get_cols();
        $field['size'] = $this->get_size();
        return $field;
    }
}
