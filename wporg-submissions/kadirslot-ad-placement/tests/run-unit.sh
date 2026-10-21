#!/bin/sh
# Unit + same-process coexistence tests. Usage: tests/run-unit.sh [plugin-dir]   (default: source/)
# Runs with the host `php`; set PHP="docker run --rm -v $PWD:$PWD -w $PWD php:8.2-cli php" for another version.
set -u
HERE=$(cd "$(dirname "$0")" && pwd)
DIR=$(cd "${1:-$HERE/../source}" && pwd)
PHP=${PHP:-php}
WORK=$(mktemp -d "${TMPDIR:-/tmp}/kadirslot-unit.XXXXXX")
trap 'rm -rf "$WORK"' EXIT
FAIL=0
run() { echo "== $*"; if ! $PHP "$@" > "$WORK/out" 2>&1; then FAIL=$((FAIL + 1)); cat "$WORK/out"; else grep -E '^(FAIL|unit:)' "$WORK/out"; fi; }

for f in "$HERE"/../source/*.php "$HERE"/../source/includes/*.php "$DIR"/*.php "$DIR"/includes/*.php; do
  $PHP -l "$f" > /dev/null || FAIL=$((FAIL + 1))
done
run "$HERE/unit/test-plugin.php" "$DIR"

for z in revguard-free-1.0.1 revguard-free-1.1.0 kadirslot-ad-placement-1.1.1; do
  mkdir -p "$WORK/$z" && unzip -qo "$HERE/fixtures/$z.zip" -d "$WORK/$z"
done
for order in new-first legacy-first; do
  run "$HERE/unit/test-coexistence.php" "$DIR" "$WORK/revguard-free-1.0.1/revguard-free/revguard-free.php" $order
  run "$HERE/unit/test-coexistence.php" "$DIR" "$WORK/revguard-free-1.1.0/revguard-free/revguard-free.php" $order
  run "$HERE/unit/test-coexistence.php" "$DIR" "$WORK/kadirslot-ad-placement-1.1.1/kadirslot-ad-placement/kadirslot-ad-placement.php" $order
done
# Two copies of this build (for example a renamed upload directory) must not fatal either.
cp -r "$DIR" "$WORK/copy"
run "$HERE/unit/test-coexistence.php" "$DIR" "$WORK/copy/kadirslot-ad-placement.php" new-first

echo "unit suites failed: $FAIL"
exit $FAIL
