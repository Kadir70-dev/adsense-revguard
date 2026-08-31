<?php
/**
 * AdSense RevGuard uninstall entry point.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$adsense_revguard_autoload = __DIR__ . '/vendor/autoload.php';

if ( ! is_readable( $adsense_revguard_autoload ) ) {
	return;
}

require_once $adsense_revguard_autoload;

$adsense_revguard_uninstaller = new \QuickAdsense\Infrastructure\Lifecycle\Uninstaller(
	new \QuickAdsense\Infrastructure\WordPress\WpOptionStore(),
	new \QuickAdsense\Infrastructure\WordPress\WpTransientStore()
);
$adsense_revguard_uninstaller->uninstall();
