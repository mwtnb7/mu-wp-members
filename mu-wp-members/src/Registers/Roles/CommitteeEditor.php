<?php

namespace MuWpMembers\Registers\Roles;

/**
 * Class CommitteeEditor for registering roles.
 * @package MuWpMembers\Registers\Roles
 */
class CommitteeEditor extends BaseRoles {
	protected string $base_role = 'author';
	protected bool $remove_roles = false; // Reload after changing to true
	public const ROLES = [
		'editor_committee' => [ 'display_name' => '編集ユーザーB' ],
	];
	protected array $add_post_types = [
		'members_post',
		'members_material',
		'members_notification',
		'members_useful',
	];
	protected array $remove_post_types = [
		'page',
	];
	protected array $additional_capabilities = [];
	protected array $removal_capabilities = [
		'manage_categories',
	];
	protected string $taxonomy = ''; // Define the taxonomy to which the terms will be added
}
