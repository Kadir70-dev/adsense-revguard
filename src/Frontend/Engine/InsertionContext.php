<?php
/**
 * Per-request insertion state.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Frontend\Engine;

final class InsertionContext {

	/**
	 * @param array<int, int> $ad_slot_ids Available ad slots.
	 */
	public function __construct(
		public int $ads_displayed,
		public array $ad_slot_ids,
		public int $begin_end_cursor,
		public readonly int $quota,
	) {}

	public function hasQuota(): bool {
		return $this->ads_displayed < $this->quota;
	}

	public function record( int $slot ): void {
		$key = array_search( $slot, $this->ad_slot_ids, true );
		if ( false !== $key ) {
			array_splice( $this->ad_slot_ids, (int) $key, 1 );
		}
		++$this->ads_displayed;
	}
}
