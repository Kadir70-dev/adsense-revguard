<?php
/**
 * Minimal WordPress bootstrap for standalone regression tests.
 *
 * Provides a small but faithful subset of WordPress so that the plugin's real
 * classes can run under plain `php-cli` with no database:
 *
 *  - the real wp-includes/kses.php (bundled from the wordpress:6.7-php8.2 image,
 *    WordPress 6.7.2) so KsesAdPolicy is tested against the genuine wp_kses();
 *  - a real hook registry (add_filter / remove_filter / apply_filters) so
 *    KsesAdPolicy's scoped `safe_style_css` adjustment actually takes effect;
 *  - byte-faithful copies of the handful of helpers kses.php pulls from
 *    wp-includes/formatting.php and wp-includes/functions.php;
 *  - an in-memory OptionStore fake for the migration/uninstall tests.
 *
 * This file and everything under tests/ is NOT shipped in the plugin ZIP.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

error_reporting(E_ALL & ~E_DEPRECATED);

define('ABSPATH', __DIR__ . '/');
define('WPINC', 'wp-includes');

/* -------------------------------------------------------------------------
 * Hook registry — enough of the Plugin API for filters used by kses.
 * ---------------------------------------------------------------------- */
$GLOBALS['__filters'] = array();

function add_filter($hook, $cb, $priority = 10, $args = 1) {
    $GLOBALS['__filters'][$hook][] = array('cb' => $cb, 'priority' => $priority);
    return true;
}
function remove_filter($hook, $cb, $priority = 10) {
    if (empty($GLOBALS['__filters'][$hook])) {
        return false;
    }
    foreach ($GLOBALS['__filters'][$hook] as $i => $entry) {
        if ($entry['cb'] === $cb) {
            unset($GLOBALS['__filters'][$hook][$i]);
            return true;
        }
    }
    return false;
}
function apply_filters($hook, $value = null, ...$rest) {
    if (empty($GLOBALS['__filters'][$hook])) {
        return $value;
    }
    $entries = $GLOBALS['__filters'][$hook];
    usort($entries, static fn ($a, $b) => $a['priority'] <=> $b['priority']);
    foreach ($entries as $entry) {
        $value = call_user_func($entry['cb'], $value, ...$rest);
    }
    return $value;
}
function apply_filters_ref_array($hook, $args) { return $args[0] ?? null; }
function has_filter($hook, $cb = false) { return !empty($GLOBALS['__filters'][$hook]); }
function do_action() {}
function did_action() { return 0; }
function current_filter() { return 'the_content'; }
function current_action() { return ''; }

/* -------------------------------------------------------------------------
 * i18n / notice / misc no-ops.
 * ---------------------------------------------------------------------- */
