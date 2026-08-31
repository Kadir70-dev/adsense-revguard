<?php
/**
 * RevGuard for AdSense uninstall entry point.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$revguard_for_adsense_autoload = __DIR__ . '/vendor/autoload.php';

if ( ! is_readable( $revguard_for_adsense_autoload ) ) {
	return;
}

require_once $revguard_for_adsense_autoload;

/**
 * Detect whether the original "Quick Adsense" plugin (or its "Reloaded" fork)
 * is installed on this site. When it is, the shared `quick_adsense_settings`
 * option must be preserved so the other plugin keeps working.
 *
 * @return bool
 */
function revguard_for_adsense_original_quick_adsense_present(): bool {
	$candidates = array( 'quick-adsense', 'quick-adsense-reloaded' );

	// 1. Plugin files present on disk (covers installed-but-inactive).
	if ( defined( 'WP_PLUGIN_DIR' ) ) {
		foreach ( $candidates as $dir ) {
			$path = WP_PLUGIN_DIR . '/' . $dir;

			if ( is_dir( $path ) && glob( $path . '/*.php' ) ) {
				return true;
			}
		}
	}

	// 2. Referenced in the active-plugins lists (covers a non-standard folder).
	$active = (array) get_option( 'active_plugins', array() );

	if ( is_multisite() ) {
		$active = array_merge(
			$active,
			array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ) )
		);
	}

	foreach ( $active as $plugin_file ) {
		if ( ! is_string( $plugin_file ) ) {
			continue;
		}

		foreach ( $candidates as $dir ) {
			if ( 0 === strpos( $plugin_file, $dir . '/' ) ) {
				return true;
			}
		}
	}

	return false;
}

$revguard_for_adsense_uninstaller = new \QuickAdsense\Infrastructure\Lifecycle\Uninstaller(
	new \QuickAdsense\Infrastructure\WordPress\WpOptionStore(),
	revguard_for_adsense_original_quick_adsense_present()
);
$revguard_for_adsense_uninstaller->uninstall();
