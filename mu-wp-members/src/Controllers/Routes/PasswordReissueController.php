<?php

namespace MuWpMembers\Controllers\Routes;

use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use function MuWpMembers\Controllers\add_query_arg;
use function MuWpMembers\Controllers\get_password_reset_key;
use function MuWpMembers\Controllers\get_user_by;
use function MuWpMembers\Controllers\is_wp_error;
use function MuWpMembers\Controllers\wp_create_nonce;
use function rawurlencode;

/**
 * Password reissue controller
 */
class PasswordReissueController extends BaseController {

	/**
	 * Handle the password reissue request and display the form
	 *
	 * @return void
	 */
	public function passwordReissue(): void {
		$formFactory = $this->createFormFactory();
		$form        = $this->createPasswordReissueForm( $formFactory );
		$request     = Request::createFromGlobals();

		$error   = '';
		$success = '';

		if ( $request->isMethod( 'POST' ) ) {
			$form->handleRequest( $request );

			if ( $form->isSubmitted() && $form->isValid() ) {
				$data  = $form->getData();
				$token = $data['_token'];

				if ( ! $this->verifyCsrfToken( $token, 'password_reissue_nonce' ) ) {
					$error = '無効なCSRFトークンです。';
				} else {
					$result  = $this->handlePasswordReissue( $data['email'] );
					$error   = $result['error'];
					$success = $result['success'];
				}
			}
		}

		$this->renderResponse( 'password_reissue', [
			'form'    => $form->createView(),
			'error'   => $error,
			'success' => $success,
		] );
	}

	/**
	 * Create password reissue form
	 *
	 * @param FormFactory $formFactory
	 *
	 * @return FormInterface
	 */
	private function createPasswordReissueForm( $formFactory ): FormInterface {
		return $formFactory->createBuilder( FormType::class )->add( 'email', EmailType::class, [
			'label' => 'メールアドレス',
			'attr'  => [ 'class' => 'form-control' ],
		] )->add( '_token', HiddenType::class, [
			'data' => wp_create_nonce( 'password_reissue_nonce' ),
		] )->add( 'submit', SubmitType::class, [
			'label' => 'パスワードリセット申請',
			'attr'  => [ 'class' => 'btn btn-primary' ],
		] )->getForm();
	}

	/**
	 * Handle the password reissue logic
	 *
	 * @param string $email
	 *
	 * @return array
	 */
	private function handlePasswordReissue( string $email ): array {
		$user = get_user_by( 'email', $email );

		if ( ! $user ) {
			return [ 'error' => '指定されたメールアドレスは登録されていません。', 'success' => '' ];
		}

		$reset_key = get_password_reset_key( $user );

		if ( is_wp_error( $reset_key ) ) {
			return [ 'error' => 'パスワードリセットのリクエストに失敗しました。', 'success' => '' ];
		}

		$reset_url = add_query_arg( [
			'key'   => $reset_key,
			'login' => rawurlencode( $user->user_login ),
		], );

		$subject = 'パスワードリセット';
		$message = '<p>以下のリンクをクリックしてパスワードをリセットしてください。</p>';
		$message .= '<p><a href="' . $reset_url . '">' . $reset_url . '</a></p>';
		$message .= '<p>このリンクは一定時間で無効になります。</p>';
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		wp_mail( $user->user_email, $subject, $message, $headers );


		return [ 'error' => '', 'success' => 'パスワードリセットのリンクをメールで送信しました。' ];
	}
}
