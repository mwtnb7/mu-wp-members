<?php

namespace MuWpMembers\Hooks\Admin\Taxonomy;

use MuWpMembers\Hooks\HookableInterface;
use WP_User;
use function in_array;
use function is_array;
use function is_object;

class RestrictRoles implements HookableInterface {

	/**
	 * Integrates with WordPress hooks.
	 *
	 * @return void
	 */
	public function initHooks(): void {
		add_action( 'admin_print_styles-edit-tags.php', [ $this, 'committeeRoles' ] );
		add_action( 'admin_print_styles-term.php', [ $this, 'committeeRoles' ] );
	}

	/**
	 * Restrict ui for committee roles.
	 *
	 * @return void
	 */
	public function committeeRoles(): void {
		$taxonomy = $_GET['taxonomy'] ?? '';

		if ( $taxonomy === 'committee_roles' ) {
			// カスタムCSSを追加して新規追加フォームを非表示にする
			echo "<style>
	            #col-left {
	                display: none;
	            }
	            #col-right {
					width: 100% !important;
				}
				.row-actions .inline,
				.row-actions .delete,
				.row-actions .view {
					display: none;
				}
				.check-column {
					display: none;
				}
				.form-field.term-slug-wrap,
				.form-field.term-parent-wrap,
				.form-field.term-description-wrap{
					display: none;
				}
				#delete-link,
				#name-description {
					display: none;
				}
				#name {
					pointer-events: none;
				}
	        </style>

			<script>
			 	jQuery(document).ready(function($) {
					$('input#name').attr('readonly', true);
				});
			</script>";
		}
	}
}
