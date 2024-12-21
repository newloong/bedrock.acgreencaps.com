<?php

namespace Barn2\Plugin\Discount_Manager\Admin\Wizard\Steps;

use Barn2\Plugin\Discount_Manager\Dependencies\Setup_Wizard\Steps\Welcome;

/**
 * The welcome step for the setup wizard.
 *
 * @codeCoverageIgnore
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class License extends Welcome {

	/**
	 * Welcome constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->set_name( esc_html__( 'Welcome', 'woocommerce-discount-manager' ) );
		$this->set_title( esc_html__( 'Welcome to WooCommerce Discount Manager', 'woocommerce-discount-manager' ) );
		$this->set_description( esc_html__( 'Start creating deals and discounts in no time', 'woocommerce-discount-manager' ) );
	}
}
