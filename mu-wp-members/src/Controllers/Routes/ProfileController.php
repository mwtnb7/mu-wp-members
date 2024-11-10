<?php

namespace MuWpMembers\Controllers\Routes;

/**
 * Profile controller.
 */
class ProfileController extends BaseController {

	public function __construct() {
		parent::__construct();

		// Check if user is logged in
		$this->protectLogin();
	}

	public function view(): void {
		// $this->render( 'profile' );
	}
}
