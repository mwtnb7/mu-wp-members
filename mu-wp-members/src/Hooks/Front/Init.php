<?php

namespace MuWpMembers\Hooks\Front;

use MuWpMembers\Utils\URLParser;

class Init {
	static function register(): void {
		if ( ! is_admin() && URLParser::isMembersPath() ) {
			( new Meta() )->initHooks();
			( new Breadcrumb() )->initHooks();
			( new Scripts() )->initHooks();
			( new TemplateRedirect() )->initHooks();
		}
	}
}
