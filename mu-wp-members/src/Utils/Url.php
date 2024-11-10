<?php

namespace MuWpMembers\Utils;

use function strtok;
use const MU_WP_MEMBERS_URL;

/**
 * Class Url for URL operations.
 * @package MuWpMembers\Utils
 */
class Url {

	/**
	 * Get current URL.
	 *
	 * @return string
	 */
	public static function getCurrentUrl(): string {
		$protocol = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ) ? 'https' : 'http';
		$host     = $_SERVER['HTTP_HOST'];
		$uri      = $_SERVER['REQUEST_URI'];

		return $protocol . '://' . $host . $uri;
	}

	/**
	 * Get current URL without query string.
	 *
	 * @return string
	 */
	public static function getCurrentUrlWithoutQueryString(): string {
		return strtok( self::getCurrentUrl(), '?' );
	}

	/**
	 * Get current URI.
	 *
	 * @return string
	 */
	public static function getHomeUrl(): string {
		return MU_WP_MEMBERS_URL;
	}

	/**
	 * Get directory URI.
	 *
	 * @param $path
	 *
	 * @return string
	 */
	public static function getUrl( $path ): string {
		return self::getHomeUrl() . $path . '/';
	}

	/**
	 * Get directory URI.
	 *
	 * @param $path
	 *
	 * @return string
	 */
	public static function getLoginUrl(): string {
		return self::getHomeUrl() . 'login/';
	}

	/**
	 * Echo directory URI.
	 *
	 * @param string $path
	 *
	 * @return void
	 */
	public static function theUrl( string $path = '' ): void {
		echo self::getUrl( $path );
	}
}
