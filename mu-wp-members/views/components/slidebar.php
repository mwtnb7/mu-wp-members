<?php

use MuWpMembers\Models\User;
use MuWpMembers\Utils\Url;
use MuWpMembers\Tags\Template;

if ( ! is_user_logged_in() ) {
	return;
}

// Get the user data
$user = new User();
?>

<button class="c-slidebar-button  js-slidebar-button" type="button" aria-label="メニュー開閉">
      <span class="c-slidebar-button__inner">
        <span class="c-slidebar-button__line"><span></span><span></span><span></span>
        </span>
      </span>
</button>
<div class="c-slidebar-menu  js-slidebar-menu is-right-to-left" inert>
	<div class="c-slidebar-menu__search">
		<?php Template::getComponent( 'search-form' ); ?>
	</div>
	<div class="l-container">
		<ul class="c-slidebar-menu__account">
			<div class="c-slidebar-menu__account-title"><strong><?php echo $user->getViewName() ?></strong>&nbsp;様
			</div>
			<div class="c-slidebar-menu__account-subtitle">会員専用ページ</div>
			<?php if ( $user->isCommittee() ) { ?>
				<div class="c-slidebar-menu__account-content">
					<ul class="c-slidebar-menu__account-committees">
						<?php foreach ( $user->getCommitteeLabels() as $label ) { ?>
							<li><span class="c-label-round is-lg"><?php echo $label ?>所属</span></li>
						<?php } ?>
					</ul>
				</div>
			<?php } ?>
		</ul>
	</div>
	<ul class="c-slidebar-menu__list">
		<li class="c-slidebar-menu__parent">
			<a class="c-slidebar-menu__parent-link" href="<?php Url::theUrl() ?>">会員向けトップ</a>
		</li>
		<li class="c-slidebar-menu__parent">
			<a class="c-slidebar-menu__parent-link" href="<?php echo get_post_type_archive_link( 'members_post' ) ?>">会員向けお知らせ</a>
		</li>
		<li class="c-slidebar-menu__parent">
			<a class="c-slidebar-menu__parent-link" href="<?php echo get_post_type_archive_link( 'members_notification' ) ?>">連絡事項</a>
		</li>
		<li class="c-slidebar-menu__parent">
			<a class="c-slidebar-menu__parent-link" href="<?php echo get_post_type_archive_link( 'members_material' ) ?>">共有資料</a>
		</li>
		<li class="c-slidebar-menu__parent">
			<a class="c-slidebar-menu__parent-link" href="<?php echo get_post_type_archive_link( 'members_useful' ) ?>">お役立ち資料</a>
		</li>
	</ul>
	<div class="l-container">
		<div class="c-slidebar-menu__tel"><a class="c-tel" href="tel:03-3500-3602"><span class="is-number">03-3500-3602</span><span class="is-text">受付時間：平日 9:00〜17:00</span></a>
		</div>
		<div class="c-slidebar-menu__buttons">
			<a class="c-button is-contact" href="<?php Url::theUrl( 'app-form' ) ?>">会員情報に関するお問い合わせ</a><a class="c-button is-bg-white is-free-icon" href="<?php Url::theUrl( 'logout' ) ?>">ログアウト<span class="c-icon-font">logout</span></a>
		</div>
	</div>
</div>
