<?php
namespace ReyCore\Modules\DynamicTags\Tags\Woo;

use \ReyCore\Modules\DynamicTags\Base as TagDynamic;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Meta extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'product-meta',
			'title'      => esc_html__( 'Product Meta', 'rey-core' ),
			'categories' => [ 'text', 'url', 'media', 'post_meta', 'gallery', 'number', 'color', 'datetime', ],
			'group'      => TagDynamic::GROUPS_WOO,
		];
	}


	protected function register_controls() {

		TagDynamic::woo_product_control($this);

		$post_custom_keys = get_post_custom_keys();

		if( is_null($post_custom_keys) ){
			return;
		}

		if( ! is_array($post_custom_keys) ){
			$post_custom_keys = [];
		}

		$keys = array_filter($post_custom_keys, function($item){
			return ! is_protected_meta($item);
		});

		$this->add_control(
			'key',
			[
				'label' => esc_html__( 'Meta Keys', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => array_merge(array_combine($keys, $keys), [
					''  => esc_html__( '- Select -', 'rey-core' ),
					'custom'  => esc_html__( '- Custom -', 'rey-core' ),
				]),
			]
		);

		$this->add_control(
			'custom_key',
			[
				'label' => esc_html__( 'Custom Meta Key', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'condition' => [
					'key' => 'custom',
				],
			]
		);
	}

	public function render() {

		if( ! ($product = TagDynamic::get_product($this)) ){
			return TagDynamic::display_placeholder_data( esc_html__( '{Product Meta}', 'rey-core' ) );
		}

		$settings = $this->get_settings();

		$key = $settings['key'];

		if( 'custom' === $key && ($custom_key = $settings['custom_key']) ){
			$key = $custom_key;
		}

		if( ! $key ){
			return;
		}

		echo get_post_meta($product->get_id(), $key, true);
	}

}
