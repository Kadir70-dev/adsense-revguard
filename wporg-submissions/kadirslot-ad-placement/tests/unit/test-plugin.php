<?php
/**
 * Unit tests for KadirSlot Ad Placement without WordPress.
 * Usage: php test-plugin.php [path/to/plugin-dir]   (defaults to ../../source)
 */
declare(strict_types=1);
require __DIR__ . '/stubs.php';
$dir = rtrim($argv[1] ?? __DIR__ . '/../../source', '/');
require $dir . '/kadirslot-ad-placement.php';

use const KadirSlotAdPlacement\{OPTION, LEGACY_OPTION, LEGACY_REPORT, VERSION};
use function KadirSlotAdPlacement\{normalized, sanitize, settings, migrate, ad, inject, defaults, bounded_int, paragraph, script_tag, render_legacy, legacy_needs_attention, activate, boot, legacy_active, dedupe_script};

$pub = 'ca-pub-1234567890123456';
$good = ['publisher' => $pub, 'load_script' => true, 'slots' => [
    1 => ['enabled' => true, 'slot' => '1111111111', 'position' => 'manual', 'paragraph' => 2],
    2 => ['enabled' => true, 'slot' => '2222222222', 'position' => 'after_paragraph', 'paragraph' => 3],
    3 => ['enabled' => true, 'slot' => '3333333333', 'position' => 'end', 'paragraph' => 2],
]];

check('version constant is 1.1.2', VERSION === '1.1.2');
check('bootstrap defines file constant', defined('KADIRSLOT_AD_PLACEMENT_FILE'));
check('no RevGuardFree symbols declared', !function_exists('RevGuardFree\\pro_active') && !defined('RevGuardFree\\VERSION'));
check('legacy plugin not detected when absent', legacy_active() === false);
check('boot + activation hooks registered', t_hook_count('plugins_loaded') === 1 && t_hook_count('activate') === 1);

// --- bounded integers ---------------------------------------------------------------------------------------
$cases = [
    [5, 5], ['7', 7], [' 8 ', 8], ['0', 1], [-3, 1], ['-3', 1], [1000, 100], ['999999999999999999', 100],
    [3.0, 3], [2.7, 2], [INF, 2], [NAN, 2], [-INF, 2], [1e300, 100], [true, 2], [false, 2], [null, 2], ['', 2], ['abc', 2], ['3abc', 2],
    ['0x1A', 2], ['1e3', 2], [[], 2], [[5], 2], [['a' => ['b' => 1]], 2], [new stdClass(), 2], [new ArrayObject([4]), 2],
    ['99999999999999999999999', 2], [PHP_INT_MAX, 100], [PHP_INT_MIN, 1],
];
foreach ($cases as $n => [$in, $want]) {
    $got = no_warning("paragraph() case $n no warning", static fn() => paragraph($in));
    check("paragraph() case $n => $want", $got === $want, var_export($got, true));
}
check('bounded_int custom default', bounded_int('x', 9, 1, 10) === 9);

