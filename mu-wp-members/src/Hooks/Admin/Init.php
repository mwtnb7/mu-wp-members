<?php

namespace MuWpMembers\Hooks\Admin;

class Init {
	static function register(): void {
		( new User\UserRole() )->initHooks();
		( new Taxonomy\RestrictRoles() )->initHooks();
		( new Gutenberg\RestrictTerms() )->initHooks();
		( new Sidebar() )->initHooks();
	}
}
