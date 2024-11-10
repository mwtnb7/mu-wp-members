<?php

namespace MuWpMembers\Hooks\Core;

use MuWpMembers\Hooks\Core\AdminBar;

class Init {
	static function register(): void {
		 ( new AdminBar() )->initHooks();
	}
}