// --- normalized(): malformed stored data ----------------------------------------------------------------------
$obj = new stdClass(); $obj->x = 1;
$incomplete = unserialize('O:8:"Missing1":1:{s:1:"a";i:1;}');
$malformed = [
    'null' => null, 'false' => false, 'string' => 'garbage', 'int' => 42, 'object' => $obj, 'incomplete class' => $incomplete,
    'slots string' => ['publisher' => $pub, 'slots' => 'x'],
    'slots object' => ['publisher' => $pub, 'slots' => $obj],
    'slot object' => ['publisher' => $pub, 'slots' => [1 => $obj, 2 => 'x', 3 => null]],
    'paragraph object (v1.1.1 line 63)' => ['publisher' => $pub, 'slots' => [1 => ['enabled' => true, 'slot' => '1', 'position' => 'after_paragraph', 'paragraph' => $obj]]],
    'paragraph incomplete class' => ['slots' => [1 => ['paragraph' => $incomplete]]],
    'paragraph nested array' => ['slots' => [1 => ['paragraph' => [[1, 2], ['x' => []]]]]],
    'paragraph INF/NAN' => ['slots' => [1 => ['paragraph' => INF], 2 => ['paragraph' => NAN], 3 => ['paragraph' => -INF]]],
    'publisher array' => ['publisher' => [$pub], 'load_script' => [1]],
    'publisher object' => ['publisher' => $obj, 'load_script' => $obj],
    'slot id object' => ['slots' => [1 => ['slot' => $obj, 'position' => $obj, 'enabled' => $obj]]],
    'slot id int' => ['slots' => [1 => ['slot' => 123, 'position' => ['manual'], 'enabled' => [true]]]],
    'string keys' => ['slots' => ['1' => ['enabled' => '1', 'slot' => '55', 'position' => 'end', 'paragraph' => '4']]],
    'bad position' => ['slots' => [1 => ['position' => '<script>']]],
];
foreach ($malformed as $label => $data) {
    $r = no_warning("normalized($label) no warning", static fn() => normalized($data));
    check("normalized($label) keeps schema", is_array($r) && array_keys($r) === ['publisher', 'load_script', 'slots'] && array_keys($r['slots']) === [1, 2, 3]
        && array_reduce($r['slots'], static fn($ok, $s) => $ok && is_bool($s['enabled']) && is_string($s['slot']) && is_string($s['position']) && is_int($s['paragraph']) && $s['paragraph'] >= 1 && $s['paragraph'] <= 100, true));
}
check('object paragraph falls back to default 2', normalized($malformed['paragraph object (v1.1.1 line 63)'])['slots'][1]['paragraph'] === 2);
check('object publisher rejected', normalized($malformed['publisher object'])['publisher'] === '');
check('int slot id rejected (must be string)', normalized($malformed['slot id int'])['slots'][1]['slot'] === '');
check('string keys and numeric strings accepted', normalized($malformed['string keys'])['slots'][1] === ['enabled' => true, 'slot' => '55', 'position' => 'end', 'paragraph' => 4]);
check('invalid position becomes manual', normalized($malformed['bad position'])['slots'][1]['position'] === 'manual');
check('valid settings round-trip unchanged', normalized($good) === $good);

foreach (['object' => $obj, 'string' => 'corrupt', 'nested' => ['slots' => [1 => ['paragraph' => $obj, 'slot' => [[]]]]]] as $label => $stored) {
    $GLOBALS['t_options'] = [OPTION => $stored];
    no_warning("settings() with stored $label", static fn() => settings());
    no_warning("ad(1) with stored $label", static fn() => ad(1));
    no_warning("inject() with stored $label", static fn() => inject('<p>a</p><p>b</p>'));
}

// --- sanitize(): Settings API callback --------------------------------------------------------------------------
$GLOBALS['t_options'] = [OPTION => $good];
foreach (['null' => null, 'string' => 'x', 'object' => $obj, 'int' => 5] as $label => $input) {
    $r = no_warning("sanitize($label) no warning", static fn() => sanitize($input));
    check("sanitize($label) keeps prior settings", $r === $good);
}
$GLOBALS['t_errors'] = [];
$evil = ['publisher' => '"><script>alert(1)</script>', 'load_script' => '1', 'slots' => [
    1 => ['enabled' => '1', 'slot' => '1 onerror=alert(1)', 'position' => 'end', 'paragraph' => '5'],
    2 => ['enabled' => $obj, 'slot' => ['2'], 'position' => ['x'], 'paragraph' => $obj],
    3 => 'not-an-array',
], 'discard_legacy' => $obj];
$r = no_warning('sanitize(hostile form) no warning', static fn() => sanitize($evil));
check('hostile publisher rejected, prior kept', $r['publisher'] === $pub && in_array('publisher', $GLOBALS['t_errors'], true));
check('hostile slot id rejected, prior kept', $r['slots'][1]['slot'] === '1111111111' && in_array('slot-1', $GLOBALS['t_errors'], true));
check('array slot id rejected, prior kept', $r['slots'][2]['slot'] === '2222222222' && in_array('slot-2', $GLOBALS['t_errors'], true));
check('object enabled is false', $r['slots'][2]['enabled'] === false);
check('object paragraph keeps saved paragraph', $r['slots'][2]['paragraph'] === 3);
check('array position becomes manual', $r['slots'][2]['position'] === 'manual');
check('numeric paragraph string accepted', $r['slots'][1]['paragraph'] === 5);
$r = sanitize(['publisher' => ' ' . $pub . ' ', 'load_script' => 'on', 'slots' => [1 => ['enabled' => 'on', 'slot' => ' 42 ', 'position' => 'BEGINNING', 'paragraph' => '250']]]);
check('trim + on + clamp + sanitize_key', $r['publisher'] === $pub && $r['load_script'] === true && $r['slots'][1] === ['enabled' => true, 'slot' => '42', 'position' => 'beginning', 'paragraph' => 100]);
$r = sanitize(['publisher' => '', 'slots' => [1 => ['enabled' => '1', 'slot' => '']]]);
check('enabled without IDs saved with warning', $r['publisher'] === '' && in_array('incomplete-1', $GLOBALS['t_errors'], true));
$r = sanitize(['publisher' => 'ca-pub-123', 'slots' => [1 => ['slot' => '12345678901234567890123']]]);
check('short publisher + 23-digit slot rejected', in_array('publisher', $GLOBALS['t_errors'], true) && in_array('slot-1', $GLOBALS['t_errors'], true));
$r = sanitize(['publisher' => "ca-pub-1234567890123456\n", 'slots' => []]);
check('publisher with trailing newline trimmed and accepted', $r['publisher'] === $pub);
check('regex rejects trailing newline in stored publisher', !\KadirSlotAdPlacement\valid_publisher("ca-pub-1234567890123456\n") && !\KadirSlotAdPlacement\valid_slot_id("123\n"));
$GLOBALS['t_options'][LEGACY_REPORT] = [1 => ['status' => 'unsupported', 'code' => 'x']];
sanitize(['discard_legacy' => $obj, 'slots' => []]);
check('object discard flag keeps report', isset($GLOBALS['t_options'][LEGACY_REPORT]));
sanitize(['discard_legacy' => '1', 'slots' => []]);
check('discard flag 1 deletes report', !isset($GLOBALS['t_options'][LEGACY_REPORT]));

