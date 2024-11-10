<?php

namespace MuWpMembers\Hooks\Permission\TaxonomyRoles;

use MuWpMembers\Hooks\HookableInterface;
use MuWpMembers\Registers\Roles\Committee;
use MuWpMembers\Utils\Url;
use MuWpMembers\Utils\User;
use MuWpMembers\Utils\URLParser;
use function array_fill;
use function array_intersect;
use function array_map;
use function array_merge;
use function count;
use function error_log;
use function get_post_type;
use function get_posts;
use function get_term_meta;
use function get_terms;
use function get_the_ID;
use function get_the_terms;
use function implode;
use function in_array;
use function intval;
use function is_singular;
use function is_tax;
use function print_r;
use function var_dump;
use function wp_get_current_user;
use function wp_list_pluck;
use function wp_redirect;
use function wp_safe_redirect;

/**
 * Base restriction for taxonomy roles.
 */
abstract class BaseRestriction implements HookableInterface {

	protected string $taxonomy = ''; // Set the taxonomy to filter
	protected array $filter_taxonomies = []; // Set taxonomies to filter for is_tax
	protected array $post_types = []; // Set post types to filter
	protected array $terms = [];
	protected array $roles = []; // Set BaseRoles::getRoleSlugs()

	public function __construct() {
	}

	/**
	 * Integrates with WordPress hooks.
	 *
	 * @return void
	 */
	public function initHooks(): void {
		// Run after get_terms is called
		add_action( 'pre_get_posts', [ $this, 'filterPosts' ], 11 );
		add_filter( 'posts_clauses', [ $this, 'filterPostsClauses' ], 11, 2 );
		add_action( 'template_redirect', [ $this, 'restrictPost' ], 11 );
		add_action( 'admin_init', [ $this, 'restrictPostInAdmin' ], 11 );

		if ( URLParser::isMembersPage() ) {
			add_filter( 'get_next_post_where', [ $this, 'filterAdjacentPostsWhere' ], 10, 3 );
			add_filter( 'get_previous_post_where', [ $this, 'filterAdjacentPostsWhere' ], 10, 3 );
			add_filter( 'get_previous_post_join', [ $this, 'filterAdjacentPostsJoin' ], 10, 3 );
			add_filter( 'get_next_post_join', [ $this, 'filterAdjacentPostsJoin' ], 10, 3 );
		}
	}

	/**
	 * Filters posts based on user roles and term meta.
	 *
	 * @param \WP_Query $query The WP_Query instance (passed by reference).
	 *
	 * @return void
	 */
	public function filterPosts( \WP_Query $query ): void {
		if ( ! $query->is_main_query() ) {
			return;
		}

		if ( User::isAdminUser() ) {
			return;
		}

		if ( ! $this->isFilterQueryEnabled( $query ) ) {
			return;
		}

		// Get terms
		$terms = $this->getTermsByQuery( $query );

		// Modify the query to only include posts with allowed terms or posts that don't belong to the taxonomy
		if ( ! empty( $terms ) ) {
			$allowed_term_ids = wp_list_pluck( $terms, 'term_id' );

			if ( ! $this->checkSearchParam( $allowed_term_ids ) ) {
				wp_redirect( Url::getHomeUrl() );
				exit();
			}

			if ( $this->hasSearchParam() ) {
				return;
			}

			if ( is_tax() ) {
				$query->set( 'tax_query', [
					[
						'relation' => 'OR',
						[
							'taxonomy' => $this->taxonomy,
							'field'    => 'term_id',
							'terms'    => $allowed_term_ids,
						],
						[
							'taxonomy' => $this->taxonomy,
							'field'    => 'term_id',
							'operator' => 'NOT EXISTS',
						]
					]
				] );
			} else {
				$query->set( 'tax_query', [
					'relation' => 'OR',
					[
						'taxonomy' => $this->taxonomy,
						'field'    => 'term_id',
						'terms'    => $allowed_term_ids,
					],
					[
						'taxonomy' => $this->taxonomy,
						'field'    => 'term_id',
						'operator' => 'NOT EXISTS',
					]
				] );
			}
		} else {
			// Exclude posts that have terms in the specified taxonomy
			$query->set( 'tax_query', [
				[
					'taxonomy' => $this->taxonomy,
					'field'    => 'term_id',
					'operator' => 'NOT EXISTS',
				]
			] );
		}
	}

