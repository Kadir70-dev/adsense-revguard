<?php
/**
 * HTML policy contract.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Contracts;

/**
 * Applies a named, auditable HTML trust boundary.
 */
interface HtmlPolicyInterface {

	/**
	 * Filter HTML through this policy.
	 */
	public function filter( string $html ): string;

	/**
	 * WordPress KSES allowlist represented by this policy.
	 *
	 * @return array<string, array<string, bool>>
	 */
	public function allowed(): array;
}
