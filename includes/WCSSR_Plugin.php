<?php
namespace WCSTUDIO_STORE_RULES;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Plugin Class
 *
 * This class initializes the plugin, sets up core properties and methods,
 * and handles instantiation through a singleton pattern.
 *
 * @package Plugin
 */
class WCSSR_Plugin {

	/**
	 * Singleton instance of the Plugin class.
	 *
	 * @var WCSSR_Plugin|null
	 */
	public static $instance = null;

	/**
	 * Current version of the plugin.
	 *
	 * @var string
	 */
	public $version = '1.0.0';

	/**
	 * The main plugin file.
	 *
	 * @var string
	 */
	public $file;

	/**
	 * Private constructor to prevent multiple instances of the plugin class.
	 *
	 * @param string $file Main plugin file.
	 * @param string $version Plugin version.
	 */
	private function __construct( $file, $version ) {
		$this->version = $version;
		$this->file    = $file;
		$this->define_constants();
		$this->includes();
		$this->activation();
	}

	/**
	 * Retrieves the singleton instance of the Plugin class.
	 *
	 * @param string $file Main plugin file.
	 * @param string $version Plugin version.
	 * @return WCSSR_Plugin The singleton instance of the Plugin class.
	 */
	public static function get_instance( $file, $version ) {
		if ( null === self::$instance ) {
			self::$instance = new WCSSR_Plugin( $file, $version );
		}
		return self::$instance;
	}

	/**
	 * Defines necessary constants for the plugin.
	 *
	 * @return void
	 */
	private function define_constants() {

		define( 'WCSSR_VERSION', $this->version );
		define( 'WCSSR_PATH', plugin_dir_path( $this->file ) );
		define( 'WCSSR_URL', plugin_dir_url( $this->file ) );
		define( 'WCSSR_BASENAME', plugin_basename( $this->file ) );
	}



	/**
	 * Includes necessary files for the plugin's functionality.
	 *
	 * @return void
	 */
	private function includes() {
		if ( is_admin() ) {
			new WCSSR_Admin();
		}
		new WCSSR_Frontend();
	}
	/**
	 * Activation.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function activation() {
		register_activation_hook( $this->file, array( $this, 'wcssr_activation_hook' ) );
	}
	/**
	 * Activation hook.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function wcssr_activation_hook() {
		$functions = WCSSR_Functions::get_instance();

		if ( ! $functions->wcssr_wc_ready() ) {
			wp_die(
				'This plugin requires WooCommerce to be active. Please activate WooCommerce and try again.',
				'Plugin Activation Error',
				array( 'back_link' => true )
			);
		}
	}
}
