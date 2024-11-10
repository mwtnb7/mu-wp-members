<?php

namespace MuWpMembers\Controllers;

/**
 * View controller.
 */

use MuWpMembers\Models\User;
use MuWpMembers\Tags\Template;
use function file_exists;

class ViewController {

	protected mixed $session;
	public array $vars;
	public User $user;

	public function __construct( $vars ) {
		$this->session = $GLOBALS['session'];
		$this->vars    = $vars ?? [];
		$this->user    = $this->getUser();
	}

	/**
	 * Get user model
	 *
	 * @return User
	 */
	public function getUser(): User {
		return new User();
	}

	/**
	 * Render the view
	 *
	 * @param array $props
	 *
	 * @return void
	 */
	public function render(): void {
		$file_name = $this->vars['content_file_name'] ?? '';

		$array = [
			'title'   => $this->getTitle(),
			'slug'    => $this->getSlug(),
			'form'    => $this->vars['form'] ?? null,
			'error'   => $this->vars['error'] ?? null,
			'success' => $this->vars['success'] ?? null,
		];

		$props = array_merge( $this->vars, $array );

		Template::getContent( $file_name, $props );
	}

	/*
	 * Get the content title
	 *
	 * @return string
	 */
	public function getTitle(): string {
		return $this->session->get( 'title' ) ?? '';
	}

	/*
	 * Get the slug
	 *
	 * @return string
	 */
	public function getSlug(): string {
		return $this->session->get( 'slug' ) ?? '';
	}
}
