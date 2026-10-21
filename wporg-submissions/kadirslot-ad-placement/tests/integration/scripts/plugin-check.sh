#!/bin/bash
# Runs inside the wordpress container: official Plugin Check (WP-CLI command) against the plugin installed
# from the exact release ZIP. Results go to /qa/artifacts/plugin-check-*. Usage: plugin-check.sh <zip-name>
# Plugin Check only enables its runtime checks when argv[1..2] is "plugin check" and the plugin is active,
# so global WP-CLI flags go after the subcommand.
set -u
cd /var/www/html
G="--allow-root --path=/var/www/html"
WP="php /usr/local/bin/wp-cli.phar"
PC="$WP plugin check"
R="--require=./wp-content/plugins/plugin-check/cli.php $G"
SLUG=kadirslot-ad-placement
$WP plugin install /qa/extra/plugin-check.zip --force --activate $G >/dev/null 2>&1 || { echo "FAIL install Plugin Check"; exit 1; }
echo "# Plugin Check $($WP plugin get plugin-check --field=version $G 2>/dev/null)"
$WP plugin install /qa/dist/$1 --force --activate $G >/dev/null 2>&1
echo "# checked plugin version: $($WP plugin get $SLUG --field=version $G 2>/dev/null), status: $($WP plugin get $SLUG --field=status $G 2>/dev/null)"
$WP plugin list-checks --format=csv --fields=slug,category,stability $R > /qa/artifacts/plugin-check-list.csv 2>/dev/null
echo "checks available: $(($(wc -l < /qa/artifacts/plugin-check-list.csv) - 1))"
$PC $SLUG --format=json $R > /qa/artifacts/plugin-check-default.json 2>&1; echo "all stable checks exit: $?"; tail -1 /qa/artifacts/plugin-check-default.json
$PC $SLUG --format=json --include-experimental $R > /qa/artifacts/plugin-check-experimental.json 2>&1; echo "stable + experimental exit: $?"; tail -1 /qa/artifacts/plugin-check-experimental.json
RUNTIME=enqueued_scripts_size,enqueued_styles_size,enqueued_styles_scope,enqueued_scripts_scope,non_blocking_scripts
$PC $SLUG --checks=$RUNTIME --format=json $R > /qa/artifacts/plugin-check-runtime.json 2>&1; echo "runtime checks only exit: $?"; tail -1 /qa/artifacts/plugin-check-runtime.json
# Control: the same command must report findings for a known-imperfect build, proving checks are not silently skipped.
$WP plugin install /qa/fixtures/revguard-free-1.0.1.zip --force $G >/dev/null 2>&1
N=$($PC revguard-free --format=csv $R 2>/dev/null | grep -c ',ERROR,\|,WARNING,')
echo "control (RevGuard Free 1.0.1) findings: $N"
$WP plugin delete revguard-free $G >/dev/null 2>&1
