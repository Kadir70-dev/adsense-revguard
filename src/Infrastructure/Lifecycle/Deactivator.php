<?php
/**
 * Plugin deactivation lifecycle.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Infrastructure\Lifecycle;

/**
 * Deactivation deliberately preserves all settings and migration state.
 */
final class Deactivator {

	public function deactivate(): void {
		// No scheduled jobs or rewrite rules exist in the current MVP.
	}
}
