<?php

namespace MuWpMembers\Models\PostType;

use CoreWpUtils\Image;

class MembersPost extends BasePost {

	use Traits\InfoModifyLink;

	protected string $post_type = 'members_post';
	protected array $taxonomies = [
		'members_category',
		'committee_roles',
	];
}
