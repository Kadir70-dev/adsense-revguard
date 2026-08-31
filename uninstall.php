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

$revguard_for_adsense_uninstaller = new \QuickAdsense\Infrastructure\Lifecycle\Uninstaller(
	new \QuickAdsense\Infrastructure\WordPress\WpOptionStore(),
	new \QuickAdsense\Infrastructure\WordPress\WpTransientStore()
);
$revguard_for_adsense_uninstaller->uninstall();
