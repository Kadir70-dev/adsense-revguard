<?php
/**
 * Legacy migration compatibility loader.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Migration;

use QuickAdsense\Contracts\MigrationStepInterface;
use QuickAdsense\Migration\Steps\MigrateFromV1x;
use QuickAdsense\Migration\Steps\MigrateFromV20;
use QuickAdsense\Migration\Steps\PreserveExisting;
use QuickAdsense\Migration\Steps\SeedDefaults;

/**
 * Supplies the frozen legacy-data detection chain in precedence order.
 *
 * This loader handles data compatibility only. It intentionally does not load
 * the legacy procedural plugin or restore retired global functions.
 */
final class LegacyCompatibilityLoader {

	/**
	 * @return array<int, MigrationStepInterface>
	 */
	public function steps(): array {
		return array(
			new PreserveExisting(),
			new MigrateFromV20(),
			new MigrateFromV1x(),
			new SeedDefaults(),
		);
	}
}
