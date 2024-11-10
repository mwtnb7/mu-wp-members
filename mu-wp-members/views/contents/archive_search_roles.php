<?php

use MuWpMembers\Models\Taxonomy\CommitteeRoles;
use MuWpMembers\Models\Taxonomy\MembersCategory;
use MuWpMembers\Tags\Nav;
use MuWpMembers\Tags\Template;


global $wp_query;
$queried_object = get_queried_object();
$post_type      = $queried_object->name;
$terms          = CommitteeRoles::getTopTerms();
$page_title     = $queried_object->label;
?>
<section class="l-section">
	<h2 class="c-heading is-md"><?php echo $page_title ?></h2>
	<?php if ( $terms ) { ?>
		<ul class="c-tabs__navs">
			<li>
				<a class="<?php echo ! CommitteeRoles::hasSearchParamKey() && is_post_type_archive( $queried_object->name ) ? 'is-active' : '' ?>" href="<?php echo get_post_type_archive_link( $post_type ) ?>">すべて</a>
			</li>
			<?php
			foreach ( $terms as $term ) {
				// Filter by Posting Type and Term has post
				$posts = get_posts( [
					'post_type'      => $post_type,
					'tax_query'      => [
						[
							'taxonomy' => $term->taxonomy,
							'field'    => 'term_id',
							'terms'    => $term->term_id,
						],
					],
					'fields'         => 'ids',
					'posts_per_page' => 1,
				] );

				if ( $posts ) {
					?>
					<li>
						<a class="<?php echo $term->isTax() ? 'is-active' : '' ?>" href="<?php echo $term->getArchiveLink() ?>">
							<?php echo $term->getName() ?>
						</a>
					</li>
					<?php
				}
			}
			?>
		</ul>
	<?php } ?>
	<div class="c-news">
		<?php
		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				Template::getProject( 'post-item' );
			}
			wp_reset_postdata();
		} else {
			echo '現在、' . $page_title . 'はありません。';
		}
		?>
	</div>
	<?php echo Nav::getPagingNav() ?>
</section>
