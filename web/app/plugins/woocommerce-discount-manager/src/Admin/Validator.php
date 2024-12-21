<?php

namespace Barn2\Plugin\Discount_Manager\Admin;

use Barn2\Plugin\Discount_Manager\Dependencies\Axiom\Collections\Collection;
use DateTime;
use WP_Error;

use function Barn2\Plugin\Discount_Manager\wdm;

/**
 * Class that helps validating settings submitted when creating
 * discounts through the admin panel.
 *
 * @package   Barn2\woocommerce-discount-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Validator {

	/**
	 * Collection of data submitted.
	 *
	 * @var Collection
	 */
	protected $data;

	/**
	 * List of errors found.
	 *
	 * @var WP_Error
	 */
	protected $errors;

	/**
	 * Initialize the validator by storing the data submitted.
	 *
	 * @param array $data
	 */
	public function __construct( array $data ) {
		$this->data   = Collection::make( $data );
		$this->errors = new WP_Error();
	}

	/**
	 * Validate submitted data.
	 *
	 * @return self
	 */
	public function validate(): self {
		$this->data->each(
			function ( $item, $key ) {
				$method = 'validate_' . $key;

				if ( method_exists( $this, $method ) ) {
					$this->$method();
				}
			}
		);
		return $this;
	}

	/**
	 * Determine if the validator has found errors.
	 *
	 * @return boolean
	 */
	public function has_errors(): bool {
		return $this->errors->has_errors();
	}

	/**
	 * Add an error to the list.
	 *
	 * @param string $code
	 * @param string $message
	 * @return self
	 */
	public function add_error( string $code, string $message ): self {
		$this->errors->add( $code, $message );

		return $this;
	}

	/**
	 * Retrieves all error messages.
	 *
	 * @return string
	 */
	public function get_error_message(): string {
		return $this->errors->get_error_message();
	}

	/**
	 * Validate that a property isn't empty.
	 *
	 * @param string $property
	 * @param string $message
	 * @return void
	 */
	private function validate_empty_property( string $property, string $message ): void {
		if ( empty( $this->data->get( $property ) ) || ! $this->data->has( $property ) ) {
			$this->add_error( 'cannot-be-empty', $message );
		}
	}

	/**
	 * Validate the discount type.
	 *
	 * @return void
	 */
	private function validate_name(): void {
		$this->validate_empty_property( 'name', __( 'Please assign a name to this discount.', 'woocommerce-discount-manager' ) );
	}

	/**
	 * Validate which products are selected.
	 *
	 * @return void
	 */
	private function validate_which_products(): void {
		$which_products      = $this->data->get( 'which_products' );
		$selected_products   = $this->data->get( 'selected_products' ) ?? [];
		$selected_categories = $this->data->get( 'selected_categories' ) ?? [];

		if ( ! is_array( $selected_products ) ) {
			$selected_products = [];
		}

		if ( ! is_array( $selected_categories ) ) {
			$selected_categories = [];
		}

		if ( empty( $which_products ) ) {
			$this->add_error( 'which-products', __( 'Please select which products this discount applies to.', 'woocommerce-discount-manager' ) );
		} elseif ( $which_products === 'products' && empty( $selected_products ) ) {
			$this->add_error( 'selected-products', __( 'Please select at least one product.', 'woocommerce-discount-manager' ) );
		} elseif ( $which_products === 'categories' && empty( $selected_categories ) ) {
			$this->add_error( 'selected-categories', __( 'Please select at least one category.', 'woocommerce-discount-manager' ) );
		}
	}

	/**
	 * Validate the "Applies to" field.
	 *
	 * @return void
	 */
	private function validate_applies_to(): void {
		$applies_to = $this->data->get( 'applies_to' );

		if ( empty( $applies_to ) ) {
			$this->add_error( 'applies-to', __( 'Please select what this discount applies to.', 'woocommerce-discount-manager' ) );
		}

		if ( $applies_to === 'user_roles' ) {
			$user_roles = $this->data->get( 'applies_to_roles' );

			if ( empty( $user_roles ) ) {
				$this->add_error( 'user-roles', __( 'Please select at least one user role.', 'woocommerce-discount-manager' ) );
			}
		}

		if ( $applies_to === 'users' ) {
			$users = $this->data->get( 'applies_to_users' );

			if ( empty( $users ) ) {
				$this->add_error( 'specific-users', __( 'Please select at least one user.', 'woocommerce-discount-manager' ) );
			}
		}
	}

	/**
	 * Validate the "Availability" dates field.
	 *
	 * @return void
	 */
	private function validate_availability(): void {
		$availability = $this->data->get( 'availability' );

		if ( empty( $availability ) ) {
			$this->add_error( 'availability', __( 'Please select when this discount is available.', 'woocommerce-discount-manager' ) );
		}

		if ( $availability === 'dates' ) {
			$from_date = $this->data->get( 'start_date' );
			$to_date   = $this->data->get( 'end_date' );

			if ( empty( $from_date ) ) {
				$this->add_error( 'from-date', __( 'Please select a start date.', 'woocommerce-discount-manager' ) );
			}

			if ( empty( $to_date ) ) {
				$this->add_error( 'to-date', __( 'Please select an end date.', 'woocommerce-discount-manager' ) );
			}

			$from_date = DateTime::createFromFormat( 'd-m-Y', $from_date );
			$to_date   = DateTime::createFromFormat( 'd-m-Y', $to_date );

			if ( $from_date > $to_date ) {
				$this->add_error( 'from-date', __( 'The start date must be before the end date.', 'woocommerce-discount-manager' ) );
			}

			if ( $from_date === $to_date ) {
				$this->add_error( 'from-date', __( 'The start date and end date cannot be the same.', 'woocommerce-discount-manager' ) );
			}
		}
	}

	/**
	 * Validate the "discount type" field.
	 * This will also validate the settings for the selected discount type.
	 *
	 * @return void
	 */
	private function validate_type(): void {
		$selected_type = $this->data->get( 'type' );
		$type          = wdm()->types()->get_by_slug( $selected_type );

		if ( ! $type ) {
			$this->add_error( 'type', __( 'Please select a valid discount type.', 'woocommerce-discount-manager' ) );
		}

		$expected_fields = isset( $type['defaults'] ) ? $type['defaults'] : [];

		if ( empty( $expected_fields ) ) {
			return;
		}

		foreach ( $expected_fields as $field => $default ) {
			$value  = $this->data->get( $field );
			$method = 'validate_type_setting_' . $field;

			if ( method_exists( $this, $method ) ) {
				$this->$method( $value );
			}
		}
	}

	/**
	 * Validate the "Discount" field.
	 *
	 * @param string $value The value of the field.
	 * @return void
	 */
	private function validate_type_setting_amount_type( $value ): void {
		$supported = [
			'percentage',
			'fixed',
		];

		if ( empty( $value ) ) {
			$this->add_error( 'amount-type', __( 'Please select an amount type.', 'woocommerce-discount-manager' ) );
		}

		if ( ! in_array( $value, $supported, true ) ) {
			$this->add_error( 'amount-type', __( 'Please select a valid amount type.', 'woocommerce-discount-manager' ) );
		}
	}

	/**
	 * Validate the fixed discount amount field.
	 *
	 * @param string $value The value of the field.
	 * @return void
	 */
	private function validate_type_setting_fixed_discount( $value ): void {
		$amount_type = $this->data->get( 'amount_type' );

		if ( $amount_type !== 'fixed' ) {
			return;
		}

		if ( empty( $value ) ) {
			$this->add_error( 'fixed-discount', __( 'Please enter a fixed discount amount.', 'woocommerce-discount-manager' ) );
		}

		if ( ! is_numeric( $value ) ) {
			$this->add_error( 'fixed-discount', __( 'Please enter a valid fixed discount amount.', 'woocommerce-discount-manager' ) );
		}

		if ( $value < 0 ) {
			$this->add_error( 'fixed-discount', __( 'Please enter a positive fixed discount amount.', 'woocommerce-discount-manager' ) );
		}
	}

	/**
	 * Validate the percentage discount amount field.
	 *
	 * @param string $value The value of the field.
	 * @return void
	 */
	private function validate_type_setting_percentage_discount( $value ): void {
		$amount_type = $this->data->get( 'amount_type' );

		if ( $amount_type !== 'percentage' ) {
			return;
		}

		if ( empty( $value ) ) {
			$this->add_error( 'percentage-discount', __( 'Please enter a percentage discount amount.', 'woocommerce-discount-manager' ) );
		}

		if ( ! is_numeric( $value ) ) {
			$this->add_error( 'percentage-discount', __( 'Please enter a valid percentage discount amount.', 'woocommerce-discount-manager' ) );
		}

		if ( $value < 0 ) {
			$this->add_error( 'percentage-discount', __( 'Please enter a positive percentage discount amount.', 'woocommerce-discount-manager' ) );
		}

		if ( $value > 100 ) {
			$this->add_error( 'percentage-discount', __( 'Please enter a percentage discount amount less than 100.', 'woocommerce-discount-manager' ) );
		}
	}

	/**
	 * Validate the "total spend" field.
	 *
	 * @param string $value The value of the field.
	 * @return void
	 */
	private function validate_type_setting_total_spend( $value ): void {
		$total_spend = $this->data->get( 'total_spend' );

		if ( empty( $total_spend ) ) {
			$this->add_error( 'total-spend', __( 'Please enter a total spend amount.', 'woocommerce-discount-manager' ) );
		}

		if ( ! is_numeric( $total_spend ) ) {
			$this->add_error( 'total-spend', __( 'Please enter a valid total spend amount.', 'woocommerce-discount-manager' ) );
		}

		if ( $total_spend < 0 ) {
			$this->add_error( 'total-spend', __( 'Please enter a positive total spend amount.', 'woocommerce-discount-manager' ) );
		}
	}

	/**
	 * Validate the "required quantity" field.
	 *
	 * @param string $value The value of the field.
	 * @return void
	 */
	private function validate_type_setting_required_qty( $value ): void {
		$required_qty = $this->data->get( 'required_qty' );

		if ( empty( $required_qty ) ) {
			$this->add_error( 'required-qty', __( 'Please enter a required quantity.', 'woocommerce-discount-manager' ) );
		}

		if ( ! is_numeric( $required_qty ) ) {
			$this->add_error( 'required-qty', __( 'Please enter a valid required quantity.', 'woocommerce-discount-manager' ) );
		}

		if ( $required_qty < 0 ) {
			$this->add_error( 'required-qty', __( 'Please enter a positive required quantity.', 'woocommerce-discount-manager' ) );
		}
	}

	/**
	 * Validate the "free quantity" field.
	 *
	 * @param string $value The value of the field.
	 * @return void
	 */
	private function validate_type_setting_free_qty( $value ): void {
		$free_qty = $this->data->get( 'free_qty' );

		if ( empty( $free_qty ) ) {
			$this->add_error( 'free-qty', __( 'Please enter a free quantity.', 'woocommerce-discount-manager' ) );
		}

		if ( ! is_numeric( $free_qty ) ) {
			$this->add_error( 'free-qty', __( 'Please enter a valid free quantity.', 'woocommerce-discount-manager' ) );
		}

		if ( $free_qty < 0 ) {
			$this->add_error( 'free-qty', __( 'Please enter a positive free quantity.', 'woocommerce-discount-manager' ) );
		}
	}

	/**
	 * Validate the "free product" field.
	 *
	 * @param string $value The value of the field.
	 * @return void
	 */
	private function validate_type_setting_free_product( $value ): void {
		$free_product = $this->data->get( 'free_product' );
		$supported    = [
			'any',
			'matching',
		];

		if ( empty( $free_product ) ) {
			$this->add_error( 'free-product', __( 'Please select a free product.', 'woocommerce-discount-manager' ) );
		}

		if ( ! in_array( $value, $supported, true ) ) {
			$this->add_error( 'free-product', __( 'Please select a valid free product.', 'woocommerce-discount-manager' ) );
		}
	}

	/**
	 * Validate the "fixed price" field.
	 *
	 * @param string $value The value of the field.
	 * @return void
	 */
	private function validate_type_setting_fixed_price( $value ): void {
		$fixed_price = $this->data->get( 'fixed_price' );

		if ( empty( $fixed_price ) ) {
			$this->add_error( 'fixed-price', __( 'Please enter a fixed price.', 'woocommerce-discount-manager' ) );
		}

		if ( ! is_numeric( $fixed_price ) ) {
			$this->add_error( 'fixed-price', __( 'Please enter a valid fixed price.', 'woocommerce-discount-manager' ) );
		}

		if ( $fixed_price < 0 ) {
			$this->add_error( 'fixed-price', __( 'Please enter a positive fixed price.', 'woocommerce-discount-manager' ) );
		}
	}

	/**
	 * Validate the "apply to" field.
	 *
	 * @param string $value The value of the field.
	 * @return void
	 */
	private function validate_type_setting_apply_to( $value ): void {
		$apply_to  = $this->data->get( 'apply_to' );
		$supported = [
			'any',
			'addition',
			'like',
		];

		if ( empty( $apply_to ) ) {
			$this->add_error( 'apply-to', __( 'Please select a apply option.', 'woocommerce-discount-manager' ) );
		}

		if ( ! in_array( $value, $supported, true ) ) {
			$this->add_error( 'apply-to', __( 'Please select a valid apply option.', 'woocommerce-discount-manager' ) );
		}
	}

	/**
	 * Validate the "range type" field.
	 *
	 * @param string $value The value of the field.
	 * @return void
	 */
	private function validate_type_setting_range_type( $value ): void {
		$range_type = $this->data->get( 'range_type' );
		$supported  = [
			'percentage',
			'price',
		];

		if ( empty( $range_type ) ) {
			$this->add_error( 'range-type', __( 'Please select a range type.', 'woocommerce-discount-manager' ) );
		}

		if ( ! in_array( $value, $supported, true ) ) {
			$this->add_error( 'range-type', __( 'Please select a valid range type.', 'woocommerce-discount-manager' ) );
		}
	}

	/**
	 * Validate the "tiers" field.
	 *
	 * @param string $value The value of the field.
	 * @return void
	 */
	private function validate_type_setting_tiers( $value ): void {
		$tiers = $this->data->get( 'tiers' );

		if ( empty( $tiers ) ) {
			$this->add_error( 'tiers', __( 'Please enter at least one tier.', 'woocommerce-discount-manager' ) );
		}

		if ( ! is_array( $tiers ) ) {
			$this->add_error( 'tiers', __( 'Please enter a valid tier.', 'woocommerce-discount-manager' ) );
		}

		$zeroed_price = wc_price(
			0,
			[
				'decimals' => 0,
			]
		);

		foreach ( $tiers as $tier ) {
			// Quantity from.
			$from = $tier['from'] ?? '';

			// Quantity to.
			$to = $tier['to'] ?? '';

			// Discount amount.
			$for = $tier['for'] ?? '';

			if ( empty( $from ) ) {
				$this->add_error( 'tiers', esc_html__( 'Please enter a minimum quantity.', 'woocommerce-discount-manager' ) );
			}

			if ( ! is_numeric( $from ) ) {
				$this->add_error( 'tiers', esc_html__( 'Please enter a valid minimum quantity.', 'woocommerce-discount-manager' ) );
			}

			if ( $from < 0 ) {
				$this->add_error( 'tiers', esc_html__( 'Please enter a positive minimum quantity.', 'woocommerce-discount-manager' ) );
			}

			if ( empty( $for ) ) {
				// Translators: %s is the zeroed price.
				$this->add_error( 'tiers', wp_strip_all_tags( html_entity_decode( wp_kses_post( sprintf( __( 'Please enter a discount greater than %s for all tiers.', 'woocommerce-discount-manager' ), $zeroed_price ) ) ) ) );
			}

			if ( ! is_numeric( $for ) ) {
				$this->add_error( 'tiers', esc_html__( 'Please enter a valid discount amount.', 'woocommerce-discount-manager' ) );
			}

			if ( $for < 0 ) {
				$this->add_error( 'tiers', esc_html__( 'Please enter a positive discount amount.', 'woocommerce-discount-manager' ) );
			}

			if ( is_numeric( $to ) && $from > $to ) {
				$this->add_error( 'tiers', esc_html__( 'Please enter a valid quantity range.', 'woocommerce-discount-manager' ) );
			}
		}
	}

	/**
	 * Validate the "additional quantity" field.
	 *
	 * @param string $value The value of the field.
	 * @return void
	 */
	private function validate_type_setting_additional_qty( $value ): void {
		$additional_qty = $this->data->get( 'additional_qty' );

		if ( empty( $additional_qty ) ) {
			$this->add_error( 'additional-qty', __( 'Please enter a value for the "Number of additional products" setting.', 'woocommerce-discount-manager' ) );
		}

		if ( ! is_numeric( $additional_qty ) ) {
			$this->add_error( 'additional-qty', __( 'Please enter a valid value for the "Number of additional products" setting.', 'woocommerce-discount-manager' ) );
		}

		if ( $additional_qty < 0 ) {
			$this->add_error( 'additional-qty', __( 'Please enter a positive value for the "Number of additional products" setting.', 'woocommerce-discount-manager' ) );
		}
	}
}
