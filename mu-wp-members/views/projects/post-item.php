<?php
/**
 * 投稿 : ループ中テンプレート
 * 投稿オブジェクトを取得し、カスタム投稿の表示を行います。
 * リンクが有効な場合は<a>タグ、そうでない場合は<div>タグで表示します。
 */

use MuWpMembers\Models\PostType\Proxy;

// 投稿オブジェクトを取得
$post_object = Proxy::fromGlobal();

// 初期設定
$post_item_tag   = "a"; // デフォルトタグ
$post_item_attrs = [ // デフォルト属性
	'href'   => $post_object->getPermalink(),
	'target' => $post_object->getLinkTarget(),
];

// 情報リンクが無効な場合の処理
if ( ! $post_object->isInfoLinkEnable() ) {
	$post_item_tag   = "div"; // タグをdivに変更
	$post_item_attrs = []; // 属性をクリア
}

// 属性文字列を生成
$post_item_attr_string = '';
foreach ( $post_item_attrs as $attr => $value ) {
	$post_item_attr_string .= sprintf( '%s="%s" ', $attr, esc_attr( $value ) );
}
?>
<<?php echo $post_item_tag ?> class="c-news__block" <?php echo $post_item_attr_string ?>>
<div class="c-news__inner">
	<div class="c-news__date c-date">
		<?php echo esc_html( $post_object->getDate( 'Y.m.d' ) ) ?>
	</div>
	<?php if ( $post_object->getFirstTermName( 'members_category' ) ) { ?>
		<div class="c-news__label c-label">
			<?php echo esc_html( $post_object->getFirstTermName( 'members_category' ) ) ?>
		</div>
	<?php } ?>
	<?php
	$terms = $post_object->getTerms( 'committee_roles' );
	if ( $terms ) {
		foreach ( $terms as $term ) {
			?>
			<div class="c-news__committees">
				<span class="c-news__committee c-label-round"><?php echo $term->getName() ?></span>
			</div>
		<?php }
	} ?>
	<div class="c-news__text">
		<?php echo esc_html( $post_object->getTitle() ) ?>
		<?php // echo $post_object->get_link_icon(); // アイコンはHTMLマークアップを含む可能性があるためエスケープしない ?>
	</div>
</div>
<?php
if ( $post_object->isInfoLinkEnable() ) {
	?>
	<span class="c-button-icon is-sm"></span>
	<?php
}
?>
</<?php echo $post_item_tag ?>>


