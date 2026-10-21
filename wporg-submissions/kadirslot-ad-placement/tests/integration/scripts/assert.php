<?php
/**
 * `wp eval-file /qa/scripts/assert.php <mode>` — runs inside a fully booted WordPress request.
 * Seeding modes change data; assertion modes print PASS/FAIL lines.
 */
require __DIR__ . '/lib.php';
$mode = $args[0] ?? '';
$ns = 'KadirSlotAdPlacement\\';
$adsense = static fn(string $pub, string $slot, string $extra = '') => '<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' . $pub . '" crossorigin="anonymous"></script><ins class="adsbygoogle" style="display:block" data-ad-client="' . $pub . '" data-ad-slot="' . $slot . '"' . $extra . '></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script>';
$legacy = [
    1 => ['enabled' => true, 'code' => $adsense(QA_PUB, '1111111111'), 'position' => 'manual', 'paragraph' => 2],
    2 => ['enabled' => false, 'code' => $adsense(QA_PUB, '2222222222', ' data-ad-format="fluid"'), 'position' => 'after_paragraph', 'paragraph' => 3],
    3 => ['enabled' => true, 'code' => '<script>alert("other-network")</script><div>Other network</div>', 'position' => 'end', 'paragraph' => 7],
];

switch ($mode) {
    case 'reset':
        foreach ([QA_OPT, 'revguard_free_slots', 'revguard_free_legacy', 'qa_dup_script'] as $o) { delete_option($o); }
        break;
    case 'fresh':
        qa_check('KadirSlot 1.1.2 runtime loaded', defined($ns . 'VERSION') && constant($ns . 'VERSION') === '1.1.2');
        qa_check('fresh activation stored defaults', get_option(QA_OPT) === call_user_func($ns . 'defaults'));
        qa_check('no RevGuardFree namespace declared by KadirSlot', !function_exists('RevGuardFree\\pro_active'));
        break;
    case 'seed-legacy':
        update_option('revguard_free_slots', $legacy, false);
        update_option('qa_unrelated', ['keep' => 'me']);
        break;
    case 'legacy-intact':
        qa_check('1.0 data untouched while older plugin active', get_option('revguard_free_slots') === $legacy && get_option(QA_OPT, null) === null);
        break;
    case 'migrated':
        $s = get_option(QA_OPT); $r = get_option('revguard_free_legacy');
        qa_check('legacy option deleted after migration', get_option('revguard_free_slots', null) === null);
        qa_check('publisher migrated', ($s['publisher'] ?? '') === QA_PUB);
        qa_check('slot 1 migrated', ($s['slots'][1] ?? null) === ['enabled' => true, 'slot' => '1111111111', 'position' => 'manual', 'paragraph' => 2]);
        qa_check('slot 2 migrated disabled', ($s['slots'][2] ?? null) === ['enabled' => false, 'slot' => '2222222222', 'position' => 'after_paragraph', 'paragraph' => 3]);
        qa_check('slot 3 unsupported -> disabled', ($s['slots'][3] ?? null) === ['enabled' => false, 'slot' => '', 'position' => 'end', 'paragraph' => 7]);
        qa_check('report statuses', is_array($r) && array_map(static fn($e) => $e['status'], $r) === [1 => 'migrated', 2 => 'migrated', 3 => 'unsupported']);
        qa_check('report not autoloaded', !array_key_exists('revguard_free_legacy', wp_load_alloptions()));
        qa_check('unrelated option untouched', get_option('qa_unrelated') === ['keep' => 'me']);
        $snap = [get_option(QA_OPT), get_option('revguard_free_legacy')];
        call_user_func($ns . 'migrate'); call_user_func($ns . 'migrate');
        qa_check('migration idempotent', [get_option(QA_OPT), get_option('revguard_free_legacy')] === $snap);
        break;
    case 'set-good':
        update_option(QA_OPT, qa_good()); delete_option('revguard_free_legacy');
        break;
    case 'is-good':
        qa_check('settings preserved exactly', get_option(QA_OPT) === qa_good(), wp_json_encode(get_option(QA_OPT)));
        break;
    case 'active':
        qa_check('KadirSlot is the rendering engine', has_filter('the_content', $ns . 'inject') !== false && shortcode_exists('revguard_ad') && !call_user_func($ns . 'legacy_active'));
        qa_check('shortcode id=4 (beyond 3-slot limit) renders nothing', do_shortcode('[revguard_ad id="4"]') === '' && do_shortcode('[revguard_ad id="0"]') === '');
        qa_check('shortcode id=1 renders slot 1', str_contains(do_shortcode('[revguard_ad id="1"]'), 'data-ad-slot="1111111111"'));
        break;
    case 'passive':
        qa_check('KadirSlot detects older build and stays passive', call_user_func($ns . 'legacy_active') && has_filter('the_content', $ns . 'inject') === false);
        break;
    case 'pro':
        qa_check('Pro detected', call_user_func($ns . 'pro_active'));
        qa_check('KadirSlot renders nothing while Pro is active', call_user_func($ns . 'ad', 1) === '' && call_user_func($ns . 'inject', '<p>a</p>') === '<p>a</p>');
        qa_check('KadirSlot settings unchanged by Pro', get_option(QA_OPT) === qa_good(), wp_json_encode(get_option(QA_OPT)));
        break;
    case 'corrupt-object':
        $o = new stdClass(); $o->x = 1;
        update_option(QA_OPT, ['publisher' => QA_PUB, 'load_script' => $o, 'slots' => [
            1 => ['enabled' => true, 'slot' => '1111111111', 'position' => 'after_paragraph', 'paragraph' => $o],
            2 => ['enabled' => [1], 'slot' => ['x'], 'position' => $o, 'paragraph' => [[1]]],
            3 => $o,
        ]]);
        update_option('revguard_free_legacy', [1 => ['status' => ['x'], 'code' => $o], 2 => ['status' => 'unsupported', 'code' => [1]], 'x' => 'y'], false);
        break;
    case 'corrupt-raw':
        global $wpdb;
        // Truncated serialized data: maybe_unserialize() returns the raw string.
        $wpdb->update($wpdb->options, ['option_value' => 'a:3:{s:9:"publisher";s:23:"ca-pub-1234567890123456";s:5:"slots";a:1:{i:1;O:8:"Missing1":1:{'], ['option_name' => QA_OPT]);
        wp_cache_delete(QA_OPT, 'options'); wp_cache_delete('alloptions', 'options');
        break;
    case 'incomplete-class':
        global $wpdb;
        $wpdb->update($wpdb->options, ['option_value' => 'a:2:{s:9:"publisher";s:23:"ca-pub-1234567890123456";s:5:"slots";a:1:{i:1;a:2:{s:9:"paragraph";O:8:"Missing1":0:{}s:7:"enabled";b:1;}}}'], ['option_name' => QA_OPT]);
        wp_cache_delete(QA_OPT, 'options'); wp_cache_delete('alloptions', 'options');
        break;
    case 'normalized-ok':
        $s = call_user_func($ns . 'settings');
        qa_check('corrupted settings read as safe values', is_array($s) && count($s['slots']) === 3 && is_int($s['slots'][1]['paragraph']), wp_json_encode($s));
        break;
    default:
        qa_check("unknown mode $mode", false);
}
