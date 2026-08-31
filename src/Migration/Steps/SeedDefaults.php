<?php
/**
 * Migration step: seed defaults for a fresh install.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Migration\Steps;

use QuickAdsense\Contracts\MigrationStepInterface;
use QuickAdsense\Contracts\OptionStoreInterface;
use QuickAdsense\Settings\Defaults;
use QuickAdsense\Settings\SettingsRepository;

/**
 * Fallback step for a brand-new install (no modern, v2.0, or v1.x data). Seeds
 * the default settings — equivalent to the legacy "New User" branch. Ordered
 * last, so it only runs when no other step matched.
 */
final class SeedDefaults implements MigrationStepInterface {

	public function appliesTo( OptionStoreInterface $options ): bool {
		return true;
	}

	public function run( OptionStoreInterface $options ): void {
		$options->update( SettingsRepository::OPTION, Defaults::all() );
	}

	public function describe(): string {
		return 'Seed default settings for a new install';
	}
}
