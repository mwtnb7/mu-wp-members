<?php
/**
 * Template for the members index page
 *
 * @var array $success
 */

use MuWpMembers\Tags\Template;

$schedule_image_id = get_field( 'o_members_schedule_image', 'option' );
?>

<section class="l-section">
	<h2 class="c-heading is-md">会員向けトップページ</h2>

	<?php if ( $schedule_image_id ) { ?>
		<div class="c-scrollable">
			<div class="js-scrollable">
				<div>
					<?php echo wp_get_attachment_image( $schedule_image_id, 'full', false, [ 'alt' => '年間スケジュール' ] ) ?>
				</div>
			</div>
		</div>
		<p class="u-right-text-link"><a href="<?php echo wp_get_attachment_url( $schedule_image_id ) ?>" target="_blank">別タブで開く</a></p>
	<?php } ?>
</section>

<section class="l-section is-sm is-top">
	<div class="c-news">
		<div class="c-news__head">
			<h2 class="c-heading is-md is-mg-none">会員向けお知らせ</h2><a class="c-button is-sm" href="<?php echo get_post_type_archive_link( 'members_post' ) ?>">会員向けお知らせ一覧へ</a>
		</div>
		<div class="c-news__content">
			<?php
			$info_query = new WP_Query( array(
				'post_type'       => 'members_post',
				'posts_per_page'  => 5,
				'is_custom_query' => true
			) );

			if ( $info_query->have_posts() ) {
				while ( $info_query->have_posts() ) {
					$info_query->the_post();
					Template::getProject( 'post-item' );
					?>
					<?php
				}
			} else {
				?>
				<p>現在、会員向けお知らせはありません。</p>
				<?php
			} ?>
		</div>
	</div>
</section>
