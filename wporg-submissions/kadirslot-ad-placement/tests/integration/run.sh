#!/bin/bash
# Host entry point: starts the offline stack, runs suite.sh and plugin-check.sh, tears it down.
# Usage: tests/integration/run.sh <release.zip> <wp-cli.phar> <extra-dir> <artifacts-dir> [wp-core-dir]
#   extra-dir: plugin-check.zip and any revguard-pro-*.zip to test against (not committed)
#   wp-core-dir: optional extracted WordPress core (for example 6.0) instead of the image's version
set -eu
HERE=$(cd "$(dirname "$0")" && pwd)
ZIP=$(readlink -f "$1")
export DIST_DIR=$(dirname "$ZIP") WPCLI=$(readlink -f "$2") EXTRA_DIR=$(readlink -f "$3") ARTIFACTS=$(readlink -f "$4")
export DB_PASS=$(head -c 18 /dev/urandom | base64 | tr -dc A-Za-z0-9)
[ -n "${5:-}" ] && export WP_CORE_DIR=$(readlink -f "$5")
mkdir -p "$ARTIFACTS" && chmod 777 "$ARTIFACTS" && rm -f "$ARTIFACTS"/http-requests.log
C="docker compose -f $HERE/compose.yaml -p kadirslot-qa"
[ -z "${KEEP:-}" ] && trap '$C down -v >/dev/null 2>&1' EXIT
$C down -v >/dev/null 2>&1 || true
$C up -d --wait >/dev/null
for i in $(seq 1 60); do $C exec -T wordpress test -f /var/www/html/wp-config.php && break; sleep 1; done
$C exec -T wordpress bash /qa/scripts/suite.sh "$(basename "$ZIP")" | tee "$ARTIFACTS/suite.txt"
if $C exec -T wordpress php /usr/local/bin/wp-cli.phar --allow-root --path=/var/www/html eval 'exit(version_compare(get_bloginfo("version"),"6.3",">=")?0:1);' 2>/dev/null; then
  $C exec -T wordpress bash /qa/scripts/plugin-check.sh "$(basename "$ZIP")" | tee "$ARTIFACTS/plugin-check-run.txt"
else
  echo "SKIP Plugin Check requires WordPress 6.3+"
fi
P=$(grep -c '^PASS' "$ARTIFACTS/suite.txt" || true); F=$(grep -c '^FAIL' "$ARTIFACTS/suite.txt" || true)
echo "integration: $P passed, $F failed"
[ "$F" = 0 ]
