<?php
/**
 * Template for the single page
 */

use MuWpMembers\Models\PostType\Proxy;

// Get the post object
$post_object = Proxy::fromGlobal();

// Post type label
$post_type_obj = get_post_type_object( $post_object->post_type );
if ( $post_type_obj ) {
	$post_type_label = $post_type_obj->labels->singular_name;
}

// Taxonomy name
$taxonomy_name = 'members_category';
if ( $post_object->post_type !== 'members_post' ) {
	$taxonomy_name = 'committee_roles';
}
?>
<article class="l-section">
	<div class="c-back-nav">
		<a class="c-button-text is-sm" href="<?php echo get_post_type_archive_link( $post_object->post_type ) ?>">
			<span class="c-icon-font">chevron_left</span>
			<span class="is-text"><?php echo esc_html( $post_type_label ?? '' ); ?>一覧へ戻る</span>
		</a>
	</div>
	<div class="c-news-header">
		<div class="c-news-header__sup">
			<div class="c-news-header__date">
				<?php echo $post_object->getDate() ?>
			</div>
		</div>
		<h1 class="c-news-header__title">
			<?php echo $post_object->getTitle() ?>
		</h1>
		<div class="c-news-header__sub">
			<div class="c-news-header__labels">
				<?php
				$categories = $post_object->getTerms( $taxonomy_name );
				if ( $categories ) {
					foreach ( $categories as $category ) {
						?>
						<a href="<?php echo $category->getArchiveLink() ?>" class="c-news-header__label c-label">
							<?php echo $category->getName(); ?>
						</a>
						<?php
					}
				}
				?>
			</div>
		</div>
	</div>
	<div class="l-post-content is-twocolumns">
		<?php
		$post_object->theContent();

		// Show the event link
		if ( $post_object->showEventLink() ) {
			?>
			<div class="u-text-center u-mbs is-top">
				<a href="<?php echo esc_url( add_query_arg( 'post_id', $post_object->post_id, $post_object->getEventFormUrl()) ) ?>" class="c-button is-md is-bg-primary">
					<span class="is-text">イベントのお申し込みはこちら</span>
				</a>
			</div>
			<?php
		}
		?>
	</div>
	<?php
	$post_object::thePostNav();
	?>
</article>
