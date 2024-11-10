<?php

namespace MuWpMembers\Core;

use MuWpMembers\Routing\Router;
use MuWpMembers\Utils\URLParser;
use function is_admin;
use function is_login;
use function is_single;
use function str_contains;
use function wp_is_rest_endpoint;

/**
 * Class Plugin for initializing the plugin.
 * @package MuWpMembers\Core
 */
class Plugin {
	/**
	 * Initialize the plugin.
	 */
	public static function init(): void {
		if ( ! URLParser::isMembersPath() ) {
			return;
		}

		// Create and configure the router
		$router     = self::createRouter();
		$requestUri = self::getRequestUri();
		$router->dispatch( $requestUri );
	}

	/**
	 * Create and configure the router.
	 *
	 * @return Router
	 */
	private static function createRouter(): Router {
		$router = new Router();
		$router->loadRoutesFromYaml( MU_WP_MEMBERS_PATH . 'config/routes.yml' );

		return $router;
	}

	/**
	 * Get the current request URI.
	 *
	 * @return string
	 */
	private static function getRequestUri(): string {
		return parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
	}
}
