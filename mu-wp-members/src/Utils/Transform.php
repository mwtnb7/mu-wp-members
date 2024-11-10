<?php

namespace MuWpMembers\Utils;

use function str_replace;
use function ucwords;

/**
 * Utility class for string conversion
 *
 * @package MuWpMembers\Utils
 */
class Transform {

	/**
	 * Convert snake_case to camelCase
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function snakeCaseToCamelCase( string $string ): string {
		// スネークケースをスペースで区切る
		$string = str_replace( '_', ' ', $string );
		// 各単語の最初の文字を大文字に変換
		$string = ucwords( $string );
		// スペースを削除
		$string = str_replace( ' ', '', $string );

		return $string;
	}

	/**
	 * Escape HTML string with wp_kses function with default allowed tags
	 *
	 * @param string $html
	 * @param array $allowed_html
	 *
	 * @return string
	 */
	public static function escHtml( string $html, array $allowed_html = [ "br" => [] ] ): string {
		return wp_kses( $html, $allowed_html );
	}
}
