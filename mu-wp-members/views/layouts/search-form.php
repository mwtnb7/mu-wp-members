<?php

use MuWpMembers\Tags\Template;
use MuWpMembers\Utils\Url;

?>
<div class="l-searchform">
	<div class="l-searchform__overlay"></div>
	<div class="l-searchform__inner">
		<button class="l-searchform__close js-header-searchform-close" aria-label="閉じる"><span class="l-searchform__close__icon"></span></button>
		<?php Template::getComponent( 'search-form' ) ?>
	</div>
</div>
