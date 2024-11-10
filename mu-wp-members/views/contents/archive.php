<?php

use MuWpMembers\Models\Taxonomy\CommitteeRoles;
use MuWpMembers\Models\Taxonomy\MembersCategory;
use MuWpMembers\Tags\Nav;
use MuWpMembers\Tags\Template;

$queried_object = get_queried_object();

if ( $queried_object->name !== 'members_post' && ! is_tax( 'members_category' ) ) {
	Template::getContent( 'archive_search_roles' );

	return;
}

$page_title = '会員向けお知らせ';
$terms      = MembersCategory::getTopTerms( true );
?>
<section class="l-section">
	<h2 class="c-heading is-md"><?php echo $page_title ?></h2>
	<?php if ( $terms ) { ?>
		<ul class="c-tabs__navs">
			<li><a class="<?php echo is_post_type_archive( $queried_object->name ) ? 'is-active' : '' ?>" href="<?php echo get_post_type_archive_link( 'members_post' ) ?>">すべて</a></li>
			<?php
			foreach ( $terms as $term ) {
				?>
				<li><a class="<?php echo $term->isTax() ? 'is-active' : '' ?>" href="<?php echo $term->getArchiveLink() ?>"><?php echo $term->getName() ?></a></li>
			<?php }
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

