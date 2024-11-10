<?php
/**
 * Singleton
 *
 * オブジェクトがただ一つだけ存在することを保証する
 *
 * @example
 * class Singleton {
 *    use Singleton;
 * }
 */

namespace MuWpMembers\Traits;

trait Singleton {
	/**
	 * @var self クラスのインスタンス
	 */
	private static $instance;

	/**
	 * クラスの唯一のインスタンスを返す
	 *
	 * @return self インスタンス
	 */
	public static function getInstance(): self {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * コンストラクタをprivateまたはprotectedにする
	 */
	protected function __construct() {
	}

	/**
	 * インスタンスの複製を防ぐ
	 */
	private function __clone() {
	}

	/**
	 * unserializeを防ぐ
	 */
	public function __wakeup() {
		throw new \Exception( "Cannot unserialize a singleton." );
	}
}
