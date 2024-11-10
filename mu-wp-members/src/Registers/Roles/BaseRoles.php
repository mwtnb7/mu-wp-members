<?php

namespace MuWpMembers\Registers\Roles;

use function array_keys;
use function current_user_can;
use function error_log;
use function show_admin_bar;
use function wp_roles;

/**
 * Base roles.
 */
abstract class BaseRoles {
	protected string $base_role = ''; // admin|editor|author|contributor|subscriber|custom_role|empty
	protected bool $remove_roles = false; // Reload after changing to true
	protected array $add_post_types = []; // ['post_type_slug1', 'post_type_slug2']
	protected array $remove_post_types = []; // ['post_type_slug1', 'post_type_slug2']
	protected array $additional_capabilities = []; // ['capability1', 'capability2']
	protected array $removal_capabilities = []; // ['capability1', 'capability2']
	protected string $taxonomy = ''; // Define the taxonomy to which the terms will be added
	protected bool $hide_admin_bar = false;

	public function __construct() {
		$this->addRoles();

		/**
		 * Remove roles if $removeRoles is true
		 */
		if ( $this->remove_roles ) {
			$this->removeRoles();
		}

		$this->hideAdminBar();
	}

	/**
	 * Add roles
	 *
	 * @return void
	 */
	protected function addRoles(): void {
		foreach ( $this->getRoles() as $role => $data ) {
			if ( ! $this->roleExists( $role ) ) {
				$capabilities = $this->base_role ? get_role( $this->base_role )->capabilities : [];
				$this->addRole( $role, $data['display_name'], $capabilities );
			}
			$this->addPostTypeCapabilities( $role, $this->add_post_types );
			$this->removePostTypeCapabilities( $role, $this->remove_post_types );
			$this->addAdditionalCapabilities( $role, $this->additional_capabilities );
			$this->removeCapabilities( $role, $this->removal_capabilities );
			$this->addTermToTaxonomy( $role, $data['display_name'], $this->taxonomy );
		}
	}

	/**
	 * Get roles
	 *
	 * @return array
	 */
	protected function getRoles(): array {
		return static::ROLES;
	}

	/**
	 * Add role
	 *
	 * @param string $role
	 * @param string $display_name
	 * @param array $capabilities
	 *
	 * @return void
	 */
	protected function addRole( string $role, string $display_name, array $capabilities = [] ): void {
		if ( ! $this->roleExists( $role ) ) {
			add_role( $role, $display_name, $capabilities );
		}
	}

	/**
	 * Remove roles
	 *
	 * @return void
	 */
	protected function removeRoles(): void {
		foreach ( array_keys( $this->getRoles() ) as $role ) {
			$this->removeRole( $role );
		}
	}

	/**
	 * Remove role
	 *
	 * @param string $role
	 *
	 * @return void
	 */
	protected function removeRole( string $role ): void {
		if ( $this->roleExists( $role ) ) {
			remove_role( $role );
		}
	}

	/**
	 * Check if role exists
	 *
	 * @param string $role
	 *
	 * @return bool
	 */
	protected function roleExists( string $role ): bool {
		return wp_roles()->is_role( $role );
	}

	/**
	 * Add post type capabilities
	 *
	 * @param string $role
	 * @param array $post_types
	 *
	 * @return void
	 */
	protected function addPostTypeCapabilities( string $role, array $post_types ): void {
		$role_object = get_role( $role );
		if ( $role_object ) {
			foreach ( $post_types as $post_type ) {
				$capabilities = $this->getPostTypeCapabilities( $post_type );
				foreach ( $capabilities as $cap => $mapped_cap ) {
					$role_object->add_cap( $mapped_cap );
				}
			}
		}
	}

	/**
	 * Remove post type capabilities
	 *
	 * @param string $role
	 * @param array $post_types
	 *
	 * @return void
	 */
	protected function removePostTypeCapabilities( string $role, array $post_types ): void {
		$role_object = get_role( $role );
		if ( $role_object ) {
			foreach ( $post_types as $post_type ) {
				$capabilities = $this->getPostTypeCapabilities( $post_type );
				foreach ( $capabilities as $cap => $mapped_cap ) {
					$role_object->remove_cap( $mapped_cap );
				}
			}
		} else {
			// Debug message if the role does not exist
			error_log( "Role $role does not exist." );
		}
	}

