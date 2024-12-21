<?php

namespace Barn2\Plugin\Discount_Manager\Api\Routes;

use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Rest\Base_Route;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Rest\Route;
use Barn2\Plugin\Discount_Manager\Traits\Api_Provide_Error_Response;
use WP_REST_Response;
use WP_REST_Request;
use WP_REST_Server;

/**
 * Registers a "products" api route.
 *
 * We need this because WC's api response is limited to 100 products.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Products extends Base_Route implements Route {

	use Api_Provide_Error_Response;

	protected $rest_base = 'products';

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
					'callback'            => [ $this, 'get_products' ],
					'permission_callback' => [ $this, 'permission_callback' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/products_with_variations',
			[
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'get_products_with_variations' ],
					'permission_callback' => [ $this, 'permission_callback' ],
				],
			]
		);
	}

	/**
	 * Retrieve products from the database.
	 *
	 * @return WP_REST_Response
	 */
	public function get_products(): WP_REST_Response {
		$args = [
			'post_type'      => 'product',
			'posts_per_page' => -1,
		];

		$query = new \WP_Query( $args );

		return new WP_REST_Response( $query->get_posts(), 200 );
	}

	/**
	 * Retrieve products and variations from the database.
	 *
	 * @param object WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_products_with_variations( WP_REST_Request $request ): WP_REST_Response {
		$selected_products = $request->get_param( 'selectedProducts' );

		$args = [
			'post_type'      => 'product',
			'include'        => $selected_products,
			'type'           => 'variable',
			'posts_per_page' => count( $selected_products ),
		];

		$response = new \WC_Product_Query( $args );
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			// Construct the error response with a 400 status code
			return $this->send_error_response( $error_message );
		}
		if ( ! empty( $response->get_products() ) ) {
			$variable_products = $response->get_products();
		}

		$products_with_variations = [];

		foreach ( $variable_products as $product ) {
			$variation_data = [
				'id'   => $product->get_id(),
				'name' => $product->get_name(),
			];

			foreach ( $product->get_available_variations() as $variation ) {
				$attributes   = $variation['attributes'];
				$display_name = [];
				foreach ( $attributes as $attribute_name => $attribute_value ) {
					// Append to display name
					if ( $attribute_value ) {
						$display_name[] = ucfirst( $attribute_value );
					}
				}
				$variation_data['variations'][] = [
					'id'   => $variation['variation_id'],
					'name' => implode( ', ', $display_name ),
				];
			}

			$products_with_variations[] = $variation_data;
		}

		return new WP_REST_Response( $products_with_variations, 200 );
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
