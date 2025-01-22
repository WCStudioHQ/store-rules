<?php
/**
 * Plugin Name:       Store Rules
 * Plugin URI:        https://github.com/WCStudioHQ/store-rules
 * Description:       Easily manage WooCommerce role-based discounts. Set percentage discounts for different user roles and automate price adjustments.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      7.2
 * Author:            WC Studio
 * Author URI:        https://wcstudio.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       store-rules
 * Domain Path:       /languages
 * Requires Plugins:  woocommerce
 *
 * @package StoreRules
 */

/**
 * Store-Rules Discounts is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Store-Rules Discounts is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Store-Rules Discounts. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
 */



if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/vendor/autoload.php';
use WCSSR\WCSSR_Plugin;

/**
 * Initializes the Store Rules plugin.
 *
 * This function creates and returns a singleton instance of the Plugin class,
 * using the plugin's main file and version as parameters.
 *
 * @return WCSSR_Plugin Plugin object instance.
 * @since 1.0.0
 */
function wcssr_init() {
	return WCSSR_Plugin::get_instance( __FILE__, '1.0.0' );
}

wcssr_init();