// --- migration --------------------------------------------------------------------------------------------------
$adcode = static fn($p, $s) => '<ins class="adsbygoogle" data-ad-client="' . $p . '" data-ad-slot="' . $s . '"></ins>';
$GLOBALS['t_options'] = [LEGACY_OPTION => [
    1 => ['enabled' => 'on', 'code' => $adcode($pub, '111'), 'position' => 'end', 'paragraph' => $obj],
    2 => ['enabled' => '1', 'code' => $obj, 'position' => $obj, 'paragraph' => [3]],
    3 => ['enabled' => true, 'code' => '<script>x()</script>', 'position' => 'middle', 'paragraph' => '7'],
], 'unrelated_option' => ['keep' => 1]];
no_warning('migrate() with malformed 1.0 data (v1.1.1 line 95)', static fn() => migrate());
$s = get_option(OPTION); $rep = get_option(LEGACY_REPORT);
check('migration: publisher + slot 1 migrated, paragraph defaulted', $s['publisher'] === $pub && $s['slots'][1] === ['enabled' => true, 'slot' => '111', 'position' => 'end', 'paragraph' => 2]);
check('migration: object code slot gets no ID and no report entry', $s['slots'][2]['slot'] === '' && $s['slots'][2]['position'] === 'manual' && !isset($rep[2]));
check('migration: unsupported code disabled and reported', $s['slots'][3]['enabled'] === false && $s['slots'][3]['paragraph'] === 7 && $rep[3]['status'] === 'unsupported');
check('migration: legacy option deleted, unrelated option untouched', get_option(LEGACY_OPTION, null) === null && get_option('unrelated_option') === ['keep' => 1]);
$snap = $GLOBALS['t_options']; migrate(); migrate();
check('migration idempotent', $GLOBALS['t_options'] === $snap);
foreach (['object' => $obj, 'string' => 'x', 'list of junk' => [$obj, 'x', null, [[]]]] as $label => $legacy) {
    $GLOBALS['t_options'] = [LEGACY_OPTION => $legacy];
    no_warning("migrate() legacy $label no warning", static fn() => migrate());
    check("migrate() legacy $label produced valid settings", normalized(get_option(OPTION)) === get_option(OPTION));
}
// Existing new-format settings are never overwritten by migration.
$GLOBALS['t_options'] = [OPTION => $good, LEGACY_OPTION => [1 => ['code' => $adcode('ca-pub-9999999999999999', '9')]]];
migrate();
check('existing settings win; legacy kept when no report', get_option(OPTION) === $good && get_option(LEGACY_OPTION) !== false);

// --- report rendering with corrupted report -------------------------------------------------------------------------
$GLOBALS['t_options'] = [LEGACY_REPORT => [1 => ['status' => ['x'], 'code' => 'a'], 2 => ['status' => 'unsupported', 'code' => ['arr']], 'z' => $obj, 3 => ['status' => 'conflict', 'code' => '<script>x</script>']]];
ob_start(); no_warning('render_legacy() with corrupted report', static fn() => render_legacy()); $html = ob_get_clean();
check('report: script escaped, array code skipped', str_contains($html, '&lt;script&gt;x') && !str_contains($html, '<script>x') && !str_contains($html, 'Array'));
check('legacy_needs_attention with corrupted report', no_warning('legacy_needs_attention no warning', static fn() => legacy_needs_attention()) === true);
$GLOBALS['t_options'] = [LEGACY_REPORT => 'corrupt'];
check('string report ignored', legacy_needs_attention() === false);

