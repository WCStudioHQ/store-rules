<?php

namespace WCSSR;

/**
 * Class WCSSR_Frontend
 *
 * Handles the frontend functionality of the Role-Based Discounts plugin.
 * Applies role-based discounts to WooCommerce cart items dynamically based on user roles.
 */
class WCSSR_Frontend {
	/**
	 * Option name used to store discount rules in the WordPress database.
	 *
	 * @var string $option_name
	 */
	private $option_name = 'wcssr_role_discount_rules';

	/**
	 * Constructor to initialize the frontend functionality.
	 *
	 * Hooks the `wcssr_apply_role_based_discount` method to the `woocommerce_before_calculate_totals` action
	 * to apply discounts during cart calculation.
	 */
	public function __construct() {
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'wcssr_apply_role_based_discount' ) );
	}
	/**
	 * Retrieves the saved discount rules from the WordPress database.
	 *
	 * @return array An array of discount rules or an empty array if no rules exist.
	 *
	 * This method fetches the role-based discount rules stored in the `wcssr_role_discount_rules` option.
	 * It is used to determine the applicable discount for the current user's role.
	 */
	private function get_discount_rules_for_apply() {
		return get_option( $this->option_name, array() );
	}
	/**
	 * Applies role-based discounts to cart items dynamically.
	 *
	 * @param WC_Cart $cart The WooCommerce cart object.
	 *
	 * Workflow:
	 * - Ensures the function does not run in the admin panel (except during AJAX calls) or more than once per request.
	 * - Retrieves the current user's role.
	 * - Checks if a discount is defined for the user's role.
	 * - Applies the discount percentage to the price of each cart item.
	 */
	public function wcssr_apply_role_based_discount( $cart ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

		if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 ) {
			return;
		}

		$user         = wp_get_current_user();
		$user_roles   = $user->roles;
		$current_role = ! empty( $user_roles ) ? $user_roles[0] : '';

		if ( empty( $current_role ) ) {
			return;
		}

		$discount_rules      = $this->get_discount_rules_for_apply();
		$applicable_discount = null;

		foreach ( $discount_rules as $rule ) {
			if ( $rule['wcssr_role_name'] === $current_role ) {
				$applicable_discount = floatval( $rule['wcssr_discount_percentage'] );
				break;
			}
		}

		if ( ! $applicable_discount ) {
			return;
		}

		foreach ( $cart->get_cart() as $cart_item ) {
			$price            = $cart_item['data']->get_regular_price();
			$discounted_price = $price * ( 1 - ( $applicable_discount / 100 ) );
			$cart_item['data']->set_price( $discounted_price );
		}
	}
}
