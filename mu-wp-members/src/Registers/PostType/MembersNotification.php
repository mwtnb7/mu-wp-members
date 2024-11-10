<?php

namespace MuWpMembers\Registers\PostType;

/**
 * Register PostType Class
 *
 * @package CoreWpRegisters\PostType
 */
class MembersNotification extends BasePostType {
	public function __construct() {
		$type    = 'members_notification'; // カスタム投稿タイプのスラッグを指定
		$label   = '連絡事項'; // カスタム投稿タイプの名前を指定
		$options = [ // カスタム投稿タイプのオプションを指定
			'rewrite' => [ 'slug' => 'members/notifications', 'with_front' => false ], // パーマリンクの設定
			'exclude_from_search' => true, // 検索結果から除外
		];

		parent::__construct( $type, $label, $options );
	}
}
