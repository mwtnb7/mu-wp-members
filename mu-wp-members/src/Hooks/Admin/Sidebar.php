<?php

namespace MuWpMembers\Hooks\Admin;

use MuWpMembers\Hooks\HookableInterface;

class Sidebar implements HookableInterface {

	/**
	 * Integrates with WordPress hooks.
	 *
	 * @return void
	 */
	public function initHooks(): void {
		add_action( 'admin_head', [ $this, 'addStyles' ] );
	}

	/**
	 * Add styles to the admin sidebar.
	 *
	 * @return void
	 */
	public function addStyles(): void {
		if ( ! current_user_can( 'editor_committee' ) ) {
			return;
		}
		?>
		<style>
            #menu-posts {
                display: none;
            }

            #menu-posts + .mywp-sidebar-item {
                display: none;
            }
		</style>
		<?php
	}
}
