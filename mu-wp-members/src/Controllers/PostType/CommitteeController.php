<?php

namespace MuWpMembers\Controllers\PostType;

class CommitteeController extends BaseController {

	protected $taxonomy = 'committee_roles';
	protected $post_types = [ 'members_material', 'members_useful', 'members_notification' ];
}
