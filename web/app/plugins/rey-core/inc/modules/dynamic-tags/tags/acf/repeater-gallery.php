<?php
namespace ReyCore\Modules\DynamicTags\Tags\Acf;

use ReyCore\Modules\DynamicTags\Base as TagDynamic;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class RepeaterGallery extends Gallery {

	public static function __config() {
		return [
			'id'         => 'acf-repeater-gallery',
			'title'      => esc_html__( 'ACF Repeater Gallery', 'rey-core' ),
			'categories' => [ 'gallery' ],
			'group'      => TagDynamic::GROUPS_ACF,
		];
	}

	protected function register_controls() {

		$this->add_control(
			'index',
			[
				'label' => esc_html__( 'Sub-field Index', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 1,
				'max' => 100,
				'step' => 1,
			]
		);

		TagDynamic::acf_field_control( $this, array_merge(self::TYPES, ['repeater']) );

	}

}