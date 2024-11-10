<?php

namespace MuWpMembers\Models\PostType;

use CoreWpUtils\Image;

class MembersNotification extends BasePost {

	use Traits\InfoModifyLink;

	protected string $post_type = 'members_notification';
	protected array $taxonomies = [
		'committee_roles',
	];
}
