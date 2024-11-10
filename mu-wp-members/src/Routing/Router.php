<?php

namespace MuWpMembers\Routing;

use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpFoundation\Request;
use function array_shift;
use function call_user_func_array;
use function class_exists;
use function explode;
use function http_response_code;
use function is_archive;
use function is_category;
use function is_user_logged_in;
use function method_exists;
use function preg_match;
use function preg_replace;
use function wp_safe_redirect;

/**
 * Class Router for routing.
 * @package MuWpMembers\Routing
 */
class Router {
	private array $routes = []; // Array of routes
	private array $params = []; // Array of URL parameters

	/**
	 * Load routes from YAML file.
	 *
	 * @param string $filePath
	 */
	public function loadRoutesFromYaml( string $filePath ): void {
		$yaml = Yaml::parseFile( $filePath );
		foreach ( $yaml as $name => $route ) {
			$this->addRoute( $name, $route['path'], $route['controller'] );
		}
	}

	/**
	 * Add route.
	 *
	 * @param string $name
	 * @param string $path
	 * @param string $controller
	 *
	 * @return void
	 */
	public function addRoute( string $name, string $path, string $controller ): void {
		$this->routes[ $name ] = [
			'path'       => $path,
			'controller' => $controller,
		];
	}

	/**
	 * Dispatch request.
	 *
	 * @param string $requestUri
	 *
	 * @return void
	 */
	public function dispatch( string $requestUri ): void {
		foreach ( $this->routes as $route ) {
			$pattern = preg_replace( '/\{[^\/]+\}/', '([^\/]+)', $route['path'] );
			if ( preg_match( '#^' . $pattern . '$#', $requestUri, $matches ) ) {
				array_shift( $matches ); // 最初の要素はフルマッチなので削除
				$this->params = $matches;

				// Requestオブジェクトを生成
				$request = Request::createFromGlobals();

				// Set global variable
				global $is_custom_route;
				$is_custom_route = true;

				// コントローラーを実行
				$this->executeController( $route['controller'], $request, $matches );
				exit();
			}
		}

		// $this->sendNotFound();
	}

	/**
	 * Get URL parameters.
	 *
	 * @return array
	 */
	public function getParams(): array {
		return $this->params;
	}

	/**
	 * Execute controller.
	 *
	 * @param string $controller
	 * @param Request $request
	 * @param array $params
	 *
	 * @return void
	 */
	private function executeController( string $controller, Request $request, array $params = [] ): void {
		list( $class, $method ) = explode( '::', $controller );
		if ( class_exists( $class ) && method_exists( $class, $method ) ) {
			$controllerInstance = new $class();

			// `Request` オブジェクトを最初の引数として渡す
			array_unshift( $params, $request );

			call_user_func_array( [ $controllerInstance, $method ], $params );
		} else {
			// $this->sendNotFound();
		}
	}


	/**
	 * Send 404 Not Found.
	 *
	 * @return void
	 */
	private function sendNotFound(): void {
		// http_response_code( 404 );

		wp_safe_redirect( home_url( '/404' ) );
		exit();
	}
}
