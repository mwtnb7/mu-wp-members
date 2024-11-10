<?php

namespace MuWpMembers\Hooks;

/**
 * Interface for class which has action call to integrates with WordPress hooks.
 */
interface HookableInterface {

	/**
	 * Integrates with WordPress hooks.
	 *
	 * @return void
	 */
	public function initHooks();
}
