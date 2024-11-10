<?php

namespace MuWpMembers\Registers;

class Init {
	static function init(): void {
		// Don't sort these, they need to be in this order
		Taxonomy\Init::register();
		PostType\Init::register();
		Roles\Init::register();
	}
}
