<?php
/**
 * Template for the two-column page
 *
 * @var $data array|string|null
 */

use MuWpMembers\Controllers\ViewController;
use MuWpMembers\Tags\Template;

$controller = new ViewController( $data );

// Render the header
Template::getLayout( 'header' );
Template::getComponent( 'slidebar' );
Template::getLayout( 'search-form' );
Template::getLayout( 'page-header', [ 'user' => $controller->user ] );
?>
	<div class="l-root-container">
		<main class="l-main">
			<div class="l-wrapper">
				<?php $controller->render( $data ) ?>
			</div>
		</main>
	</div>
<?php
// Render the footer
Template::getLayout( 'footer' );

