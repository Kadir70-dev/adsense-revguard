#!/usr/bin/env bash
#
# Build and verify the RevGuard for AdSense release ZIP.
#
#   - Uses `git archive`, which always writes POSIX "/" member paths, so the
#     Windows-backslash BLOCKER cannot recur.
#   - Ships runtime files only (tests/, build/, dot-files are export-ignored via
#     .gitattributes).
#   - Fails hard if any archive member contains a backslash, if the tree is
#     double-nested, or if the plugin main file is not directly under the single
#     root directory.
#   - Extracts into a clean temp dir and re-checks the extracted release, then
#     prints SHA-256, file count, uncompressed size and ZIP size.
#
# Usage:  bash build/make-release.sh [git-ref]     (default ref: HEAD)

set -euo pipefail

SLUG="revguard-for-adsense"
REF="${1:-HEAD}"
ROOT="$(git rev-parse --show-toplevel)"
cd "$ROOT"

VERSION="$(sed -n 's/^ \* Version:[[:space:]]*//p' "${SLUG}.php" | head -1 | tr -d '[:space:]')"
[ -n "$VERSION" ] || { echo "FAIL: could not read Version from ${SLUG}.php"; exit 1; }

DIST="${ROOT}/dist"
ZIP="${DIST}/${SLUG}-${VERSION}.zip"
mkdir -p "$DIST"
rm -f "$ZIP"

echo "==> git archive ${REF}  ->  ${ZIP##*/}  (prefix ${SLUG}/)"
git archive --format=zip --prefix="${SLUG}/" -o "$ZIP" "$REF"

echo "==> Inspecting every ZIP member"
MEMBERS="$(unzip -Z1 "$ZIP")"

if printf '%s\n' "$MEMBERS" | grep -q '\\'; then
	echo "FAIL: a ZIP member contains a backslash:"
	printf '%s\n' "$MEMBERS" | grep '\\'
	exit 1
fi
echo "    ok: no backslash in any member path"

if printf '%s\n' "$MEMBERS" | grep -vqE "^${SLUG}/"; then
	echo "FAIL: member(s) outside the single root '${SLUG}/':"
	printf '%s\n' "$MEMBERS" | grep -vE "^${SLUG}/"
	exit 1
fi
echo "    ok: every member is under the single root '${SLUG}/'"

if printf '%s\n' "$MEMBERS" | grep -qE "^${SLUG}/${SLUG}/"; then
	echo "FAIL: tree is double-nested (${SLUG}/${SLUG}/...)"
	exit 1
fi
echo "    ok: not double-nested"

printf '%s\n' "$MEMBERS" | grep -qxE "${SLUG}/${SLUG}\.php" \
	|| { echo "FAIL: ${SLUG}/${SLUG}.php not at the root of the plugin dir"; exit 1; }
echo "    ok: ${SLUG}/${SLUG}.php present directly under the root"

for must in "${SLUG}/uninstall.php" "${SLUG}/readme.txt" "${SLUG}/LICENSE.md" \
            "${SLUG}/license.txt" "${SLUG}/vendor/autoload.php" "${SLUG}/src/Plugin.php"; do
	printf '%s\n' "$MEMBERS" | grep -qxE "$must" || { echo "FAIL: missing required member: $must"; exit 1; }
done
echo "    ok: required runtime members present"

for banned in "${SLUG}/tests/" "${SLUG}/.git" "${SLUG}/build/" "${SLUG}/.gitignore" \
              "${SLUG}/.gitattributes" "${SLUG}/README.md" "${SLUG}/CREDITS.md" \
              "${SLUG}/dist/" "${SLUG}/node_modules/"; do
	if printf '%s\n' "$MEMBERS" | grep -qE "^${banned}"; then
		echo "FAIL: development artifact leaked into ZIP: $banned"
		exit 1
	fi
done
echo "    ok: no dev/test/VCS artifacts in ZIP"

echo "==> Secret / junk scan across ZIP members"
if printf '%s\n' "$MEMBERS" | grep -qiE '\.(env|pem|key|log|tmp|bak)$|(^|/)\.env'; then
	echo "FAIL: suspicious file in ZIP"; printf '%s\n' "$MEMBERS" | grep -iE '\.(env|pem|key|log|tmp|bak)$'
	exit 1
fi
echo "    ok"

echo "==> Extraction test into a clean temp dir"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT
unzip -qq "$ZIP" -d "$TMP"

[ -f "${TMP}/${SLUG}/${SLUG}.php" ] || { echo "FAIL: extracted main file missing"; exit 1; }
[ "$(find "${TMP}" -mindepth 1 -maxdepth 1 -type d | wc -l)" -eq 1 ] || { echo "FAIL: extracted ZIP has more than one top dir"; exit 1; }
grep -q 'Plugin Name:[[:space:]]*RevGuard for AdSense' "${TMP}/${SLUG}/${SLUG}.php" || { echo "FAIL: extracted plugin header wrong"; exit 1; }
grep -q "Version:[[:space:]]*${VERSION}" "${TMP}/${SLUG}/${SLUG}.php" || { echo "FAIL: extracted version != ${VERSION}"; exit 1; }
echo "    ok: extracted tree valid, single root, header + version correct"

if command -v php >/dev/null 2>&1; then
	echo "==> php -l on every extracted PHP file"
	while IFS= read -r f; do php -l "$f" >/dev/null || { echo "FAIL lint: $f"; exit 1; }; done \
		< <(find "${TMP}/${SLUG}" -name '*.php')
	echo "    ok: all extracted PHP files parse"
else
	echo "==> (php not on PATH here; lint the extracted tree separately)"
fi

FILE_COUNT="$(unzip -Z1 "$ZIP" | grep -vE '/$' | wc -l | tr -d ' ')"
UNCOMPRESSED="$(unzip -l "$ZIP" | awk 'END{print $1}')"
ZIP_SIZE="$(wc -c < "$ZIP" | tr -d ' ')"
SHA="$(sha256sum "$ZIP" | awk '{print $1}')"

echo
echo "=================== RELEASE ARTIFACT ==================="
echo "path            : $ZIP"
echo "version         : $VERSION"
echo "files (no dirs) : $FILE_COUNT"
echo "uncompressed    : $UNCOMPRESSED bytes"
echo "zip size        : $ZIP_SIZE bytes"
echo "sha256          : $SHA"
echo "======================================================="
