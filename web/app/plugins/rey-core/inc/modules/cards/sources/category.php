<?php
namespace ReyCore\Modules\Cards\Sources;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Category extends CatBase {

	public function get_id(){
		return 'category';
	}

	public function get_title(){
		return esc_html__( 'Posts Categories', 'rey-core' );
	}

	public function controls($element){
		$this->controls_tax($element );
	}

	public function query($element){
		return $this->query_tax($element);
	}

	public function parse_item($element){
		if( ! (isset($element->_items[$element->item_key]) && ($item = $element->_items[$element->item_key])) ){
			return [];
		}

		if( ! (($term = get_term( $item )) && isset($term->name)) ){
			return [];
		}

		$thumbnail_id = get_term_meta( $item, 'thumbnail_id', true );

		$args = [
			'image' => [
				'id' => $thumbnail_id,
				// 'url' => wp_get_attachment_url( $thumbnail_id ),
			],
			'button_url' => [
				'url' => get_term_link($item, $term->taxonomy)
			],
			'captions' => 'yes',
			'button_text' => $element->_settings['button_text'],
			'_id' => 'post-cat-' . $item,
			'term' => $term,
		];

		$args['title'] = $term->name;
		$args['subtitle'] = $term->description;

		if( '' !== $element->_settings['category_show_count'] ){
			$args['title'] .= sprintf(' <sup>%d</sup>', $term->count);
		}

		return $args;
	}

	public function load_more_button_per_page($element){
		return isset($element->_settings['category_limit']) && ($limit = $element->_settings['category_limit']) ? $limit : false;
	}
}
