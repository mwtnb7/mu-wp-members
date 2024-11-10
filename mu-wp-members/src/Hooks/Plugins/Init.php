<?php

namespace MuWpMembers\Hooks\Plugins;

class Init {
	static function register(): void {
		MWWPForm\Init::register();
	}
}
