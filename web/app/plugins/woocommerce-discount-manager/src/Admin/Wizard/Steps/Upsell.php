<?php

namespace Barn2\Plugin\Discount_Manager\Admin\Wizard\Steps;

use Barn2\Plugin\Discount_Manager\Dependencies\Setup_Wizard\Steps\Cross_Selling;

/**
 * The upsell step for the setup wizard.
 *
 * @codeCoverageIgnore
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Upsell extends Cross_Selling {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->set_title( esc_html__( 'Extra features', 'woocommerce-discount-manager' ) );
		$this->set_description( __( 'Enhance your store with these fantastic plugins from Barn2.', 'woocommerce-discount-manager' ) );
	}
}
