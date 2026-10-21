<?php
/** Shared assertion helper for `wp eval-file` scripts. Output lines start with PASS or FAIL. */
if (!function_exists('qa_check')) {
    function qa_check(string $name, bool $ok, string $detail = ''): void {
        echo ($ok ? 'PASS ' : 'FAIL ') . $name . ($detail !== '' ? "  [$detail]" : '') . "\n";
    }
}
const QA_OPT = 'revguard_free_settings';
const QA_PUB = 'ca-pub-1234567890123456';
function qa_good(): array {
    return ['publisher' => QA_PUB, 'load_script' => true, 'slots' => [
        1 => ['enabled' => true, 'slot' => '1111111111', 'position' => 'manual', 'paragraph' => 2],
        2 => ['enabled' => true, 'slot' => '2222222222', 'position' => 'after_paragraph', 'paragraph' => 3],
        3 => ['enabled' => true, 'slot' => '3333333333', 'position' => 'end', 'paragraph' => 2],
    ]];
}
