<?php

namespace MuWpMembers\Registers\Taxonomy;

/**
 * Class Init for registering roles.
 * @package MuWpMembers\Registers\Roles
 */
class Init {
	static function register(): void {
		( new CommitteeRoles() )->register();
		( new MembersCategory() )->register();
	}
}
