<?php

namespace Barn2\Plugin\Discount_Manager\Admin;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Settings_Manager;
use Barn2\Plugin\Discount_Manager\Plugin;

/**
 * Handles registration of the settings manager
 * that powers the settings page.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Settings_Controller {
	/**
	 * @var Settings_Manager
	 */
	protected $manager;

	/**
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * Get things started.
	 *
	 * @param Plugin $plugin
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		$this->register();
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'init', [ $this, 'configure_manager' ] );
	}

	/**
	 * Get the settings manager.
	 *
	 * @return Settings_Manager
	 */
	public function get_manager(): Settings_Manager {
		return $this->manager;
	}

	/**
	 * Configure the settings manager.
	 *
	 * @return void
	 */
	public function configure_manager(): void {
		$settings_manager = new Settings_Manager( $this->plugin );
		$settings_manager->set_library_path( $this->plugin->get_dir_path( 'dependencies/barn2/settings-api' ) );
		$settings_manager->set_library_url( $this->plugin->get_dir_url( 'dependencies/barn2/settings-api' ) );

		$settings_manager->setup( $this->get_settings_config() );

		$this->manager = $settings_manager;

		$this->manager->boot();
	}

	/**
	 * Get the settings config.
	 *
	 * @return array
	 */
	private function get_settings_config(): array {
		return [
			[
				'id'       => 'general',
				'title'    => esc_html__( 'Discount Manager', 'woocommerce-discount-manager' ),
				'sections' => [
					[
						'id'          => 'general',
						'title'       => esc_html__( 'Discount Manager', 'woocommerce-discount-manager' ),
						'description' => esc_html__( 'The following options control the WooCommerce Discount Manager extension.', 'woocommerce-discount-manager' ),
						'fields'      => [
							[
								'name'  => 'license',
								'label' => esc_html__( 'License key', 'woocommerce-discount-manager' ),
								'type'  => 'license',
							],
						],
					],
				],
			],
		];
	}
}
