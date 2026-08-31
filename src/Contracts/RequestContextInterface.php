<?php
/**
 * Request context contract.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Contracts;

/**
 * Read-only view of the current WordPress request that the ad engine needs.
 *
 * Keeping every WordPress conditional/global behind this interface is what makes
 * the engine a pure, testable domain component (ARCHITECTURE_V2.md §3).
 */
interface RequestContextInterface {

	public function isFeed(): bool;

	public function isSingle(): bool;

	public function isPage(): bool;

	public function isHome(): bool;

	public function isCategory(): bool;

	public function isArchive(): bool;

	public function isTag(): bool;

	public function isUserLoggedIn(): bool;

	/**
	 * The current post ID (used to target the more-tag placeholder).
	 */
	public function postId(): int;

	/**
	 * The site URL, used to derive the ad-container CSS class.
	 */
	public function siteUrl(): string;

	/**
	 * Whether the AdsWidget for the given ad index is an active sidebar widget.
	 */
	public function isActiveWidget( int $index ): bool;

	/**
	 * The HTTP referer, or '' when absent.
	 */
	public function referer(): string;

	/**
	 * The client IP, or '' when unavailable.
	 */
	public function clientIp(): string;
}
