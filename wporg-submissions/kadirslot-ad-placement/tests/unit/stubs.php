<?php
/**
 * Minimal WordPress stand-ins for unit tests. Not shipped. Options live in memory; hooks are recorded.
 * Any PHP warning, notice or deprecation becomes an ErrorException so it fails the test that triggered it.
 */
declare(strict_types=1);

error_reporting(E_ALL);
set_error_handler(static function (int $no, string $msg, string $file, int $line): bool {
    throw new ErrorException($msg, 0, $no, $file, $line);
});

define('ABSPATH', __DIR__ . '/');

$GLOBALS['t_options'] = [];
$GLOBALS['t_hooks'] = [];
$GLOBALS['t_shortcodes'] = [];
$GLOBALS['t_errors'] = [];
$GLOBALS['t_enqueued'] = [];
$GLOBALS['t_ctx'] = ['admin' => false, 'feed' => false, 'singular' => 'post', 'loop' => true, 'main' => true, 'cap' => true];

function get_option($name, $default = false) { return array_key_exists($name, $GLOBALS['t_options']) ? $GLOBALS['t_options'][$name] : $default; }
function add_option($name, $value = '', $deprecated = '', $autoload = null) {
    if (array_key_exists($name, $GLOBALS['t_options'])) { return false; }
    $GLOBALS['t_options'][$name] = $value; return true;
}
function update_option($name, $value, $autoload = null) { $GLOBALS['t_options'][$name] = $value; return true; }
function delete_option($name) { $had = array_key_exists($name, $GLOBALS['t_options']); unset($GLOBALS['t_options'][$name]); return $had; }
function add_settings_error($setting, $code, $message, $type = 'error') { $GLOBALS['t_errors'][] = $code; }
function __($t, $d = 'default') { return $t; }
function esc_html__($t, $d = 'default') { return htmlspecialchars($t, ENT_QUOTES); }
function esc_html($t) { return htmlspecialchars((string) $t, ENT_QUOTES); }
function esc_attr($t) { return htmlspecialchars((string) $t, ENT_QUOTES); }
function esc_textarea($t) { return htmlspecialchars((string) $t, ENT_QUOTES); }
function esc_url($u) { return (string) $u; }
function sanitize_key($k) { return preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $k)); }
function absint($v) { return abs((int) $v); }
function is_admin() { return $GLOBALS['t_ctx']['admin']; }
function is_feed() { return $GLOBALS['t_ctx']['feed']; }
function is_singular($type = '') { return $GLOBALS['t_ctx']['singular'] === $type; }
function in_the_loop() { return $GLOBALS['t_ctx']['loop']; }
function is_main_query() { return $GLOBALS['t_ctx']['main']; }
function current_user_can($cap) { return $GLOBALS['t_ctx']['cap']; }
function plugins_url($path, $file) { return 'http://example.test/wp-content/plugins/kadirslot-ad-placement/' . $path; }
function add_query_arg($k, $v, $url) { return $url . '?' . $k . '=' . rawurlencode((string) $v); }
function wp_enqueue_script($handle, $src = '', $deps = [], $ver = false, $footer = false) { $GLOBALS['t_enqueued'][$handle] = $src; }
function wp_enqueue_style($handle, $src = '', $deps = [], $ver = false) {}
function wp_script_is($handle, $what = 'enqueued') { return isset($GLOBALS['t_enqueued'][$handle]); }
function wp_dequeue_script($handle) { unset($GLOBALS['t_enqueued'][$handle]); }
function wp_scripts() {
    $o = new stdClass(); $o->registered = [];
    foreach ($GLOBALS['t_enqueued'] as $h => $src) { $s = new stdClass(); $s->src = $src; $o->registered[$h] = $s; }
    return $o;
}
function add_action($hook, $cb, $prio = 10, $args = 1) { $GLOBALS['t_hooks'][] = [$hook, $cb, $prio]; return true; }
function add_filter($hook, $cb, $prio = 10, $args = 1) { $GLOBALS['t_hooks'][] = [$hook, $cb, $prio]; return true; }
function add_shortcode($tag, $cb) { $GLOBALS['t_shortcodes'][$tag][] = $cb; }
function register_activation_hook($file, $cb) { $GLOBALS['t_hooks'][] = ['activate', $cb, 10]; }
function shortcode_atts($pairs, $atts, $sc = '') {
    $out = [];
    foreach ($pairs as $k => $v) { $out[$k] = array_key_exists($k, $atts) ? $atts[$k] : $v; }
    return $out;
}
function wp_die($m = '', $t = '', $a = []) { throw new RuntimeException('wp_die'); }

/** Run every callback registered for $hook (in registration order), like do_action without priorities. */
function t_do(string $hook): void {
    foreach ($GLOBALS['t_hooks'] as [$h, $cb]) { if ($h === $hook) { $cb(); } }
}
function t_hook_count(string $hook): int {
    return count(array_filter($GLOBALS['t_hooks'], static fn($h) => $h[0] === $hook));
}

$GLOBALS['t_fail'] = 0; $GLOBALS['t_pass'] = 0;
function check(string $name, bool $ok, string $detail = ''): void {
    $ok ? $GLOBALS['t_pass']++ : $GLOBALS['t_fail']++;
    echo ($ok ? 'PASS ' : 'FAIL ') . $name . ($detail !== '' ? "  [$detail]" : '') . "\n";
}
/** Assert $fn runs without any PHP warning/notice/error. Returns its result. */
function no_warning(string $name, callable $fn): mixed {
    try { $r = $fn(); check($name, true); return $r; }
    catch (Throwable $e) { check($name, false, get_class($e) . ': ' . $e->getMessage() . ' @' . basename($e->getFile()) . ':' . $e->getLine()); return null; }
}
function t_done(): never {
    echo "unit: {$GLOBALS['t_pass']} passed, {$GLOBALS['t_fail']} failed (PHP " . PHP_VERSION . ")\n";
    exit($GLOBALS['t_fail'] === 0 ? 0 : 1);
}
