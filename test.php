<?php
/**
 * Plugin Name: Test Connection
 * Plugin URI:
 * Description:
 * Author: Automattic
 * Author URI: https://woocommerce.com/
 * Text Domain: test
 * Domain Path: /languages
 * WC requires at least: 4.0
 * WC tested up to: 4.3
 * Requires WP: 5.3
 * Version: 1.3.0
 *
 */

defined( 'ABSPATH' ) || exit;

define( 'TEST_PLUGIN_FILE', __FILE__ );
define( 'TEST_ABSPATH', dirname( TEST_PLUGIN_FILE ) . '/' );

require_once TEST_ABSPATH . 'vendor/autoload_packages.php';

/**
 * Initialize the Jetpack connection functionality.
 */
function test_jetpack_init() {
	$jetpack_config = new Automattic\Jetpack\Config();
	$jetpack_config->ensure(
		'connection',
		[
			'slug' => 'test', // plugin slug
			'name' => __( 'Test Connection', 'test' ), // plugin name
		]
	);
}

// Jetpack-config will initialize the modules on "plugins_loaded" with priority 2, so this code needs to be run before that.
add_action( 'plugins_loaded', 'test_jetpack_init', 1 );

/**
 * Initialize the extension. Note that this gets called on the "plugins_loaded" filter,
 * so WooCommerce classes are guaranteed to exist at this point (if WooCommerce is enabled).
 */
function test_init() {
	require_once TEST_ABSPATH . 'vendor/autoload.php';
	Test::init();
}

// Make sure this is run *after* WooCommerce has a chance to initialize its packages (wc-admin, etc). That is run with priority 10.
add_action( 'plugins_loaded', 'test_init', 11 );
