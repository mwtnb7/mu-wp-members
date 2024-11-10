<?php

namespace MuWpMembers\Registers\Roles;

/**
 * Class Subscriber for registering roles.
 * @package MuWpMembers\Registers\Roles
 */
class Members extends BaseRoles {
	protected string $base_role = 'subscriber';
	protected bool $removeRoles = false; // Reload after changing to true
	public const ROLES = [
		'corporate_viewer'  => [ 'display_name' => '法人会員' ],
		'individual_viewer' => [ 'display_name' => '個人会員' ],
	];
	protected array $additional_capabilities = [];
	protected array $removal_capabilities = [];
	protected string $taxonomy = ''; // Define the taxonomy to which the terms will be added
	protected bool $hide_admin_bar = true;
}
