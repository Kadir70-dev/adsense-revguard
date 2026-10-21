<?php
/**
 * Plugin Name: KadirSlot Ad Placement
 * Description: Lightweight Google AdSense placement with three ad slots, automatic placements and shortcodes.
 * Version: 1.1.1
 * Requires at least: 6.0
 * Requires PHP: 8.2
 * Author: Abdul Kadir
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: kadirslot-ad-placement
 */
declare(strict_types=1);
namespace RevGuardFree;
if (!defined('ABSPATH')) { exit; }

const VERSION = '1.1.1';
const OPTION = 'revguard_free_settings';
/** 1.0.x option holding raw ad code. Read only by the one-time migration. */
const LEGACY_OPTION = 'revguard_free_slots';
/** Migration report: per-slot status, plus unsupported 1.0.x code kept for the admin to copy. Never rendered. */
const LEGACY_REPORT = 'revguard_free_legacy';
const MAX_SLOTS = 3;
const POSITIONS = ['manual', 'beginning', 'middle', 'end', 'after_paragraph'];
const PUBLISHER_PATTERN = '/^ca-pub-[0-9]{16}$/';
const SLOT_PATTERN = '/^[0-9]{1,20}$/';
const SCRIPT_HANDLE = 'revguard-free-adsense';
const SCRIPT_SRC = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js';

/** The Pro plugin owns rendering while active. Free settings are retained for downgrade. */
function pro_active(): bool {
    return defined('REVGUARD_PRO_VERSION') && class_exists('\RevGuardPro\Plugin', false);
}

function default_slot(): array {
    return ['enabled' => false, 'slot' => '', 'position' => 'manual', 'paragraph' => 2];
}

function defaults(): array {
    $slots = [];
    for ($i = 1; $i <= MAX_SLOTS; $i++) { $slots[$i] = default_slot(); }
    return ['publisher' => '', 'load_script' => true, 'slots' => $slots];
}

function valid_publisher(mixed $value): bool { return is_string($value) && preg_match(PUBLISHER_PATTERN, $value) === 1; }
function valid_slot_id(mixed $value): bool { return is_string($value) && preg_match(SLOT_PATTERN, $value) === 1; }

/** Coerce any stored value into the known schema. Invalid IDs become empty, never passed through. */
function normalized(mixed $data): array {
    $result = defaults();
    if (!is_array($data)) { return $result; }
    $result['publisher'] = valid_publisher($data['publisher'] ?? null) ? $data['publisher'] : '';
    $result['load_script'] = in_array($data['load_script'] ?? true, [true, 1, '1'], true);
    $slots = is_array($data['slots'] ?? null) ? $data['slots'] : [];
    foreach ($result['slots'] as $id => $default) {
        $slot = $slots[$id] ?? $slots[(string) $id] ?? null;
        if (!is_array($slot)) { continue; }
        $pos = $slot['position'] ?? 'manual';
        $result['slots'][$id] = [
            'enabled' => in_array($slot['enabled'] ?? false, [true, 1, '1'], true),
            'slot' => valid_slot_id($slot['slot'] ?? null) ? $slot['slot'] : '',
            'position' => is_string($pos) && in_array($pos, POSITIONS, true) ? $pos : 'manual',
            'paragraph' => max(1, min(100, (int) ($slot['paragraph'] ?? 2))),
        ];
    }
    return $result;
}

function settings(): array { return normalized(get_option(OPTION, [])); }

/**
 * One-time, idempotent 1.0.x migration. Valid AdSense IDs are extracted from the raw code; other code is never
 * rendered again but is kept in the report for the admin. The legacy option is deleted only after the new
 * settings and the report have been written and read back.
 */
