<?php
/**
 * Template for the committee members page
 *
 * @var string $title
 * @var string $slug
 */

use MuWpMembers\Models\Taxonomy\CommitteeRoles;
use MuWpMembers\Registers\Roles\Committee;
use MuWpMembers\Tags\Template;
use MuWpMembers\Utils\Url;

$role_key = Committee::getRoleKeyBySlug( $slug );

if ( ! $role_key ) {
	wp_redirect( Url::getHomeUrl() );
}

$term_object = CommitteeRoles::getTermByRoleKey( $role_key );
?>

<section class="l-section">
	<h2 class="c-heading is-md"><?php echo $title ?></h2>
	<?php if ( $term_object->getScheduleImageUrl() ) { ?>
		<h3 class="c-heading is-sm">年間スケジュール</h3>
		<div class="c-scrollable">
			<div class="js-scrollable">
				<div>
					<img src="<?php echo $term_object->getScheduleImageUrl() ?>" alt="年間スケジュール表">
				</div>
			</div>
		</div>
		<p class="u-right-text-link"><a href="<?php echo $term_object->getScheduleImageUrl() ?>" target="_blank">別タブで開く</a></p>
	<?php } ?>
</section>

<section class="l-section is-sm is-top">
	<div class="c-news">
		<div class="c-news__head">
			<h2 class="c-heading is-md is-mg-none">連絡事項</h2><a class="c-button is-sm" href="<?php echo get_post_type_archive_link( 'members_notification' ) ?>">連絡事項一覧へ</a>
		</div>
		<div class="c-news__content">
			<?php
			$notification_query = new WP_Query( array(
				'post_type'             => 'members_notification',
				'posts_per_page'        => 3,
				'is_custom_query'       => true,
				'custom_filter_term_by' => $slug
			) );

			if ( $notification_query->have_posts() ) {
				while ( $notification_query->have_posts() ) {
					$notification_query->the_post();
					Template::getProject( 'post-item' );
				}
			} else {
				?>
				<p>現在、連絡事項はありません。</p>
				<?php
			}
			?>
		</div>
	</div>
</section>


<section class="l-section is-sm is-top">
	<div class="c-news">
		<div class="c-news__head">
			<h2 class="c-heading is-md is-mg-none">共有資料</h2><a class="c-button is-sm" href="<?php echo get_post_type_archive_link( 'members_material' ) ?>">共有資料一覧へ</a>
		</div>
		<div class="c-news__content">
			<?php
			$material_query = new WP_Query( array(
				'post_type'             => 'members_material',
				'posts_per_page'        => 3,
				'is_custom_query'       => true,
				'custom_filter_term_by' => $slug
			) );

			if ( $material_query->have_posts() ) {
				while ( $material_query->have_posts() ) {
					$material_query->the_post();
					Template::getProject( 'post-item' );
				}
			} else {
				?>
				<p>現在、共有資料はありません。</p>
				<?php
			} ?>
		</div>
	</div>
</section>
<section class="l-section is-sm is-top">
	<div class="c-news">
		<div class="c-news__head">
			<h2 class="c-heading is-md is-mg-none">お役立ち情報</h2><a class="c-button is-sm" href="<?php echo get_post_type_archive_link( 'members_useful' ) ?>">お役立ち情報一覧へ</a>
		</div>
		<div class="c-news__content">
			<?php
			$useful_query = new WP_Query( array(
				'post_type'             => 'members_useful',
				'posts_per_page'        => 3,
				'is_custom_query'       => true,
				'custom_filter_term_by' => $slug
			) );

			if ( $useful_query->have_posts() ) {
				while ( $useful_query->have_posts() ) {
					$useful_query->the_post();
					Template::getProject( 'post-item' );
				}
			} else {
				?>
				<p>現在、お役立ち情報はありません。</p>
				<?php
			} ?>
		</div>
	</div>
</section>
