<?php
/**
 * Base class for term models
 */

namespace MuWpMembers\Models\Taxonomy;

use WP_Query;
use WP_Term;
use ErrorException;

abstract class BaseTerm {
	public string $taxonomy = '';
	public static string $_taxonomy = '';
	public int $term_id = 0;
	public WP_Term $term;

	/**
	 * Property to store ACF custom fields for the term
	 * @var array
	 */
	public array $fields = [];

	/**
	 * Property to store custom fields for the term
	 * @var array
	 */
	public array $metas = [];

	/**
	 * Initialize the term model
	 *
	 * @param WP_Term|int $term_id Term ID or WP_Term object
	 */
	public function __construct( WP_Term|int $term_id ) {
		if ( is_object( $term_id ) && isset( $term_id->term_id ) ) {
			$term_id = $term_id->term_id;
		}
		$this->term_id = $term_id;
		$this->term    = WP_Term::get_instance( $term_id );
		$this->setFields();
		$this->setMetas();
	}

	/**
	 * Getter method
	 *
	 * @param string $name Property name
	 *
	 * @return mixed
	 */
	public function __get( string $name ) {
		if ( isset( $this->term->{$name} ) ) {
			return $this->term->{$name};
		}

		throw new ErrorException( "Attempting to access a non-existing property" );
	}

	/**
	 * Magic method for dynamic method calls
	 *
	 * @param string $name Method name
	 * @param array $args Method arguments
	 *
	 * @return mixed
	 */
	public function __call( string $name, array $args ) {
		$type = substr( $name, 0, 3 );
		$prop = substr( $name, 4 );

		if ( $type === "get" ) {
			if ( isset( $this->{$prop} ) ) {
				return $this->{$prop};
			}
			if ( isset( $this->term->{$prop} ) ) {
				return $this->term->{$prop};
			}
			if ( isset( $this->fields[ $prop ] ) ) {
				if ( $this->fields[ $prop ]["type"] === "image" && $this->fields[ $prop ]["return_format"] === "id" ) {
					return wp_get_attachment_image_url( $this->fields[ $prop ]["value"], "full" );
				}
				if ( $this->fields[ $prop ]["type"] === "image" && $this->fields[ $prop ]["return_format"] === "array" ) {
					return $this->fields[ $prop ]["value"]["url"];
				}

				return $this->fields[ $prop ]["value"];
			}
		}

		throw new ErrorException( "Attempting to access a non-existing method" );
	}

	/**
	 * Set the custom fields for the term
	 */
	public function setFields(): void {
		$this->fields = get_field_objects( $this->term ) ?: [];
	}

	/**
	 * Get the term name
	 *
	 * @return string
	 */
	public function getName(): string {
		return $this->term->name;
	}

	/**
	 * Get the term ID
	 *
	 * @return int
	 */
	public function getTermId(): int {
		return $this->term_id;
	}

	/**
	 * Get the term slug
	 *
	 * @return string
	 */
	public function getSlug(): string {
		return $this->term->slug;
	}

	/**
	 * Get the term description
	 *
	 * @return string
	 */
	public function getDescription(): string {
		return $this->term->description;
	}

	/**
	 * Get the post count for the term
	 *
	 * @return int
	 */
	public function getCount(): int {
		return $this->term->count;
	}

	/**
	 * Get the parent term
	 *
	 * @return int
	 */
	public function getParent(): int {
		return $this->term->parent;
	}

	/**
	 * Set custom fields for the term
	 */
	public function setMetas(): void {
		$this->metas = get_option( "taxonomy_" . $this->term_id ) ?: [];
	}

	/**
	 * Get a custom field value
	 *
	 * @param string $key Custom field key
	 * @param string $default Default value if not found
	 * @param bool $raw Whether to get the raw value without filtering
	 *
	 * @return mixed
	 */
	public function getField( string $key, string $default = "", bool $raw = false ): mixed {
		if ( isset( $this->fields[ $key ]["value"] ) && $this->fields[ $key ]["value"] ) {
			if ( ! $raw ) {
				if ( $this->fields[ $key ]["type"] === "taxonomy" ) {
					if ( ! is_array( $this->fields[ $key ]["value"] ) ) {
						$term = get_term( $this->fields[ $key ]["value"] );

						return $term->name;
					}
					if ( is_array( $this->fields[ $key ]["value"] ) ) {
						$terms = [];
						foreach ( $this->fields[ $key ]["value"] as $term_id ) {
							$terms[] = get_term( $term_id );
						}

						return $terms;
					}
				}
				if ( $this->fields[ $key ]["type"] === "image" && is_numeric( $this->fields[ $key ]["value"] ) ) {
					return wp_get_attachment_image_url( $this->fields[ $key ]["value"], "full" );
				}
			}

			return $this->fields[ $key ]["value"];
		}

		return $default;
	}

	/**
	 * Get a custom field value
	 *
	 * @param string $key Custom field key
	 * @param string $default Default value if not found
	 * @param bool $raw Whether to get the raw value without filtering
	 *
	 * @return mixed
	 * @deprecated Use getField for naming consistency
	 */
	public function getFieldValue( string $key, string $default = "", bool $raw = false ): mixed {
		return $this->getField( $key, $default, $raw );
	}

