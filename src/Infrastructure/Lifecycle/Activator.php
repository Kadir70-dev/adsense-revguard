<?php
/**
 * Plugin activation lifecycle.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Infrastructure\Lifecycle;

use QuickAdsense\Migration\MigrationRunner;

/**
 * Runs the version-gated migration during activation.
 */
final class Activator {

	public function __construct(
		private readonly MigrationRunner $migrations,
	) {}

	public function activate(): void {
		$this->migrations->run();
	}
}