	/**
	 * Add additional capabilities
	 *
	 * @param string $role
	 * @param array $capabilities
	 *
	 * @return void
	 */
	protected function addAdditionalCapabilities( string $role, array $capabilities ): void {
		$role_object = get_role( $role );
		if ( $role_object ) {
			foreach ( $capabilities as $capability ) {
				$role_object->add_cap( $capability );
			}
		}
	}

	/**
	 * Remove capabilities
	 *
	 * @param string $role
	 * @param array $capabilities
	 *
	 * @return void
	 */
	protected function removeCapabilities( string $role, array $capabilities ): void {
		$role_object = get_role( $role );
		if ( $role_object ) {
			foreach ( $capabilities as $capability ) {
				$role_object->remove_cap( $capability );
			}
		}
	}

	/**
	 * Get post type capabilities
	 *
	 * @param string $post_type
	 *
	 * @return array
	 */
	protected function getPostTypeCapabilities( string $post_type ): array {
		return [
			'edit_posts'             => "edit_{$post_type}s",
			'edit_others_posts'      => "edit_others_{$post_type}s",
			'publish_posts'          => "publish_{$post_type}s",
			'read_private_posts'     => "read_private_{$post_type}s",
			'delete_posts'           => "delete_{$post_type}s",
			'delete_private_posts'   => "delete_private_{$post_type}s",
			'delete_published_posts' => "delete_published_{$post_type}s",
			'delete_others_posts'    => "delete_others_{$post_type}s",
			'edit_private_posts'     => "edit_private_{$post_type}s",
			'edit_published_posts'   => "edit_published_{$post_type}s",
		];
	}

	/**
	 * Add term to taxonomy
	 *
	 * @param string $slug
	 * @param string $name
	 * @param string $taxonomy
	 *
	 * @return void
	 */
	protected function addTermToTaxonomy( string $slug, string $name, string $taxonomy ): void {
		if ( ! $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
			return;
		}

		if ( ! term_exists( $slug, $taxonomy ) ) {
			$term = wp_insert_term( $name, $taxonomy, [ 'slug' => $slug ] );
			if ( ! is_wp_error( $term ) ) {
				add_term_meta( $term['term_id'], 'role_key', $slug, true );
			}
		}
	}

	/**
	 * Get role list
	 *
	 * @return array
	 */
	public static function getRoleList(): array {
		$roles = [];

		foreach ( static::ROLES as $role => $data ) {
			$roles[ $role ]['name'] = $data['display_name'] ?? $role;
			$roles[ $role ]['slug'] = $data['slug'] ?? '';
		}

		return $roles;
	}

	/**
	 * Get role name
	 *
	 * @param string $role
	 *
	 * @return string
	 */
	public static function getRoleName( string $role ): string {
		return static::ROLES[ $role ]['display_name'] ?? $role;
	}

	/**
	 * Get role keys
	 *
	 * @return array
	 */
	public static function getRoleKeys(): array {
		$roles = [];

		foreach ( static::ROLES as $role => $data ) {
			$roles[] = $role;
		}

		return $roles;
	}

	/**
	 * Get role key by slug
	 *
	 * @param string $slug
	 *
	 * @return string
	 */
	public static function getRoleKeyBySlug( string $slug ): string {
		foreach ( static::ROLES as $role => $data ) {
			if ( $data['slug'] === $slug ) {
				return $role;
			}
		}

		return '';
	}

	/**
	 * Get role slugs
	 *
	 * @return array
	 */
	public static function getRoleSlugs(): array {
		$roles = [];

		foreach ( static::ROLES as $role => $data ) {
			$roles[ $role ] = $data['slug'] ?? $role;
		}

		return $roles;
	}

	/**
	 * Show admin bar for specific roles
	 *
	 * @return void
	 */
	protected function hideAdminBar(): void {
		$has_role = false;

		foreach ( array_keys( $this->getRoles() ) as $role ) {
			if ( current_user_can( $role ) ) {
				$has_role = true;
				break;
			}
		}

		if ( $has_role ) {
			if ( $this->hide_admin_bar ) {
				show_admin_bar( false );
			} else {
				show_admin_bar( true );
			}
		}
	}
}
