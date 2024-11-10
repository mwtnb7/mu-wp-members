<?php

namespace MuWpMembers\Registers\Roles;

/**
 * Class Committee for registering roles.
 * @package MuWpMembers\Registers\Roles
 */
class Committee extends BaseRoles {
	protected string $base_role = '';
	protected bool $remove_roles = false;
	const ROLES = [
		'executive_committee'               => [
			'display_name' => '委員会A',
			'slug'         => 'executive',
		],
		'robot_committee'                   => [
			'display_name' => '委員会B',
			'slug'         => 'robot'
		],
		'construction_committee'            => [
			'display_name' => '委員会C',
			'slug'         => 'construction'
		],
		'manufacturing_committee'           => [
			'display_name' => '委員会D',
			'slug'         => 'manufacturing'
		],
		'safety2_committee'                 => [
			'display_name' => '委員会E',
			'slug'         => 'safety2'
		],
		'international_standards_committee' => [
			'display_name' => '委員会F',
			'slug'         => 'international'
		],
	];

	protected array $add_post_types = [];
	protected array $remove_post_types = [];
	protected array $additional_capabilities = [];
	protected array $removal_capabilities = [];
	protected string $taxonomy = 'committee_roles'; // Define the taxonomy to which the terms will be added
	protected bool $hide_admin_bar = true;
}
