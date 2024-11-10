<?php

namespace MuWpMembers\Utils;

use function is_user_logged_in;

/**
 * Class Url for URL operations.
 * @package MuWpMembers\Utils
 */
class User {

	public static function isAdminUser(): bool {
		if ( current_user_can( 'administrator' ) || current_user_can( 'editor' ) ) {
			return true;
		}

		// 編集ユーザーA
		if ( current_user_can( 'office_admin' ) ) {
			return true;
		}

		return false;
	}
}
