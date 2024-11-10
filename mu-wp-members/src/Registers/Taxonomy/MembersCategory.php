<?php

namespace MuWpMembers\Registers\Taxonomy;

use function acf_add_local_field_group;
use function function_exists;

class MembersCategory extends BaseTaxonomy {
	public function __construct() {
		$taxonomy       = 'members_category'; // タクソノミーのスラッグを指定
		$taxonomy_label = 'カテゴリー'; // タクソノミーのラベルを指定
		$post_types     = [ 'members_post' ]; // カスタム投稿タイプの名前を配列で指定
		$options        = [
			'rewrite' => [
				'slug'       => 'members/news/category',
				'with_front' => false
			]
		]; // タクソノミーのオプションを配列で指定

		parent::__construct( $taxonomy, $taxonomy_label, $post_types, $options );
	}
}
