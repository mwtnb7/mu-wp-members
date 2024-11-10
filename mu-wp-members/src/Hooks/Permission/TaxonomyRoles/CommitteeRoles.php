<?php

namespace MuWpMembers\Hooks\Permission\TaxonomyRoles;

use MuWpMembers\Registers\Roles\Committee;

/**
 * Committee roles restriction.
 */
class CommitteeRoles extends BaseRestriction {

	protected string $taxonomy = 'committee_roles';
	protected array $post_types = [ 'members_post', 'members_notification', 'members_material', 'members_useful' ];
	protected array $filter_taxonomies = [ 'members_category' ];

	public function __construct() {
		$this->roles = Committee::getRoleSlugs();
		parent::__construct();
	}
}
