<?php

namespace MuWpMembers\Hooks\Permission;

use MuWpMembers\Hooks\HookableInterface;
use MuWpMembers\Utils\User;
use function defined;
use function in_array;
use function is_object;
use function strpos;
use const MU_WP_MEMBERS_URI;

/**
 * Class TermRestriction for restricting terms based on user roles.
 * @package MuWpMembers\Hooks\Permission
 */
class Terms implements HookableInterface {

	private array $taxonomies = [
		'committee_roles'
	];

	public function initHooks(): void {
		add_filter( 'get_terms', [ $this, 'restrictTerms' ], 10, 3 );
	}

	/**
	 * Restrict terms based on user roles.
	 *
	 * @param array $terms
	 * @param array $taxonomies
	 * @param array $args
	 *
	 * @return array
	 */
	public function restrictTerms( array $terms, array $taxonomies, array $args ): array {
		if ( $args['update_term_meta_cache'] === false ) {
			return $terms;
		}

		if ( User::isAdminUser() ) {
			return $terms;
		}

		if ( $this->isGutenbergRequest() ) {
			return $terms;
		}

		// Check if the taxonomy is allowed
		if ( ! $this->checkAllowedTaxonomy( $taxonomies ) ) {
			return $terms;
		}

		$user       = wp_get_current_user();
		$user_roles = $user->roles;
		$_terms     = [];

		// Check if the user has the role
		if ( $terms ) {
			foreach ( $terms as $term ) {
				if ( ! is_object( $term ) ) {
					continue;
				}
				$role_key = get_term_meta( $term->term_id, 'role_key', true );
				if ( $this->checkRoles( $user_roles, $role_key ) ) {
					$_terms[] = $term;
				}
			}
		}

		return $_terms;
	}

	/**
	 * Check if the taxonomy is allowed.
	 *
	 * @param array $taxonomies
	 *
	 * @return bool
	 */
	private function checkAllowedTaxonomy( array $taxonomies ): bool {
		foreach ( $taxonomies as $taxonomy ) {
			if ( in_array( $taxonomy, $this->taxonomies, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if the user has the role.
	 *
	 * @param array $roles
	 * @param string $role_key
	 *
	 * @return bool
	 */
	private function checkRoles( array $roles, string $role_key ): bool {
		return in_array( $role_key, $roles, true );
	}

	/**
	 * Check if the request is from Gutenberg.
	 *
	 * @return bool
	 */
	private function isGutenbergRequest(): bool {
		return defined( 'REST_REQUEST' ) && REST_REQUEST && isset( $_SERVER['HTTP_REFERER'] ) && ( strpos( $_SERVER['HTTP_REFERER'], 'wp-admin/post.php' ) !== false || strpos( $_SERVER['HTTP_REFERER'], 'wp-admin/post-new.php' ) !== false );
	}

}
