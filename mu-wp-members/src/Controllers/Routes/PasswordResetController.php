<?php

namespace MuWpMembers\Controllers\Routes;

use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use function MuWpMembers\Controllers\check_password_reset_key;
use function MuWpMembers\Controllers\is_wp_error;
use function MuWpMembers\Controllers\reset_password;
use function MuWpMembers\Controllers\wp_create_nonce;

/**
 * Password reset controller
 */
class PasswordResetController extends BaseController {

	/**
	 * Password reset
	 *
	 * @return void
	 */
	public function passwordReset(): void {
		$form    = $this->createPasswordResetForm();
		$request = Request::createFromGlobals();
		$error   = $this->validateResetRequest( $request );

		if ( $error ) {
			$this->session->getFlashBag()->add( 'error', $error );
			$this->redirectLogin();
			exit;
		}

		if ( $request->isMethod( 'POST' ) ) {
			$form->handleRequest( $request );

			if ( $form->isSubmitted() && $form->isValid() ) {
				$this->handlePasswordReset( $form, $request );
			}
		}

		$this->renderResponse( 'password_reset', [
			'form'  => $form->createView(),
			'error' => $error
		] );
	}

	/**
	 * Create password reset form
	 *
	 * @return FormInterface
	 */
	private function createPasswordResetForm(): FormInterface {
		$formFactory = $this->createFormFactory();

		return $formFactory->createBuilder( FormType::class )->add( 'password', PasswordType::class, [
			'label' => '新しいパスワード',
			'attr'  => [ 'class' => 'form-control' ],
		] )->add( 'password_confirmation', PasswordType::class, [
			'label' => '新しいパスワード（確認）',
			'attr'  => [ 'class' => 'form-control' ],
		] )->add( '_token', HiddenType::class, [
			'data' => wp_create_nonce( 'password_reset_nonce' ),
		] )->add( 'submit', SubmitType::class, [
			'label' => 'パスワードリセット',
			'attr'  => [ 'class' => 'btn btn-primary' ],
		] )->getForm();
	}

	/**
	 * Validate reset request
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	private function validateResetRequest( Request $request ): string {
		$key   = $request->query->get( 'key' );
		$login = $request->query->get( 'login' );

		if ( empty( $key ) || empty( $login ) ) {
			return '無効なパスワードリセットリンクです。';
		}

		$user = check_password_reset_key( $key, $login );

		if ( is_wp_error( $user ) ) {
			return '無効なパスワードリセットキーです。';
		}

		return '';
	}

	/**
	 * Handle password reset
	 *
	 * @param $form FormInterface
	 * @param Request $request
	 *
	 * @return void
	 */
	#[NoReturn] private function handlePasswordReset( FormInterface $form, Request $request ): void {
		$data  = $form->getData();
		$token = $data['_token'];
		$error = '';

		if ( ! $this->verifyCsrfToken( $token, 'password_reset_nonce' ) ) {
			$error = '無効なCSRFトークンです。';
		}

		if ( $data['password'] !== $data['password_confirmation'] ) {
			$error = 'パスワードが一致しません。';
		}

		if ( $error ) {
			$this->session->getFlashBag()->add( 'error', $error );
			$this->redirectLogin();
			exit;
		}

		$key   = $request->query->get( 'key' );
		$login = $request->query->get( 'login' );
		$user  = check_password_reset_key( $key, $login );

		reset_password( $user, $data['password'] );
		$this->session->getFlashBag()->add( 'success', 'パスワードがリセットされました。' );
		$this->redirectLogin();
		exit;
	}
}
