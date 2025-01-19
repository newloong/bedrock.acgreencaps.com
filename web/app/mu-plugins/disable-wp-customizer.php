<?php
/*
 * Plugin name: Disable Customizer
 * Description: Completely turn off customizer on your site.
 *
 * Plugin URI: 
 * Author URI: https://newloong.com
 * Author: Newloong LLC
 * Note:
 * Version: 2.3.0
 */

use function Env\env;

defined( 'ABSPATH' ) || exit;

Disable_WP_Customizer::init();

class Disable_WP_Customizer {
	
	public static function init(): void {
		if ('development' !== env('WP_ENV')) {
			add_filter( 'map_meta_cap', [ __CLASS__, 'map_meta_cap__remove_customize_capability' ], 10, 2 );
			add_action( 'admin_init', [ __CLASS__, 'on_admin_init' ], 10 );
		}
	}

	public static function on_admin_init(): void {
		remove_action( 'plugins_loaded', '_wp_customize_include', 10 );
		remove_action( 'admin_enqueue_scripts', '_wp_customize_loader_settings', 11 );
		add_action( 'load-customize.php', [ __CLASS__, 'on_load_customizer', ] );
		add_action('admin_head', function() { echo '<style>p.parent-theme,.customize-pane-parent .acontrol-panel-themes,.elementor-add-section-area-button.rey-templatesButton{display:none!important}</style>'; });
        remove_submenu_page('rey-dashboard','rey-importer-manager'); 
        remove_submenu_page('rey-dashboard','rey-modules-manager'); 
        remove_submenu_page('rey-dashboard','rey-plugins-manager');
	
	}

	public static function map_meta_cap__remove_customize_capability( $caps, $cap ) {
		return ( $cap === 'customize' ) ? [ 'do_not_allow' ] : $caps;
	}

	public static function on_load_customizer(): void {
		/** @noinspection ForgottenDebugOutputInspection */
		wp_die( 'The Customizer is currently disabled.' );
	}

}
