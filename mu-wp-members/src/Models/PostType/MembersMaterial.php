<?php

namespace MuWpMembers\Models\PostType;

use CoreWpUtils\Image;

class MembersMaterial extends BasePost {

	use Traits\InfoModifyLink;

	protected string $post_type = 'members_material';
	protected array $taxonomies = [
		'committee_roles',
	];
}
