<?php

namespace MuWpMembers\Controllers\Routes;

use MuWpMembers\Utils\Url;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use function MuWpMembers\Controllers\email_exists;
use function MuWpMembers\Controllers\is_user_logged_in;
use function MuWpMembers\Controllers\is_wp_error;
use function MuWpMembers\Controllers\username_exists;
use function MuWpMembers\Controllers\wp_create_nonce;
use function MuWpMembers\Controllers\wp_insert_user;
use function MuWpMembers\Controllers\wp_redirect;
use function preg_match;
use function str_contains;
use function strlen;
use function strtolower;

/**
 * Register controller
 */
class RegisterController extends BaseController {

	/**
	 * Register
	 *
	 * @return void
	 */
	public function register(): void {
		// Redirect to home if user is logged in
		if ( is_user_logged_in() ) {
			wp_redirect( Url::getHomeUrl() );
			exit;
		}

		$request = Request::createFromGlobals();
		$form    = $this->createForm();

		if ( $request->isMethod( 'POST' ) ) {
			$this->handlePostRequest( $request, $form );
		}

		$this->renderRegisterResponse( $form );
	}

	/**
	 * Create form
	 *
	 * @return FormInterface
	 */
	private function createForm(): FormInterface {
		$formFactory = $this->createFormFactory();

		return $formFactory->createBuilder( FormType::class )->add( 'username', TextType::class, [
			'label' => 'ユーザー名',
			'attr'  => [ 'class' => 'form-control' ],
		] )->add( 'email', EmailType::class, [
			'label' => 'メールアドレス',
			'attr'  => [ 'class' => 'form-control' ],
		] )->add( 'password', PasswordType::class, [
			'label' => 'パスワード',
			'attr'  => [ 'class' => 'form-control' ],
		] )->add( '_token', HiddenType::class, [
			'data' => wp_create_nonce( 'register_nonce' ),
		] )->add( 'submit', SubmitType::class, [
			'label' => '登録',
			'attr'  => [ 'class' => 'btn btn-primary' ],
		] )->getForm();
	}

	/**
	 * Handle post request
	 *
	 * @param Request $request
	 * @param $form FormInterface
	 */
	private function handlePostRequest( Request $request, FormInterface $form ): void {
		$form->handleRequest( $request );
		if ( $form->isSubmitted() && $form->isValid() ) {
			$data  = $form->getData();
			$token = $data['_token'];

			if ( ! $this->verifyCsrfToken( $token, 'register_nonce' ) ) {
				return;
			}

			if ( username_exists( $data['username'] ) ) {
				$this->session->getFlashBag()->add( 'error', 'このユーザー名は既に使用されています。' );

				return;
			}

			if ( email_exists( $data['email'] ) ) {
				$this->session->getFlashBag()->add( 'error', 'このメールアドレスは既に登録されています。' );

				return;
			}

			if ( ! $this->check_password_strength( $data['password'], $data['username'] ) ) {
				return;
			}

			$userdata = [
				'user_login' => $data['username'],
				'user_email' => $data['email'],
				'user_pass'  => $data['password'],
			];
			$user_id  = wp_insert_user( $userdata );

			if ( is_wp_error( $user_id ) ) {
				$this->session->getFlashBag()->add( 'error', $user_id->get_error_message() );
			} else {
				$this->session->getFlashBag()->add( 'success', 'ユーザー登録が完了しました。ログインしてください。' );
				$this->redirectLogin();
				exit;
			}
		}
	}

	/**
	 * Render register response
	 *
	 * @param $form
	 */
	protected function renderRegisterResponse( $form ): void {
		$this->renderResponse( 'register', [
			'form'    => $form->createView(),
			'error'   => $this->session->getFlashBag()->get( 'error' ),
			'success' => $this->session->getFlashBag()->get( 'success' ),
		] );
	}

	/**
	 * Check password strength
	 *
	 * @param string $password
	 * @param string $username
	 * @param string $strength
	 * @param int $min_length
	 *
	 * @return bool
	 */
	private function check_password_strength(
		string $password, string $username, string $strength = 'medium', // 'medium' または 'strong' を指定
		int $min_length = 12
	): bool {
		// Check password length
		if ( strlen( $password ) < $min_length ) {
			$this->session->getFlashBag()->add( 'error', "パスワードは{$min_length}文字以上である必要があります。" );

			return false;
		}

		// Check password strength
		if ( $strength === 'medium' ) {
			// 中の強度: 大文字または小文字、数字
			$pattern       = '/^(?=.*[a-zA-Z])(?=.*\d)/';
			$error_message = 'パスワードは大文字または小文字、数字をそれぞれ1つ以上含む必要があります。';
		} else {
			// 強の強度: 大文字、小文字、数字、特殊文字
			$pattern       = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>])/';
			$error_message = 'パスワードは大文字、小文字、数字、特殊文字をそれぞれ1つ以上含む必要があります。';
		}

		// Pattern match
		if ( ! preg_match( $pattern, $password ) ) {
			$this->session->getFlashBag()->add( 'error', $error_message );

			return false;
		}

		// Check if password contains username
		if ( str_contains( strtolower( $password ), strtolower( $username ) ) ) {
			$this->session->getFlashBag()->add( 'error', 'パスワードにユーザー名を含めることはできません。' );

			return false;
		}

		return true;
	}
}
