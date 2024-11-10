<?php

namespace MuWpMembers\Controllers\PostType;

use MuWpMembers\Traits\Singleton;
use WP_Query;
use function array_map;
use function explode;
use function in_array;

abstract class BaseController {

	use Singleton;

	/**
	 * Taxonomy name to be used for filtering.
	 *
	 * @var string
	 */
	protected $taxonomy;

	/**
	 * Post types to be queried.
	 *
	 * @var array
	 */
	protected $post_types = [];

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'pre_get_posts', [ $this, 'restrictMainQuery' ] );
	}

	/**
	 * Restrict main query based on taxonomy.
	 *
	 * @param WP_Query $query
	 *
	 * @return void
	 */
	public function restrictMainQuery( WP_Query $query ): void {
		if ( ! is_admin() && $query->is_main_query() && $this->isCorrectPostType( $query ) ) {
			$term_ids = $this->getTermsFromRequest();
			if ( ! empty( $term_ids ) ) {
				$this->modifyQueryWithTerms( $query, $term_ids );
			}
		}
	}

	/**
	 * Check if the current query is for the correct post types.
	 *
	 * @param WP_Query $query
	 *
	 * @return bool
	 */
	protected function isCorrectPostType( WP_Query $query ): bool {
		return ! empty( $this->post_types ) && in_array( $query->get( 'post_type' ), $this->post_types, true );
	}

	/**
	 * Get terms from the URL parameters.
	 *
	 * @return array
	 */
	protected function getTermsFromRequest(): array {
		$param_name = 's_' . $this->taxonomy;
		if ( isset( $_GET[ $param_name ] ) && $_GET[ $param_name ] ) {
			return array_map( 'intval', explode( ',', $_GET[ $param_name ] ) );
		}

		return [];
	}

	/**
	 * Modify the query to filter by taxonomy terms.
	 *
	 * @param WP_Query $query
	 * @param array $term_ids
	 *
	 * @return void
	 */
	protected function modifyQueryWithTerms( WP_Query $query, array $term_ids ): void {
		$tax_query = [
			[
				'taxonomy' => $this->taxonomy,
				'field'    => 'term_id',
				'terms'    => $term_ids,
			],
		];
		$query->set( 'tax_query', $tax_query );
	}
}
