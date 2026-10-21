<?php
/**
 * QA only, never shipped. (1) Logs every outbound WP HTTP API request with the calling plugin and blocks external
 * ones. (2) When the qa_dup_script option is set, enqueues Google's tag under a foreign handle to test deduplication.
 */
add_filter('pre_http_request', static function ($pre, $args, $url) {
    $who = 'core';
    foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $f) {
        if (isset($f['file']) && preg_match('~/plugins/([^/]+)/~', $f['file'], $m)) { $who = $m[1]; break; }
    }
    file_put_contents('/qa/artifacts/http-requests.log', gmdate('c') . "\t$who\t" . ($args['method'] ?? 'GET') . "\t$url\n", FILE_APPEND);
    if (wp_parse_url($url, PHP_URL_HOST) === 'localhost') { return $pre; }
    return new WP_Error('qa_blocked', 'Outbound HTTP blocked in QA');
}, 1, 3);
add_action('wp_enqueue_scripts', static function () {
    if (get_option('qa_dup_script')) {
        wp_enqueue_script('theme-adsense', 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1234567890123456', [], null, true);
    }
});
