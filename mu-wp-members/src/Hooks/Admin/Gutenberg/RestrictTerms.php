<?php

namespace MuWpMembers\Hooks\Admin\Gutenberg;

use MuWpMembers\Hooks\HookableInterface;
use WP_User;

class RestrictTerms implements HookableInterface {

	/**
	 * Integrates with WordPress hooks.
	 *
	 * @return void
	 */
	public function initHooks(): void {
		add_action( 'admin_print_styles', [ $this, 'enqueueScripts' ] );
	}

	/**
	 * Enqueue custom JavaScript to restrict term checkboxes.
	 *
	 * @return void
	 */
	public function enqueueScripts(): void {
		// タームの名前を取得
		$terms = get_terms( [
			'taxonomy'   => 'committee_roles',
			'hide_empty' => false,
		] );

		$restricted_names = wp_list_pluck( $terms, 'name' );

		if ( ! empty( $restricted_names ) ) {
			$script = "
            document.addEventListener('DOMContentLoaded', function() {
                const observer = new MutationObserver(function(mutations, me) {
                    const wrapper = document.querySelector('.editor-styles-wrapper');
                    if (wrapper) {
	                    const checkboxes = document.querySelectorAll('.editor-post-taxonomies__hierarchical-terms-choice input[type=\"checkbox\"]');
	                    
	                    checkboxes.forEach(function(checkbox) {
	                        const label = checkbox.closest('.components-checkbox-control__input-container').nextElementSibling.textContent.trim();
							const ariaLabel = checkbox.closest('.editor-post-taxonomies__hierarchical-terms-list').getAttribute('aria-label').trim();
							// console.log(ariaLabel)
							if (ariaLabel === '委員会権限' && !" . json_encode($restricted_names) . ".includes(label)) {
	                            checkbox.style.pointerEvents = 'none';
	                            checkbox.disabled = true;
								me.disconnect();
							}
	                    }); 
                    }
                });

                // Observerの設定
                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });
            });";

			echo '<script type="text/javascript">' . $script . '</script>';
		}
	}
}
