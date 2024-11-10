<?php

namespace MuWpMembers\Registers\PostType;

use function post_type_exists;
use function register_post_type;
use function wp_parse_args;

/**
 * RegisterPostType クラスはカスタム投稿タイプをWordPressに登録するために使用します。
 */
abstract class BasePostType {
	protected string $type;
	protected string $label;
	protected array $options;

	/**
	 * コンストラクタ
	 *
	 * @param string $type 投稿タイプのスラッグ
	 * @param string $label 投稿タイプの表示名
	 * @param array $options 投稿タイプの追加オプション
	 */
	public function __construct( string $type, string $label, array $options = [] ) {
		$this->set_type( $type );
		$this->set_label( $label );
		$this->set_options( $label, $options );
	}

	/**
	 * 投稿タイプがすでに存在するかどうかをチェックし、存在しない場合は登録します
	 */
	public function register(): void {
		if ( ! post_type_exists( $this->type ) ) {
			$result = register_post_type( $this->type, $this->options );
			if ( is_wp_error( $result ) ) {
				throw new \RuntimeException( 'カスタム投稿タイプの登録に失敗しました: ' . $result->get_error_message() );
			}
		}
	}

	/**
	 * 投稿タイプのスラッグを設定します
	 *
	 * @param string $type 投稿タイプのスラッグ
	 */
	private function set_type( string $type ): void {
		if ( empty( $type ) ) {
			throw new \InvalidArgumentException( 'カスタム投稿タイプのスラッグを指定してください' );
		}
		$this->type = $type;
	}

	/**
	 * 投稿タイプのラベルを設定
	 *
	 * @param string $label 投稿タイプの表示名
	 */
	private function set_label( string $label ): void {
		if ( empty( $label ) ) {
			throw new \InvalidArgumentException( 'カスタム投稿タイプのラベルを指定してください' );
		}
		$this->label = $label;
	}

	/**
	 * 投稿タイプのオプションを初期化
	 *
	 * @param string $label 投稿タイプのラベル
	 * @param array $options 投稿タイプのオプション
	 */
	private function set_options( string $label, array $options ): void {
		$labels         = $this->generate_labels( $label );
		$defaultOptions = [
			'label'               => $label,
			'labels'              => $labels,
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'query_var'           => true,
			'rewrite'             => [ 'slug' => $this->type, 'with_front' => false ],
			'capability_type'     => 'post',
			'has_archive'         => true,
			'hierarchical'        => false,
			'menu_position'       => 5,
			'supports'            => [ 'title', 'editor', 'author', 'revisions' ],
			'description'         => "カスタム投稿タイプ「{$label}」の説明文です。",
			'taxonomies'          => [],
			'exclude_from_search' => false,
			'show_in_rest'        => true,
		];
		$this->options  = wp_parse_args( $options, $defaultOptions );
	}

	/**
	 * 投稿タイプのラベルオプションを生成
	 *
	 * @param string $label 投稿タイプの表示名
	 *
	 * @return array ラベルの配列
	 */
	private function generate_labels( string $label ): array {
		return [
			'name'                     => $label,
			'singular_name'            => $label,
			'add_new'                  => '新規追加',
			'add_new_item'             => '新しい' . $label . 'を追加',
			'edit_item'                => $label . 'の編集',
			'new_item'                 => '新規' . $label,
			'view_item'                => $label . 'の表示',
			'view_items'               => $label . 'の表示',
			'search_items'             => $label . 'の検索',
			'not_found'                => $label . 'が見つかりませんでした',
			'not_found_in_trash'       => 'ゴミ箱に' . $label . 'は見つかりませんでした',
			'parent_item_colon'        => '親:',
			'all_items'                => $label . '一覧',
			'archives'                 => $label . 'アーカイブ',
			'attributes'               => $label . '属性',
			'insert_into_item'         => $label . 'の挿入',
			'uploaded_to_this_item'    => $label . 'へのアップロード',
			'featured_image'           => 'アイキャッチ画像',
			'set_featured_image'       => 'アイキャッチ画像を設定',
			'remove_featured_image'    => 'アイキャッチ画像を削除',
			'use_featured_image'       => 'アイキャッチ画像を使用',
			'filter_items_list'        => $label . '一覧の絞り込み',
			'items_list_navigation'    => $label . '一覧ナビゲーション',
			'items_list'               => $label . '一覧',
			'item_published'           => '公開済みの' . $label,
			'item_published_privately' => '非公開の' . $label,
			'item_reverted_to_draft'   => '下書きの' . $label,
			'item_scheduled'           => 'スケジュールされた' . $label,
			'item_updated'             => $label . 'の更新',
		];
	}
}
