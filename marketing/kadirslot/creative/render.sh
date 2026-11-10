#!/usr/bin/env bash
# Renders brand-owned WordPress.org directory graphics from source SVGs.
# These assets go to the top-level WordPress.org SVN /assets folder, NOT plugin ZIP.
set -euo pipefail
cd "$(dirname "$0")"
command -v rsvg-convert >/dev/null || { echo "Install librsvg2-bin first" >&2; exit 1; }
mkdir -p output
rsvg-convert --width 128 --height 128 icon.svg --output output/icon-128x128.png
rsvg-convert --width 256 --height 256 icon.svg --output output/icon-256x256.png
rsvg-convert --width 772 --height 250 banner.svg --output output/banner-772x250.png
rsvg-convert --width 1544 --height 500 banner.svg --output output/banner-1544x500.png
for file in output/*.png; do test -s "$file"; done
echo "Rendered: icon 128/256 and banner 772x250/1544x500"
