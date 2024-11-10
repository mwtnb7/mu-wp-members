<?php

namespace MuWpMembers\Models;

use MuWpMembers\Utils\User as UserUtils;
use MuWpMembers\Registers\Roles\Members;
use MuWpMembers\Registers\Roles\Committee;
use MuWpMembers\Registers\Roles\CommitteeEditor;
use WP_User;
use function in_array;
use function is_user_logged_in;

class User {

	public $user;

	public function __construct( $user_id = null ) {
		$this->user = $user_id ? get_userdata( $user_id ) : wp_get_current_user();
	}

	/**
	 * Check if the current user is a members
	 *
	 * @return bool
	 */
	public function isAdminUser(): bool {
		return UserUtils::isAdminUser();
	}

	/**
	 * Check login user
	 *
	 * @return bool
	 */
	public function isLoginUser(): bool {
		return is_user_logged_in();
	}

	/**
	 * Check if the current user is a members
	 *
	 * @return bool
	 */
	public function isMembers(): bool {
		if ( ! $this->isLoginUser() ) {
			return false;
		}

		if ( $this->isAdminUser() ) {
			return true;
		}

		$roles[] = Members::getRoleKeys();
		$roles[] = CommitteeEditor::getRoleKeys();

		foreach ( $this->user->roles as $role ) {
			if ( in_array( $role, $roles ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check if the current user is a committee member
	 *
	 * @return bool
	 */
	public function isCommittee(): bool {
		if ( ! $this->isLoginUser() ) {
			return false;
		}

		if ( $this->isAdminUser() ) {
			return true;
		}

		$roles = Committee::getRoleKeys();

		foreach ( $this->user->roles as $role ) {
			if ( in_array( $role, $roles ) ) {
				return true;
			}
		}

		return false;
	}

	public function getCommitteeLabels(): array {
		$roles  = Committee::getRoleKeys();
		$labels = [];
		foreach ( $this->user->roles as $role ) {
			if ( in_array( $role, $roles ) ) {
				$labels[] = Committee::getRoleName( $role );
			}
		}

		return $labels;
	}

	/**
	 * Check if the user has a specific role
	 *
	 * @param string $role
	 *
	 * @return bool
	 */
	public function hasRole( string $role ): bool {
		return in_array( $role, $this->user->roles );
	}

	/**
	 * Get the user's full name
	 *
	 * @return string
	 */
	public function getViewName(): string {
		if ( $this->user->first_name === '' && $this->user->last_name === '' ) {
			if ( $this->getDisplayName() ) {
				return $this->getDisplayName();
			} else {
				return $this->user->user_login;
			}
		}

		return $this->getFullName();
	}

	/**
	 * Get the user's display name
	 *
	 * @return string
	 */
	public function getDisplayName(): string {
		return $this->user->display_name;
	}

	/**
	 * Get the user's full name
	 *
	 * @return string
	 */
	public function getFullName(): string {
		return $this->user->last_name . ' ' . $this->user->first_name;
	}

	/**
	 * Get the user's email
	 *
	 * @return string
	 */
	public function getEmail(): string {
		return $this->user->user_email;
	}

	/**
	 * Get the user's roles
	 *
	 * @return array
	 */
	public function getRoles(): array {
		return $this->user->roles;
	}

	/**
	 * Get the user's role names
	 *
	 * @return array
	 */
	public function getRoleNames(): array {
		$roles      = $this->user->roles;
		$role_names = [];
		foreach ( $roles as $role ) {
			$role_names[] = Committee::getRoleName( $role );
		}

		return $role_names;
	}

	/**
	 * Get the user's ID
	 *
	 * @return int
	 */
	public function getId(): int {
		return $this->user->ID;
	}

	/**
	 * Update the user's display name
	 *
	 * @param string $display_name
	 *
	 * @return bool|WP_Error
	 */
	public function updateDisplayName( string $display_name ) {
		return wp_update_user( [
			'ID'           => $this->user->ID,
			'display_name' => $display_name
		] );
	}

	/**
	 * Update the user's email
	 *
	 * @param string $email
	 *
	 * @return bool|WP_Error
	 */
	public function updateEmail( string $email ): WP_Error|bool {
		return wp_update_user( [
			'ID'         => $this->user->ID,
			'user_email' => $email
		] );
	}

	/**
	 * Add a role to the user
	 *
	 * @param string $role
	 */
	public function addRole( string $role ): void {
		$this->user->add_role( $role );
	}

	/**
	 * Remove a role from the user
	 *
	 * @param string $role
	 */
	public function removeRole( string $role ): void {
		$this->user->remove_role( $role );
	}
}
