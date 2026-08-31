<?php
/**
 * Plugin uninstall lifecycle.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Infrastructure\Lifecycle;

use QuickAdsense\Contracts\OptionStoreInterface;
use QuickAdsense\Migration\MigrationRunner;
use QuickAdsense\Migration\SchemaVersion;
use QuickAdsense\Settings\SettingsRepository;

/**
 * Removes data owned by RevGuard for AdSense when WordPress deletes the plugin.
 *
 * Coexistence safety: several `quick_adsense_*` keys are the property of the
 * original "Quick Adsense" plugin (and its "Reloaded" fork), which may still be
 * installed alongside this one. Uninstall therefore only removes:
 *
 *   1. Keys this plugin alone introduced (`quick_adsense_schema_version`,
 *      `quick_adsense_migration_backup`, `revguard_for_adsense_data_owner`).
 *   2. The shared `quick_adsense_settings` array — but only when this plugin is
 *      the recorded owner of that data AND no original plugin is present to keep
 *      using it.
 *
 * Legacy keys that belong to the original plugin — `quick_adsense_2_options`,
 * `quick_adsense_2_options_bak`, `quick_adsense_onpost_ad_{n}_stats`,
 * `quick_adsense_adstxt_*`, and the ads.txt autocheck transient — are never
 * touched here. Destroying them could wipe an actively installed original
 * plugin's data.
 */
final class Uninstaller {

	/**
	 * Options this plugin created and no other plugin reads. Always removed.
	 *
	 * @var array<int, string>
	 */
	private const OWNED_OPTIONS = array(
		SchemaVersion::OPTION,          // quick_adsense_schema_version
		MigrationRunner::BACKUP_OPTION, // quick_adsense_migration_backup
		MigrationRunner::OWNER_OPTION,  // revguard_for_adsense_data_owner
	);

	/**
	 * @param OptionStoreInterface $options                     Option store.
	 * @param bool                 $originalQuickAdsensePresent Whether the
	 *        original "Quick Adsense" plugin (or its "Reloaded" fork) is
	 *        installed on this site. Computed by uninstall.php.
	 */
	public function __construct(
		private readonly OptionStoreInterface $options,
		private readonly bool $originalQuickAdsensePresent = false,
	) {}

	public function uninstall(): void {
		// Record ownership before the marker itself is deleted below.
		$owns_shared_settings = $this->options->exists( MigrationRunner::OWNER_OPTION );

		foreach ( self::OWNED_OPTIONS as $key ) {
			$this->options->delete( $key );
		}

		// The settings array is shared with the original plugin. Only remove it
		// when this plugin established it and nothing is left that needs it.
		if ( $owns_shared_settings && ! $this->originalQuickAdsensePresent ) {
			$this->options->delete( SettingsRepository::OPTION );
		}

		// Legacy keys owned by the original plugin (quick_adsense_2_options,
		// *_bak, quick_adsense_onpost_ad_{n}_stats, quick_adsense_adstxt_*) and
		// its ads.txt autocheck transient are deliberately left untouched.
	}
}
