<?php

namespace MuWpMembers\Utils;

use function str_contains;

/**
 * Parse URL
 * @package MuWpMembers\Utils
 */
class URLParser {

	/**
	 * 会員ページかどうかを判定する
	 *
	 * @return bool
	 */
	public static function isMembersPage(): bool {
		$request_uri = $_SERVER['REQUEST_URI'];

		if ( str_contains( $request_uri, '/login/' ) ) {
			return false;
		}

		if ( self::isMembersPath() ) {
			return true;
		}

		return false;
	}

	/**
	 * 会員ページパス対象かどうかを判定する
	 *
	 * @return bool
	 */
	public static function isMembersPath(): bool {
		$request_uri = $_SERVER['REQUEST_URI'];

		if ( str_contains( $request_uri, '/' . MU_WP_MEMBERS_SLUG . '/' ) ) {
			return true;
		}

		return false;
	}
}
