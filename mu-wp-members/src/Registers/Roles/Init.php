<?php

namespace MuWpMembers\Registers\Roles;

/**
 * Class Init for registering roles.
 * @package MuWpMembers\Registers\Roles
 */
class Init {
	static function register(): void {
		new Committee();
		new CommitteeEditor();
		new Editor();
		new Members();

		/**
		 * Flush rewrite rules. This is necessary to make the new roles work.
		 */
		// flush_rewrite_rules();
	}
}
