<?php

namespace MuWpMembers\Hooks\Admin\User;

use MuWpMembers\Hooks\HookableInterface;
use WP_User;
use function in_array;
use function is_array;
use function is_object;

class UserRole implements HookableInterface {

	/**
	 * Integrates with WordPress hooks.
	 *
	 * @return void
	 */
	public function initHooks(): void {
		add_action( 'user_new_form', [ $this, 'addCustomRolesField' ] );
		add_action( 'edit_user_profile', [ $this, 'addCustomRolesField' ] );
		add_action( 'user_register', [ $this, 'saveCustomRoles' ] );
		add_action( 'profile_update', [ $this, 'saveCustomRoles' ] );
	}

	/**
	 * Add custom roles field to the user-new.php and user-edit.php forms.
	 *
	 * @param WP_User $user The user object.
	 */
	/**
	 * Add custom roles field to the user-new.php and user-edit.php forms.
	 *
	 * @param WP_User $user The user object.
	 */
	public function addCustomRolesField( $user ): void {
		// Get all available roles
		global $wp_roles;
		$all_roles = $wp_roles->roles;

		// Define the roles to exclude
		$excluded_roles = [
			'administrator',
			'editor',
			'author',
			'contributor',
			'subscriber',
			'wpseo_manager',
			'wpseo_editor',
			'corporate_viewer',
			'individual_viewer',
			'editor_committee',
			'office_admin'
		];

		// Translate roles to Japanese
		$translated_roles = [
			'administrator' => '管理者',
			'editor'        => '編集者',
			'author'        => '投稿者',
			'contributor'   => '寄稿者',
			'subscriber'    => '購読者'
		];

		// Get user roles if editing an existing user
		$selected_roles = is_object( $user ) ? $user->roles : [];
		?>
		<h3><?php _e( "所属委員会の選択", "growp" ); ?></h3>
		<p class="description"><?php _e( "ユーザーが所属する委員会を選択してください。", "growp" ); ?></p>
		<table class="form-table">
			<tr>
				<th><label for="additional_roles"><?php _e( "所属委員会", "growp" ); ?></label></th>
				<td>
					<?php foreach ( $all_roles as $role_key => $role ) : ?>
						<?php if ( ! in_array( $role_key, $excluded_roles ) ) : ?>
							<label>
								<input type="checkbox" name="additional_roles[]" value="<?php echo esc_attr( $role_key ); ?>" <?php echo in_array( $role_key, $selected_roles ) ? 'checked="checked"' : ''; ?>>
								<?php echo esc_html( $translated_roles[ $role_key ] ?? $role['name'] ); ?>
							</label><br>
						<?php endif; ?>
					<?php endforeach; ?>
				</td>
			</tr>
		</table>
		<?php
	}


	/**
	 * Save additional roles when a user is created or updated.
	 *
	 * @param int $user_id The user ID.
	 */
	public function saveCustomRoles( int $user_id ): void {
		if ( isset( $_POST['additional_roles'] ) && is_array( $_POST['additional_roles'] ) ) {
			$user = new WP_User( $user_id );

			// Add selected roles
			foreach ( $_POST['additional_roles'] as $role ) {
				$user->add_role( sanitize_text_field( $role ) );
			}
		}
	}
}
