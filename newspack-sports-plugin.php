<?php
/**
 * Plugin Name: Newspack Sports Plugin
 * Description: Comprehensive sports organization system with teams, matches, and athletes
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: newspack-sports
 * Requires at least: 5.8
 * Tested up to: 6.3
 * Requires PHP: 7.4
 *
 * @package Newspack_Sports
 */

namespace Newspack_Sports;

defined( 'ABSPATH' ) || exit;

// Define plugin constants.
define( 'NEWSPACK_SPORTS_PLUGIN_FILE', __FILE__ );
define( 'NEWSPACK_SPORTS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'NEWSPACK_SPORTS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'NEWSPACK_SPORTS_VERSION', '1.0.0' );

// Autoload classes.
spl_autoload_register( function( $class_name ) {
	if ( false !== strpos( $class_name, 'Newspack_Sports' ) ) {
		$classes_dir = realpath( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR;
		$class_file = str_replace( array( 'Newspack_Sports\\', '_' ), array( '', '-' ), $class_name );
		$class_file = strtolower( $class_file ) . '.php';
		$file = $classes_dir . $class_file;
		if ( file_exists( $file ) ) {
			require $file;
		}
	}
} );

// Initialize the plugin.
add_action( 'plugins_loaded', __NAMESPACE__ . '\\init' );

/**
 * Initialize the plugin.
 */
function init() {
	// Load required classes.
	Core\Sports_Taxonomy::instance();
	Core\Sports_Meta::instance();
	Core\Custom_Post_Types::instance();
	Core\Template_Handler::instance();
	Core\Sports_Data_Manager::instance();
	Core\Compatibility::instance();
	Core\Ajax_Handler::instance();
	Core\Setup_Wizard::instance();
	
	if ( is_admin() ) {
		Admin\Admin::instance();
	}
	
	// Load text domain.
	load_plugin_textdomain( 'newspack-sports', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	
	// Enqueue frontend assets
	add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_frontend_assets' );
}

/**
 * Enqueue frontend assets
 */
function enqueue_frontend_assets() {
	wp_enqueue_style(
		'newspack-sports-frontend',
		NEWSPACK_SPORTS_PLUGIN_URL . 'assets/css/frontend.css',
		[],
		NEWSPACK_SPORTS_VERSION
	);

	wp_enqueue_script(
		'newspack-sports-frontend',
		NEWSPACK_SPORTS_PLUGIN_URL . 'assets/js/frontend.js',
		[ 'jquery' ],
		NEWSPACK_SPORTS_VERSION,
		true
	);

	wp_localize_script(
		'newspack-sports-frontend',
		'newspack_sports_frontend',
		[
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'newspack_sports_frontend' ),
		]
	);
}

/**
 * Activation hook.
 */
register_activation_hook( __FILE__, __NAMESPACE__ . '\\activate' );
function activate() {
	// Flush rewrite rules on activation.
	flush_rewrite_rules();
}

/**
 * Deactivation hook.
 */
register_deactivation_hook( __FILE__, __NAMESPACE__ . '\\deactivate' );
function deactivate() {
	// Clean up on deactivation.
	flush_rewrite_rules();
}