<?php

namespace MuWpMembers\Controllers;

use MuWpMembers\Controllers\PostType\CommitteeController;

/**
 * Initialize controller classes
 */
class Init {

	public static function init() {
		CommitteeController::getInstance();
	}
}
