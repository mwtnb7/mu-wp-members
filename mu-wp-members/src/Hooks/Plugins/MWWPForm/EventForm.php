<?php

namespace MuWpMembers\Hooks\Plugins\MWWPForm;

use MuWpMembers\Hooks\HookableInterface;
use MuWpMembers\Models\User;
use function get_field;
use function implode;

class EventForm implements HookableInterface {

	/**
	 * Integrates with WordPress hooks.
	 *
	 * @return void
	 */
	public function initHooks() {
		add_filter( 'mwform_value_mw-wp-form-3475', [ $this, 'setDefaultUserInfo' ], 10, 2 );
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

		}

		// Params
		if ( $name === 'イベント名' && empty( $value ) ) {
			$post_id = $_GET['post_id'] ?? null;

			if ( $post_id ) {
				$event_title = get_field( 'event_title', $post_id );
			}

			return $event_title ?? $value;
		}

		return $value;
	}
}