function __($t, $d = null) { return $t; }
function _e($t, $d = null) { echo $t; }
function _x($t, $c, $d = null) { return $t; }
function esc_html($t) { return htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8'); }
function esc_url($u, $p = null, $c = 'display') { return $u; }
function _doing_it_wrong() {}
function _deprecated_argument() {}
function _deprecated_function() {}
function _deprecated_file() {}
function is_admin() { return false; }
function wp_generate_password($length = 12, $special = true, $extra = false) {
    return substr(str_shuffle(str_repeat('abcdefghijklmnopqrstuvwxyz0123456789', 4)), 0, $length);
}
function wp_parse_url($url, $component = -1) {
    $p = @parse_url($url);
    return (-1 === $component) ? $p : ($p[$component] ?? null);
}

/* Admin-form / capability stubs. $GLOBALS['__caps'] toggles current_user_can. */
$GLOBALS['__caps'] = true;
class WPDieException extends \RuntimeException {}
function current_user_can($cap) { return (bool) $GLOBALS['__caps']; }
function wp_verify_nonce($nonce, $action = -1) { return $nonce === 'valid-nonce' ? 1 : false; }
function wp_die($msg = '', $title = '', $args = array()) { throw new WPDieException(is_string($msg) ? $msg : 'wp_die'); }
function wp_unslash($v) { return $v; }
function sanitize_key($k) { return preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $k)); }
function sanitize_text_field($s) { return trim(preg_replace('/\s+/', ' ', strip_tags((string) $s))); }
function esc_attr__($t, $d = null) { return esc_attr($t); }
function esc_html__($t, $d = null) { return esc_html($t); }
function esc_textarea($t) { return htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8'); }
function checked($a, $b = true, $echo = true) { $r = ((string) $a === (string) $b) ? ' checked' : ''; if ($echo) { echo $r; } return $r; }
function selected($a, $b = true, $echo = true) { $r = ((string) $a === (string) $b) ? ' selected' : ''; if ($echo) { echo $r; } return $r; }

/* -------------------------------------------------------------------------
 * Byte-faithful copies from wp-includes/{formatting,functions}.php (WP 6.7.2).
 * ---------------------------------------------------------------------- */
function _deep_replace($search, $subject) {
    $subject = (string) $subject;
    $count   = 1;
    while ($count) {
        $subject = str_replace($search, '', $subject, $count);
    }
    return $subject;
}
function map_deep($value, $callback) {
    if (is_array($value)) {
        foreach ($value as $index => $item) {
            $value[$index] = map_deep($item, $callback);
        }
    } elseif (is_object($value)) {
        foreach (get_object_vars($value) as $property_name => $property_value) {
            $value->$property_name = map_deep($property_value, $callback);
        }
    } else {
        $value = call_user_func($callback, $value);
    }
    return $value;
}
function wp_allowed_protocols() {
    return array(
        'http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'ircs', 'gopher', 'nntp',
        'feed', 'telnet', 'mms', 'rtsp', 'svn', 'tel', 'sms', 'fax', 'xmpp', 'webcal', 'urn',
    );
}
function wp_check_invalid_utf8($string, $strip = false) { return (string) $string; }
function _wp_specialchars($string, $quote_style = ENT_NOQUOTES, $charset = false, $double_encode = false) {
    $string = (string) $string;
    if ('' === $string) {
        return '';
    }
    if (!$double_encode) {
        $string = wp_kses_normalize_entities($string, array());
    }
    $flags = ENT_NOQUOTES;
    if (ENT_QUOTES === $quote_style || 'single' === $quote_style || 'double' === $quote_style) {
        $flags = ENT_QUOTES;
    }
    return htmlspecialchars($string, $flags, 'UTF-8', $double_encode);
}
function esc_attr($text) { return _wp_specialchars((string) $text, ENT_QUOTES); }

require __DIR__ . '/kses.php';

/* -------------------------------------------------------------------------
 * In-memory OptionStore fake for migration / uninstall tests.
 * ---------------------------------------------------------------------- */
final class FakeOptionStore implements \QuickAdsense\Contracts\OptionStoreInterface {

    /** @var array<string, mixed> */
    public array $data;

    /** @param array<string, mixed> $seed */
    public function __construct(array $seed = array()) {
        $this->data = $seed;
    }

    // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.defaultFound
    public function get(string $key, mixed $default = null): mixed {
        return array_key_exists($key, $this->data) ? $this->data[$key] : $default;
    }

    public function update(string $key, mixed $value): bool {
        $this->data[$key] = $value;
        return true;
    }

    public function delete(string $key): bool {
        unset($this->data[$key]);
        return true;
    }

    public function exists(string $key): bool {
        return array_key_exists($key, $this->data);
    }
}

/* -------------------------------------------------------------------------
 * Tiny assertion helpers.
 * ---------------------------------------------------------------------- */
$GLOBALS['__t_pass'] = 0;
$GLOBALS['__t_fail'] = 0;

function t_ok(bool $cond, string $label): void {
    if ($cond) {
        $GLOBALS['__t_pass']++;
        echo "  PASS  {$label}\n";
    } else {
        $GLOBALS['__t_fail']++;
        echo "  FAIL  {$label}\n";
    }
}

function t_eq($expected, $actual, string $label): void {
    $cond = $expected === $actual;
    t_ok($cond, $label);
    if (!$cond) {
        echo "        expected: " . var_export($expected, true) . "\n";
        echo "        actual  : " . var_export($actual, true) . "\n";
    }
}

function t_contains(string $needle, string $haystack, string $label): void {
    t_ok(str_contains($haystack, $needle), $label);
    if (!str_contains($haystack, $needle)) {
        echo "        needle  : {$needle}\n";
        echo "        haystack: {$haystack}\n";
    }
}

function t_not_contains(string $needle, string $haystack, string $label): void {
    t_ok(!str_contains($haystack, $needle), $label);
    if (str_contains($haystack, $needle)) {
        echo "        unwanted: {$needle}\n";
        echo "        haystack: {$haystack}\n";
    }
}
