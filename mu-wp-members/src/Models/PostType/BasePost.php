<?php

namespace MuWpMembers\Models\PostType;

use MuWpMembers\Tags\Nav;
use CoreWpUtils\Image;
use MuWpMembers\Utils\Transform;
use ErrorException;
use WP_Post;
use WP_Term;

abstract class BasePost {
	protected int $post_id;
	protected string $post_type = '';
	protected WP_Post $post;
	protected array $fields = [];
	protected array $terms = [];
	protected array $taxonomies = [];

	public function __construct( int $post_id ) {
		$this->post_id = $post_id;
		$this->post    = is_preview() ? $GLOBALS['post'] : get_post( $post_id );
		$this->setFields();
		$this->setTerms();
	}

	public static function fromGlobal(): self {
		return new static( get_the_ID() );
	}

	public function __call( string $name, array $args ) {
		if ( strpos( $name, 'get' ) === 0 ) {
			$prop = lcfirst( substr( $name, 3 ) );
			if ( ! property_exists( $this, $prop ) && ! property_exists( $this->post, $prop ) ) {
				error_log( "Attempted to call $name, but the property $prop does not exist." );
			}

			return $this->getProperty( $prop );
		}

		return null;
	}

	public function __get( string $name ) {
		return $this->getProperty( $name );
	}

	protected function getProperty( string $prop ) {
		if ( property_exists( $this, $prop ) ) {
			return $this->$prop;
		}
		if ( property_exists( $this->post, $prop ) ) {
			return $this->post->$prop;
		}
		if ( isset( $this->fields[ $prop ] ) ) {
			return $this->getFieldValue( $prop );
		}

		return null;
	}

	protected function getFieldValue( string $prop ): mixed {
		$field = $this->fields[ $prop ];
		if ( $field['type'] === 'image' ) {
			return $this->getImageFieldValue( $field );
		}

		return $field['value'];
	}

	protected function getImageFieldValue( array $field ): string {
		if ( $field['return_format'] === 'id' ) {
			return wp_get_attachment_image_url( $field['value'], 'full' );
		}
		if ( $field['return_format'] === 'array' ) {
			return $field['value']['url'];
		}

		return '';
	}

	protected function setFields(): void {
		$this->fields = get_field_objects( $this->post_id ) ?: [];
	}

	protected function setTerms(): void {
		foreach ( $this->taxonomies as $taxonomy ) {
			$this->setTermsByTaxonomy( $taxonomy );
		}
	}

	protected function setTermsByTaxonomy( string $taxonomy ): void {
		$terms = get_the_terms( $this->post_id, $taxonomy );
		if ( ! $terms ) {
			return;
		}

		$this->terms[ $taxonomy ] = array_map( function ( $term ) {
			$class_name = 'MuWpMembers\\Models\\Taxonomy\\' . Transform::snakeCaseToCamelCase( $term->taxonomy );

			return class_exists( $class_name ) ? new $class_name( $term->term_id ) : $term;
		}, $terms );
	}

	public function getPermalink(): string {
		return get_permalink( $this->post_id ) ?: '';
	}

	public function getDate( string $format = 'Y.m.d' ): string {
		return get_the_date( $format, $this->post_id );
	}

	public function getTitle( int $trim_width = 0, string $trim_marker = '&hellip;' ): string {
		$title = esc_html( $this->post->post_title );

		return $trim_width ? mb_strimwidth( $title, 0, $trim_width, $trim_marker ) : $title;
	}

	public function getContent(): string {
		$content = apply_filters( 'the_content', $this->post->post_content );
		$content .= $this->getLinkPages();

		return str_replace( ']]>', ']]&gt;', $content );
	}

	public function theContent(): void {
		echo $this->getContent();
	}

	public function getField( string $key, $default = '', bool $raw = false ): mixed {
		if ( ! isset( $this->fields[ $key ]['value'] ) ) {
			return $default;
		}

		$value = $this->fields[ $key ]['value'];
		if ( $raw ) {
			return $value;
		}

		return $this->formatFieldValue( $this->fields[ $key ] );
	}