function migrate(): void {
    $legacy = get_option(LEGACY_OPTION, null);
    if ($legacy === null) { return; }
    if (get_option(OPTION, null) !== null) {
        // A previous run already stored the new settings; only the legacy cleanup is left.
        if (get_option(LEGACY_REPORT, null) !== null) { delete_option(LEGACY_OPTION); }
        return;
    }
    $new = defaults();
    $report = [];
    $legacy = is_array($legacy) ? $legacy : [];
    for ($i = 1; $i <= MAX_SLOTS; $i++) {
        $old = $legacy[$i] ?? $legacy[(string) $i] ?? [];
        $old = is_array($old) ? $old : [];
        $pos = $old['position'] ?? 'manual';
        $slot = [
            'enabled' => in_array($old['enabled'] ?? false, [true, 1, '1', 'on'], true),
            'slot' => '',
            'position' => is_string($pos) && in_array($pos, POSITIONS, true) ? $pos : 'manual',
            'paragraph' => max(1, min(100, (int) ($old['paragraph'] ?? 2))),
        ];
        $code = is_string($old['code'] ?? null) ? trim($old['code']) : '';
        if ($code !== '') {
            $client = preg_match('/data-ad-client\s*=\s*["\']?(ca-pub-[0-9]{16})["\'\s>]/i', $code, $c) ? $c[1] : '';
            $id = preg_match('/data-ad-slot\s*=\s*["\']?([0-9]{1,20})["\'\s>]/i', $code, $s) ? $s[1] : '';
            if ($client === '' || $id === '') {
                $slot['enabled'] = false;
                $report[$i] = ['status' => 'unsupported', 'code' => $code];
            } elseif ($new['publisher'] !== '' && $new['publisher'] !== $client) {
                $slot['enabled'] = false;
                $report[$i] = ['status' => 'conflict', 'code' => $code];
            } else {
                $new['publisher'] = $client;
                $slot['slot'] = $id;
                $report[$i] = ['status' => 'migrated', 'code' => $code];
            }
        }
        $new['slots'][$i] = $slot;
    }
    $new = normalized($new);
    add_option(OPTION, $new);
    if (normalized(get_option(OPTION, null)) !== $new) { return; }
    add_option(LEGACY_REPORT, $report, '', false);
    if (get_option(LEGACY_REPORT, null) !== $report) { return; }
    delete_option(LEGACY_OPTION);
}

/** Build the AdSense unit for a slot. Only validated IDs reach the markup, and every attribute is escaped. */
function ad(int $id): string {
    if (pro_active() || is_feed() || $id < 1 || $id > MAX_SLOTS) { return ''; }
    $settings = settings();
    $slot = $settings['slots'][$id];
    if (!$slot['enabled'] || !valid_publisher($settings['publisher']) || !valid_slot_id($slot['slot'])) { return ''; }
    enqueue_assets($settings);
    return '<div class="revguard-free-ad" data-revguard-free-slot="' . esc_attr((string) $id) . '">'
        . '<ins class="adsbygoogle" style="display:block" data-ad-client="' . esc_attr($settings['publisher'])
        . '" data-ad-slot="' . esc_attr($slot['slot']) . '" data-ad-format="auto" data-full-width-responsive="true"></ins>'
        . '</div>';
}

function enqueue_assets(array $settings): void {
    if ($settings['load_script']) {
        // Null version: Google's script must be requested exactly as published, without a ?ver= suffix.
        // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Google requires the unversioned URL.
        wp_enqueue_script(SCRIPT_HANDLE, add_query_arg('client', $settings['publisher'], SCRIPT_SRC), [], null, true);
    }
    wp_enqueue_script('revguard-free-front', plugins_url('assets/front.js', __FILE__), [], VERSION, true);
}

/** Google's tag requires async and crossorigin="anonymous". */
function script_tag(string $tag, string $handle): string {
    if ($handle !== SCRIPT_HANDLE || str_contains($tag, 'crossorigin=')) { return $tag; }
    return (string) preg_replace('/<script\s/', '<script async crossorigin="anonymous" ', $tag, 1);
}

/** Do not load Google's tag a second time when another enqueued script already loads it. */
function dedupe_script(): void {
    if (!wp_script_is(SCRIPT_HANDLE, 'enqueued')) { return; }
    foreach (wp_scripts()->registered as $handle => $script) {
        if ($handle === SCRIPT_HANDLE || !is_string($script->src) || !str_contains($script->src, 'pagead2.googlesyndication.com/pagead/js/adsbygoogle.js')) { continue; }
        if (wp_script_is($handle, 'enqueued') || wp_script_is($handle, 'done')) { wp_dequeue_script(SCRIPT_HANDLE); return; }
    }
}

