<?php

namespace Barn2\Plugin\Discount_Manager\Admin\Wizard\Steps;

use Barn2\Plugin\Discount_Manager\Dependencies\Setup_Wizard\Steps\Ready;

/**
 * The completed step for the setup wizard.
 *
 * @codeCoverageIgnore
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Completed extends Ready {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->set_name( esc_html__( 'Ready', 'woocommerce-discount-manager' ) );
		$this->set_title( esc_html__( 'Complete Setup', 'woocommerce-discount-manager' ) );
		$this->set_description( $this->get_custom_description() );
	}

	/**
	 * Retrieves the description.
	 *
	 * @return string
	 */
	private function get_custom_description(): string {
		return esc_html__( 'Congratulations, you have finished setting up the plugin. Now it’s time to start creating discounts.', 'woocommerce-discount-manager' );
	}
}
