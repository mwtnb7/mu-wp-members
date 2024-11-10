<?php

namespace MuWpMembers\Controllers\Routes;

use MuWpMembers\Utils\User;
use Symfony\Component\HttpFoundation\Response;
use MuWpMembers\Registers\Roles\Committee;
use MuWpMembers\Utils\Url;
use function is_array;
use function ob_get_clean;
use function ob_start;

/**
 * Committee controller.
 */
class CommitteeController extends BaseController {

	public function __construct() {
		parent::__construct();

		// Check if user is logged in
		$this->protectLogin();

		// Check if user has access to committee roles
		$this->protectCommitteeRoles();

		// Set slugs for committee roles
		$this->allow_slugs = Committee::getRoleSlugs();

		// Protect slugs from unauthorized access
		$this->protectSlugs();

		// setTitle
		$this->setCommitteeTitle();
	}

	/**
	 * Index page for committee roles.
	 *
	 * @return void
	 */
	public function index(): void {
		$response = new Response();
		ob_start();
		$this->render( 'two-column', 'committee_index', [
			'success' => $this->session->getFlashBag()->get( 'success' ),
		] );
		$response->setContent( ob_get_clean() );
		$response->send();
	}

	/**
	 * Set the title for the committee page.
	 *
	 * @return void
	 */
	private function setCommitteeTitle(): void {
		$roles = Committee::getRoleList();
		$slug  = $this->getSlug();

		foreach ( $roles as $role ) {
			if ( isset( $role['slug'] ) && $role['slug'] === $slug ) {
				$this->setTitle( $role['name'] );

				return;
			}
		}

		$this->setTitle( '委員会' );
	}

	/**
	 * Protect committee roles from unauthorized access.
	 *
	 * @return void
	 */
	private function protectCommitteeRoles(): void {
		if ( User::isAdminUser() ) {
			return;
		}

		$role_key = Committee::getRoleKeyBySlug( $this->getSlug() );

		if ( $this->user->hasRole( $role_key ) ) {
			return;
		}

		wp_safe_redirect( Url::getHomeUrl() );
		exit;
	}
}
