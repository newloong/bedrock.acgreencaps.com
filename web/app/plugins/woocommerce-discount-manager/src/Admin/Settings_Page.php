<?php

namespace Barn2\Plugin\Discount_Manager\Admin;

use Barn2\Plugin\Discount_Manager\Admin\Settings_Tab\Discounts;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Conditional;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Plugin\Licensed_Plugin;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Registerable;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Util;
use Barn2\Plugin\Discount_Manager\Util as Plugin_Util;

use function Barn2\Plugin\Discount_Manager\wdm;

/**
 * Handles registration of the settings page.
 *
 * @package   Barn2/woocommerce-discount-manager
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 * @codeCoverageIgnore
 */
class Settings_Page implements Registerable, Conditional {

	/**
	 * Plugin handling the page.
	 *
	 * @var Licensed_Plugin
	 */
	public $plugin;

	/**
	 * License handler.
	 *
	 * @var License
	 */
	public $license;

	/**
	 * List of settings.
	 *
	 * @var array
	 */
	public $registered_settings = [];

	/**
	 * Constructor.
	 *
	 * @param Licensed_Plugin $plugin
	 */
	public function __construct( Licensed_Plugin $plugin ) {
		$this->plugin              = $plugin;
		$this->license             = $plugin->get_license();
		$this->registered_settings = $this->get_settings_tabs();
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_required() {
		return Util::is_admin();
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$this->register_settings_tabs();

		add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Retrieves the settings tab classes.
	 *
	 * @return array
	 */
	private function get_settings_tabs(): array {
		$settings_tabs = [
			Settings_Tab\Discounts::TAB_ID => new Settings_Tab\Discounts( $this->plugin ),
			Settings_Tab\General::TAB_ID   => new Settings_Tab\General( $this->plugin ),
		];

		/**
		 * Filters the settings tabs in the settings page.
		 *
		 * @param array $settings_tabs The settings tabs.
		 * @param Licensed_Plugin $this->plugin The plugin.
		 * @return array
		 */
		return apply_filters( 'wdm_settings_tabs', $settings_tabs, $this->plugin );
	}

	/**
	 * Register the settings tab classes.
	 *
	 * @return void
	 */
	private function register_settings_tabs(): void {
		array_map(
			function ( $setting_tab ) {
				if ( $setting_tab instanceof Registerable ) {
					$setting_tab->register();
				}
			},
			$this->registered_settings
		);
	}

	/**
	 * Enqueue reg for the settings page.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		$screen = get_current_screen();

		if ( ! Plugin_Util::string_ends_with( $screen->id, 'wdm_options' ) ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style( 'wdm-settings-page' );
		wp_enqueue_script( 'wdm-settings-page' );
		wp_enqueue_editor();
	}

	/**
	 * Register the settings page.
	 *
	 * @return void
	 */
	public function add_settings_page() {
		add_submenu_page(
			'woocommerce-marketing',
			__( 'Discounts', 'woocommerce-discount-manager' ),
			__( 'Discounts', 'woocommerce-discount-manager' ),
			'manage_woocommerce',
			'wdm_options',
			[ $this, 'render_settings_page' ]
		);
	}

	/**
	 * Renders the settings page.
	 *
	 * @return void
	 */
	public function render_settings_page(): void {
		$active_tab = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ?? Discounts::TAB_ID;

		if ( $active_tab === 'general' ) {
			$settings_manager = wdm()->get_service( 'settings' );
			$settings_manager->get_manager()->register_and_enqueue_assets();
		}

		$is_renderable = array_key_exists( $active_tab, $this->registered_settings ) && $this->registered_settings[ $active_tab ] instanceof Settings_Tab\Renderable;

		?>
		<div class='woocommerce-layout__header'>
			<div class="woocommerce-layout__header-wrapper">
				<h3 class='woocommerce-layout__header-heading'>
					<?php esc_html_e( 'WooCommerce Discount Manager', 'woocommerce-discount-manager' ); ?>
				</h3>
				<div class="links-area">
					<?php $this->support_links(); ?>
				</div>
			</div>
		</div>

		<div class="wrap barn2-settings">

			<?php do_action( 'barn2_before_plugin_settings', $this->plugin->get_id() ); ?>

			<div class="barn2-settings-inner">

				<h2 class="nav-tab-wrapper">
					<?php
					foreach ( $this->registered_settings as $setting_tab ) {
						$active_class = $active_tab === $setting_tab::TAB_ID ? ' nav-tab-active' : '';
						?>
							<a href="<?php echo esc_url( add_query_arg( 'tab', $setting_tab::TAB_ID, $this->plugin->get_settings_page_url() ) ); ?>" class="<?php echo esc_attr( sprintf( 'nav-tab%s', $active_class ) ); ?>">
								<?php echo esc_html( $setting_tab->get_title() ); ?>
							</a>
							<?php
					}
					?>
				</h2>

				<div class="barn2-inside-wrapper">
					<?php if ( $is_renderable ) : ?>
						<?php echo $this->registered_settings[ $active_tab ]->output(); //phpcs:ignore ?>
					<?php else : ?>
						<?php
							$settings_manager = wdm()->get_service( 'settings' );
							$settings_manager->get_manager()->render_settings();
						?>
					<?php endif; ?>
				</div>

			</div>

			<?php do_action( 'barn2_after_plugin_settings', $this->plugin->get_id() ); ?>
		</div>
		<?php
	}

	/**
	 * Support links for the settings page.
	 *
	 * @return void
	 */
	public function support_links(): void {
		printf(
			'<p>%s | %s | %s</p>',
            // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
			Util::format_link( $this->plugin->get_documentation_url(), __( 'Documentation', 'woocommerce-discount-manager' ), true ),
			Util::format_link( $this->plugin->get_support_url(), __( 'Support', 'woocommerce-discount-manager' ), true ),
			sprintf(
				'<a class="barn2-wiz-restart-btn" href="%s">%s</a>',
				add_query_arg( [ 'page' => $this->plugin->get_slug() . '-setup-wizard' ], admin_url( 'admin.php' ) ),
				__( 'Setup wizard', 'woocommerce-discount-manager' )
			)
            // phpcs:enable
		);
	}
}
