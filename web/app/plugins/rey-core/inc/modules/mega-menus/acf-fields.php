<?php
namespace ReyCore\Modules\MegaMenus;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class AcfFields {

	const GROUP_KEY = 'group_5c4f2dec3824e';

	public function __construct(){

		$this->add_fields();

		add_filter('acf/get_field_group', [$this, 'add_location']);

	}

	public function add_location($group){

		if( ! (isset($group['key']) && self::GROUP_KEY === $group['key']) ){
			return $group;
		}

		$location = [
			[
				[
					'param' => 'nav_menu_item',
					'operator' => '==',
					'value' => 'location/main-menu',
				],
			],
		];

		if( ! \ReyCore\ACF\Helper::prevent_export_dynamic_field() ){
			foreach (get_option(Base::SUPPORTED_MENUS, []) as $menu_id) {
				$location[] = [
					[
						'param' => 'nav_menu_item',
						'operator' => '==',
						'value' => $menu_id,
					]
				];
			}
		}

		$group['location'] = $location;

		return $group;
	}

	public function add_fields(){

		if( ! function_exists('acf_add_local_field_group') ){
			return;
		}

		acf_add_local_field_group(array(
			'key' => self::GROUP_KEY,
			'title' => 'Menu Settings',
			'fields' => array(
				array(
					'key' => 'field_5c4f2e4b77834',
					'label' => 'Mega Menu',
					'name' => 'mega_menu',
					'type' => 'true_false',
					'instructions' => 'Activate the mega menu for this menu item.',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'message' => '',
					'default_value' => 0,
					'ui' => 1,
					'ui_on_text' => '',
					'ui_off_text' => '',
				),
				array(
					'key' => 'field_5c4f2e9f77836',
					'label' => 'Mega Menu Type',
					'name' => 'mega_menu_type',
					'type' => 'select',
					'instructions' => 'Select the type of mega menu. Columns will only show submenu trees into columns, while Global Sections allows much more complex layouts.',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_5c4f2e4b77834',
								'operator' => '==',
								'value' => '1',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'choices' => array(
						'columns' => 'Menu Columns',
						'global_sections' => 'Global Sections',
					),
					'default_value' => 'columns',
					'allow_null' => 0,
					'multiple' => 0,
					'ui' => 0,
					'return_format' => 'value',
					'ajax' => 0,
					'placeholder' => '',
				),
				array(
					'key' => 'field_5c4f31f6d86fd',
					'label' => 'Menu Columns per row',
					'name' => 'mega_menu_columns',
					'type' => 'select',
					'instructions' => 'Select how many columns per row.',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_5c4f2e4b77834',
								'operator' => '==',
								'value' => '1',
							),
							array(
								'field' => 'field_5c4f2e9f77836',
								'operator' => '==',
								'value' => 'columns',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '--dependent',
						'id' => '',
					),
					'choices' => array(
						2 => '2 Columns',
						3 => '3 Columns',
						4 => '4 Columns',
						5 => '5 Columns',
					),
					'default_value' => 2,
					'allow_null' => 0,
					'multiple' => 0,
					'ui' => 0,
					'return_format' => 'value',
					'ajax' => 0,
					'placeholder' => '',
				),
				array(
					'key' => 'field_5c4f2f2277837',
					'label' => 'Select Global Section',
					'name' => 'menu_global_section',
					'type' => 'select',
					'instructions' => 'Select the global section to load in this mega menu panel.',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_5c4f2e4b77834',
								'operator' => '==',
								'value' => '1',
							),
							array(
								'field' => 'field_5c4f2e9f77836',
								'operator' => '==',
								'value' => 'global_sections',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '--dependent',
						'id' => '',
					),
					'choices' => array(
					),
					'default_value' => false,
					'allow_null' => 1,
					'multiple' => 0,
					'ui' => 0,
					'return_format' => 'value',
					'ajax' => 0,
					'placeholder' => '',
					'rey_export' => 'post_id',
				),
				array(
					'key' => 'field_5c24f22778f37',
					'name' => 'mega_lazy',
					'label' => 'Ajax Lazy load',
					'type' => 'select',
					'instructions' => 'Load the content via Ajax.',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_5c4f2e4b77834',
								'operator' => '==',
								'value' => '1',
							),
							array(
								'field' => 'field_5c4f2e9f77836',
								'operator' => '==',
								'value' => 'global_sections',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '--dependent',
						'id' => '',
					),
					'choices' => array(
						'' => 'No',
						'yes_mo' => 'Yes, on mouseover item',
						'yes_pm' => 'Yes, on mouseover parent menu',
						'yes_pl' => 'Yes, on page load',
					),
					'default_value' => '',
					'allow_null' => 1,
					'multiple' => 0,
					'ui' => 0,
					'return_format' => 'value',
					'ajax' => 0,
					'placeholder' => '',
				),
				array(
					'key' => 'field_5c4f7fcc3be58',
					'label' => 'Panel layout',
					'name' => 'panel_layout',
					'type' => 'select',
					'instructions' => 'Select the panel\'s layout',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_5c4f2e4b77834',
								'operator' => '==',
								'value' => '1',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'choices' => array(
						'full' => 'Window full width',
						'boxed' => 'Boxed (Container Width)',
						'custom' => 'Custom Width',
					),
					'default_value' => 'boxed',
					'allow_null' => 0,
					'multiple' => 0,
					'ui' => 0,
					'return_format' => 'value',
					'ajax' => 0,
					'placeholder' => '',
				),
				array(
					'key' => 'field_5ce2d5578c1b9',
					'label' => 'Panel Width (px)',
					'name' => 'panel_width',
					'type' => 'number',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_5c4f2e4b77834',
								'operator' => '==',
								'value' => '1',
							),
							array(
								'field' => 'field_5c4f7fcc3be58',
								'operator' => '==',
								'value' => 'custom',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '--dependent',
						'id' => '',
					),
					'default_value' => 800,
					'placeholder' => 'eg: 800',
					'prepend' => '',
					'append' => 'px',
					'min' => 200,
					'max' => 1800,
					'step' => '',
				),
				array(
					'key' => 'field_5e60c30ec556b',
					'label' => 'Sub-Panel Styles',
					'name' => 'panel_styles',
					'type' => 'true_false',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'message' => '',
					'default_value' => 0,
					'ui' => 1,
					'ui_on_text' => '',
					'ui_off_text' => '',
				),
				array(
					'key' => 'field_5e60c40ec556c',
					'label' => 'Text Color',
					'name' => 'panel_text_color',
					'type' => 'color_picker',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_5e60c30ec556b',
								'operator' => '==',
								'value' => '1',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '--dependent',
						'id' => '',
					),
					'default_value' => '',
					'enable_opacity' => 1,
				),
				array(
					'key' => 'field_5e60c459c556d',
					'label' => 'Background Color',
					'name' => 'panel_bg_color',
					'type' => 'color_picker',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_5e60c30ec556b',
								'operator' => '==',
								'value' => '1',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '--dependent',
						'id' => '',
					),
					'default_value' => '',
					'enable_opacity' => 1,
				),
				array(
					'key' => 'field_5e60c468c556e',
					'label' => 'Padding',
					'name' => 'panel_padding',
					'type' => 'number',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_5e60c30ec556b',
								'operator' => '==',
								'value' => '1',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '--dependent',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => 50,
					'prepend' => '',
					'append' => 'px',
					'min' => '',
					'max' => '',
					'step' => '',
				),
			),
			'location' => [],
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => true,
			'description' => '',
		));

	}
}
