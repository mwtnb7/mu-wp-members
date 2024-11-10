<?php

namespace MuWpMembers\Registers\Taxonomy;

use function taxonomy_exists;
use function register_taxonomy;
use function wp_parse_args;
use WP_Error;

/**
 * RegisterTaxonomy クラスは、WordPress でカスタムタクソノミーを登録するために使用します。
 */
abstract class BaseTaxonomy {
	protected string $taxonomy;
	protected string $taxonomy_label;
	protected array $post_types;
	protected array $options;

	/**
	 * コンストラクタでカスタムタクソノミーの基本情報を設定
	 *
	 * @param string $taxonomy タクソノミーのスラッグ
	 * @param string $taxonomy_label タクソノミーの表示ラベル
	 * @param array $post_types このタクソノミーを関連付ける投稿タイプの配列
	 * @param array $options タクソノミーの追加オプション
	 */
	public function __construct( string $taxonomy, string $taxonomy_label, array $post_types, array $options = [] ) {
		$this->validateInputs( $taxonomy, $taxonomy_label, $post_types );
		$this->initializeOptions( $taxonomy_label, $options );

		$this->taxonomy   = $taxonomy;
		$this->post_types = $post_types;
	}

	/**
	 * タクソノミーの登録
	 */
	public function register(): void {
		if ( ! taxonomy_exists( $this->taxonomy ) ) {
			$result = register_taxonomy( $this->taxonomy, $this->post_types, $this->options );
			if ( is_wp_error( $result ) ) {
				throw new \RuntimeException( 'タクソノミーの登録に失敗しました: ' . $result->get_error_message() );
			}
		}
	}

	/**
	 * 入力パラメータのバリデーション
	 *
	 * @param string $taxonomy タクソノミースラッグ
	 * @param string $taxonomy_label タクソノミーラベル
	 * @param array $post_types 関連付けられる投稿タイプの配列
	 */
	private function validateInputs( string $taxonomy, string $taxonomy_label, array $post_types ): void {
		if ( empty( $taxonomy ) ) {
			throw new \InvalidArgumentException( 'タクソノミースラッグを指定してください' );
		}
		if ( empty( $taxonomy_label ) ) {
			throw new \InvalidArgumentException( 'タクソノミーラベルを指定してください' );
		}
		if ( empty( $post_types ) ) {
			throw new \InvalidArgumentException( 'Post type を配列で最低1件指定してください' );
		}
	}

	/**
	 * タクソノミーのオプションを初期化
	 *
	 * @param string $taxonomy_label タクソノミーのラベル
	 * @param array $options タクソノミーの追加オプション
	 */
	private function initializeOptions( string $taxonomy_label, array $options ): void {
		$labels        = $this->generateLabels( $taxonomy_label );
		$defaults      = [
			'labels'            => $labels,
			'public'            => true,
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => [ 'with_front' => false ],
			'show_in_rest'      => true,
		];
		$this->options = wp_parse_args( $options, $defaults );
	}

	/**
	 * タクソノミーのラベルを生成
	 *
	 * @param string $taxonomy_label タクソノミーのラベル（単数形）
	 *
	 * @return array ラベルの設定配列
	 */
	private function generateLabels( string $taxonomy_label ): array {
		return [
			'name'                       => $taxonomy_label,
			'singular_name'              => $taxonomy_label,
			'menu_name'                  => $taxonomy_label,
			'all_items'                  => $taxonomy_label . '一覧',
			'edit_item'                  => $taxonomy_label . 'の編集',
			'view_item'                  => $taxonomy_label . 'を表示',
			'update_item'                => $taxonomy_label . 'を更新',
			'add_new_item'               => '新しい' . $taxonomy_label . 'の追加',
			'new_item_name'              => '新しい' . $taxonomy_label . '名',
			'parent_item'                => '親' . $taxonomy_label,
			'search_items'               => $taxonomy_label . 'を検索',
			'not_found'                  => '見つかりませんでした',
			'not_found_in_trash'         => 'ゴミ箱内で見つかりませんでした',
			'most_used'                  => 'よく使うもの',
			'back_to_items'              => $taxonomy_label . 'を戻す',
			'popular_items'              => '人気の' . $taxonomy_label,
			'parent_item_colon'          => '親の' . $taxonomy_label,
			'separate_items_with_commas' => $taxonomy_label . 'をコンマで区切ってください',
			'add_or_remove_items'        => $taxonomy_label . 'の追加または削除',
			'choose_from_most_used'      => 'よく使われる' . $taxonomy_label . 'から選択',
			'no_terms'                   => $taxonomy_label . '無し',
			'items_list_navigation'      => $taxonomy_label . '一覧ナビゲーション',
			'items_list'                 => $taxonomy_label . '一覧',
		];
	}
}
