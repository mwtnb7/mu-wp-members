<?php

namespace MuWpMembers\Hooks\Plugins\MWWPForm;

class Init {
	static function register(): void {
		( new EventForm() )->initHooks();
		( new MembersForm() )->initHooks();
	}
}
