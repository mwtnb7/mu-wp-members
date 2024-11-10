<?php

namespace MuWpMembers\Hooks\Admin\User;

use MuWpMembers\Hooks\HookableInterface;

class UserRole implements HookableInterface {

	/**
	 * Integrates with WordPress hooks.
	 *
	 * @return void
	 */
	public function initHooks(): void {
		add_action( 'wp_head', [ $this, 'loadAssets' ] );
		add_filter( 'show_admin_bar', [ $this, 'showAdminBar' ], 9999, 1 );
	}

	/**
	 * Show the admin bar.
	 *
	 * @param bool $show Whether to show the admin bar.
	 *
	 * @return bool
	 */
	public function showAdminBar( bool $show ): bool {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		if ( ! current_user_can( 'administrator' ) && ! current_user_can( 'editor' ) ) {
			return false;
		}

		return true;
	}


	/**
	 * Load assets for the admin bar.
	 */
	public function loadAssets(): void {
		echo '<pre>';
		echo var_dump(is_admin_bar_showing());
		echo '</pre>';
		if ( ! is_admin_bar_showing() ) {
			return;
		}

		wp_enqueue_style( 'admin-bar', includes_url( 'css/admin-bar.css' ) );
		wp_enqueue_script( 'admin-bar', includes_url( 'js/admin-bar.js' ), [ 'jquery' ], false, true );
	}
}
