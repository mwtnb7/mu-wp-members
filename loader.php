<?php
/**
 * Plugin Name: mu-wp Members
 * Description: WordPress plugin for content restriction and member management
 * Version: 1.0.0
 * Author: mwtnb7
 */

define( 'MU_WP_MEMBERS_PATH', plugin_dir_path( __FILE__ ) . 'mu-wp-members/' );
define( 'MU_WP_MEMBERS_URI', plugin_dir_url( __FILE__ ) . 'mu-wp-members/' );

require MU_WP_MEMBERS_PATH . 'vendor/autoload.php';

use MuWpMembers\Registers\Init as Registers;
use MuWpMembers\Hooks\Init as Hooks;
use MuWpMembers\Controllers\Init as Controllers;
use MuWpMembers\Core\Plugin;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

// Initialize the plugin
add_action( 'muplugins_loaded', function () {
	if ( headers_sent() ) {
		// error_log( 'Headers already sent, session cannot be started.' );
	} else {
		if ( session_status() === PHP_SESSION_NONE ) {
			$storage            = new NativeSessionStorage( [], new NativeFileSessionHandler() );
			$GLOBALS['session'] = new Session( $storage );
			$GLOBALS['session']->start();
		}
	}
}, 0 );

add_action( 'init', function () {
	Registers::init();
	Hooks::init();
	Controllers::init();
	Plugin::init();
} );

function members_enqueue_assets() {
	wp_enqueue_style( 'mu-wp-members-styles', MEMBERS_URI . 'assets/css/styles.css' );
	wp_enqueue_script( 'mu-wp-members-scripts', MEMBERS_URI . 'assets/js/scripts.js', [], false, true );
}
