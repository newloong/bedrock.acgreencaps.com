<?php
namespace ReyCore\Modules\ProductVideo;

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
				'key' => 'field_5e92dd2e2be4f',
				'label' => 'Video',
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
				'key' => 'field_5e92dd4f2be50',
				'label' => 'Video URL',
				'name' => 'product_video_url',
				'type' => 'text',
				'instructions' => 'Supports YouTube and Vimeo urls. For self-hosted videos, paste in the video path.',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'default_value' => '',
				'placeholder' => 'eg: https://www.youtube.com/watch?v=L6P3nI6VnlY',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
				'parent' => self::FIELDS_GROUP_KEY,
			],
			[
				'key' => 'field_5e92dde22be51',
				'label' => 'Gallery - Show "play" icon button over main image?',
				'name' => 'product_video_main_image',
				'type' => 'true_false',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'message' => '',
				'default_value' => 1,
				'ui' => 1,
				'ui_on_text' => '',
				'ui_off_text' => '',
				'parent' => self::FIELDS_GROUP_KEY,
			],
			[
				'key' => 'field_5f678ebf2c933',
				'label' => 'Gallery - Video Image',
				'name' => 'product_video_gallery_image',
				'type' => 'image',
				'instructions' => 'Show this image in product gallery. For gallery with thumbnails, it\'s mandatory to set an image.',
				'required' => 0,
				'conditional_logic' => [
					[
						[
							'field' => 'field_5e92dde22be51',
							'operator' => '!=',
							'value' => '1',
						],
					],
				],
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'return_format' => 'array',
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
				'key' => 'field_615c464aa9fb2',
				'label' => 'Gallery - Show Inline?',
				'name' => 'product_video_inline',
				'type' => 'true_false',
				'instructions' => 'Enable if you want to show the video inside the gallery (without modal).',
				'required' => 0,
				'conditional_logic' => [
					[
						[
							'field' => 'field_5e92dde22be51',
							'operator' => '!=',
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
			[
				'key' => 'field_5e92de0d2be53',
				'label' => 'Product Summary - Add button link?',
				'name' => 'product_video_summary',
				'type' => 'select',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'choices' => [
					'disabled' => 'Don\'t add',
					'before_product_meta' => 'Before Product Meta',
					'after_product_meta' => 'After Product Meta',
					'after_share' => 'After Sharing buttons',
					'before_add_to_cart' => 'Before Add to cart button',
					'after_title' => 'After Title',
				],
				'default_value' => 'disabled',
				'allow_null' => 1,
				'multiple' => 0,
				'ui' => 0,
				'return_format' => 'value',
				'ajax' => 0,
				'placeholder' => '',
				'parent' => self::FIELDS_GROUP_KEY,
			],
			[
				'key' => 'field_5f71b8be0bc81',
				'label' => 'Product Summary - Link Text',
				'name' => 'product_video_link_text',
				'type' => 'text',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => [
					[
						[
							'field' => 'field_5e92de0d2be53',
							'operator' => '!=',
							'value' => 'disabled',
						],
						[
							'field' => 'field_5e92de0d2be53',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'default_value' => '',
				'placeholder' => 'eg: PLAY PRODUCT VIDEO',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
				'parent' => self::FIELDS_GROUP_KEY,
			],
			// [
			// 	'key' => 'field_5e92dead5526d',
			// 	'label' => 'Display - Modal Width',
			// 	'name' => 'product_video_modal_size',
			// 	'type' => 'number',
			// 	'instructions' => '',
			// 	'required' => 0,
			// 	'conditional_logic' => 0,
			// 	'wrapper' => [
			// 		'width' => '',
			// 		'class' => '',
			// 		'id' => '',
			// 	],
			// 	'default_value' => '',
			// 	'placeholder' => 'eg: 600',
			// 	'prepend' => '',
			// 	'append' => 'px',
			// 	'min' => '',
			// 	'max' => '',
			// 	'step' => '',
			// 	'parent' => self::FIELDS_GROUP_KEY,
			// ],
			[
				'key' => 'field_5ead2de65529d',
				'label' => 'Display - Video Ratio (h/w]',
				'name' => 'product_video_modal_ratio',
				'type' => 'number',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'default_value' => '',
				'placeholder' => 'eg: 56.25',
				'prepend' => '',
				'append' => '%',
				'min' => '',
				'max' => '',
				'step' => '',
				'parent' => self::FIELDS_GROUP_KEY,
			],

		];
	}
}
