<?php

namespace MuWpMembers\Controllers\Routes;

use Symfony\Component\HttpFoundation\Response;
use function ob_get_clean;
use function ob_start;

/**
 * Members controller.
 */
class MembersController extends BaseController {

	public function __construct() {
		parent::__construct();

		// Check if user is logged in
		$this->protectLogin();

		// Set title
		$this->setTitle( MU_WP_MEMBERS_TITLE );
	}

	/**
	 * Index page for members.
	 *
	 * @return void
	 */
	public function index(): void {
		$response = new Response();
		ob_start();
		$this->render( 'two-column', 'index', [
			'success' => $this->session->getFlashBag()->get( 'success' ),
		] );
		$response->setContent( ob_get_clean() );
		$response->send();
	}
}
