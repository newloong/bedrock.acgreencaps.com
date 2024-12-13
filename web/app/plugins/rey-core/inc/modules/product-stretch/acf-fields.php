<?php
namespace ReyCore\Modules\ProductStretch;

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
				'key' => 'field_5fa0535dede64',
				'label' => 'Product Stretch (Colspan)',
				'name' => 'product_stretch',
				'type' => 'number',
				'instructions' => 'Select a size to stretch this product into the catalog. It will span across multiple columns.',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => [
					'width' => '',
					'class' => 'rey-acf-title',
					'id' => '',
				],
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'min' => 1,
				'max' => 5,
				'step' => '',
				'parent' => self::FIELDS_GROUP_KEY,
			],
			[
				'key' => 'field_5fa053bbede65',
				'label' => 'Image display',
				'name' => 'product_stretch_image_display',
				'type' => 'select',
				'instructions' => 'Select how to display the image.',
				'required' => 0,
				'conditional_logic' => [
					[
						[
							'field' => 'field_5fa0535dede64',
							'operator' => '>',
							'value' => '1',
						],
					],
				],
				'wrapper' => [
					'width' => '',
					'class' => 'rey-decrease-list-size',
					'id' => '',
				],
				'choices' => [
					'contain' => 'Contain',
					'cover' => 'Cover',
					'images' => 'Display multiple images',
				],
				'default_value' => false,
				'allow_null' => 0,
				'multiple' => 0,
				'ui' => 0,
				'return_format' => 'value',
				'ajax' => 0,
				'placeholder' => '',
				'parent' => self::FIELDS_GROUP_KEY,
			],
			[
				'key' => 'field_5fa0fd45f4b9e',
				'label' => 'Custom Thumbnail',
				'name' => 'product_stretch_custom_thumbnail',
				'type' => 'image',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => [
					[
						[
							'field' => 'field_5fa0535dede64',
							'operator' => '>',
							'value' => '1',
						],
						[
							'field' => 'field_5fa053bbede65',
							'operator' => '==',
							'value' => 'cover',
						],
					],
				],
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'return_format' => 'id',
				'preview_size' => 'thumbnail',
				'library' => 'all',
				'min_width' => '',
				'min_height' => '',
				'min_size' => '',
				'max_width' => '',
				'max_height' => '',
				'max_size' => '',
				'mime_types' => '',
				'parent' => self::FIELDS_GROUP_KEY,
			],
			[
				'key' => 'field_5fa10edcdd397',
				'label' => 'Force Center Content',
				'name' => 'product_stretch_center',
				'type' => 'true_false',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => [
					[
						[
							'field' => 'field_5fa0535dede64',
							'operator' => '>',
							'value' => '1',
						],
					],
				],
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'message' => '',
				'default_value' => 0,
				'ui' => 1,
				'ui_on_text' => '',
				'ui_off_text' => '',
				'parent' => self::FIELDS_GROUP_KEY,
			],
		];
	}
}
