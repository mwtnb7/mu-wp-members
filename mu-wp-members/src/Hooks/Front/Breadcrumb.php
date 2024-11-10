<?php

namespace MuWpMembers\Hooks\Front;

use MuWpMembers\Hooks\HookableInterface;
use MuWpMembers\Utils\Url;
use MuWpMembers\Utils\URLParser;
use function acf_get_post_type_label;
use function array_merge;

class Breadcrumb implements HookableInterface {

	public mixed $session;

	public $base_title = MU_WP_MEMBERS_TITLE;

	public function __construct() {
		$this->session = $GLOBALS['session'];
	}

	/**
	 * Integrates with WordPress hooks.
	 *
	 * @return void
	 */
	public function initHooks(): void {
		if ( ! function_exists( 'yoast_breadcrumb' ) ) {
			return;
		}

		if ( ! URLParser::isMembersPage() ) {
			return;
		}

		add_filter( 'wpseo_breadcrumb_links', [ $this, 'filterBreadcrumbLinks' ] );
	}

	/**
	 * Filter Yoast SEO breadcrumb links.
	 *
	 * @param array $links The current breadcrumb links.
	 *
	 * @return array The modified breadcrumb links.
	 */
	public function filterBreadcrumbLinks( array $links ): array {
		$custom_title = $this->session->get( 'title' );

		if ( $custom_title ) {
			if ( $custom_title !== $this->base_title ) {
				$links[] = [
					'url'  => Url::getHomeUrl(),
					'text' => $this->base_title
				];
			}
			$links[] = [
				'url'  => '',
				'text' => esc_html( $custom_title )
			];
		} else {
			if ( is_archive() ) {
				$before_links[] = [
					'url'  => Url::getHomeUrl(),
					'text' => $this->base_title
				];
			}
			if ( is_tax() ) {
				$post_type      = get_post_type();
				$before_links[] = [
					'url'  => get_post_type_archive_link( $post_type ),
					'text' => acf_get_post_type_label( $post_type )
				];
			}
			if ( is_single() ) {
				$before_links[] = [
					'url'  => Url::getHomeUrl(),
					'text' => $this->base_title
				];
			}
		}

		if ( ! empty( $before_links ) ) {
			array_splice( $links, 1, 0, $before_links );
		}


		return $links;
	}
}
