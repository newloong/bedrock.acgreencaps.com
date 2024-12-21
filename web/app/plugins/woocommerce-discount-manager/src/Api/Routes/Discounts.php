<?php

namespace Barn2\Plugin\Discount_Manager\Api\Routes;

use Barn2\Plugin\Discount_Manager\Admin\Validator;
use Barn2\Plugin\Discount_Manager\Cache as Discount_ManagerCache;
use Barn2\Plugin\Discount_Manager\Dependencies\Axiom\Collections\Collection;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Cache;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Rest\Base_Route;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Rest\Route;
use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Util;
use Barn2\Plugin\Discount_Manager\Entities\Discount;
use Barn2\Plugin\Discount_Manager\Entities\Discount_Setting;
use Barn2\Plugin\Discount_Manager\Traits\Api_Provide_Error_Response;
use Barn2\Plugin\Discount_Manager\Util as Discount_ManagerUtil;
use WP_REST_Response;
use WP_REST_Request;
use WP_REST_Server;

use function Barn2\Plugin\Discount_Manager\wdm;

/**
 * Registers the discounts route.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Discounts extends Base_Route implements Route {

	use Api_Provide_Error_Response;

	protected $rest_base = 'discounts';

	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		// Get discounts.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_discounts' ],
					'permission_callback' => [ $this, 'permission_callback' ],
				],
			]
		);

		// Duplicate discount.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/duplicate',
			[
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'duplicate' ],
					'permission_callback' => [ $this, 'permission_callback' ],
				],
			]
		);

		// Update discounts.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update' ],
					'permission_callback' => [ $this, 'permission_callback' ],
				],
			]
		);

		// Toggle status of a discount.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/toggle',
			[
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'toggle' ],
					'permission_callback' => [ $this, 'permission_callback' ],
				],
			]
		);

		// Reorder discounts.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/reorder',
			[
				'args' => [
					'reorder' => [
						'type'        => 'array',
						'required'    => true,
						'description' => __( 'An array of discount_id => menu_order data.', 'woocommerce-discount-manager' ),
					],
				],
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'reorder' ],
					'permission_callback' => [ $this, 'permission_callback' ],
				],
			]
		);

		// Delete discount.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete' ],
					'permission_callback' => [ $this, 'permission_callback' ],
				],
			]
		);
	}

	/**
	 * Returns the list of discounts from the database.
	 *
	 * @return WP_REST_Response
	 */
	public function get_discounts(): WP_REST_Response {
		$orm       = wdm()->orm();
		$discounts = $orm( Discount::class )
			->orderBy( 'priority' )
			->with( 'settings' )
			->all();

		return new WP_REST_Response( $discounts, 200 );
	}

	/**
	 * Reorder discount rules.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function reorder( WP_REST_Request $request ): WP_REST_Response {
		$reorder_map = $request->get_param( 'reorder' );
		$orm         = wdm()->orm();
		$discounts   = $orm( Discount::class )->findAll( ...array_values( $reorder_map ) );

		if ( empty( $discounts ) ) {
			return $this->send_error_response( __( 'Something went wrong with reodering. No discounts have been found.', 'woocommerce-discount-manager' ) );
		}

		foreach ( $reorder_map as $index => $discount_id ) {
			/** @var Discount $discount */
			foreach ( $discounts as $discount ) {

				if ( ! $discount || ! $discount instanceof Discount ) {
					return $this->send_error_response( __( 'Something went wrong with reodering. Please try again later.', 'woocommerce-discount-manager' ) );
				}

				if ( $discount->id() === $discount_id ) {
					$discount->set_priority( $index );
					$orm->save( $discount );
				}
			}
		}

		$this->clean_cache();

		$discounts = $orm( Discount::class )
			->orderBy( 'priority' )
			->with( 'settings' )
			->all();

		/**
		 * Action: fired after the discounts have been reordered.
		 *
		 * @param array $discounts The list of discounts.
		 */
		do_action( 'wdm_discounts_reordered', $discounts );

		return new WP_REST_Response(
			[
				'all' => $discounts,
			],
			200
		);
	}

	/**
	 * Set the discout as enabled or disabled.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function toggle( WP_REST_Request $request ): WP_REST_Response {

		$discount_id = $request->get_param( 'discount' );
		$enabled     = $request->get_param( 'enabled' );
		$orm         = wdm()->orm();

		$discount = $orm( Discount::class )->find( $discount_id );

		if ( ! $discount || ! $discount instanceof Discount ) {
			return $this->send_error_response( __( 'Could not update the status of the discount. Discount not found.', 'woocommerce-discount-manager' ) );
		}

		$discount->set_enabled( $enabled );

		$orm->save( $discount );

		$this->clean_cache();

		/**
		 * Action: fired after the activation status of a discount
		 * has been toggled.
		 *
		 * @param Discount $discount
		 * @param bool $enabled
		 */
		do_action( 'wdm_discount_status_toggled', $discount, $enabled );

		return new WP_REST_Response( $discount, 200 );
	}

	/**
	 * Delete discounts via the api.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function delete( WP_REST_Request $request ): WP_REST_Response {
		$discount_id = $request->get_param( 'id' );
		$orm         = wdm()->orm();
		$discount    = $orm( Discount::class )->find( $discount_id );

		// If the discount is a temporary one, just return true.
		if ( $this->is_temporary_id( $discount_id ) ) {
			return new WP_REST_Response( true, 200 );
		}

		if ( ! $discount || ! $discount instanceof Discount ) {
			return $this->send_error_response( __( 'Something went wrong: could not find the discount.', 'woocommerce-discount-manager' ) );
		}

		$name = $discount->get_name();

		$orm->delete( $discount );

		$this->clean_cache();

		/**
		 * Action: fired after a discount is deleted.
		 *
		 * @param string|int $discount_id
		 */
		do_action( 'wdm_discount_deleted', $discount_id );

		return new WP_REST_Response(
			[
				'name' => $name,
				'id'   => $discount_id,
			],
			200
		);
	}

	/**
	 * Create a discount.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function create( WP_REST_Request $request ): WP_REST_Response {
		$priority = $request->get_param( 'priority' );

		/** @var Discount $discount */
		$discount = Discount::create(
			[
				'priority' => $priority,
			]
		);

		/**
		 * Action: fired after a discount is created.
		 *
		 * @param Discount $discount The newly created discount.
		 */
		do_action( 'wdm_discount_created', $discount );

		return new WP_REST_Response( $discount, 200 );
	}

	/**
	 * Duplicate a discount.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function duplicate( WP_REST_Request $request ): WP_REST_Response {
		$discount_id = $request->get_param( 'discount_id' );
		$orm         = wdm()->orm();
		$discount    = $orm( Discount::class )->find( $discount_id );

		if ( ! $discount || ! $discount instanceof Discount ) {
			return $this->send_error_response( __( 'Something went wrong: could not find the discount.', 'woocommerce-discount-manager' ) );
		}

		$source_discount = $discount;
		$source_settings = $source_discount->settings();

		/** @var Discount $cloned_discount */
		$cloned_discount = $orm->create( Discount::class );
		$cloned_discount->set_name( $source_discount->get_name() . ' - ' . __( 'Copy', 'woocommerce-discount-manager' ) );
		$cloned_discount->set_slug( $source_discount->get_slug() . '-copy' );
		$cloned_discount->set_priority( -1 );
		$cloned_discount->set_enabled( true );

		$orm->save( $cloned_discount );

		foreach ( $source_settings as $source_setting ) {
			$key   = $source_setting->key();
			$value = $source_setting->value();

			/** @var Discount_Setting $cloned_setting */
			$cloned_setting = $orm->create( Discount_Setting::class );
			$cloned_setting->set_discount_id( $cloned_discount->id() );
			$cloned_setting->set_key( $key );
			$cloned_setting->set_value( maybe_serialize( $value ) );

			$orm->save( $cloned_setting );
		}

		/**
		 * Action: fired after a discount is duplicated.
		 *
		 * @param Discount $cloned_discount The newly created discount.
		 */
		do_action( 'wdm_discount_duplicated', $cloned_discount );

		$discounts = $orm( Discount::class )
			->orderBy( 'priority' )
			->with( 'settings' )
			->all();

		return new WP_REST_Response(
			[
				'source' => $source_discount,
				'cloned' => $cloned_discount,
				'all'    => $discounts,
			],
			200
		);
	}

	/**
	 * Update a discount.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function update( WP_REST_Request $request ): WP_REST_Response {
		$discount_id = $request->get_param( 'discount_id' );
		$settings    = $request->get_param( 'values' );

		if ( ! $discount_id ) {
			return $this->send_error_response( __( 'Something went wrong: no discount was provided.', 'woocommerce-discount-manager' ) );
		}

		$submitted_settings = $this->prepare_submitted_settings( Util::clean( $settings ) );
		$submitted_settings = Collection::make( $submitted_settings );
		$validator          = new Validator( $submitted_settings->all() );

		if ( empty( $submitted_settings ) ) {
			return $this->send_error_response( __( 'Something went wrong while updating the discount. No data was submitted.', 'woocommerce-discount-manager' ) );
		}

		$validator->validate();

		if ( $validator->has_errors() ) {
			$name = empty( $submitted_settings->get( 'name' ) ) ? __( 'No name', 'woocommerce-discount-manager' ) : $submitted_settings->get( 'name' );

			$message = sprintf(
				/* translators: %1$s: discount name, %2$s: error message */
				__( 'Something went wrong while updating the "%1$s" discount: %2$s', 'woocommerce-discount-manager' ),
				$name,
				$validator->get_error_message()
			);
			return $this->send_error_response( $message );
		}

		$orm = wdm()->orm();

		if ( ! is_numeric( $discount_id ) ) {
			$discount = Discount::create();
		} else {
			$discount = $orm( Discount::class )->find( $discount_id );
		}

		if ( ! $discount instanceof Discount ) {
			return $this->send_error_response( __( 'Something went wrong while updating the discount. The discount was not found.', 'woocommerce-discount-manager' ) );
		}

		// These come from the React Row data. Needed because of the workarounds that we're doing to
		// make the React Table work as required by the brief.
		$disallowed_settings = [ 'id', 'slug', 'priority', 'enabled', 'conditions', 'settings' ];

		// Remove disallowed settings.
		foreach ( $disallowed_settings as $disallowed_setting ) {
			$submitted_settings->remove( $disallowed_setting );
		}

		// Grab discount name.
		$discount_name = $submitted_settings->get( 'name' );

		// Remove "name" from the list of settings to be saved.
		$submitted_settings->remove( 'name' );

		// The "text" setting needs to be sanitized differently.
		$should_sanitized_text = Discount_ManagerUtil::should_escape_discount_text();
		$text_setting_value    = isset( $settings['text'] ) ? $settings['text'] : '';
		$text_setting_value    = $should_sanitized_text ? wp_kses_post( $text_setting_value ) : $text_setting_value;

		// Override the value of the "text" setting into the collection.
		$submitted_settings->put( 'text', $text_setting_value );

		$settings = $discount->settings();

		foreach ( $submitted_settings->all() as $key => $value ) {
			$setting = $settings->get( $key );
			$value   = maybe_serialize( $value );

			if ( $setting instanceof Discount_Setting ) {
				$setting->set_value( $value );
			} else {
				/** @var Discount_Setting $setting */
				$setting = $orm->create(
					Discount_Setting::class,
					[
						'key'   => $key,
						'value' => $value,
					]
				);

				$setting->set_discount( $discount );
			}

			$orm->save( $setting );
		}

		$discount->set_name( $discount_name );
		$discount->set_slug( sanitize_title( $discount_name ) );

		$saved = $orm->save( $discount );

		// Get a new copy.
		$discount = $orm( Discount::class )->find( $discount->id() );

		/**
		 * Action: fired after a discount is updated.
		 *
		 * @param Discount $discount The updated discount.
		 */
		do_action( 'wdm_discount_updated', $discount );

		$this->clean_cache();

		$orm       = wdm()->orm();
		$discounts = $orm( Discount::class )
			->orderBy( 'priority' )
			->with( 'settings' )
			->all();

		return new WP_REST_Response(
			[
				'discount' => $discount,
				'all'      => $discounts,
			],
			200
		);
	}

	/**
	 * Prepare submitted settings by merging them with the defaults before saving.
	 *
	 * @param array $submitted_settings The submitted settings.
	 * @return array The prepared settings.
	 */
	private function prepare_submitted_settings( array $submitted_settings ): array {
		$submitted_settings = Util::clean( $submitted_settings );
		$type               = isset( $submitted_settings['type'] ) ? $submitted_settings['type'] : 'simple';
		$discount_type      = wdm()->types()->get_by_slug( $type );

		if ( ! empty( $discount_type ) && isset( $discount_type['defaults'] ) ) {
			$submitted_settings = array_merge( $discount_type['defaults'], $submitted_settings );
		}

		return $submitted_settings;
	}

	/**
	 * Determine if the given ID is a temporary ID.
	 *
	 * @param string|int $id
	 * @return boolean
	 */
	private function is_temporary_id( $id ): bool {
		return ! is_numeric( $id );
	}

	/**
	 * Clean the cached list of discounts.
	 *
	 * @return void
	 */
	private function clean_cache(): void {
		Cache::forget( Discount_ManagerCache::DISCOUNTS_CACHE_KEY );
	}

	/**
	 * Determine if route can be accessed.
	 *
	 * @return boolean
	 */
	public function permission_callback(): bool {
		return current_user_can( 'manage_woocommerce' );
	}
}
