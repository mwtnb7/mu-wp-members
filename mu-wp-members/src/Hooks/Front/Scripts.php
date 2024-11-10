<?php

namespace MuWpMembers\Hooks\Front;

use MuWpMembers\Hooks\HookableInterface;

class Scripts implements HookableInterface {

	/**
	 * Integrates with WordPress hooks.
	 *
	 * @return void
	 */
	public function initHooks(): void {
		add_action( 'wp_enqueue_scripts', [ $this, 'addStyles' ] );
	}

	/**
	 * Add custom styles to the front-end.
	 *
	 * @return void
	 */
	public function addStyles(): void {
		wp_enqueue_style( 'my-custom-styles', MU_WP_MEMBERS_URI . '/assets/css/styles.css' );
	}
}
