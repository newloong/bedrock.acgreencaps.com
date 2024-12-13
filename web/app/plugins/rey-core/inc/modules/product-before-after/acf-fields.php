<?php
namespace ReyCore\Modules\ProductBeforeAfter;

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
				'key' => 'field_5fa00e8b3dead',
				'label' => 'Catalog Display',
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
				'key' => 'field_5fa00e9e3deae',
				'label' => 'Content Before',
				'name' => 'content_before',
				'type' => 'select',
				'instructions' => 'Select what type of content you want to show before the product.',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => [
					'width' => '',
					'class' => 'rey-decrease-list-size rey-acf-title',
					'id' => '',
				],
				'choices' => [
					'gs' => 'Generic Global Section',
					'product' => 'Product',
				],
				'default_value' => false,
				'allow_null' => 1,
				'multiple' => 0,
				'ui' => 0,
				'return_format' => 'value',
				'ajax' => 0,
				'placeholder' => '',
				'parent' => self::FIELDS_GROUP_KEY,
			],
			[
				'key' => 'field_5fa014ff3deaf',
				'label' => 'Global section',
				'name' => 'content_before_global_section',
				'type' => 'select',
				'instructions' => 'Select a generic global section to display before the product.',
				'required' => 0,
				'conditional_logic' => [
					[
						[
							'field' => 'field_5fa00e9e3deae',
							'operator' => '==',
							'value' => 'gs',
						],
					],
				],
				'wrapper' => [
					'width' => '',
					'class' => 'rey-decrease-list-size',
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
				'key' => 'field_5fa0156a3deb0',
				'label' => 'Choose Product',
				'name' => 'content_before_product',
				'type' => 'relationship',
				'instructions' => 'Select a product to display before the product.',
				'required' => 0,
				'conditional_logic' => [
					[
						[
							'field' => 'field_5fa00e9e3deae',
							'operator' => '==',
							'value' => 'product',
						],
					],
				],
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'post_type' => [
					0 => 'product',
				],
				'taxonomy' => '',
				'filters' => [
					0 => 'search',
				],
				'elements' => '',
				'min' => '',
				'max' => 1,
				'return_format' => 'id',
				'parent' => self::FIELDS_GROUP_KEY,
				'rey_export' => 'post_id',
			],
			[
				'key' => 'field_5fa016223deb1',
				'label' => 'Column Span',
				'name' => 'content_before_colspan',
				'type' => 'number',
				'instructions' => 'Stretch product per multiple columns.',
				'required' => 0,
				'conditional_logic' => [
					[
						[
							'field' => 'field_5fa00e9e3deae',
							'operator' => '==',
							'value' => 'gs',
						],
					],
				],
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'min' => '',
				'max' => '',
				'step' => '',
				'parent' => self::FIELDS_GROUP_KEY,
			],
			[
				'key' => 'field_5fa01ec02e100',
				'label' => 'Content After',
				'name' => 'content_after',
				'type' => 'select',
				'instructions' => 'Select what type of content you want to show after the product.',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => [
					'width' => '',
					'class' => 'rey-decrease-list-size rey-acf-title',
					'id' => '',
				],
				'choices' => [
					'gs' => 'Generic Global Section',
					'product' => 'Product',
				],
				'default_value' => false,
				'allow_null' => 1,
				'multiple' => 0,
				'ui' => 0,
				'return_format' => 'value',
				'ajax' => 0,
				'placeholder' => '',
				'parent' => self::FIELDS_GROUP_KEY,
			],
			[
				'key' => 'field_5fa01ed02e101',
				'label' => 'Global section',
				'name' => 'content_after_global_section',
				'type' => 'select',
				'instructions' => 'Select a generic global section to display after the product.',
				'required' => 0,
				'conditional_logic' => [
					[
						[
							'field' => 'field_5fa01ec02e100',
							'operator' => '==',
							'value' => 'gs',
						],
					],
				],
				'wrapper' => [
					'width' => '',
					'class' => 'rey-decrease-list-size',
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
				'key' => 'field_5fa01ed42e102',
				'label' => 'Choose Product',
				'name' => 'content_after_product',
				'type' => 'relationship',
				'instructions' => 'Select a product to display after the product.',
				'required' => 0,
				'conditional_logic' => [
					[
						[
							'field' => 'field_5fa01ec02e100',
							'operator' => '==',
							'value' => 'product',
						],
					],
				],
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'post_type' => [
					0 => 'product',
				],
				'taxonomy' => '',
				'filters' => [
					0 => 'search',
				],
				'elements' => '',
				'min' => '',
				'max' => 1,
				'return_format' => 'id',
				'parent' => self::FIELDS_GROUP_KEY,
				'rey_export' => 'post_id',
			],
			[
				'key' => 'field_5fa01ed92e103',
				'label' => 'Column Span',
				'name' => 'content_after_colspan',
				'type' => 'number',
				'instructions' => 'Stretch product per multiple columns.',
				'required' => 0,
				'conditional_logic' => [
					[
						[
							'field' => 'field_5fa01ec02e100',
							'operator' => '==',
							'value' => 'gs',
						],
					],
				],
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'min' => '',
				'max' => '',
				'step' => '',
				'parent' => self::FIELDS_GROUP_KEY,
			],
		];
	}
}