function inject(string $content): string {
    if (pro_active()) { return $content; }
    if (is_admin() || is_feed() || (!is_singular('post') && !is_singular('page')) || !in_the_loop() || !is_main_query()) { return $content; }
    $ends = [];
    if (preg_match_all('~</p\s*>~i', $content, $matches, PREG_OFFSET_CAPTURE)) {
        foreach ($matches[0] as $m) { $ends[] = $m[1] + strlen($m[0]); }
    }
    $edits = [];
    foreach (settings()['slots'] as $id => $slot) {
        if (!$slot['enabled'] || $slot['position'] === 'manual') { continue; }
        $html = ad((int) $id);
        if ($html === '') { continue; }
        $n = count($ends);
        $at = match ($slot['position']) {
            'beginning' => 0,
            'middle' => $n ? $ends[(int) floor(($n - 1) / 2)] : strlen($content),
            'after_paragraph' => $n && $slot['paragraph'] <= $n ? $ends[$slot['paragraph'] - 1] : strlen($content),
            default => strlen($content),
        };
        $edits[] = ['at' => $at, 'id' => $id, 'html' => $html];
    }
    usort($edits, static fn($a, $b) => ($b['at'] <=> $a['at']) ?: ($b['id'] <=> $a['id']));
    foreach ($edits as $edit) { $content = substr_replace($content, $edit['html'], $edit['at'], 0); }
    return $content;
}

/** Settings API sanitize callback. Rejected values keep the previously saved value and raise a settings error. */
function sanitize(mixed $input): array {
    $prior = settings();
    if (!is_array($input)) { return $prior; }
    if (!empty($input['discard_legacy'])) { delete_option(LEGACY_REPORT); }
    $clean = $prior;
    // Validate the raw value: stripping tags first would turn "1<script>" into an accepted "1".
    $publisher = $input['publisher'] ?? null;
    if (is_string($publisher) && (trim($publisher) === '' || valid_publisher(trim($publisher)))) {
        $clean['publisher'] = trim($publisher);
    } else {
        add_settings_error(OPTION, 'publisher', __('Publisher ID must look like ca-pub- followed by 16 digits. The previous value was kept.', 'kadirslot-ad-placement'));
    }
    $clean['load_script'] = in_array($input['load_script'] ?? false, [true, 1, '1', 'on'], true);
    $slots = is_array($input['slots'] ?? null) ? $input['slots'] : [];
    for ($i = 1; $i <= MAX_SLOTS; $i++) {
        $entry = is_array($slots[$i] ?? null) ? $slots[$i] : [];
        $id = $entry['slot'] ?? null;
        if (!is_string($id) || (trim($id) !== '' && !valid_slot_id(trim($id)))) {
            /* translators: %d: ad slot number. */
            add_settings_error(OPTION, "slot-$i", sprintf(__('Ad slot %d: the AdSense ad unit ID must contain digits only. The previous value was kept.', 'kadirslot-ad-placement'), $i));
            $id = $prior['slots'][$i]['slot'];
        } else {
            $id = trim($id);
        }
        $pos = is_string($entry['position'] ?? null) ? sanitize_key($entry['position']) : 'manual';
        $clean['slots'][$i] = [
            'enabled' => in_array($entry['enabled'] ?? false, [true, 1, '1', 'on'], true),
            'slot' => $id,
            'position' => in_array($pos, POSITIONS, true) ? $pos : 'manual',
            'paragraph' => max(1, min(100, absint(is_scalar($entry['paragraph'] ?? null) ? $entry['paragraph'] : 2))),
        ];
        if ($clean['slots'][$i]['enabled'] && ($clean['publisher'] === '' || $id === '')) {
            /* translators: %d: ad slot number. */
            add_settings_error(OPTION, "incomplete-$i", sprintf(__('Ad slot %d is enabled but will not display until a publisher ID and ad unit ID are saved.', 'kadirslot-ad-placement'), $i), 'warning');
        }
    }
    return normalized($clean);
}

function legacy_report(): array {
    $report = get_option(LEGACY_REPORT, []);
    return is_array($report) ? $report : [];
}

function legacy_needs_attention(): bool {
    foreach (legacy_report() as $entry) {
        if (is_array($entry) && ($entry['status'] ?? '') !== 'migrated') { return true; }
    }
    return false;
}

