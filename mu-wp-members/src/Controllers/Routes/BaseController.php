<?php

namespace MuWpMembers\Controllers\Routes;

use MuWpMembers\Routing\Router;
use MuWpMembers\Tags\Template;
use MuWpMembers\Utils\Url;
use MuWpMembers\Models\User;
use JetBrains\PhpStorm\NoReturn;
use function array_filter;
use function end;
use function explode;
use function extract;
use function is_user_logged_in;
use function wp_redirect;
use function wp_verify_nonce;
use function in_array;
use function ob_get_clean;
use function ob_start;
use function parse_url;
use const MU_WP_MEMBERS_PATH;
use const MU_WP_MEMBERS_TITLE;
use const PHP_URL_PATH;

/**
 * Base controller.
 */
abstract class BaseController {
	protected mixed $session;
	protected array $allow_slugs = [];
	protected Router $router;
	protected User $user;

	public function __construct() {
		$this->session = $GLOBALS['session'];
		$this->user    = new User();
		$this->setSlug();
		$this->setTitle( '' );
		$this->setDescription( '会員向けページ | ' . get_bloginfo( 'name' ) );
	}

	/*
	 * Render content
	 *
	 * @param string template
	 * @param string content
	 * @param array $data
	 *
	 * @return void
	 */
	protected function render( string $template, string $content, mixed $data = null ): void {
		extract( $data );
		Template::getTemplate( $template, $content, $data );
	}

	/*
	 * Create form factory
	 *
	 * @return \Symfony\Component\Form\FormFactoryInterface
	 */
	protected function createFormFactory(): \Symfony\Component\Form\FormFactoryInterface {
		$formFactoryBuilder = \Symfony\Component\Form\Forms::createFormFactoryBuilder();
		$formFactoryBuilder->addExtension( new \Symfony\Component\Form\Extension\Core\CoreExtension() );
		$formFactoryBuilder->addExtension( new \Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension() );

		return $formFactoryBuilder->getFormFactory();
	}

	/*
	 * Render response
	 *
	 * @param string $view
	 * @param string $content
	 * @param array $data
	 *
	 * @return void
	 */
	protected function renderResponse( string $template, string $content, array $data = [] ): void {
		$response = new \Symfony\Component\HttpFoundation\Response();
		ob_start();
		$this->render( $template, $content, $data );
		$response->setContent( ob_get_clean() );
		$response->send();
	}

	/*
	 * Verify CSRF token
	 *
	 * @param string $token
	 * @param string $action
	 *
	 * @return bool
	 */
	protected function verifyCsrfToken( string $token, string $action ): bool {
		if ( ! wp_verify_nonce( $token, $action ) ) {
			$this->session->getFlashBag()->add( 'error', '無効なCSRFトークンです。' );

			return false;
		}

		return true;
	}

	/*
	 * Redirect to login page
	 *
	 * @return void
	 */
	#[NoReturn] protected function redirectLogin(): void {
		wp_redirect( Url::getUrl( 'login' ) );
		exit;
	}

	/*
	 * Protect login
	 *
	 * @return void
	 */
	protected function protectLogin(): void {
		if ( ! is_user_logged_in() ) {
			$this->redirectLogin();
		}
	}

	/*
	 * Protect slugs
	 *
	 * @return void
	 */
	protected function protectSlugs(): void {
		if ( empty( $this->allow_slugs ) ) {
			return;
		}

		if ( ! in_array( $this->getSlug(), $this->allow_slugs, true ) ) {
			$this->redirectLogin();
		}
	}

	/*
	 * Get the slug from request uri
	 *
	 * @return string
	 */
	protected function getSlug(): string {
		$request_uri = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
		$paths       = explode( '/', $request_uri );
		$params      = array_filter( $paths, fn( $slug ) => $slug !== '' );

		if ( empty( $params ) ) {
			return '';
		}

		return end( $params );
	}

	/*
	 * Set the title
	 *
	 * @param string $title
	 *
	 * @return void
	 */
	protected function setTitle( string $title ): void {
		$this->session->set( 'title', $title );
	}

	/*
	 * Set the slug
	 *
	 * @return void
	 */
	protected function setSlug(): void {
		$this->session->set( 'slug', $this->getSlug() );
	}

	/*
	 * Set the slug
	 *
	 * @param string $description
	 *
	 * @return void
	 */
	protected function setDescription( string $description ): void {
		$this->session->set( 'description', $description );
	}
}
