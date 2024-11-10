<?php
/**
 * Template for the password reissue page
 *
 * @var \Symfony\Component\Form\FormView $form
 * @var string $error
 * @var string $success
 */
?>

<?php get_header(); ?>

<div class="mu-wp-members-form">
	<h1><?php esc_html_e( 'パスワードリセット申請', 'mu-wp-members' ); ?></h1>

	<?php if ( ! empty( $error ) ): ?>
		<div class="flash-message error-message" onclick="this.style.display='none';">
			<?php echo esc_html( $error ); ?>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $success ) ): ?>
		<div class="flash-message success-message" onclick="this.style.display='none';">
			<?php echo esc_html( $success ); ?>
		</div>
	<?php endif; ?>

	<form id="mu-wp-members-password-reissue-form" method="post">
		<input type="hidden" name="form_type" value="password_reissue">

		<div class="form-group">
			<label for="<?php echo $form['email']->vars['id']; ?>"><?php echo $form['email']->vars['label']; ?></label>
			<input type="email" id="<?php echo $form['email']->vars['id']; ?>" name="<?php echo $form['email']->vars['full_name']; ?>" value="<?php echo $form['email']->vars['value']; ?>" class="form-control" required>
			<?php if ( $form['email']->vars['errors'] ): ?>
				<?php foreach ( $form['email']->vars['errors'] as $error ): ?>
					<div class="error"><?php echo $error->getMessage(); ?></div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>

		<input type="hidden" name="<?php echo $form['_token']->vars['full_name']; ?>" value="<?php echo $form['_token']->vars['value']; ?>">

		<div class="form-group">
			<input type="submit" value="<?php esc_attr_e( 'パスワードリセット申請', 'mu-wp-members' ); ?>" class="submit-button">
		</div>
	</form>

	<p>
		<a href="<?php echo esc_url( home_url( '/members/login/' ) ); ?>">
			<?php esc_html_e( 'ログインはこちら', 'mu-wp-members' ); ?>
		</a>
	</p>
</div>

<?php get_footer(); ?>
