<?php
/**
 * Security-no-regression tests: settings save stays gated by manage_options +
 * nonce, and only allow-listed keys are ever persisted (MED-7 "unauthorized
 * save attempt" coverage).
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

use QuickAdsense\Admin\Controls\ControlFactory;
use QuickAdsense\Admin\SettingsPage;
use QuickAdsense\Contracts\SettingsRepositoryInterface;
use QuickAdsense\Contracts\ViewRendererInterface;
use QuickAdsense\Settings\Settings;

final class RecordingSettingsRepository implements SettingsRepositoryInterface {
    /** @var array<string,mixed>|null */
    public ?array $saved = null;
    public function get(): Settings { return new Settings(array()); }
    /** @param array<string,mixed> $input */
    public function save(array $input): Settings { $this->saved = $input; return new Settings($input); }
    /** @return array<string,mixed> */
    public function raw(): array { return $this->saved ?? array(); }
}

final class NullViewRenderer implements ViewRendererInterface {
    public function render(string $view, array $context = array()): void {}
}

function make_settings_page(RecordingSettingsRepository $repo): SettingsPage {
    return new SettingsPage($repo, new NullViewRenderer(), new ControlFactory());
}

function test_settings_guard(): void {
    echo "\n[SEC] Settings save guardrails (no regression)\n";

    /* --- unauthorized: user lacks manage_options --- */
    $GLOBALS['__caps'] = false;
    $repo = new RecordingSettingsRepository();
    $threw = false;
    try {
        make_settings_page($repo)->save(array('max_ads_per_page' => '5'), 'valid-nonce');
    } catch (WPDieException $e) {
        $threw = true;
    }
    t_ok($threw, 'save() halts (wp_die) when current_user_can(manage_options) is false');
    t_ok($repo->saved === null, 'save() persisted nothing for the unauthorized user');

    /* --- authorized but bad nonce --- */
    $GLOBALS['__caps'] = true;
    $repo  = new RecordingSettingsRepository();
    $threw = false;
    try {
        make_settings_page($repo)->save(array('max_ads_per_page' => '5'), 'WRONG');
    } catch (WPDieException $e) {
        $threw = true;
    }
    t_ok($threw, 'save() halts (wp_die) on an invalid nonce');
    t_ok($repo->saved === null, 'save() persisted nothing when the nonce failed');

    /* --- authorized + valid nonce: only allow-listed keys pass through --- */
    $GLOBALS['__caps'] = true;
    $repo = new RecordingSettingsRepository();
    make_settings_page($repo)->save(
        array(
            'max_ads_per_page' => '4',
            'header_embed_code' => '<script>ok</script>',
            'evil_arbitrary_option' => 'x',
            'active_plugins' => array('everything'),
        ),
        'valid-nonce'
    );
    t_ok(is_array($repo->saved), 'save() persisted an array for the authorized request');
    t_ok(array_key_exists('max_ads_per_page', $repo->saved), 'allow-listed key kept: max_ads_per_page');
    t_ok(array_key_exists('header_embed_code', $repo->saved), 'allow-listed key kept: header_embed_code');
    t_ok(!array_key_exists('evil_arbitrary_option', $repo->saved), 'unknown key rejected: evil_arbitrary_option');
    t_ok(!array_key_exists('active_plugins', $repo->saved), 'unknown key rejected: active_plugins');
}
