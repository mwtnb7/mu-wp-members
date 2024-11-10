<?php

namespace MuWpMembers\Models\PostType;

use CoreWpUtils\Image;

class MembersUseful extends BasePost {

	use Traits\InfoModifyLink;

	protected string $post_type = 'members_useful';
	protected array $taxonomies = [
		'committee_roles',
	];
}