	/**
	 * Filters posts based on user roles and term meta using SQL clauses.
	 *
	 * @param array $clauses The SQL clauses for the query.
	 * @param \WP_Query $query The WP_Query instance.
	 *
	 * @return array
	 */
	public function filterPostsClauses( array $clauses, \WP_Query $query ): array {
		if ( $query->is_main_query() ) {
			return $clauses;
		}

		if ( User::isAdminUser() && ! isset( $query->query_vars['custom_filter_term_by'] ) ) {
			return $clauses;
		}

		if ( ! $this->isFilterQueryEnabled( $query ) ) {
			return $clauses;
		}

		// Get terms
		$terms = $this->getTermsByQuery( $query );

		global $wpdb;

		// Modify the SQL clauses to only include posts with allowed terms or exclude posts that belong to the taxonomy
		if ( ! empty( $terms ) ) {
			$allowed_term_ids = wp_list_pluck( $terms, 'term_id' );

			$placeholders = implode( ',', array_fill( 0, count( $allowed_term_ids ), '%d' ) );

			$clauses['join']  .= " LEFT JOIN {$wpdb->term_relationships} AS tr ON {$wpdb->posts}.ID = tr.object_id";
			$clauses['where'] .= $wpdb->prepare( " AND (
			tr.term_taxonomy_id IN ($placeholders) OR 
			(
				SELECT COUNT(*) 
				FROM {$wpdb->term_relationships} AS sub_tr 
				INNER JOIN {$wpdb->term_taxonomy} AS sub_tt 
					ON sub_tr.term_taxonomy_id = sub_tt.term_taxonomy_id 
				WHERE sub_tr.object_id = {$wpdb->posts}.ID 
				AND sub_tt.taxonomy = %s
			) = 0
		)", ...array_merge( $allowed_term_ids, [ $this->taxonomy ] ) );
		} else {
			// Exclude posts that have terms in the specified taxonomy
			$clauses['where'] .= $wpdb->prepare( " AND {$wpdb->posts}.ID NOT IN (
				SELECT tr.object_id
				FROM {$wpdb->term_relationships} AS tr
				INNER JOIN {$wpdb->term_taxonomy} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				WHERE tt.taxonomy = %s
			)", $this->taxonomy );
		}

		return $clauses;
	}

	/**
	 * Redirects users to the homepage if they try to access restricted posts.
	 *
	 * @return void
	 */
	public function restrictPost(): void {
		if ( User::isAdminUser() ) {
			return;
		}

		if ( ! is_singular( $this->post_types ) ) {
			return;
		}

		$post_id        = get_the_ID();
		$terms          = get_the_terms( $post_id, $this->taxonomy );
		$filtered_terms = $this->filterTerms( $terms ?: [] );

		if ( empty( $filtered_terms ) ) {
			// Check if the post is not in the taxonomy
			$results = get_posts( [
				'post_type'      => get_post_type( $post_id ),
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'post__in'       => [ $post_id ],
				'tax_query'      => [
					[
						'taxonomy' => $this->taxonomy,
						'operator' => 'NOT EXISTS',
					],
				],
			] );

			if ( empty( $results ) ) {
				wp_safe_redirect( home_url( '/404' ) );
				exit;
			}
		}
	}

	/**
	 * Restricts access to posts in the admin area.
	 *
	 * @return void
	 */
	public function restrictPostInAdmin(): void {
		if ( User::isAdminUser() ) {
			return;
		}

		global $pagenow, $typenow;

		if ( ! in_array( $typenow, $this->post_types ) ) {
			return;
		}

		if ( $pagenow === 'post.php' || $pagenow === 'post-new.php' ) {
			$post_id = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : 0;
			if ( $post_id ) {
				$terms = get_the_terms( $post_id, $this->taxonomy );

				if ( empty( $terms ) ) {
					wp_safe_redirect( home_url( '/wp-admin' ) );
					exit;
				}
			}
		}
	}

