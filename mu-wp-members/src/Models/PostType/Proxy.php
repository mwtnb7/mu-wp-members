<?php

namespace MuWpMembers\Models\PostType;

/**
 * Class Proxy
 * @package MuWpMembers\Models\PostType
 */

use CoreWpUtils\Transform;
use function class_exists;
use function is_object;

class Proxy {
	/**
	 * @param int|\WP_Post $post_id
	 *
	 * @return mixed|null
	 */
	public static function getInstance( $post_id ) {
		$post = is_object( $post_id ) ? $post_id : get_post( $post_id );
		if ( ! $post || ! isset( $post->post_type ) ) {
			return null;
		}

		$class_name = "MuWpMembers\\Models\\PostType\\" . Transform::snake_case_to_camel_case( $post->post_type );

		if ( class_exists( $class_name ) ) {
			return new $class_name( $post->ID );
		}

		return null;
	}

	/**
	 * Get instance from global post
	 * @return mixed|null
	 */
	public static function fromGlobal() {
		$post_id = get_the_ID();

		return self::getInstance( $post_id );
	}
}
