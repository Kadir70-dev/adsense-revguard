<?php
/**
 * Migration step: preserve existing (2.1+/2.8.x) settings.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Migration\Steps;

use QuickAdsense\Contracts\MigrationStepInterface;
use QuickAdsense\Contracts\OptionStoreInterface;
use QuickAdsense\Settings\SettingsRepository;

/**
 * Handles installs that already store the modern `quick_adsense_settings` array.
 *
 * This is intentionally a no-op on the settings themselves: the existing array
 * is preserved verbatim so upgrading changes nothing observable (in particular
 * it avoids re-introducing default keys, which would alter the legacy
 * isset()-based gating — see BASELINE_REPORT.md B2). The runner records the
 * schema-version marker separately.
 */
final class PreserveExisting implements MigrationStepInterface {

	public function appliesTo( OptionStoreInterface $options ): bool {
		$settings = $options->get( SettingsRepository::OPTION );

		return is_array( $settings ) && array() !== $settings;
	}

	public function run( OptionStoreInterface $options ): void {
		// Deliberately empty: preserve the user's stored settings unchanged.
	}

	public function describe(): string {
		return 'Preserve existing 2.1+/2.8.x settings verbatim';
	}
}