	/**
	 * Checks if the current query should be filtered.
	 *
	 * @param $query
	 *
	 * @return bool
	 */
	protected function isFilterQueryEnabled( $query ): bool {
		if ( isset( $query->query_vars['is_custom_query'] ) && $query->query_vars['is_custom_query'] ) {
			return true;
		}

		if ( $query->is_post_type_archive( $this->post_types ) ) {
			return true;
		}

		if ( $query->is_singular( $this->post_types ) ) {
			return true;
		}

		//		if ( $query->is_search() ) {
		//			return true;
		//		}

		//		if ( $this->query->is_main_query() && ! $this->query->is_page() && ! $this->query->is_home() ) {
		//			return true;
		//		}

		foreach ( $this->filter_taxonomies as $taxonomy ) {
			if ( $query->is_tax( $taxonomy ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Sets terms to filter.
	 *
	 * @param \WP_Query $query The WP_Query instance.
	 *
	 * @return array
	 */
	protected function getTermsByQuery( \WP_Query $query ): array {
		$terms     = [];
		$org_terms = get_terms( [
			'taxonomy' => $this->taxonomy
		] );

		if ( isset( $query->query_vars['custom_filter_term_by'] ) && $query->query_vars['custom_filter_term_by'] ) {
			$slugs = $this->getRoles();

			$role = '';
			foreach ( $slugs as $key => $slug ) {
				if ( $slug === $query->query_vars['custom_filter_term_by'] ) {
					$role = $key;
				}
			}

			foreach ( $org_terms as $term ) {
				$role_key = get_term_meta( $term->term_id, 'role_key', true );

				if ( $role === $role_key ) {
					$terms[] = $term;
				}
			}
		} else {
			$terms = $org_terms;
		}

		return $terms;
	}

	/**
	 * Has search parameter and check if it is in the allowed terms.
	 *
	 * @param array $term_ids
	 *
	 * @return bool
	 */
	protected function checkSearchParam( array $term_ids ): bool {
		if ( isset( $_GET[ 's_' . $this->taxonomy ] ) && ! in_array( $_GET[ 's_' . $this->taxonomy ], $term_ids ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Has search parameter.
	 *
	 * @return bool
	 */
	protected function hasSearchParam(): bool {
		return isset( $_GET[ 's_' . $this->taxonomy ] ) && (int) $_GET[ 's_' . $this->taxonomy ] > 0;
	}

	/**
	 * Filter terms by role.
	 *
	 * @param array $terms
	 *
	 * @return array
	 */
	protected function filterTerms( array $terms ): array {
		if ( empty( $terms ) ) {
			return $terms;
		}

		// Get current user roles
		$current_user = wp_get_current_user();
		$user_roles   = $current_user->roles;

		// Filter terms by role
		$filtered_terms = [];
		foreach ( $terms as $term ) {
			$role_key = get_term_meta( $term->term_id, 'role_key', true );

			if ( in_array( $role_key, $user_roles ) ) {
				$filtered_terms[] = $term;
			}
		}

		return $filtered_terms;
	}

	/**
	 * Sets terms to filter.
	 *
	 * @return array
	 */
	protected function getRoles(): array {
		return $this->roles;
	}

	/**
	 * Filters the JOIN clause for adjacent post links to exclude posts with the 'c_links' meta key.
	 *
	 * @param string $join The JOIN clause in the SQL.
	 * @param bool $in_same_term Whether post should be in the same taxonomy term.
	 * @param string|int[] $excluded_terms Array of excluded term IDs. Empty string if none were provided.
	 *
	 * @return string Modified JOIN clause.
	 */
	public function filterAdjacentPostsJoin( string $join, bool $in_same_term, array|string $excluded_terms ): string {
		global $wpdb;

		// Join postmeta table
		$join .= " LEFT JOIN " . $wpdb->prefix . "postmeta ON (p.ID = " . $wpdb->prefix . "postmeta.post_id) ";

		// Join term_relationships and term_taxonomy tables
		$join .= " LEFT JOIN {$wpdb->term_relationships} AS tr ON (p.ID = tr.object_id) ";
		$join .= " LEFT JOIN {$wpdb->term_taxonomy} AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id) ";

		return $join;
	}

	/**
	 * Filters adjacent posts based on user roles and term meta.
	 *
	 * @param string $where The `WHERE` clause in the SQL.
	 * @param bool $in_same_term Whether post should be in the same taxonomy term.
	 * @param string|int[] $excluded_terms Array of excluded term IDs. Empty string if none were provided.
	 *
	 * @return string
	 *
	 */
	public function filterAdjacentPostsWhere( string $where, bool $in_same_term, array|string $excluded_terms ): string {
		global $post, $wpdb;

		if ( ! in_array( $post->post_type, $this->post_types ) ) {
			return $where;
		}

		// Include only posts with the 'c_links' meta key
		$where .= $wpdb->prepare( " AND p.ID IN (
	        SELECT post_id FROM {$wpdb->postmeta}
	        WHERE meta_key = %s AND meta_value = %s
	    )", 'c_links_type', 'normal' );

		// Get terms for the current user role
		// @see MuWpMembers\Permission\Terms
		$filtered_terms = get_terms( $this->taxonomy );

		// Filter terms by role
		if ( ! empty( $filtered_terms ) ) {
			$allowed_term_ids = wp_list_pluck( $filtered_terms, 'term_id' );

			if ( ! empty( $allowed_term_ids ) ) {
				$allowed_term_ids = implode( ',', array_map( 'intval', $allowed_term_ids ) );

				$where .= " AND ( 
                EXISTS (
                    SELECT 1 
                    FROM {$wpdb->term_relationships} tr
                    WHERE tr.object_id = p.ID
                    AND tr.term_taxonomy_id IN ($allowed_term_ids)
                )
                OR NOT EXISTS (
                    SELECT 1
                    FROM {$wpdb->term_relationships} tr
                    INNER JOIN {$wpdb->term_taxonomy} tt 
                        ON tr.term_taxonomy_id = tt.term_taxonomy_id
                    WHERE tr.object_id = p.ID
                    AND tt.taxonomy = '{$this->taxonomy}'
                )
            )";
			}
		} else {
			$where .= $wpdb->prepare( " AND p.ID NOT IN (
	            SELECT tr.object_id
	            FROM {$wpdb->term_relationships} AS tr
	            INNER JOIN {$wpdb->term_taxonomy} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
	            WHERE tt.taxonomy = %s
	        )", $this->taxonomy );
		}

		return $where;
	}
}
