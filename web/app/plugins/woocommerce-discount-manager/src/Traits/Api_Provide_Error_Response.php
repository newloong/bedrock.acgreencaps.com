<?php

namespace Barn2\Plugin\Discount_Manager\Traits;

use WP_REST_Response;

/**
 * Provides a `send_error_response` method that can be used
 * to communicate error messages via the react app.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
trait Api_Provide_Error_Response {

	/**
	 * Send an error response via `WP_Rest_Response`.
	 *
	 * @param string $message the message to display as an error.
	 * @return WP_REST_Response
	 */
	public function send_error_response( string $message ): WP_REST_Response {
		return new WP_REST_Response( $message, 403 );
	}
}
