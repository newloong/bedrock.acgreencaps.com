<?php
namespace ReyCore\Modules\DynamicTags\Tags\Post;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Content extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'post-content',
			'title'      => esc_html__( 'Post Content', 'rey-core' ),
			'categories' => [ 'text' ],
			'group'      => \ReyCore\Modules\DynamicTags\Base::GROUPS_POST,
		];
	}

	public function render() {
		echo wp_kses_post( get_the_content() );
	}

}