// --- rendering ----------------------------------------------------------------------------------------------------
$GLOBALS['t_options'] = [OPTION => $good]; $GLOBALS['t_enqueued'] = [];
$html = ad(1);
check('ad(1) builds escaped AdSense unit', str_contains($html, 'data-ad-client="' . $pub . '"') && str_contains($html, 'data-ad-slot="1111111111"'));
check('script enqueued with client param', ($GLOBALS['t_enqueued']['revguard-free-adsense'] ?? '') === 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' . rawurlencode($pub));
check('out-of-range ids render nothing', ad(0) === '' && ad(4) === '' && ad(-1) === '');
$c = inject("<p>one</p>\n<p>two</p>\n<p>three</p>\n<p>four</p>");
preg_match_all('/<p>(\w+)<\/p>|data-ad-slot="(\d+)"/', $c, $m);
check('after_paragraph 3 then end', implode(',', array_map(static fn($a, $b) => $a !== '' ? $a : $b, $m[1], $m[2])) === 'one,two,three,2222222222,four,3333333333');
$GLOBALS['t_options'][OPTION]['slots'][2]['paragraph'] = 50;
$c = inject('<p>one</p>');
check('paragraph beyond count appends at end', substr_count($c, 'adsbygoogle') === 2 && str_starts_with($c, '<p>one</p>'));
check('non-string content passes through', inject(null) === null && inject(['x']) === ['x']);
$GLOBALS['t_ctx']['feed'] = true; check('no ads in feeds', inject('<p>a</p>') === '<p>a</p>' && ad(1) === ''); $GLOBALS['t_ctx']['feed'] = false;
$GLOBALS['t_ctx']['singular'] = ''; check('no auto ads off singular', inject('<p>a</p>') === '<p>a</p>'); $GLOBALS['t_ctx']['singular'] = 'page';
check('pages get auto ads', str_contains(inject('<p>a</p>'), 'adsbygoogle')); $GLOBALS['t_ctx']['singular'] = 'post';
check('script_tag adds async+crossorigin once', script_tag('<script src="x"></script>', 'revguard-free-adsense') === '<script async crossorigin="anonymous" src="x"></script>');
check('script_tag leaves other handles', script_tag('<script src="x"></script>', 'other') === '<script src="x"></script>');
check('script_tag tolerates non-string', script_tag(null, 'revguard-free-adsense') === null);
$GLOBALS['t_enqueued'] = ['revguard-free-adsense' => 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=x', 'theme-ads' => 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js'];
dedupe_script();
check('duplicate AdSense script dequeued', !isset($GLOBALS['t_enqueued']['revguard-free-adsense']) && isset($GLOBALS['t_enqueued']['theme-ads']));
$off = $good; $off['load_script'] = false; $GLOBALS['t_options'] = [OPTION => $off]; $GLOBALS['t_enqueued'] = [];
ad(1); check('load_script off: Google script not enqueued', !isset($GLOBALS['t_enqueued']['revguard-free-adsense']));

// --- boot / shortcode / activation ------------------------------------------------------------------------------
$GLOBALS['t_options'] = [OPTION => $good]; $GLOBALS['t_hooks'] = []; $GLOBALS['t_shortcodes'] = [];
boot();
check('boot registers content filter + shortcode', t_hook_count('the_content') === 1 && count($GLOBALS['t_shortcodes']['revguard_ad'] ?? []) === 1);
$sc = $GLOBALS['t_shortcodes']['revguard_ad'][0];
check('shortcode id=2', str_contains($sc(['id' => '2']), '2222222222'));
foreach (['empty string' => '', 'array' => [], 'object attr' => ['id' => $obj], 'nested' => ['id' => ['1']], 'big' => ['id' => '99999999999999999999'], 'neg' => ['id' => '-1']] as $label => $atts) {
    $out = no_warning("shortcode $label no warning", static fn() => $sc($atts));
    if ($label !== 'empty string' && $label !== 'array') { check("shortcode $label renders nothing", $out === ''); }
}
$GLOBALS['t_options'] = [];
activate();
check('fresh activation stores defaults', get_option(OPTION) === defaults());

t_done();
