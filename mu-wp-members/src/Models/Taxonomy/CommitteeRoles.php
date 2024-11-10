<?php

namespace MuWpMembers\Models\Taxonomy;

class CommitteeRoles extends BaseTerm {
	public string $taxonomy = 'committee_roles';
	public static string $_taxonomy = 'committee_roles';

	/**
	 * Override the getArchiveLink method to add the search param.
	 *
	 * @return string Archive link
	 */
	public function getArchiveLink(): string {
		$params = [
			$this->searchParamKey() => $this->term_id,
		];

		return $this->addParamsToBaseUrl( $params );
	}

	/**
	 * Get the committee role image.
	 *
	 * @return string|null Image URL or null if not set
	 */
	public function getScheduleImageUrl(): ?string {
		$image_id = $this->getField( 'committee_role_schedule_image', '', true );

		if ( $image_id && is_numeric( $image_id ) ) {
			return wp_get_attachment_image_url( $image_id, 'full' );
		}

		return null;
	}
}
