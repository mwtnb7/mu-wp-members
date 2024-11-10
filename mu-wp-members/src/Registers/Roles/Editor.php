<?php

namespace MuWpMembers\Registers\Roles;

/**
 * Class Editor for registering roles.
 * @package MuWpMembers\Registers\Roles
 */
class Editor extends BaseRoles {
	protected string $base_role = 'editor';
	protected bool $remove_roles = false; // Reload after changing to true
	public const ROLES = [
		'office_admin' => [ 'display_name' => '編集ユーザーA' ],
	];
	protected array $additional_capabilities = [];
	protected array $removal_capabilities = [];
	protected string $taxonomy = ''; // Define the taxonomy to which the terms will be added
}
