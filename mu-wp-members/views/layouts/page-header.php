<?php
/**
 * Template for the page header
 *
 * @var array $data
 */

use MuWpMembers\Models\User;
use MuWpMembers\Utils\Url;
use MuWpMembers\Tags\Template;

if ( ! is_user_logged_in() ) {
	return;
}

// Get the user data
$user = $data['user'] ?? new User();
?>

<div class="l-page-header">
	<div class="l-container">
		<div class="l-page-header__members">
			<div class="l-page-header__members-left">
				<h1 class="l-page-header__members-title">
					<span class="is-name"><strong><?php echo $user->getViewName() ?></strong>&nbsp;様&nbsp;</span>
					<span class="is-pagename">会員専用ページ</span>
				</h1>
				<?php if ( $user->isCommittee() ) { ?>
					<ul class="l-page-header__labels">
						<?php foreach ( $user->getCommitteeLabels() as $label ) { ?>
							<li><span><?php echo $label ?>所属</span></li>
						<?php } ?>
					</ul>
				<?php } ?>
			</div>
			<div class="l-page-header__members-right">
				<div class="l-page-header__buttons">
					<a class="c-button is-contact" href="<?php Url::theUrl( 'app-form' ) ?>">会員情報に関するお問い合わせ</a>
					<a class="c-button is-bg-white is-free-icon" href="<?php Url::theUrl( 'logout' ) ?>">ログアウト<span class="c-icon-font">logout</span></a>
				</div>
			</div>
		</div>
	</div>
</div>

<?php Template::getComponent( 'breadcrumb' ) ?>
