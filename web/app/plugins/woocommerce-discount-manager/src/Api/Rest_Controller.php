<?php

namespace Barn2\Plugin\Discount_Manager\Api;

use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Registerable;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Rest\Base_Server;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Rest\Rest_Server;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Rest\Route;

/**
 * Main controller which registers the REST routes for the plugin.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Rest_Controller extends Base_Server implements Registerable, Rest_Server {

	const NAMESPACE = 'wdm/v1';

	/**
	 * The list of REST route objects handled by this server.
	 *
	 * @var Route[]
	 */
	private $routes = [];

	/**
	 * Get things started.
	 */
	public function __construct() {
		$this->routes = [
			new Routes\Discounts( self::NAMESPACE ),
			new Routes\Products( self::NAMESPACE ),
			new Routes\Users( self::NAMESPACE ),
		];
	}

	/**
	 * Retrieve the namespace.
	 *
	 * @return string
	 */
	public function get_namespace() {
		return self::NAMESPACE;
	}

	/**
	 * Retrieve the routes.
	 *
	 * @return Route[]
	 */
	public function get_routes() {
		return $this->routes;
	}
}
