<?php
/**
 * ナビゲーションに関するタグ
 *
 * @package CoreWpTags
 */

namespace MuWpMembers\Tags;

use function array_merge;
use function explode;
use function html_entity_decode;
use function intval;
use function is_array;
use function join;
use function str_replace;
use function strpos;

class Nav {

	/**
	 * 次の投稿を取得する
	 *
	 * @param string $taxonomy
	 * @param bool $in_same_term
	 *
	 * @return null|string|WP_Post
	 */
	public static function getNextPost( $taxonomy = "category", $in_same_term = false ) {
		return get_adjacent_post( $in_same_term, "", false, $taxonomy );
	}

	/**
	 * 前の投稿を取得する
	 *
	 * @param string $taxonomy
	 * @param bool $in_same_term
	 *
	 * @return null|string|WP_Post
	 */
	public static function getPrevPost( $taxonomy = "category", $in_same_term = false ) {
		return get_adjacent_post( $in_same_term, "", true, $taxonomy );
	}

	/**
	 * 投稿詳細での投稿ナビゲーションを表示
	 *
	 * @param string $taxonomy タクソノミー名
	 * @param string $prev_text 前の記事へリンクのテキスト
	 * @param string $next_text 次の記事へリンクのテキスト
	 *
	 * @return string
	 */
	public static function getPostNav( string $taxonomy = "category", string $prev_text = "前の記事へ", string $next_text = "次の記事へ", $list_text = "記事一覧へ" ): string {
		$prev = self::getPrevPost();
		$next = self::getNextPost();
		$html = '';
		$html .= '<nav class="c-post-navs"><ul>';
		if ( $prev ) {
			$prev_url = get_the_permalink( $prev->ID );
			if ( ! $prev_text ) {
				$prev_text = $prev->post_title;
			}
			$html .= '<li class="c-post-navs__prev"><a href="' . $prev_url . '" class=" c-button is-arrow-left"><span>' . $prev_text . '</span></a></li>';
		} else {
			$html .= '<li class="c-post-navs__prev">&nbsp;</li>';
		}

		global $post;
		if ( $post ) {
			$url  = get_post_type_archive_link( $post->post_type );
			$html .= '<li class="c-post-navs__archive"><a href="' . $url . '" class="c-button is-free-icon ' . $post->post_type . '">' . $list_text . '<span class="c-icon-font">apps</span></a></li>';
		} else {
			$html .= '<li class="c-post-navs__archive">&nbsp;</li>';
		}
		if ( $next ) {
			$next_url = get_the_permalink( $next->ID );
			if ( ! $next_text ) {
				$next_text = $next->post_title;
			}
			$html .= '<li class="c-post-navs__next"><a href="' . $next_url . '" class=" c-button"><span>' . $next_text . '</span></a></li>';
		} else {
			$html .= '<li class="c-post-navs__next">&nbsp;</li>';
		}
		$html .= '</ul></nav>';

		return $html;
	}

	/**
	 * 投稿ナビゲーションをそのまま出力
	 *
	 * @param string $taxonomy
	 * @param string $prev_text
	 * @param string $next_text
	 */
	public static function thePostNav( $taxonomy = "category", $prev_text = "前の記事へ", $next_text = "次の記事へ", $list_text = "記事一覧へ" ): void {
		echo self::getPostNav( $taxonomy, $prev_text, $next_text, $list_text );
	}


	/**
	 * 投稿一覧ページでのページングナビゲーションの表示
	 *
	 * @param string $prev_text 前の記事へリンクのテキスト
	 * @param string $next_text 次の記事へリンクのテキスト
	 * @param array $args $defaultを上書きする配列
	 *
	 * @return string
	 */
	public static function getPagingNav( $args = [], $prev_text = '<span class="c-icon-font">chevron_left</span>前へ', $next_text = '次へ<span class="c-icon-font">chevron_right</span>'): string {

		$html = "";
		$html .= '<div class="c-pagination">';

		$defaults = array(
			'show_all'           => false,
			'prev_next'          => true,
			'prev_text'          => $prev_text,
			'next_text'          => $next_text,
			'end_size'           => 1,
			'mid_size'           => 2,
			'type'               => 'list',
			'add_args'           => array(),
			'add_fragment'       => '',
			'before_page_number' => '',
			'after_page_number'  => ''
		);

		$defaults = wp_parse_args( $args, $defaults );

		$html .= self::_paginateLinks( $defaults );

		$html .= '</div>';

		return $html;
	}

