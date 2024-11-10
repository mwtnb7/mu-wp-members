<?php

namespace MuWpMembers\Hooks;

class Init {
	static function init(): void {
		if ( is_admin() ) {
			Admin\Init::register();
		}

		if ( ! is_admin() ) {
			Front\Init::register();
		}

		Permission\Init::register();
		Plugins\Init::register();
	}
}
