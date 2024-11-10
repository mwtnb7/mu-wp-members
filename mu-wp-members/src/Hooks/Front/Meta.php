<?php

namespace MuWpMembers\Hooks\Front;

use MuWpMembers\Hooks\HookableInterface;
use const MU_WP_MEMBERS_TITLE;

class Meta implements HookableInterface {

	public mixed $session;

	public function __construct() {
		$this->session = $GLOBALS['session'];
	}

	/**
	 * Integrates with WordPress hooks.
	 *
	 * @return void
	 */
	public function initHooks(): void {
		add_filter( 'pre_get_document_title', [ $this, 'filter_document_title' ] );
		add_action( 'wp_head', [ $this, 'add_meta_description' ] );
	}

	/**
	 * Filter the document title.
	 *
	 * @param string $title The document title.
	 *
	 * @return string The filtered document title.
	 */
	public function filter_document_title( string $title ): string {
		$new_title = $this->session->get( 'title' );

		if ( $new_title && $new_title !== MU_WP_MEMBERS_TITLE ) {
			return $new_title . ' | ' . MU_WP_MEMBERS_TITLE . ' | ' . get_bloginfo( 'name' );
		}

		return MU_WP_MEMBERS_TITLE . ' | ' . get_bloginfo( 'name' );
	}

	/**
	 * Add meta description.
	 *
	 * @return void
	 */
	public function add_meta_description(): void {
		$description = $this->session->get( 'description' );

		if ( $description ) {
			echo '<meta name = "description" content = "' . esc_attr( $description ) . '">';
		}
	}
}
