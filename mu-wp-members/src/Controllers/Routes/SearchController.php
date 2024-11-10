<?php

namespace MuWpMembers\Controllers\Routes;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function ob_get_clean;
use function ob_start;
use WP_Query;

/**
 * Search controller
 */
class SearchController extends BaseController {

	/**
	 * The post types to be included in the search query
	 *
	 * @var array
	 */
	protected array $post_types = [ 'members_post', 'members_notification', 'members_material', 'members_useful' ];

	/**
	 * The number of posts per page
	 *
	 * @var int
	 */
	public int $posts_per_page = 10;

	public function __construct() {
		parent::__construct();

		// Check if user is logged in
		$this->protectLogin();

		// Check if user has access to committee roles
		//      $this->protectCommitteeRoles();

		// setTitle
		$this->setTitle( '検索結果' );
	}

	/**
	 * Search action
	 *
	 * @param Request $request
	 *
	 * @return void
	 */
	public function index( Request $request ): void {
		$search_keyword = $request->query->get( 's' ) ?? '';

		$response = new Response();
		ob_start();
		$this->render( 'two-column', 'search', [
			'search_keyword' => esc_html( $search_keyword ),
			'query'          => $this->getQuery( $search_keyword )
		] );
		$response->setContent( ob_get_clean() );
		$response->send();
	}

	/**
	 * Filter the search query
	 *
	 * @param string $search_keyword
	 *
	 * @return WP_Query
	 */
	public function getQuery( string $search_keyword ): WP_Query {
		return new WP_Query( [
			'post_type'       => $this->post_types,
			'posts_per_page'  => $this->posts_per_page,
			'paged'           => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
			's'               => $search_keyword,
			'is_custom_query' => true
		] );
	}
}
