<?php

namespace Barn2\Plugin\Discount_Manager\Admin\Wizard;

use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Plugin\License\EDD_Licensing;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Plugin\License\Plugin_License;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Plugin\Plugin;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Registerable;
use Barn2\Plugin\Discount_Manager\Dependencies\Setup_Wizard\Setup_Wizard as Wizard;

/**
 * This class is responsible for setting up the setup wizard.
 *
 * @codeCoverageIgnore
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Setup_Wizard implements Registerable {

	/**
	 * The plugin instance.
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * The setup wizard instance.
	 *
	 * @var Wizard
	 */
	private $wizard;

	/**
	 * Setup_Wizard constructor.
	 *
	 * @param Plugin $plugin
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		$steps = [
			new Steps\License(),
			new Steps\Upsell(),
			new Steps\Completed(),
		];

		$wizard = new Wizard( $this->plugin, $steps );

		$wizard->configure(
			[
				'skip_url'    => admin_url( 'admin.php?page=wdm_options&tab=general' ),
				'premium_url' => 'https://barn2.com/wordpress-plugins/woocommerce-discount-manager/',
				'utm_id'      => 'wdm',
				'signpost'    => [
					[
						'title' => __( 'Create discounts', 'woocommerce-discount-manager' ),
						'href'  => admin_url( 'admin.php?page=wdm_options&tab=discounts' ),
					],
				],
			]
		);

		$wizard->set_lib_url( $plugin->get_dir_url() . '/dependencies/barn2/setup-wizard/' );
		$wizard->set_lib_path( $plugin->get_dir_path() . '/dependencies/barn2/setup-wizard/' );

		$wizard->add_edd_api( EDD_Licensing::class );
		$wizard->add_license_class( Plugin_License::class );

		$this->wizard = $wizard;
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$this->wizard->boot();
	}
}
