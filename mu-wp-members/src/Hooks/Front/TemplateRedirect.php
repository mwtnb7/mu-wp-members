<?php

namespace MuWpMembers\Hooks\Front;

use MuWpMembers\Models\User;
use MuWpMembers\Hooks\HookableInterface;
use MuWpMembers\Tags\Template;
use MuWpMembers\Utils\Url;
use MuWpMembers\Utils\URLParser;
use MuWpMembers\Utils\User as UserUtils;
use function is_page;
use function is_user_logged_in;

class TemplateRedirect implements HookableInterface {

	private mixed $session;
	private User $user;

	private array $post_types = [
		'members_post',
		'members_notification',
		'members_material',
		'members_useful'
	];

	private array $taxonomies = [
		'members_category',
	];

	public function __construct() {
		$this->session = $GLOBALS['session'];
		$this->user    = new User();
	}

	/**
	 * Integrates with WordPress hooks.
	 *
	 * @return void
	 */
	public function initHooks(): void {
		add_filter( 'template_redirect', [ $this, 'archive' ], 99 );
		add_filter( 'template_include', [ $this, 'single' ], 99 );
		add_filter( 'template_include', [ $this, 'page' ], 99 );
	}

	/**
	 * Archive page.
	 *
	 * @return void
	 */
	public function archive(): void {
		global $post_type;

		if ( is_post_type_archive( $this->post_types ) ) {
			// Check if the user is logged in and is a member.
			if ( ! $this->user->isMembers() ) {
				$this->redirectLogin();
			}
			$this->resetTitle();
			Template::getTemplate( 'two-column', 'archive' );
			exit();
		}

		if ( is_tax( $this->taxonomies ) ) {
			// Check if the user is logged in and is a member.
			if ( ! $this->user->isMembers() ) {
				$this->redirectLogin();
			}
			$this->resetTitle();
			Template::getTemplate( 'two-column', 'archive' );
			exit();
		}
	}

	/**
	 * Single page.
	 * Using template_include because the shortcode is not rendering.
	 * @see https://developer.wordpress.org/reference/hooks/template_include/
	 *
	 * @param string $template
	 *
	 * @return string|void
	 */
	public function single( string $template ) {
		if ( $this->user->isMembers() ) {
			if ( is_singular( $this->post_types ) ) {
				$this->resetTitle();
				Template::getTemplate( 'two-column', 'single' );
				exit();
			}
		}

		return $template;
	}

	/**
	 * Page.
	 * Using template_include because the shortcode is not rendering.
	 * @see https://developer.wordpress.org/reference/hooks/template_include/
	 *
	 * @param string $template
	 *
	 * @return string|void
	 */
	public function page( string $template ) {
		if ( URLParser::isMembersPage() && is_page() ) {
			if ( ! $this->user->isMembers() ) {
				wp_redirect( home_url() );
				exit();
			}
			$this->resetTitle();
			Template::getTemplate( 'default', 'page' );
			exit();
		}

		return $template;
	}

	/**
	 * Reset the title.
	 */
	public function resetTitle(): void {
		$this->session->set( 'title', '' );
	}

	/**
	 * Redirect to the login page.
	 */
	public function redirectLogin(): void {
		wp_redirect( Url::getLoginUrl() );
		exit();
	}
}
