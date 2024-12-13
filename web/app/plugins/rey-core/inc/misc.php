<?php
namespace ReyCore;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Misc {

	public function __construct(){
		add_action('wp_loaded', [$this , 'prevent_wc_login_reg_process'], 0);
		add_action( 'plugins_loaded', [$this, 'revslider_fix_import_notices_php819'], 0);
		$this->wp_rocket();
	}

	/**
	 * Helps Ajax Login from Rey.
	 * Added here to be loaded before plugins_loaded hook.
	 * @since 1.7.0
	 */
	public function prevent_wc_login_reg_process(){

		if(
			wp_doing_ajax() &&
			isset( $_REQUEST[\ReyCore\Ajax::ACTION_KEY] ) &&
			$_REQUEST[\ReyCore\Ajax::ACTION_KEY] === 'account_forms'
		){
			remove_action( 'wp_loaded', ['WC_Form_Handler', 'process_login'], 20 );
			remove_action( 'wp_loaded', ['WC_Form_Handler', 'process_registration'], 20 );
			remove_action( 'wp_loaded', ['WC_Form_Handler', 'process_lost_password'], 20 );
		}

	}

	public function wp_rocket(){

		// disable cache for empty cart because Rey delays it already
		add_filter( 'rocket_cache_wc_empty_cart', '__return_false' );

	}

	private static function get_json_data($fname){

		$data = [];

		$data_json_file = sprintf( '%sinc/libs/sample/%s.json', REY_CORE_DIR, $fname);

		$wp_filesystem = reycore__wp_filesystem();

		if(	$wp_filesystem->is_file( $data_json_file ) ) {
			if( $json_raw = $wp_filesystem->get_contents( $data_json_file ) ){
				$data = json_decode($json_raw, true );
			}
		}

		if( empty($data) ){
			return $data;
		}

		return reycore__clean($data);
	}

	function revslider_fix_import_notices_php819(){

		if( ! class_exists('\RevSliderPluginUpdate') ){
			return;
		}

		if( ! (isset($_REQUEST['reycore-ajax']) && 'run_import' === $_REQUEST['reycore-ajax']) ){
			return;
		}

		remove_action('plugins_loaded', ['RevSliderPluginUpdate', 'do_update_checks']);
	}

}

new Misc();
