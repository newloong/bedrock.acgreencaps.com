<?php

namespace Barn2\Plugin\Discount_Manager;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Helper;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Plugin\Licensed_Plugin;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Plugin\Plugin_Activation_Listener;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Registerable;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Util;
use Barn2\Plugin\Discount_Manager\Dependencies\Setup_Wizard\Starter;

/**
 * Handles registration of the tables and default data on activation.
 *
 * @package   Barn2/woocommerce-discount-manager
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 * @codeCoverageIgnore
 */
class Plugin_Setup implements Plugin_Activation_Listener, Registerable {

	/**
	 * Plugin's entry file
	 *
	 * @var string
	 */
	private $file;

	/**
	 * Plugin instance
	 *
	 * @var Licensed_Plugin
	 */
	private $plugin; // @phpstan-ignore-line

	/**
	 * Wizard starter.
	 *
	 * @var Starter
	 */
	private $starter;

	/**
	 * Get things started
	 *
	 * @param string $file
	 * @param Licensed_Plugin $plugin
	 */
	public function __construct( $file, Licensed_Plugin $plugin ) {
		$this->file    = $file;
		$this->plugin  = $plugin;
		$this->starter = new Starter( $this->plugin );
	}

	/**
	 * Register the service
	 *
	 * @return void
	 */
	public function register() {
		register_activation_hook( $this->file, [ $this, 'on_activate' ] );
		add_action( 'admin_init', [ $this, 'after_plugin_activation' ] );
	}

	/**
	 * On plugin activation determine if the setup wizard should run.
	 *
	 * @return void
	 */
	public function on_activate() {
		// Network wide.
		// phpcs:disable
		$network_wide = ! empty( $_GET['networkwide'] )
			? (bool) $_GET['networkwide']
			: false;
		// phpcs:enable

		self::install_tables();
		self::disable_delete_data( $this->plugin );

		if ( $this->starter->should_start() && Util::is_woocommerce_active() ) {
			$this->starter->create_transient();
		}
	}

	/**
	 * Disable the delete data option.
	 *
	 * @param Licensed_Plugin $plugin
	 * @return void
	 */
	public static function disable_delete_data( $plugin ) {
		// Instantiate the helper.
		$options_helper = new Helper( $plugin->get_slug() );

		// Delete the option.
		$options_helper->delete_option( 'delete_data' );
	}

	/**
	 * Install database tables.
	 *
	 * @return void
	 */
	public static function install_tables() {
		$tables = [
			new Database\Discounts(),
			new Database\Discount_Settings(),
		];

		foreach ( $tables as $table ) {
			$table->register();
		}
	}

	/**
	 * Detect the transient and redirect to wizard.
	 *
	 * @return void
	 */
	public function after_plugin_activation() {
		if ( ! $this->starter->detected() ) {
			return;
		}

		$this->starter->delete_transient();
		$this->starter->redirect();
	}

	/**
	 * Do nothing.
	 *
	 * @return void
	 */
	public function on_deactivate() {}
}
