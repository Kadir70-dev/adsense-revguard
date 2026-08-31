<?php
/**
 * Plugin uninstall lifecycle.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Infrastructure\Lifecycle;

use QuickAdsense\Contracts\OptionStoreInterface;
use QuickAdsense\Contracts\TransientStoreInterface;
use QuickAdsense\Migration\MigrationRunner;
use QuickAdsense\Migration\SchemaVersion;
use QuickAdsense\Settings\SettingsRepository;

/**
 * Removes data owned by AdSense RevGuard when WordPress deletes the plugin.
 */
final class Uninstaller {

	private const OPTIONS = array(
		SettingsRepository::OPTION,
		SchemaVersion::OPTION,
		MigrationRunner::BACKUP_OPTION,
		'quick_adsense_2_options',
		'quick_adsense_2_options_bak',
		'quick_adsense_adstxt_adsense_admin_notice_dismissed',
	);

	private const TRANSIENTS = array(
		'quick_adsense_adstxt_adsense_autocheck_content',
	);

	public function __construct(
		private readonly OptionStoreInterface $options,
		private readonly TransientStoreInterface $transients,
	) {}

	public function uninstall(): void {
		foreach ( self::OPTIONS as $key ) {
			$this->options->delete( $key );
		}

		for ( $slot = 1; $slot <= 10; ++$slot ) {
			$this->options->delete( 'quick_adsense_onpost_ad_' . $slot . '_stats' );
		}

		foreach ( self::TRANSIENTS as $key ) {
			$this->transients->delete( $key );
		}
	}
}
