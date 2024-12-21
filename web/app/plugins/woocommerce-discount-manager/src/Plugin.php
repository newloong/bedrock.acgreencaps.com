<?php

namespace Barn2\Plugin\Discount_Manager;

use Barn2\Plugin\Discount_Manager\Admin\Wizard\Setup_Wizard;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\Database;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\EntityManager;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Plugin\Licensed_Plugin;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Plugin\Premium_Plugin;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Registerable;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Service_Provider;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Traits\Check_WP_Requirements;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Translatable;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Util;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\WooCommerce\Templates;

/**
 * The main plugin class.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Plugin extends Premium_Plugin implements Licensed_Plugin, Registerable, Translatable, Service_Provider {

	use Check_WP_Requirements;

	const NAME    = 'WooCommerce Discount Manager';
	const ITEM_ID = 518670;

	/**
	 * List of services.
	 *
	 * @var array
	 */
	private $services = [];

	/**
	 * Constructs and initalizes the main plugin class.
	 *
	 * @param string $file The main plugin file.
	 * @param string $version The current plugin version.
	 */
	public function __construct( $file = null, $version = '1.0.0' ) {
		parent::__construct(
			[
				'name'               => self::NAME,
				'item_id'            => self::ITEM_ID,
				'version'            => $version,
				'file'               => $file,
				'is_woocommerce'     => true,
				'settings_path'      => 'admin.php?page=wdm_options&tab=general',
				'documentation_path' => 'kb-categories/discount-manager-kb',
			]
		);

		// We create Plugin_Setup here so the plugin activation hook will run.
		$this->add_service( 'plugin_setup', new Plugin_Setup( $this->get_file(), $this ), true );
	}

	/**
	 * Hook into WordPress
	 *
	 * @return void
	 */
	public function register(): void {
		parent::register();

		Util::declare_hpos_compatibility( $this->get_file() );

		add_action( 'plugins_loaded', [ $this, 'maybe_load_plugin' ] );

		add_action( 'init', [ $this, 'register_services' ] );
		add_action( 'init', [ $this, 'load_textdomain' ] );
	}

	/**
	 * Maybe boot the plugin after requirements are met.
	 *
	 * @return void
	 */
	public function maybe_load_plugin(): void {
		if ( ! $this->check_wp_requirements( '5.2', $this ) ) {
			return;
		}

		// Don't load anything if WooCommerce not active.
		if ( ! $this->check_wc_requirements( '5.9', $this ) ) {
			return;
		}

		$db = new Database();

		$services = [
			'setup_wizard'     => new Setup_Wizard( $this ),
			'rest_controller'  => new Api\Rest_Controller(),
			'settings'         => new Admin\Settings_Controller( $this ),
			'admin_controller' => new Admin\Admin_Controller( $this ),
			'database'         => $db,
			'orm'              => new EntityManager( $db ),
			'types'            => new Types_Manager(),
		];

		$additional_services = [
			'cache'            => new Cache(),
			'cart'             => new Cart(),
			'orders'           => new Orders(),
			'products'         => new Products(),
			'templates'        => Template_Loader_Factory::create(),
			'assets'           => new Assets(),
			'shortcodes'       => new Shortcodes(),
			'integration_wro'  => new Integrations\Restaurant_Ordering(),
			'integration_wqv'  => new Integrations\Quick_View_Pro(),
			'integration_wbv'  => new Integrations\Bulk_Variations(),
			'integration_wfc'  => new Integrations\Fast_Cart(),
			'integration_wpo'  => new Integrations\Product_Options(),
			'integration_wpt'  => new Integrations\Product_Table(),
			// 'integration_wvp' => new Integrations\Variation_Prices(),
			'integration_wwp'  => new Integrations\Wholesale_Pro(),
			'integration_divi' => new Integrations\Divi(),
		];

		foreach ( $services as $name => $service ) {
			$this->add_service( $name, $service );
		}

		if ( $this->has_valid_license() ) {
			foreach ( $additional_services as $name => $service ) {
				$this->add_service( $name, $service );
			}
		}
	}

	/**
	 * Make plugin translatable
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain( 'woocommerce-discount-manager', false, $this->get_slug() . '/languages' );
	}

	/**
	 * Returns the query builder.
	 *
	 * @return Database
	 */
	public function db() {
		return $this->get_service( 'db' );
	}

	/**
	 * Returns the ORM.
	 *
	 * @return EntityManager
	 */
	public function orm() {
		return $this->get_service( 'orm' );
	}

	/**
	 * Returns access to the types manager class.
	 *
	 * @return Types_Manager
	 */
	public function types() {
		return $this->get_service( 'types' );
	}

	/**
	 * Returns access to the cart class.
	 *
	 * @return Products
	 */
	public function products() {
		return $this->get_service( 'products' );
	}

	/**
	 * Returns access to the cache class.
	 *
	 * @return Cache
	 */
	public function cache() {
		return $this->get_service( 'cache' );
	}

	/**
	 * Returns access to the template loader class.
	 *
	 * @return Templates
	 */
	public function templates() {
		return $this->get_service( 'templates' );
	}
}
