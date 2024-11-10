<?php


namespace MuWpMembers\Models\PostType\Traits;

use CoreWpUtils\Icon;
use CoreWpUtils\URLParser;

trait InfoModifyLink {
	/**
	 * 投稿のリンク設定を取得
	 *
	 * @return mixed
	 */
	public function getLinkSetting(): mixed {
		return $this->getField( 'c_links' );
	}

	/**
	 * URL取得用のメソッドを投稿のカスタムフィールドによって取得するように変更
	 *
	 * @return string
	 */
	public function getPermalink(): string {
		$post_url  = parent::getPermalink();
		$link_data = $this->getLinkSetting();

		if ( $link_data ) {
			if ( $link_data['type'] == 'file' ) {
				$file = wp_get_attachment_url( $link_data['link_file'] );
				if ( $file ) {
					return $file;
				}
			} else if ( $link_data['type'] == 'url' ) {
				if ( $link_data['link_url'] ) {
					return $link_data['link_url'];
				}
			}
		}


		return $post_url;
	}

	/**
	 * 投稿用のURLオプションが有効かどうか
	 * @return bool
	 */
	public function isInfoLinkEnable(): bool {
		$link_data = $this->getLinkSetting();
		if ( isset( $link_data['type'] ) && $link_data['type'] === 'none' ) {
			return false;
		}

		return true;
	}

	/**
	 * 投稿のURLのtarget属性をカスタムフィールド別に取得
	 * @return string
	 */
	public function getLinkTarget(): string {
		$target    = '_self'; // デフォルトのターゲット
		$link_data = $this->getLinkSetting();

		// リンクデータが無い、またはタイプがnormalの場合は、_selfを返す
		if ( ! $link_data || $link_data['type'] === 'normal' ) {
			return $target;
		}

		// リンクタイプに基づいてターゲットを決定
		switch ( $link_data['type'] ) {
			case 'file':
				// ファイルリンクの場合
				$target = $this->determineTargetForFile( $link_data['link_file'] );
				break;
			case 'url':
				// URLの場合
				$target = $this->determineTargetForUrl( $link_data['link_url'] );
				break;
		}

		return $target;
	}

	/**
	 * ファイルリンク用のターゲットを決定
	 *
	 * @param mixed $link_file リンクされたファイル
	 *
	 * @return string
	 */
	private function determineTargetForFile( $link_file ): string {
		$file = wp_get_attachment_url( $link_file );

		return URLParser::is_file_link( $file ) ? '_blank' : '_self';
	}

	/**
	 * URLリンク用のターゲットを決定
	 *
	 * @param string $link_url リンクURL
	 *
	 * @return string
	 */
	private function determineTargetForUrl( string $link_url ): string {
		if ( URLParser::is_external_link( $link_url ) || URLParser::is_file_link( $link_url ) ) {
			return '_blank';
		}

		return '_self';
	}

	/**
	 * リンクのラベルを取得
	 * @return string
	 */
	public function getLinkLabel(): string {
		$label     = 'ページを表示';
		$link_data = $this->getLinkSetting();
		if ( $link_data['type'] == 'file' ) {
			$label = 'ファイルを表示 ';
		} else if ( $link_data['type'] == 'url' ) {
			$url = self::getLinkUrl();

			if ( URLParser::is_file_link( $url ) ) {
				$label = 'ファイルを表示';
			}
		}
		$label .= self::getLinkIcon();

		return $label;
	}

	/**
	 * 投稿一覧でタイトル箇所に表示するアイコンを取得
	 *
	 * @param string $icon_type outlined / rounded / sharp / two tone / two-tone
	 *
	 * @return string
	 */
	public function getLinkIcon( string $icon_type = '' ): string {
		$link_array = wp_parse_args( $this->get_field( 'c_links' ), [
			'type'      => '',
			'link_file' => '',
			'link_url'  => '',
		] );

		// 条件が満たない場合はreturn
		if ( $link_array['type'] === 'normal' || $link_array['type'] === '' ) {
			return '';
		}

		switch ( $link_array['type'] ) {
			case 'file':
				$link = wp_get_attachment_url( (int) $link_array['link_file'] );

				return Icon::get_link_icon( $link );
				break;
			case 'url':
				return Icon::get_link_icon( $link_array['link_url'] );
		}

		return '';
	}
}
