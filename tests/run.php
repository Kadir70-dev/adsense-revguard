<?php
/**
 * Standalone regression-test runner for RevGuard for AdSense.
 *
 * Usage (from the plugin root):
 *   docker run --rm -v "$PWD:/app" -w /app php:8.2-cli php tests/run.php
 *
 * No database and no PHPUnit required. Exercises the plugin's real classes
 * against a faithful WordPress 6.7.2 kses.php. Everything under tests/ is
 * excluded from the release ZIP.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/wp-stubs/bootstrap.php';

require __DIR__ . '/KsesAdPolicyTest.php';
require __DIR__ . '/UninstallerCoexistenceTest.php';
require __DIR__ . '/SettingsGuardTest.php';

test_med7();
test_med6();
test_settings_guard();

echo "\n---------------------------------------------\n";
printf("PASS: %d   FAIL: %d\n", $GLOBALS['__t_pass'], $GLOBALS['__t_fail']);

exit($GLOBALS['__t_fail'] > 0 ? 1 : 0);
