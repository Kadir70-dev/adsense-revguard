<?php
/**
 * MED-6 regression tests: uninstall must not destroy a separately installed
 * original "Quick Adsense" plugin's data.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

use QuickAdsense\Infrastructure\Lifecycle\Uninstaller;
use QuickAdsense\Migration\MigrationRunner;
use QuickAdsense\Migration\SchemaVersion;
use QuickAdsense\Settings\SettingsRepository;

/**
 * Option keys that belong to the ORIGINAL Quick Adsense plugin and must always
 * survive our uninstall.
 */
const MED6_ORIGINAL_KEYS = array(
    'quick_adsense_2_options',
    'quick_adsense_2_options_bak',
    'quick_adsense_onpost_ad_1_stats',
    'quick_adsense_onpost_ad_10_stats',
    'quick_adsense_adstxt_adsense_admin_notice_dismissed',
    'quick_adsense_adstxt_adsense_autocheck_content',
);

function med6_full_site(): array {
    $seed = array(
        SettingsRepository::OPTION      => array('max_ads_per_page' => 3),
        SchemaVersion::OPTION           => 1,
        MigrationRunner::BACKUP_OPTION  => array('quick_adsense_settings' => array('exists' => false)),
        MigrationRunner::OWNER_OPTION   => 1,
    );
    foreach (MED6_ORIGINAL_KEYS as $k) {
        $seed[$k] = 'ORIGINAL-DATA';
    }
    return $seed;
}

function test_med6(): void {
    echo "\n[MED-6] Uninstaller coexistence safety\n";

    /* --- Case 1: our plugin owns the data, original plugin NOT installed --- */
    $store = new FakeOptionStore(med6_full_site());
    (new Uninstaller($store, false))->uninstall();

    t_ok(!$store->exists(SettingsRepository::OPTION), 'case1: quick_adsense_settings removed (we own it, original absent)');
    t_ok(!$store->exists(SchemaVersion::OPTION), 'case1: schema version removed');
    t_ok(!$store->exists(MigrationRunner::BACKUP_OPTION), 'case1: migration backup removed');
    t_ok(!$store->exists(MigrationRunner::OWNER_OPTION), 'case1: ownership marker removed');
    foreach (MED6_ORIGINAL_KEYS as $k) {
        t_ok($store->exists($k) && $store->get($k) === 'ORIGINAL-DATA', "case1: original key preserved -> {$k}");
    }

    /* --- Case 2: original Quick Adsense plugin IS installed alongside --- */
    $store = new FakeOptionStore(med6_full_site());
    (new Uninstaller($store, true))->uninstall();

    t_ok($store->exists(SettingsRepository::OPTION), 'case2: quick_adsense_settings PRESERVED (original plugin present)');
    t_ok(!$store->exists(SchemaVersion::OPTION), 'case2: our schema version still removed');
    t_ok(!$store->exists(MigrationRunner::OWNER_OPTION), 'case2: our ownership marker still removed');
    foreach (MED6_ORIGINAL_KEYS as $k) {
        t_ok($store->exists($k), "case2: original key preserved -> {$k}");
    }

    /* --- Case 3: ambiguous ownership (no marker) => preserve shared key --- */
    $seed = med6_full_site();
    unset($seed[MigrationRunner::OWNER_OPTION]);
    $store = new FakeOptionStore($seed);
    (new Uninstaller($store, false))->uninstall();

    t_ok($store->exists(SettingsRepository::OPTION), 'case3: quick_adsense_settings PRESERVED when ownership is unproven');
    t_ok(!$store->exists(SchemaVersion::OPTION), 'case3: our own keys still removed');

    /* --- Case 4: the migration runner writes the ownership marker on success --- */
    $store  = new FakeOptionStore(array('quick_adsense_2_options' => array('legacy' => 1)));
    $runner = new MigrationRunner($store, array(), SchemaVersion::CURRENT);
    $ok     = $runner->run();

    t_ok($ok, 'case4: migration run() succeeds');
    t_ok($store->exists(MigrationRunner::OWNER_OPTION), 'case4: ownership marker written after migration');
    t_eq(SchemaVersion::CURRENT, $store->get(MigrationRunner::OWNER_OPTION), 'case4: marker records schema version');
    t_ok($store->exists('quick_adsense_2_options'), 'case4: legacy source key untouched by runner');
}
