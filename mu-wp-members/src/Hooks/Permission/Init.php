<?php

namespace MuWpMembers\Hooks\Permission;

class Init {
	static function register(): void {
		( new Terms() )->initHooks();

		// Taxonomy restrictions
		( new TaxonomyRoles\CommitteeRoles() )->initHooks();
	}
}
