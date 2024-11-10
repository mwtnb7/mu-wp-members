<?php

namespace MuWpMembers\Tags;

use function file_exists;
use const MU_WP_MEMBERS_PATH;

/**
 * テンプレートファイルを読み込むためのクラス
 *
 * @package CoreWpHooks\Tags
 */
class Template {

	/**
	 * Include template file core function
	 *
	 * @param string $file_name
	 * @param mixed|null $data
	 *
	 * @return bool
	 */
	public static function includeTemplate( string $file_name, mixed $data = null ): bool {
		if ( $data || is_array( $data ) ) {
			extract( $data );
		}
		$template_path = MU_WP_MEMBERS_PATH . '/' . $file_name . '.php';

		if ( ! file_exists( $template_path ) ) {
			self::error();

			return false;
		}

		include $template_path;

		return true;
	}

	/**
	 * Include the layout file
	 *
	 * @param string $file_name
	 * @param array|string|null $data
	 *
	 * @return void
	 */
	public static function getLayout( string $file_name, mixed $data = null ): void {
		self::includeTemplate( 'views/layouts/' . $file_name, $data );
	}

	/**
	 * Include the component file
	 *
	 * @param string $file_name
	 * @param array|null $data
	 *
	 * @return void
	 */
	public static function getComponent( string $file_name, mixed $data = null ): void {
		self::includeTemplate( 'views/components/' . $file_name, $data );
	}

	/**
	 * Include the project file
	 *
	 * @param string $file_name
	 * @param array $data
	 */
	public static function getProject( string $file_name, mixed $data = null ): void {
		self::includeTemplate( 'views/projects/' . $file_name, $data );
	}

	/**
	 * Include the template file
	 *
	 * @param string $template_name
	 * @param string $content_name
	 * @param array $data
	 *
	 * @return void
	 */
	public static function getTemplate( string $template_name, string $content_name = '', mixed $data = null ): void {
		$template_path = MU_WP_MEMBERS_PATH . 'views/contents/' . $content_name . '.php';
		if ( file_exists( $template_path ) ) {
			$data['content_file_name'] = $content_name;
		}
		self::includeTemplate( 'templates/' . $template_name, $data );
	}

	/**
	 * Include the content file
	 *
	 * @param string $file_name
	 * @param array|null $data
	 */
	public static function getContent( string $file_name, mixed $data = null ): void {
		self::includeTemplate( 'views/contents/' . $file_name, $data );
	}

	/**
	 * Only for logged in users
	 *
	 * @return void
	 */
	private static function error(): void {
		if ( is_user_logged_in() ) {
			echo 'テンプレートがありません';
		}
	}
}
