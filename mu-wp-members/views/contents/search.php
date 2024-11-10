<?php
/**
 * Template for the search results page
 *
 * @var string $search_keyword
 * @var WP_Query $query
 */

use MuWpMembers\Tags\Template;
use MuWpMembers\Tags\Nav;
?>
<section class="l-section">
	<div class="u-mbs is-bottom is-lg">
		<div class="c-archive">
			<header class="page-header">
				<h1 class="c-heading is-sm">
					<i class="fa fa-search"></i>
					<?php echo '<span>「' . $search_keyword . '」</span>の検索結果'; ?>
				</h1>
			</header><!-- .page-header -->
			<div class="c-news">
				<div class="c-news__content">
					<?php
					if ( $query->have_posts() ) {
						while ( $query->have_posts() ) {
							$query->the_post();
							Template::getProject( 'search-item' );
						}
						wp_reset_postdata();
					} else {
						?>
						<div class="u-mbs is-xxlg u-text-center " style="width: 100% !important;">
							該当がありませんでした
						</div>
						<?php
					}
					?>
				</div>
				<?php
				$paged = $_GET['paged'] ?? 1;
				$args = [
					'base'    => add_query_arg( 'paged', '%#%' ),
					'current' => max( 1, $paged ),
					'format'  => 'page/%#%/',
					'total'   => ceil( $query->found_posts / $query->query_vars['posts_per_page'] )
				];
				echo Nav::getPagingNav( $args );
				?>
			</div>
		</div>
	</div>
</section>
