<?php

namespace MuWpMembers\Hooks\Plugins\MWWPForm;

use MuWpMembers\Hooks\HookableInterface;
use MuWpMembers\Models\User;
use function implode;

class MembersForm implements HookableInterface {

	/**
	 * Integrates with WordPress hooks.
	 *
	 * @return void
	 */
	public function initHooks() {
		add_filter( 'mwform_value_mw-wp-form-3501', [ $this, 'setDefaultUserInfo' ], 10, 2 );
	}

	/**
	 * Set default user info to form fields.
	 *
	 * @param mixed $value
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function setDefaultUserInfo( mixed $value, string $name ): mixed {

		if ( is_user_logged_in() ) {
			$user_object = new User();

			// Set user info
			if ( $name === 'お名前' && empty( $value ) ) {
				return $user_object->getFullName();
			}
			if ( $name === 'メールアドレス' && empty( $value ) ) {
				return $user_object->getEmail();
			}

			// Set ACF fields
			if ( $name === '法人・団体名' && empty( $value ) ) {
				$company_name = get_field( 'u_company', 'user_' . $user_object->user->ID );

				return $company_name ? $company_name : $value;
			}
			if ( $name === '所属・部署' && empty( $value ) ) {
				$department = get_field( 'u_department', 'user_' . $user_object->user->ID );

				return $department ? $department : $value;
			}

			// Set committee roles
			if ( $name === '現在の所属委員会' && empty( $value ) ) {
				$labels = $user_object->getCommitteeLabels();

				return $labels ? implode( ', ', $labels ) : $value;
			}
		}

		return $value;
	}
}
