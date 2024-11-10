<?php
/**
 * Template for the login page
 *
 * @var \Symfony\Component\Form\FormView $form
 * @var array $error
 * @var array $success
 */
?>

<section class="l-section is-xlg is-color-secondary">
	<div class="l-container">
		<div class="c-forms">
			<div class="c-forms__inner">
				<h1 class="c-heading is-md">会員ログイン</h1>
				<?php
				if ( ! empty( $error ) ) {
					foreach ( $error as $errorMessage ) {
						?>
						<div class="flash-message error-message">
							<?php echo esc_html( $errorMessage ); ?>
						</div>
						<?php
					}
				}
				if ( ! empty( $success ) ) {
					foreach ( $success as $successMessage ) {
						?>
						<div class="flash-message success-message">
							<?php echo esc_html( $successMessage ); ?>
						</div>
						<?php
					}
				}
				?>
				<form id="mu-wp-members-login-form" method="post">
					<div class="c-forms__blocks">
						<div class="c-forms__block">
							<div class="c-forms__title">メールアドレス<span class="c-forms__label">必須</span></div>
							<div class="c-forms__content">
								<div class="c-forms__input">
									<input type="text" id="<?php echo $form['username']->vars['id'] ?>" name="<?php echo $form['username']->vars['full_name'] ?>" value="<?php echo $form['username']->vars['value'] ?>" placeholder="" required>
								</div>
							</div>
						</div>

						<div class="c-forms__block">
							<div class="c-forms__title">パスワード<span class="c-forms__label">必須</span></div>
							<div class="c-forms__content">
								<div class="c-forms__input">
									<input type="password" id="<?php echo $form['password']->vars['id'] ?>" name="<?php echo $form['password']->vars['full_name'] ?>" placeholder="" required>
								</div>
							</div>
						</div>
					</div>

					<input type="hidden" name="<?php echo $form['_token']->vars['full_name'] ?>" value="<?php echo $form['_token']->vars['value'] ?>">

					<div class="c-forms__submit">
						<input type="submit" value="<?php esc_attr_e( 'ログイン', 'mu-wp-members' ) ?>" class="c-button-submit">
					</div>
					<div class="c-forms__submit">
						<a class="c-button-submit is-bg-accent" href="<?php echo esc_url( home_url() . '/wp/institute-gsafety-login.php?action=lostpassword' ) ?>" target="_blank">パスワードの任意設定はこちら</a>
					</div>
				</form>

				<!--				<div class="c-forms__submit-note">-->
				<!--					<p>ログインできない方は <a href="mailto:info@xxxx.com">info@xxxx.com</a> までお問い合わせください。</p>-->
				<!--				</div>-->
				<hr class="c-forms__divider">
				<div class="c-forms__submit">
					<a class="c-button-submit" href="<?php echo esc_url( home_url( '/add-members-corp/' ) ) ?>">新規会員登録はこちら</a>
				</div>
				<div class="c-forms__submit">
					<a class="c-button-submit is-bg-accent" href="<?php echo esc_url( home_url( '/membership/' ) ) ?>">入会についてはこちら</a>
				</div>
			</div>
		</div>
	</div>
</section>
