<?php

namespace MuWpMembers\Registers\PostType;

/**
 * Class Init for registering roles.
 * @package MuWpMembers\Registers\Roles
 */
class Init {
	static function register(): void {
		( new MembersMaterial() )->register();
		( new MembersNotification() )->register();
		( new MembersPost() )->register();
		( new MembersUseful() )->register();
	}
}
