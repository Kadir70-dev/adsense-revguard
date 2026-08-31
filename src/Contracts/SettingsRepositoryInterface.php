<?php
/**
 * Settings repository contract.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Contracts;

use QuickAdsense\Settings\Settings;

/**
 * Reads and writes the plugin configuration, preserving the legacy option key.
 */
interface SettingsRepositoryInterface {

	/**
	 * The resolved settings (stored values overlaid on defaults). Memoised.
	 */
	public function get(): Settings;

	/**
	 * Validate/sanitise the given input, persist it, and return the result.
	 *
	 * @param array<string, mixed> $input Raw settings input.
	 */
	public function save( array $input ): Settings;

	/**
	 * The raw stored settings array exactly as persisted (no defaults overlay).
	 *
	 * @return array<string, mixed>
	 */
	public function raw(): array;
}
