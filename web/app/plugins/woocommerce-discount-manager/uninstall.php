<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$settings = get_option( 'woocommerce-discount-manager_settings', [] );

if ( ! isset( $settings['delete_data'] ) || ! $settings['delete_data'] ) {
	return;
}

$options_to_delete = [
	'woocommerce-discount-manager_settings',
	'barn2_plugin_license_518670',
	'barn2_plugin_promo_518670',
	'barn2_plugin_review_banner_518670',
];

foreach ( $options_to_delete as $option ) {
	delete_option( $option );
}

global $wpdb;

$tables_to_delete = [
	"{$wpdb->prefix}wdm_discount_settings",
	"{$wpdb->prefix}wdm_discounts",
];

foreach ( $tables_to_delete as $table ) {
	$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
}
