<?php

namespace MuWpMembers\Controllers\Routes;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use function get_user_by;
use function is_email;
use function is_user_logged_in;
use function wp_check_password;
use function wp_create_nonce;
use function wp_logout;
use function wp_redirect;
use function wp_set_auth_cookie;
use function wp_set_current_user;

/**
 * Auth controller
 */
class AuthController extends BaseController {

	/**
	 * Login page.
	 *
	 * @return void
	 */
	public function login(): void {
		if ( is_user_logged_in() ) {
			wp_redirect( MU_WP_MEMBERS_URL );
			exit;
		}

		$formFactory = $this->createFormFactory();

		$form = $formFactory->createBuilder( FormType::class )->add( 'username', TextType::class, [
			'label' => '会員ID、またはメールアドレス',
			'attr'  => [ 'required' => 'required' ],
		] )->add( 'password', PasswordType::class, [
			'label' => 'パスワード',
			'attr'  => [ 'required' => 'required' ],
		] )->add( '_token', HiddenType::class, [
			'data' => wp_create_nonce( 'login_nonce' ),
		] )->getForm();

		$request = Request::createFromGlobals();

		if ( $request->isMethod( 'POST' ) ) {
			$this->handleLoginPost( $form, $request );
		} else {
			$this->handleLoginGet( $form );
		}
	}

	/**
	 * Redirect to the login page.
	 *
	 * @param $form
	 *
	 * @return void
	 */
	private function handleLoginGet( $form ): void {
		$this->renderResponse( 'default', 'login', [
			'form'    => $form->createView(),
			'error'   => $this->session->getFlashBag()->get( 'error' ),
			'success' => $this->session->getFlashBag()->get( 'success' ),
		] );
	}

	/**
	 * Handle the login form submission.
	 *
	 * @param $form
	 * @param $request
	 *
	 * @return void
	 */
	private function handleLoginPost( $form, $request ): void {
		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
			$data  = $form->getData();
			$token = $data['_token'];

			if ( ! $this->verifyCsrfToken( $token, 'login_nonce' ) ) {
				$this->session->getFlashBag()->add( 'error', '無効なCSRFトークンです。' );
				$this->redirectLogin();
			}

			$username = $data['username'];
			$password = $data['password'];

			if ( is_email( $username ) ) {
				$user = get_user_by( 'email', $username );
			} else {
				$user = get_user_by( 'login', $username );
			}

			if ( $user && ! wp_check_password( $password, $user->user_pass, $user->ID ) ) {
				$this->session->getFlashBag()->add( 'error', 'パスワードが間違っています。' );
				$this->redirectLogin();
			}

			if ( ! $user || ! wp_check_password( $password, $user->user_pass, $user->ID ) ) {
				$this->session->getFlashBag()->add( 'error', 'ユーザー名またはパスワードが無効です。' );
				$this->redirectLogin();
			}

			wp_set_current_user( $user->ID );
			wp_set_auth_cookie( $user->ID );
			$this->session->getFlashBag()->add( 'success', 'ログインに成功しました。' );
			wp_redirect( MU_WP_MEMBERS_URL );
			exit;
		}

		$this->handleLoginGet( $form );
	}

	/**
	 * Redirect to the login page.
	 *
	 * @return void
	 */
	public function logout() {
		wp_logout();
		$this->session->getFlashBag()->add( 'success', 'ログアウトしました。' );
		$this->redirectLogin();
	}
}
