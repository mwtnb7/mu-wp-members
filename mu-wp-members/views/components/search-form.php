<?php

use MuWpMembers\Utils\Url;

?>
<form class="c-searchform js-header-searchform-content" action="<?php Url::theUrl('search') ?>">
	<input type="text" name="s" placeholder="キーワードから検索" aria-label="キーワードから検索">
	<button><span class="c-icon-font">search</span></button>
</form>