	protected function formatFieldValue( array $field ): mixed {
		switch ( $field['type'] ) {
			case 'taxonomy':
				return $this->formatTaxonomyField( $field['value'] );
			case 'image':
				return is_numeric( $field['value'] ) ? wp_get_attachment_image_url( $field['value'], 'full' ) : $field['value'];
			default:
				return $field['value'];
		}
	}

	protected function formatTaxonomyField( $value ): string|array {
		if ( ! is_array( $value ) ) {
			$term = get_term( $value );

			return $term->name;
		}

		return array_map( fn( $term_id ) => get_term( $term_id ), $value );
	}

	public function getTerms( string $taxonomy ): array {
		if ( ! isset( $this->terms[ $taxonomy ] ) ) {
			$this->setTermsByTaxonomy( $taxonomy );
		}

		return $this->terms[ $taxonomy ] ?? [];
	}

	public function getMeta( string $key, $default = '', bool $single = true ): mixed {
		return get_post_meta( $this->post_id, $key, $single ) ?: $default;
	}

	public function getThumbnailUrl( string $size = 'large' ): string {
		return Image::getThumbnailUrl( $this->post_id, $size ) ?: '';
	}

	public function hasThumbnail(): bool {
		return has_post_thumbnail( $this->post_id );
	}

	public function getFirstTerm( string $taxonomy ): object|null {
		$terms = $this->getTerms( $taxonomy );

		return $terms[0] ?? null;
	}

	public function getFirstTermName( string $taxonomy ): string {
		$first_term = $this->getFirstTerm( $taxonomy );

		return $first_term ? ( $first_term->name ?? $first_term->getName() ) : '';
	}

	public function update(): void {
		wp_update_post( get_object_vars( $this->post ) );
		foreach ( $this->fields as $field_key => $field ) {
			update_field( $field_key, $field['value'], $this->post_id );
		}
	}

	public function updateField( string $selector, $value ): bool {
		return ! empty( $selector ) && update_field( $selector, $value, $this->post_id );
	}

	public function getLinkPages(): string {
		return wp_link_pages( [
			'before' => '<div class="page-links">' . __( 'Pages:', 'growp' ),
			'after'  => '</div>',
			'echo'   => false
		] );
	}

	public function theSocialIcons(): void {
		if ( function_exists( 'ADDTOANY_SHARE_SAVE_KIT' ) ) {
			ADDTOANY_SHARE_SAVE_KIT();
		}
	}

	public function thePagingNav( string $prev_text = "<i class=\"fa fa-angle-left\" aria-hidden=\"true\"></i>", string $next_text = "<i class=\"fa fa-angle-right\" aria-hidden=\"true\"></i>" ): void {
		echo Nav::getPagingNav( $prev_text, $next_text );
	}

	public static function thePostNav( string $taxonomy = "category", string $prev_text = "前の記事へ", string $next_text = "次の記事へ", string $list_text = "記事一覧へ" ): void {
		echo Nav::getPostNav( $taxonomy, $prev_text, $next_text, $list_text );
	}

	public function getRelatedPosts( array $args = [] ): array {
		if ( ! function_exists( "yarpp_get_related" ) ) {
			return [];
		}

		$defaults = [
			"post_type" => $this->post->post_type,
		];
		$args     = wp_parse_args( $args, $defaults );

		return yarpp_get_related( $args );
	}

	public function showEventLink(): bool {
		if ( ! $this->getField( 'event_show_link' ) ) {
			return false;
		}

		if ( ! $this->getEventFormUrl() ) {
			return false;
		}

		return true;
	}

	public function getEventTitle(): string {
		$event_title = $this->getField( 'event_title' );

		return $event_title ?: '';
	}

	public function getEventFormUrl(): string {
		return $this->getField( 'event_form' );
	}
}
