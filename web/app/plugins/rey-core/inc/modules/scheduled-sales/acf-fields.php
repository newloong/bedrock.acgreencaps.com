<?php
namespace ReyCore\Modules\ScheduledSales;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class AcfFields {

	const FIELDS_GROUP_KEY = 'group_5d4ff536a2684';

	public function __construct($acf_fields){

		foreach ($this->fields() as $field) {
			// $field['parent'] = self::FIELDS_GROUP_KEY;
			$acf_fields->set_group_fields( 'product_settings', $field, 'single_specifications_block' );
		}

	}

	public function fields(){
		return [
			[
				'key' => 'field_6341d412c9183',
				'label' => 'Evergreen Sale',
				'name' => 'evergreen_sale',
				'type' => 'group',
				'instructions' => 'Choose to display a permanent sale badge or countdown to this product, regardless if the Scheduled Sale expired.',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				// 'layout' => 'block',
				// 'layout' => 'row',
				'layout' => 'table',
				'sub_fields' => array(
					array(
						'key' => 'field_6341d75dc9187',
						'label' => 'Duration',
						'name' => 'duration',
						'type' => 'number',
						'instructions' => 'How long does it last.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => 'days',
						'min' => '',
						'max' => '',
						'step' => '',
					),
					array(
						'key' => 'field_6341d6a4c9185',
						'label' => 'Starting from',
						'name' => 'starting_from',
						'type' => 'date_picker',
						'instructions' => 'Enter the starting date',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'display_format' => 'Y-m-d',
						'return_format' => 'Y-m-d',
						'first_day' => 1,
					),
					array(
						'key' => 'field_6341d480c9184',
						'label' => 'Repeat Count',
						'name' => 'repeat_count',
						'type' => 'number',
						'instructions' => 'How many times to repeat the sale.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => 'times',
						'min' => '',
						'max' => '',
						'step' => '',
					),
					// array(
					// 	'key' => 'field_6341d6efc9186',
					// 	'label' => 'Pause',
					// 	'name' => 'pause',
					// 	'type' => 'number',
					// 	'instructions' => 'How many days to pause.',
					// 	'required' => 0,
					// 	'conditional_logic' => 0,
					// 	'wrapper' => array(
					// 		'width' => '',
					// 		'class' => '',
					// 		'id' => '',
					// 	),
					// 	'default_value' => '',
					// 	'placeholder' => '',
					// 	'prepend' => '',
					// 	'append' => 'days',
					// 	'min' => '',
					// 	'max' => '',
					// 	'step' => '',
					// ),
				),
			]
		];
	}
}