	/**
	 * Get a meta value
	 *
	 * @param string $key Meta key
	 * @param string $default Default value if not found
	 *
	 * @return mixed
	 */
	public function getMetaValue( string $key, string $default = "" ): mixed {
		return $this->metas[ $key ] ?? $default;
	}

	/**
	 * Get the archive link for the term
	 *
	 * @return string|WP_Error
	 */
	public function getArchiveLink(): string {
		return get_term_link( $this->term );
	}

	/**
	 * Get the current term for the post
	 *
	 * @param bool $post_id Post ID
	 * @param bool $only Whether to return only the first term
	 *
	 * @return array|mixed
	 */
	public static function getCurrentTerm( bool $post_id = false, bool $only = true ): mixed {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}
		$categories = get_the_terms( $post_id, static::class );
		$terms      = [];
		foreach ( $categories as $category ) {
			$terms[] = new static( $category->term_id );
		}
		if ( $only && $terms && isset( $terms[0] ) && $terms[0] ) {
			return $terms[0];
		}

		return $terms;
	}

	/**
	 * Determine if this term's archive page is being viewed
	 *
	 * @return bool
	 */
	public function isTax(): bool {
		if ( $this->taxonomy === 'category' ) {
			return is_category( $this->term_id );
		}
		if ( $this->taxonomy === 'post_tag' ) {
			return is_tag( $this->term_id );
		}
		if ( $this->isParamMatchingTermId() ) {
			return true;
		}

		return is_tax( $this->taxonomy, [ $this->term_id ] );
	}

	/**
	 * Determine if the current page is the term's archive page
	 *
	 * @return bool
	 */
	public function isParamMatchingTermId(): bool {
		if ( isset( $_GET[ $this->searchParamKey() ] ) ) {
			$param_value = intval( $_GET[ $this->searchParamKey() ] );

			if ( $param_value === $this->term_id ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get a WP_Query object for posts associated with this term
	 *
	 * @param string $post_type Post type
	 * @param int $posts_per_page Number of posts per page
	 *
	 * @return WP_Query
	 */
	public function getPostsQuery( string $post_type, int $posts_per_page = 6 ): WP_Query {
		return new WP_Query( [
			'post_type'      => $post_type,
			'posts_per_page' => $posts_per_page,
			'tax_query'      => [
				[
					'taxonomy' => $this->taxonomy,
					'terms'    => $this->term_id,
				]
			]
		] );
	}

	/**
	 * Get child terms for the current term
	 *
	 * @param array $args Query arguments
	 *
	 * @return array
	 */
	public function getChildrenTerms( array $args = [] ): array {
		$args  = wp_parse_args( $args, [
			'parent'   => $this->term_id,
			'taxonomy' => $this->taxonomy
		] );
		$terms = get_terms( $args );

		$_terms = [];
		if ( $terms ) {
			foreach ( $terms as $term ) {
				$_terms[] = new static( $term->term_id );
			}
		}

		return $_terms;
	}

	/**
	 * Wrapper for get_terms
	 *
	 * @param array $args Query arguments
	 *
	 * @return array
	 */
	public static function getTerms( array $args = [] ): array {
		$args       = wp_parse_args( $args, [
			"taxonomy"   => static::$_taxonomy,
			'hide_empty' => false
		] );
		$terms      = get_terms( $args );
		$repository = [];
		foreach ( $terms as $term ) {
			$repository[] = new static( $term->term_id );
		}

		return $repository;
	}

	/**
	 * Get all terms
	 *
	 * @param bool $has_post hide terms without posts
	 *
	 * @return array(static,static,static...)
	 */
	public static function getAllTerms( bool $has_post = false ): array {
		return static::getTerms( [
			'hide_empty' => $has_post
		] );
	}

	/**
	 * Get top level terms
	 *
	 * @param bool $has_post If true, hide terms without posts
	 *
	 * @return array(static,static,static...)
	 */
	public static function getTopTerms( bool $has_post = false ): array {
		return static::getTerms( [
			'hide_empty' => $has_post,
			'parent'     => 0
		] );
	}

	/**
	 * Get search param key name for the term
	 */
	public function searchParamKey(): string {
		return 's_' . $this->taxonomy;
	}

	/**
	 * Add query params to the current URL
	 *
	 * @param array $params
	 *
	 * @return string
	 */
	public function addParamsToBaseUrl( array $params = [] ): string {
		global $post_type;

		$base_url = get_post_type_archive_link( $post_type );

		if ( ! empty( $params ) ) {
			$query_string = http_build_query( $params );

			return $base_url . '?' . $query_string;
		}

		return $base_url;
	}

	/**
	 * Has search param key in the URL
	 *
	 * @return bool
	 */
	public static function hasSearchParamKey(): bool {
		return isset( $_GET[ 's_' . static::$_taxonomy ] );
	}

	/**
	 * Get current term instance by the role_key
	 *
	 * @param string $role_key
	 *
	 * @return static|bool
	 */
	public static function getTermByRoleKey( string $role_key ): bool|static {
		$terms = self::getAllTerms();
		foreach ( $terms as $term ) {
			$term_meta_key = get_term_meta( $term->term_id, 'role_key', true );
			if ( $term_meta_key && $term_meta_key === $role_key ) {
				return $term;
			}
		}

		return false;
	}
}
