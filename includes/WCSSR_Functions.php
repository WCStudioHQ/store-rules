<?php

namespace WCSTUDIO_STORE_RULES;

defined( 'ABSPATH' ) || exit;
/**
 * Class WCSSR_Functions
 *
 * This class provides a collection of common, reusable functions that can be used across
 * various parts of the application. It is designed to serve as a utility class, offering
 * methods that handle general-purpose tasks such as data manipulation, settings retrieval,
 * validation, and formatting.
 *
 * @package Functions
 * @version 1.0
 * @since 1.0
 */
class WCSSR_Functions {

	/**
	 * Holds the singleton instance of the class.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Gets the Functions class instance
	 *
	 * @since v.4.0.0
	 * @returns WCSSR_Functions
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Checks if WooCommerce is installed and active.
	 *
	 * @since 1.0.0
	 * @return bool True if WooCommerce is installed and active, otherwise false.
	 */
	public function wcssr_wc_ready() {
		$active = is_multisite() ? array_keys( get_site_option( 'active_sitewide_plugins', array() ) ) : (array) get_option( 'active_plugins', array() );
		if ( file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' ) && in_array( 'woocommerce/woocommerce.php', $active, true ) ) {
			return true;
		} else {
			return false;
		}
	}
}
