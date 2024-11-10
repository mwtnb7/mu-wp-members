<?php
/**
 * 記事一覧時の1記事分のテンプレート
 * =====================================================
 * @package  growp
 * @license  GPLv2 or later
 * @since 1.0.0
 * =====================================================
 */

use MuWpMembers\Tags\Template;
use MuWpMembers\Models\PostType\Proxy;

$post_object = Proxy::getInstance( get_the_ID() );

Template::getProject( 'post-item' );

return;
?>
<a class="c-news__block" href="<?php echo $post_object->getPermalink() ?>">
	<div class="c-news__inner">
		<div class="c-news__text">
			<?php echo $post_object->getTitle() ?>
		</div>
	</div>
</a>