	/**
	 * ページネーションを出力
	 *
	 * @param string $prev_text 前へボタンのテキスト
	 * @param string $next_text 次へボタンのテキスト
	 * @param array $args $defaultを上書きする配列
	 */
	public static function thePagingNav( $prev_text = '<span class="c-icon-font">chevron_left</span>前へ', $next_text = '次へ<span class="c-icon-font">chevron_right</span>', $args = [] ): void {
		echo self::getPagingNav( $prev_text, $next_text, $args );
	}


	/**
	 * _paginateLinks の細部を変更したメソッド
	 *
	 * @param string|array $args {
	 *     Optional. Array or string of arguments for generating paginated links for archives.
	 *
	 * @type string $base Base of the paginated url. Default empty.
	 * @type string $format Format for the pagination structure. Default empty.
	 * @type int $total The total amount of pages. Default is the value WP_Query's
	 *                                      `max_num_pages` or 1.
	 * @type int $current The current page number. Default is 'paged' query var or 1.
	 * @type bool $show_all Whether to show all pages. Default false.
	 * @type int $end_size How many numbers on either the start and the end list edges.
	 *                                      Default 1.
	 * @type int $mid_size How many numbers to either side of the current pages. Default 2.
	 * @type bool $prev_next Whether to include the previous and next links in the list. Default true.
	 * @type bool $prev_text The previous page text. Default '« Previous'.
	 * @type bool $next_text The next page text. Default '« Previous'.
	 * @type string $type Controls format of the returned value. Possible values are 'plain',
	 *                                      'array' and 'list'. Default is 'plain'.
	 * @type array $add_args An array of query args to add. Default false.
	 * @type string $add_fragment A string to append to each link. Default empty.
	 * @type string $before_page_number A string to appear before the page number. Default empty.
	 * @type string $after_page_number A string to append after the page number. Default empty.
	 * }
	 * @return array|string|void String of page links or array of page links.
	 * @global WP_Query $wp_query
	 * @global WP_Rewrite $wp_rewrite
	 *
	 */
	public static function _paginateLinks( $args = '' ) {
		global $wp_query, $wp_rewrite;

		// Setting up default values based on the current URL.
		$pagenum_link = html_entity_decode( get_pagenum_link() );
		$url_parts    = explode( '?', $pagenum_link );

		// Get max pages and current page out of the current query, if available.
		$total   = isset( $wp_query->max_num_pages ) ? $wp_query->max_num_pages : 1;
		$current = get_query_var( 'paged' ) ? intval( get_query_var( 'paged' ) ) : 1;

		// Append the format placeholder to the base URL.
		$pagenum_link = trailingslashit( $url_parts[0] ) . '%_%';

		// URL base depends on permalink settings.
		$format = $wp_rewrite->using_index_permalinks() && ! strpos( $pagenum_link, 'index.php' ) ? 'index.php/' : '';
		$format .= $wp_rewrite->using_permalinks() ? user_trailingslashit( $wp_rewrite->pagination_base . '/%#%', 'paged' ) : '?paged=%#%';

		$defaults = array(
			'base'               => $pagenum_link,
			// http://example.com/all_posts.php%_% : %_% is replaced by format (below)
			'format'             => $format,
			// ?page=%#% : %#% is replaced by the page number
			'total'              => $total,
			'current'            => $current,
			'show_all'           => false,
			'prev_next'          => true,
			'prev_text'          => __( '&laquo; Previous' ),
			'next_text'          => __( 'Next &raquo;' ),
			'end_size'           => 1,
			'mid_size'           => 2,
			'type'               => 'plain',
			'add_args'           => array(),
			// array of query args to add
			'add_fragment'       => '',
			'before_page_number' => '',
			'after_page_number'  => ''
		);

		$args = wp_parse_args( $args, $defaults );

		if ( ! is_array( $args['add_args'] ) ) {
			$args['add_args'] = array();
		}

		// Merge additional query vars found in the original URL into 'add_args' array.
		if ( isset( $url_parts[1] ) ) {
			// Find the format argument.
			$format       = explode( '?', str_replace( '%_%', $args['format'], $args['base'] ) );
			$format_query = isset( $format[1] ) ? $format[1] : '';
			wp_parse_str( $format_query, $format_args );

			// Find the query args of the requested URL.
			wp_parse_str( $url_parts[1], $url_query_args );

			// Remove the format argument from the array of query arguments, to avoid overwriting custom format.
			foreach ( $format_args as $format_arg => $format_arg_value ) {
				unset( $url_query_args[ $format_arg ] );
			}

			$args['add_args'] = array_merge( $args['add_args'], urlencode_deep( $url_query_args ) );
		}

		// Who knows what else people pass in $args
		$total = (int) $args['total'];
		if ( $total < 2 ) {
			return;
		}
		$current  = (int) $args['current'];
		$end_size = (int) $args['end_size']; // Out of bounds?  Make it the default.
		if ( $end_size < 1 ) {
			$end_size = 1;
		}
		$mid_size = (int) $args['mid_size'];
		if ( $mid_size < 0 ) {
			$mid_size = 2;
		}
		$add_args   = $args['add_args'];
		$r          = '';
		$page_links = array();
		$dots       = false;

		if ( $args['prev_next'] && $current && 1 < $current ) :
			$link = str_replace( '%_%', 2 == $current ? '' : $args['format'], $args['base'] );
			$link = str_replace( '%#%', $current - 1, $link );
			if ( $add_args ) {
				$link = add_query_arg( $add_args, $link );
			}
			$link .= $args['add_fragment'];

			/**
			 * Filters the paginated links for the given archive pages.
			 *
			 * @param string $link The paginated link URL.
			 *
			 * @since 3.0.0
			 *
			 */
			$page_links[] = '<li class="c-pagination__prev "><a class="prev c-pagination__num page-numbers" href="' . esc_url( apply_filters( 'paginate_links', $link ) ) . '">' . $args['prev_text'] . '</a></li>';
		endif;
		for ( $n = 1; $n <= $total; $n ++ ) :
			if ( $n == $current ) :
				$page_links[] = "<li class='is-active'><span class='c-pagination__num page-numbers current is-current'>" . $args['before_page_number'] . number_format_i18n( $n ) . $args['after_page_number'] . "</span></li>";
				$dots         = true;
			else :
				if ( $args['show_all'] || ( $n <= $end_size || ( $current && $n >= $current - $mid_size && $n <= $current + $mid_size ) || $n > $total - $end_size ) ) :
					$link = str_replace( '%_%', 1 == $n ? '' : $args['format'], $args['base'] );
					$link = str_replace( '%#%', $n, $link );
					if ( $add_args ) {
						$link = add_query_arg( $add_args, $link );
					}
					$link .= $args['add_fragment'];

					/** This filter is documented in wp-includes/general-template.php */
					$page_links[] = "<li><a class='c-pagination__num page-numbers' href='" . esc_url( apply_filters( 'paginate_links', $link ) ) . "'>" . $args['before_page_number'] . number_format_i18n( $n ) . $args['after_page_number'] . "</a></li>";
					$dots         = true;
				elseif ( $dots && ! $args['show_all'] ) :
					$page_links[] = '<li><span class="c-pagination__num page-numbers dots is-dot">' . __( '&hellip;' ) . '</span></li>';
					$dots         = false;
				endif;
			endif;
		endfor;
		if ( $args['prev_next'] && $current && ( $current < $total || - 1 == $total ) ) :
			$link = str_replace( '%_%', $args['format'], $args['base'] );
			$link = str_replace( '%#%', $current + 1, $link );
			if ( $add_args ) {
				$link = add_query_arg( $add_args, $link );
			}
			$link .= $args['add_fragment'];

			/** This filter is documented in wp-includes/general-template.php */
			$page_links[] = '<li class="c-pagination__next"><a class="c-pagination__num next page-numbers" href="' . esc_url( apply_filters( 'paginate_links', $link ) ) . '">' . $args['next_text'] . '</a></li>';
		endif;
		switch ( $args['type'] ) {
			case 'array' :
				return $page_links;

			case 'list' :
				$r .= "<ul class='c-pagination__list'>\n\t";
				$r .= join( "\n\t", $page_links );
				$r .= "\n</ul>\n";
				break;

			default :
				$r = join( "\n", $page_links );
				break;
		}

		return $r;
	}
}