function render_legacy(): void {
    $report = legacy_report();
    if ($report === []) { return; }
    echo '<div class="notice notice-warning inline"><p><strong>' . esc_html__('Upgrade from RevGuard Free 1.0', 'kadirslot-ad-placement') . '</strong> ';
    echo esc_html__('Version 1.1 only displays Google AdSense units built from validated IDs. Custom HTML and JavaScript are no longer accepted or displayed.', 'kadirslot-ad-placement') . '</p><ul>';
    $labels = [
        'migrated' => __('AdSense publisher and ad unit IDs were migrated and the slot keeps its previous state. Generated AdSense markup replaces the old code, which is shown below for reference only.', 'kadirslot-ad-placement'),
        'unsupported' => __('No AdSense publisher and ad unit IDs were found in the old code. The slot was disabled. The old code is shown below so you can copy it; it is never displayed on your site.', 'kadirslot-ad-placement'),
        'conflict' => __('The old code used a different publisher ID than another slot. Only one publisher ID is supported, so the slot was disabled. The old code is shown below.', 'kadirslot-ad-placement'),
    ];
    foreach ($report as $id => $entry) {
        if (!is_array($entry) || !isset($labels[$entry['status'] ?? ''])) { continue; }
        /* translators: %d: ad slot number. */
        echo '<li><strong>' . esc_html(sprintf(__('Ad slot %d:', 'kadirslot-ad-placement'), (int) $id)) . '</strong> ' . esc_html($labels[$entry['status']]);
        if (($entry['code'] ?? '') !== '') {
            echo '<br><textarea readonly rows="3" class="large-text code">' . esc_textarea((string) $entry['code']) . '</textarea>';
        }
        echo '</li>';
    }
    echo '</ul><p><label><input type="checkbox" name="' . esc_attr(OPTION) . '[discard_legacy]" value="1"> ';
    echo esc_html__('I have reviewed this. Delete the upgrade report and any old code when saving.', 'kadirslot-ad-placement') . '</label></p></div>';
}

function screen(): void {
    if (!current_user_can('manage_options')) { wp_die(esc_html__('Sorry, you are not allowed to access this page.', 'kadirslot-ad-placement'), '', ['response' => 403]); }
    $s = settings();
    $name = OPTION;
    echo '<div class="wrap rgf-admin"><h1>' . esc_html__('KadirSlot Ad Placement — AdSense Slots', 'kadirslot-ad-placement') . '</h1>';
    echo '<p>' . esc_html__('Show up to 3 Google AdSense display units on posts and pages. Enter the IDs from your AdSense account; the plugin builds the official ad code. Use the shortcode [revguard_ad id="1"] for manual placement.', 'kadirslot-ad-placement') . '</p>';
    if (pro_active()) {
        echo '<div class="notice notice-info inline"><p>' . esc_html__('The compatible premium plugin is active and handles all ad rendering. KadirSlot Ad Placement settings remain saved and are used again if Pro is deactivated.', 'kadirslot-ad-placement') . '</p></div>';
    }
    echo '<form method="post" action="' . esc_url(admin_url('options.php')) . '">';
    settings_fields('revguard_free');
    render_legacy();
    echo '<section class="rgf-card"><h2>' . esc_html__('AdSense account', 'kadirslot-ad-placement') . '</h2>';
    echo '<p><label for="rgf-publisher">' . esc_html__('Publisher ID', 'kadirslot-ad-placement') . '</label><br><input id="rgf-publisher" type="text" class="regular-text code" name="' . esc_attr($name) . '[publisher]" value="' . esc_attr($s['publisher']) . '" placeholder="ca-pub-0000000000000000" pattern="ca-pub-[0-9]{16}" autocomplete="off"></p>';
    echo '<p><label><input type="checkbox" name="' . esc_attr($name) . '[load_script]" value="1" ' . checked($s['load_script'], true, false) . '> ' . esc_html__('Load the Google AdSense script on pages that show an ad. Turn this off if your theme or another plugin already loads it.', 'kadirslot-ad-placement') . '</label></p></section>';
    foreach ($s['slots'] as $id => $slot) {
        $field = $name . '[slots][' . $id . ']';
        /* translators: %d: ad slot number. */
        echo '<section class="rgf-card"><h2>' . esc_html(sprintf(__('Ad slot %d', 'kadirslot-ad-placement'), $id)) . '</h2>';
        echo '<label><input type="checkbox" name="' . esc_attr($field . '[enabled]') . '" value="1" ' . checked($slot['enabled'], true, false) . '> ' . esc_html__('Enabled', 'kadirslot-ad-placement') . '</label> ';
        echo '<label>' . esc_html__('Ad unit ID', 'kadirslot-ad-placement') . ' <input type="text" class="code" inputmode="numeric" pattern="[0-9]{1,20}" name="' . esc_attr($field . '[slot]') . '" value="' . esc_attr($slot['slot']) . '" autocomplete="off"></label> ';
        echo '<label>' . esc_html__('Placement', 'kadirslot-ad-placement') . ' <select name="' . esc_attr($field . '[position]') . '">';
        $labels = ['manual' => __('Manual (shortcode)', 'kadirslot-ad-placement'), 'beginning' => __('Beginning', 'kadirslot-ad-placement'), 'middle' => __('Middle', 'kadirslot-ad-placement'), 'end' => __('End', 'kadirslot-ad-placement'), 'after_paragraph' => __('After paragraph', 'kadirslot-ad-placement')];
        foreach (POSITIONS as $pos) { echo '<option value="' . esc_attr($pos) . '" ' . selected($slot['position'], $pos, false) . '>' . esc_html($labels[$pos]) . '</option>'; }
        echo '</select></label> <label>' . esc_html__('Paragraph', 'kadirslot-ad-placement') . ' <input type="number" min="1" max="100" class="small-text" name="' . esc_attr($field . '[paragraph]') . '" value="' . esc_attr((string) $slot['paragraph']) . '"></label></section>';
    }
    submit_button(__('Save ad slots', 'kadirslot-ad-placement'));
    echo '</form><p>' . esc_html__('KadirSlot Ad Placement does not track visitors or send data anywhere itself. Ads are served by Google AdSense under your AdSense agreement; see the plugin readme for details.', 'kadirslot-ad-placement') . '</p></div>';
}

