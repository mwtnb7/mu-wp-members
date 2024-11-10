<?php

use MuWpMembers\Models\Taxonomy\CommitteeRoles;
use MuWpMembers\Registers\Roles\Committee;
use MuWpMembers\Models\User;
use MuWpMembers\Utils\User as UserUtils;
use MuWpMembers\Utils\Url;

$user  = new User();
$roles = Committee::getRoleList();
?>
<aside class="l-aside">
	<div class="c-menu-list">
		<h2 class="c-menu-list__head">会員メニュー</h2>
		<ul class="c-menu-list__list">
			<li>
				<a class="js-current-nav" href="/members/<?php echo $role['slug'] ?? '' ?>">会員向けトップページ</a>
			</li>
			<li>
				<a class="<?php echo is_post_type_archive( 'members_post' ) || is_tax( 'members_category' ) ? ' is-current' : '' ?>" href="/members/news/">会員向けお知らせ</a>
			</li>
			<?php
			if ( $roles ) {
				foreach ( $roles as $key => $role ) {
					$add_class = '';
					if ( ! UserUtils::isAdminUser() && ! $user->hasRole( $key ) ) {
						$add_class = ' is-disabled';
					}
					$slug = $role['slug'] ?? '';
					?>
					<li>
						<a class="js-current-nav<?php echo $add_class ?>" href="<?php echo Url::getUrl( 'committee/' . $slug ) ?>">
							<?php echo $role['name'] ?? '' ?>
						</a>
					</li>
				<?php }
			} ?>
		</ul>

		<hr class="c-menu-list__hr">

		<?php
		$schedule_image_id = get_field( 'o_members_schedule_image', 'option' );
		if ( (int) $schedule_image_id !== 0 ) {
			?>
			<div class="c-menu-list__box">
				<div class="c-menu-list__box-title-sub">
					一般社団法人<br>
					セーフティグローバル<br>
					推進機構
				</div>
				<div class="c-menu-list__box-title">
					年間スケジュール
				</div>
				<div class="c-menu-list__button-wrap">
					<a class="c-button is-sm is-bg-primary" href="<?php echo wp_get_attachment_url( $schedule_image_id ) ?>" target="_blank">スケジュールを見る</a>
				</div>
			</div>
		<?php } ?>

		<?php
		if ( $roles ) {
			foreach ( $roles as $key => $role ) {
				if ( ! UserUtils::isAdminUser() && ! $user->hasRole( $key ) ) {
					continue;
				}
				$term_object = CommitteeRoles::getTermByRoleKey( $key );

				if ( ! $term_object->getScheduleImageUrl() ) {
					continue;
				}
				?>
				<div class="c-menu-list__box">
					<div class="c-menu-list__box-title-sub">
						<?php echo $term_object->getName() ?>
					</div>
					<div class="c-menu-list__box-title">年間スケジュール</div>
					<div class="c-menu-list__button-wrap">
						<a class="c-button is-sm is-bg-primary" href="<?php echo $term_object->getScheduleImageUrl() ?>" target="_blank">スケジュールを見る</a>
					</div>
				</div>
			<?php }
		} ?>

		<div class="c-menu-list__box">
			<div class="c-menu-list__box-title">一般向けイベント</div>
			<div class="c-menu-list__button-wrap">
				<a class="c-button is-sm is-bg-primary" href="<?php echo esc_url( home_url( '/event/' ) ) ?>">イベントを見る</a>
			</div>
		</div>
	</div>
</aside>
