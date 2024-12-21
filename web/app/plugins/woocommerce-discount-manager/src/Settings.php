<?php

namespace Barn2\Plugin\Discount_Manager;

/**
 * Handles plugin settings.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Settings {

	// The option name for the plugin settings.
	const OPTION_NAME = 'wdm_settings';

	/**
	 * Get an option
	 * Looks to see if the specified setting exists, returns default if not.
	 *
	 * @param string $key the key to retrieve.
	 * @param mixed  $default default value to use in case option is not available.
	 * @return mixed
	 */
	public static function get_option( $key = '', $default = false ) {
		$plugin_options = get_option( self::OPTION_NAME, [] );
		$value          = ! empty( $plugin_options[ $key ] )
			? $plugin_options[ $key ]
			: $default;

		/**
		 * Filters the retrieval of an option.
		 *
		 * @param mixed $value the original value.
		 * @param string $key the key of the option being retrieved.
		 * @param mixed $default default value if nothing is found.
		 */
		$value = apply_filters( 'wdm_get_option', $value, $key, $default );
		return apply_filters( 'wdm_get_option_' . $key, $value, $key, $default );
	}

	/**
	 * Update an option.
	 *
	 * Updates an etting value in both the db and the global variable.
	 * Warning: Passing in an empty, false or null string value will remove
	 *          the key from the options array.
	 *
	 * @param string          $key         The Key to update.
	 * @param string|bool|int $value       The value to set the key to.
	 * @param bool $bypass_cap whether or not the capability check should be bypassed.
	 * @return boolean True if updated, false if not.
	 */
	public static function update_option( $key = '', $value = false, $bypass_cap = false ) {

		if ( ! current_user_can( 'manage_options' ) && ! $bypass_cap ) {
			return false;
		}

		// If no key, exit.
		if ( empty( $key ) ) {
			return false;
		}

		if ( empty( $value ) ) {
			$remove_option = self::delete_option( $key );
			return $remove_option;
		}

		// First let's grab the current settings.
		$options = get_option( self::OPTION_NAME, [] );

		/**
		 * Filter the final value of an option before being saved into the database.
		 *
		 * @param mixed $value the value about to be saved.
		 * @param string $key the key of the option that is being saved.
		 */
		$value = apply_filters( 'wdm_update_option', $value, $key );

		// Next let's try to update the value.
		$options[ $key ] = $value;
		$did_update      = update_option( self::OPTION_NAME, $options );

		return $did_update;
	}

	/**
	 * Removes a setting value in the database.
	 *
	 * @param string $key         The Key to delete.
	 * @return boolean True if removed, false if not.
	 */
	public static function delete_option( $key = '' ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		// If no key, exit.
		if ( empty( $key ) ) {
			return false;
		}

		// First let's grab the current settings.
		$options = get_option( self::OPTION_NAME, [] );

		// Next let's try to update the value.
		if ( isset( $options[ $key ] ) ) {
			unset( $options[ $key ] );
		}

		$did_update = update_option( self::OPTION_NAME, $options );

		return $did_update;
	}

}
