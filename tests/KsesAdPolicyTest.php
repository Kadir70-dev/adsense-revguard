<?php
/**
 * MED-7 regression tests: real AdSense snippets through KsesAdPolicy.
 *
 * Run via tests/run.php. Requires tests/wp-stubs/bootstrap.php (real WP 6.7.2
 * kses.php + hook registry) and the plugin's own autoloader.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

use QuickAdsense\Infrastructure\Security\KsesAdPolicy;

/** @return array{0:string,1:string} label => before/after is printed by caller */
function med7_snippets(): array {
    return array(
        'external_loader' => '<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1234567890123456" crossorigin="anonymous"></script>',
        'ins_block'       => '<ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-1234567890123456" data-ad-slot="1234567890" data-ad-format="auto" data-full-width-responsive="true"></ins>',
        'inline_init'     => '<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>',
        'inline_amp'      => '<script>if (a && b && c < d) { (adsbygoogle = window.adsbygoogle || []).push({}); }</script>',
        'responsive_ins'  => '<ins class="adsbygoogle" style="display:inline-block;width:728px;height:90px" data-ad-client="ca-pub-1" data-ad-slot="9"></ins>',
    );
}

function test_med7(): void {
    $policy = new KsesAdPolicy();

    echo "\n[MED-7] Real AdSense snippet compatibility (WordPress 6.7.2 wp_kses)\n";

    $s   = med7_snippets();
    $ext = $policy->filter($s['external_loader']);
    t_contains('<script', $ext, 'external loader: <script> kept');
    t_contains('src="https://pagead2.googlesyndication.com', $ext, 'external loader: src preserved');
    t_contains('async', $ext, 'external loader: async attr preserved');
    t_contains('crossorigin', $ext, 'external loader: crossorigin preserved');

    $ins = $policy->filter($s['ins_block']);
    t_contains('<ins', $ins, 'ins block: <ins> kept');
    t_contains('class="adsbygoogle"', $ins, 'ins block: class preserved');
    t_contains('style="display:block"', $ins, 'ins block: style="display:block" SURVIVES (was stripped before fix)');
    t_contains('data-ad-client="ca-pub-1234567890123456"', $ins, 'ins block: data-ad-client preserved');
    t_contains('data-ad-slot="1234567890"', $ins, 'ins block: data-ad-slot preserved');
    t_contains('data-full-width-responsive="true"', $ins, 'ins block: data-full-width-responsive preserved');

    $resp = $policy->filter($s['responsive_ins']);
    t_contains('display:inline-block', $resp, 'responsive ins: display:inline-block survives');
    t_contains('width:728px', $resp, 'responsive ins: width survives');
    t_contains('height:90px', $resp, 'responsive ins: height survives');

    $init = $policy->filter($s['inline_init']);
    t_eq($s['inline_init'], $init, 'inline init: standard adsbygoogle push preserved byte-for-byte');

    $amp = $policy->filter($s['inline_amp']);
    t_contains('a && b && c < d', $amp, 'inline init with && and <: operators NOT entity-mangled (was "&amp;&amp;" before fix)');
    t_not_contains('&amp;&amp;', $amp, 'inline init: no "&amp;&amp;" corruption');

    echo "\n[MED-7] Malicious / unauthorized markup still neutralised\n";

    $evil_handler = $policy->filter('<img src=x onerror="alert(1)"><div onclick="steal()">x</div>');
    t_not_contains('onerror', $evil_handler, 'inline event handler onerror stripped');
    t_not_contains('onclick', $evil_handler, 'inline event handler onclick stripped');

    $evil_proto = $policy->filter('<a href="javascript:alert(2)">x</a><iframe src="javascript:alert(3)"></iframe>');
    t_not_contains('javascript:', $evil_proto, 'javascript: protocol stripped from href/src');

    $evil_script_attr = $policy->filter('<script src="//ok.example/a.js" onload="evil()"></script>');
    t_not_contains('onload', $evil_script_attr, 'external <script> onload= handler stripped by wp_kses');
    t_not_contains('evil()', $evil_script_attr, 'external <script> onload= leaves no residue');
    t_contains('src="//ok.example/a.js"', $evil_script_attr, 'external <script src> loader itself is kept');

    $data_src = $policy->filter('<script src="data:text/javascript,alert(1)"></script>');
    t_not_contains('data:text/javascript', $data_src, 'external <script src="data:..."> URI is stripped (not bypassed)');

    $nested = $policy->filter('<script><img src=x onerror=alert(1)></script>');
    // The <script> body is preserved verbatim (admin-trusted); but it is inert
    // as text inside <script> and cannot execute as HTML. Assert the dangerous
    // HTML did not escape the script element into live markup.
    t_ok(str_starts_with(trim($nested), '<script>'), 'nested-markup script: stays wrapped in <script>');

    $style_expr = $policy->filter('<ins style="display:block;background:url(javascript:alert(1))"></ins>');
    t_not_contains('javascript:', $style_expr, 'CSS url(javascript:...) in style is stripped by safecss_filter_attr');
    t_contains('display:block', $style_expr, 'CSS: legitimate display:block still allowed alongside');
}
