<?php

namespace MuWpMembers\Registers\PostType;

use function acf_add_local_field_group;
use function function_exists;

/**
 * Register PostType Class
 *
 * @package CoreWpRegisters\PostType
 */
class MembersPost extends BasePostType {
	public function __construct() {
		$type    = 'members_post'; // カスタム投稿タイプのスラッグを指定
		$label   = '会員向けお知らせ'; // カスタム投稿タイプの名前を指定
		$options = [ // カスタム投稿タイプのオプションを指定
			'rewrite' => [ 'slug' => 'members/news', 'with_front' => false ], // パーマリンクの設定
		];

		parent::__construct( $type, $label, $options );
	}
}
