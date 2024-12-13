<?php
namespace ReyCore\Modules\ProductPageAfterContent;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class AcfFields {

	const FIELDS_GROUP_KEY = 'group_5d4ff536a2684';

	public function __construct(){

		if( ! function_exists('acf_add_local_field') ){
			return;
		}

		foreach ($this->fields() as $key => $field) {
			acf_add_local_field($field);
		}

	}

	public function fields(){
		return [

			[
				'key' => 'field_5d5016664053b',
				'label' => 'Global Sections',
				'name' => '',
				'type' => 'tab',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'placement' => 'top',
				'endpoint' => 0,
				'parent' => self::FIELDS_GROUP_KEY,
			],
			[
				'key' => 'field_5d501dda4053c',
				'label' => 'After product summary section',
				'name' => 'product_content_after_summary',
				'type' => 'select',
				'instructions' => 'Select a global section to append after this product page\'s summary section.',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'choices' => [
				],
				'default_value' => false,
				'allow_null' => 1,
				'multiple' => 0,
				'ui' => 0,
				'return_format' => 'value',
				'ajax' => 0,
				'placeholder' => '',
				'parent' => self::FIELDS_GROUP_KEY,
				'rey_export' => 'post_id',
			],
			[
				'key' => 'field_5d501f579616f',
				'label' => 'After content',
				'name' => 'product_content_after_content',
				'type' => 'select',
				'instructions' => 'Select a global section to append after this product page\'s content (after reviews].',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'choices' => [
				],
				'default_value' => false,
				'allow_null' => 1,
				'multiple' => 0,
				'ui' => 0,
				'return_format' => 'value',
				'ajax' => 0,
				'placeholder' => '',
				'parent' => self::FIELDS_GROUP_KEY,
				'rey_export' => 'post_id',
			],

		];
	}
}