function admin_notice(): void {
    if (!current_user_can('manage_options') || !legacy_needs_attention()) { return; }
    $screen = get_current_screen();
    if ($screen && $screen->id === 'settings_page_kadirslot-ad-placement') { return; }
    echo '<div class="notice notice-warning"><p>' . esc_html__('KadirSlot Ad Placement disabled one or more legacy ad slots whose custom code is no longer supported.', 'kadirslot-ad-placement')
        . ' <a href="' . esc_url(admin_url('options-general.php?page=kadirslot-ad-placement')) . '">' . esc_html__('Review ad slots', 'kadirslot-ad-placement') . '</a></p></div>';
}

function boot(): void {
    migrate();
    add_filter('the_content', __NAMESPACE__ . '\\inject', 20);
    add_shortcode('revguard_ad', static function ($attrs): string {
        $vals = shortcode_atts(['id' => 1], is_array($attrs) ? $attrs : [], 'revguard_ad');
        return ad((int) $vals['id']);
    });
    add_filter('script_loader_tag', __NAMESPACE__ . '\\script_tag', 10, 2);
    add_action('wp_print_footer_scripts', __NAMESPACE__ . '\\dedupe_script', 9);
    add_action('admin_init', static function (): void {
        register_setting(
            'revguard_free',
            OPTION,
            [
                'type' => 'array',
                'sanitize_callback' => __NAMESPACE__ . '\\sanitize',
                'default' => defaults(),
                'show_in_rest' => false,
            ]
        );
    });
    add_action('admin_menu', static function (): void {
        add_options_page(__('KadirSlot Ad Placement', 'kadirslot-ad-placement'), __('KadirSlot Ad Placement', 'kadirslot-ad-placement'), 'manage_options', 'kadirslot-ad-placement', __NAMESPACE__ . '\\screen');
    });
    add_action('admin_notices', __NAMESPACE__ . '\\admin_notice');
    add_action('admin_enqueue_scripts', static function (string $hook): void {
        if ($hook === 'settings_page_kadirslot-ad-placement') { wp_enqueue_style('revguard-free-admin', plugins_url('assets/admin.css', __FILE__), [], VERSION); }
    });
}
add_action('plugins_loaded', __NAMESPACE__ . '\\boot');
register_activation_hook(__FILE__, static function (): void {
    migrate();
    add_option(OPTION, defaults());
});
