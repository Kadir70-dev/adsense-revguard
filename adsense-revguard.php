<?php
/**
 * Plugin Name:       AdSense RevGuard
 * Description:       A quick, flexible way to insert Google AdSense or any ad code into your WordPress site.
 * Version:           3.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.2
 * Author:            Abdul Kadir
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       adsense-revguard
 *
 * @package QuickAdsense
 *
 * AdSense RevGuard 3.0 — full rebuild. Architecture is frozen in
 * ../ARCHITECTURE_V2.md. This bootstrap file is intentionally thin: it guards
 * the runtime environment, loads the Composer autoloader, defines the plugin
 * constants, and hands control to the composition root (QuickAdsense\Plugin).
 * No business logic lives here.
 */

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

/**
 * Hard requirements. Kept as bare constants so the environment guard below can
 * run before the autoloader (and therefore before any class) is available.
 */
const ADSENSE_REVGUARD_MIN_PHP = '8.2';
const ADSENSE_REVGUARD_MIN_WP  = '6.0';

/*
 * --------------------------------------------------------------------------
 * 1. Environment guard — degrade gracefully, never fatal.
 * --------------------------------------------------------------------------
 */
if ( version_compare( PHP_VERSION, ADSENSE_REVGUARD_MIN_PHP, '<' ) ) {
	add_action(
		'admin_notices',
		static function (): void {
			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				esc_html(
					sprintf(
						/* translators: 1: required PHP version, 2: current PHP version. */
						__( 'AdSense RevGuard requires PHP %1$s or higher. Your server is running PHP %2$s. Please upgrade PHP to activate the plugin.', 'adsense-revguard' ),
						ADSENSE_REVGUARD_MIN_PHP,
						PHP_VERSION
					)
				)
			);
		}
	);

	return;
}

if ( version_compare( get_bloginfo( 'version' ), ADSENSE_REVGUARD_MIN_WP, '<' ) ) {
	add_action(
		'admin_notices',
		static function (): void {
			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				esc_html(
					sprintf(
						/* translators: 1: required WordPress version, 2: current WordPress version. */
						__( 'AdSense RevGuard requires WordPress %1$s or higher. This site is running WordPress %2$s.', 'adsense-revguard' ),
						ADSENSE_REVGUARD_MIN_WP,
						get_bloginfo( 'version' )
					)
				)
			);
		}
	);

	return;
}

/*
 * --------------------------------------------------------------------------
 * 2. Composer autoloader — required to run; degrade gracefully if absent.
 * --------------------------------------------------------------------------
 */
$adsense_revguard_autoload = __DIR__ . '/vendor/autoload.php';

if ( ! is_readable( $adsense_revguard_autoload ) ) {
	add_action(
		'admin_notices',
		static function (): void {
			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				esc_html__(
					'AdSense RevGuard could not start because its dependencies are missing. Run "composer install" inside the plugin directory.',
					'adsense-revguard'
				)
			);
		}
	);

	return;
}

require_once $adsense_revguard_autoload;

/*
 * --------------------------------------------------------------------------
 * 3. Plugin constants (runtime values that require WordPress functions).
 * --------------------------------------------------------------------------
 */
define( 'ADSENSE_REVGUARD_VERSION', '3.0.0' );
define( 'ADSENSE_REVGUARD_FILE', __FILE__ );
define( 'ADSENSE_REVGUARD_PATH', plugin_dir_path( __FILE__ ) );
define( 'ADSENSE_REVGUARD_URL', plugin_dir_url( __FILE__ ) );

/*
 * --------------------------------------------------------------------------
 * 4. Register lifecycle callbacks and hand off to the composition root.
 * --------------------------------------------------------------------------
 * Providers, migrations, settings, and the ad engine are wired inside
 * Plugin::boot() in later tasks. Booting on `plugins_loaded` ensures WordPress
 * core and other plugins are available before we register our hooks.
 */
$adsense_revguard_plugin = \QuickAdsense\Plugin::instance();
$adsense_revguard_plugin->registerLifecycleHooks();

add_action(
	'plugins_loaded',
	array( $adsense_revguard_plugin, 'boot' )
);
