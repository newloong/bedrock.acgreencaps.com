<?php

namespace Barn2\Plugin\Discount_Manager\Api\Routes;

use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Rest\Base_Route;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Rest\Route;
use WP_REST_Response;
use WP_REST_Request;
use WP_REST_Server;

/**
 * Registers a "users" api route.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Users extends Base_Route implements Route {

	protected $rest_base = 'users';

	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_users' ],
					'permission_callback' => [ $this, 'permission_callback' ],
				],
			]
		);
	}

	/**
	 * Retrieve users from the database.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_users( WP_REST_Request $request ): WP_REST_Response {
		$search_term = $request->get_param( 'search' );
		$include     = $request->get_param( 'include' );

		$args = [
			'blog_id'        => get_current_blog_id(),
			'orderby'        => 'display_name',
			'order'          => 'ASC',
			'fields'         => [ 'ID', 'user_login', 'display_name', 'user_email' ],
			'search'         => '*' . esc_attr( $search_term ) . '*',
			'search_columns' => [
				'user_login',
				'user_nicename',
				'user_email',
			],
		];

		if ( ! empty( $include ) ) {
			unset( $args['search'] );
			unset( $args['search_columns'] );
			$args['include'] = array_map( 'absint', explode( ',', $include ) );
		}

		$query = ( new \WP_User_Query( $args ) )->get_results();

		if ( ! empty( $include ) ) {
			$results = array_unique( $query, SORT_REGULAR );
			$users   = $this->parse_results( $results );
			return new WP_REST_Response( $users, 200 );
		}

		$meta_args = [
			'blog_id'    => get_current_blog_id(),
			'orderby'    => 'display_name',
			'order'      => 'ASC',
			'fields'     => [ 'ID', 'user_login', 'display_name' ],
			'meta_query' => [
				'relation' => 'OR',
				[
					'key'     => 'first_name',
					'value'   => $search_term ?? '',
					'compare' => 'LIKE',
				],
				[
					'key'     => 'last_name',
					'value'   => $search_term ?? '',
					'compare' => 'LIKE',
				],
			],
		];

		$meta_query = ( new \WP_User_Query( $meta_args ) )->get_results();

		$results = array_merge( $query, $meta_query );
		$results = array_unique( $results, SORT_REGULAR );

		$users = $this->parse_results( $results );

		return new WP_REST_Response( $users, 200 );
	}

	/**
	 * Helper function to parse results for usage in the react app.
	 *
	 * @param array $results
	 * @return array
	 */
	private function parse_results( array $results = [] ): array {
		$list_of_ids = [];
		$users       = [];

		if ( ! empty( $results ) ) {
			foreach ( $results as $user ) {
				if ( ! in_array( $user->ID, $list_of_ids, true ) ) {
					$list_of_ids[] = $user->ID;
					$users[]       = [
						'id'   => $user->ID,
						'name' => $user->user_login,
					];
				}
			}
		}

		return $users;
	}

	/**
	 * Determine if route can be accessed.
	 *
	 * @return boolean
	 */
	public function permission_callback(): bool {
		return current_user_can( 'manage_woocommerce' );
	}
}
