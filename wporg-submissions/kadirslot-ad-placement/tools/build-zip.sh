#!/bin/sh
# Build the installable WordPress.org ZIP from source/ using an explicit file allowlist.
# Usage: tools/build-zip.sh   -> kadirslot-ad-placement-<version>.zip next to source/
set -eu
ROOT=$(cd "$(dirname "$0")/.." && pwd)
SRC="$ROOT/source"
SLUG=kadirslot-ad-placement
FILES="kadirslot-ad-placement.php includes/plugin.php readme.txt LICENSE assets/admin.css assets/front.js"

HEADER=$(sed -n 's/^ \* Version: *//p' "$SRC/$SLUG.php")
CONST=$(sed -n "s/^const VERSION = '\(.*\)';/\1/p" "$SRC/includes/plugin.php")
STABLE=$(sed -n 's/^Stable tag: *//p' "$SRC/readme.txt")
[ "$HEADER" = "$CONST" ] && [ "$HEADER" = "$STABLE" ] || { echo "version mismatch: header=$HEADER const=$CONST stable=$STABLE" >&2; exit 1; }
for f in $FILES; do php -l "$SRC/$f" >/dev/null 2>&1 || case "$f" in *.php) echo "syntax error: $f" >&2; exit 1;; esac; done

STAGE=$(mktemp -d "${TMPDIR:-/tmp}/kadirslot-build.XXXXXX")
trap 'rm -rf "$STAGE"' EXIT
for f in $FILES; do
  mkdir -p "$STAGE/$SLUG/$(dirname "$f")"
  cp "$SRC/$f" "$STAGE/$SLUG/$f"
done
# Fixed permissions and timestamps so the same source gives the same ZIP.
find "$STAGE/$SLUG" -type d -exec chmod 755 {} + && find "$STAGE/$SLUG" -type f -exec chmod 644 {} +
find "$STAGE/$SLUG" -exec touch -h -d '2026-10-21 00:00:00 UTC' {} +
OUT="$ROOT/$SLUG-$HEADER.zip"
rm -f "$OUT"
(cd "$STAGE" && find "$SLUG" | LC_ALL=C sort | TZ=UTC zip -q -X "$OUT" -@)
echo "$OUT"
sha256sum "$OUT"
