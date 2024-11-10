<?php

namespace MuWpMembers\Registers\Taxonomy;

class CommitteeRoles extends BaseTaxonomy {
	public function __construct() {
		$taxonomy       = 'committee_roles'; // タクソノミーのスラッグを指定
		$taxonomy_label = '委員会権限'; // タクソノミーのラベルを指定
		$post_types     = [ 'members_post', 'members_material', 'members_useful', 'members_notification' ]; // カスタム投稿タイプの名前を配列で指定
		$options        = [ // タクソノミーのオプションを配列で指定
			// 'show_in_rest' => false,
		];

		parent::__construct( $taxonomy, $taxonomy_label, $post_types, $options );

		$this->registerAcfFields();
	}

	/**
	 * Register ACF fields
	 *
	 * @return void
	 */
	public function registerAcfFields(): void {
		if ( function_exists( 'acf_add_local_field_group' ) ) {
			acf_add_local_field_group( [
				'key'                   => 'group_committee_roles_fields',
				'title'                 => '委員会コンテンツ設定',
				'fields'                => [
					[
						'key'           => 'field_committee_role_image',
						'label'         => '画像',
						'name'          => 'committee_role_schedule_image',
						'type'          => 'image',
						'instructions'  => 'この委員会権限に関連付けられたスケジュール画像を選択してください。',
						'required'      => 0,
						'return_format' => 'id',
						'preview_size'  => 'medium',
						'library'       => 'all',
						'min_width'     => '',
						'min_height'    => '',
						'min_size'      => '',
						'max_width'     => '',
						'max_height'    => '',
						'max_size'      => '',
						'mime_types'    => '',
					],
				],
				'location'              => [
					[
						[
							'param'    => 'taxonomy',
							'operator' => '==',
							'value'    => $this->taxonomy,
						],
					],
				],
				'menu_order'            => 0,
				'position'              => 'normal',
				'style'                 => 'default',
				'label_placement'       => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen'        => '',
				'active'                => true,
				'description'           => '',
			] );
		}
	}

	/**
	 * Get the committee role image
	 *
	 * @return string|null Image URL or null if not set
	 */
	public function getScheduleImage(): ?string {
		// Retrieve the image field
		$image_id = $this->getField( 'committee_role_schedule_image', '', true );

		// Return the URL of the image if available
		if ( $image_id && is_numeric( $image_id ) ) {
			return wp_get_attachment_image_url( $image_id, 'full' );
		}

		return null;
	}
}
