<?php
/**
 * Loads KadirSlot and an older plugin build in one PHP process, as WordPress does when both are active.
 * Usage: php test-coexistence.php <plugin-dir> <legacy-main-file|kadirslot-copy-dir> <new-first|legacy-first>
 * A "Cannot redeclare" error is a compile-time fatal and ends the process with a non-zero exit code.
 */
declare(strict_types=1);
require __DIR__ . '/stubs.php';
[$_, $dir, $other, $order] = $argv + [null, null, null, 'new-first'];
$new = rtrim($dir, '/') . '/kadirslot-ad-placement.php';
$files = $order === 'legacy-first' ? [$other, $new] : [$new, $other];
foreach ($files as $f) { require $f; }
check("both files loaded ($order)", true);
t_do('plugins_loaded');

$engines = t_hook_count('the_content');
$shortcodes = count($GLOBALS['t_shortcodes']['revguard_ad'] ?? []);
check('exactly one the_content ad engine', $engines === 1, "registered: $engines");
check('exactly one [revguard_ad] handler', $shortcodes === 1, "registered: $shortcodes");
$ours = array_filter($GLOBALS['t_hooks'], static fn($h) => is_string($h[1]) && str_starts_with($h[1], 'KadirSlotAdPlacement\\') && !in_array($h[0], ['plugins_loaded', 'activate'], true));
$ourHooks = array_values(array_unique(array_map(static fn($h) => $h[0], $ours)));
if (defined('RevGuardFree\\VERSION')) {
    check('KadirSlot detects the older build', KadirSlotAdPlacement\legacy_active());
    check('KadirSlot is passive (only its notice is hooked)', $ourHooks === ['admin_notices'], implode(',', $ourHooks));
    KadirSlotAdPlacement\activate();
    check('activation with older build active does not create settings', get_option(KadirSlotAdPlacement\OPTION, null) === null);
} else {
    check('second KadirSlot copy loaded nothing; first copy owns rendering', !KadirSlotAdPlacement\legacy_active() && in_array('the_content', $ourHooks, true));
}
t_done();
