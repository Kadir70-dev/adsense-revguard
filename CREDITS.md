# Credits & Attribution

## This work

**RevGuard for AdSense**, version 3.0.0 and onward.
Rebuild, architecture, and continued maintenance: **Abdul Kadir** (2026).
Copyright (C) 2026 Abdul Kadir. Licensed under GPL-2.0-or-later.

## Original work this plugin derives from

**Quick Adsense** — a GPL-licensed WordPress ad-insertion plugin.

| Field | Value | Source |
|---|---|---|
| Project home | https://quickadsense.com/ | `quick-adsense.php` header, 2.8.7 |
| Original author | namithjawahar | `Author:` header, 2.8.7 |
| Author URI | https://smartlogix.co.in/ | `Author URI:` header, 2.8.7 |
| WordPress.org contributor slug | `namithjawahar` | `readme.txt` `Contributors:` line, 2.8.7 |
| Referenced version | 2.8.7 | `Version:` header / `Stable tag:`, 2022 |
| License | GNU GPL v2 or later | See note below |

**License note.** The Quick Adsense 2.8.7 distribution does not contain an
explicit `License:` line in either `readme.txt` or the main plugin file.
Its GPL status is established by (a) WordPress.org's requirement that all hosted
plugins be GPLv2-or-later compatible, and (b) GPL-licensed components bundled in
that release (`includes/vendor/GeoIP/LICENSE` is the GNU GPL). This plugin
therefore treats the original as **GPL v2 or later** and licenses the combined
derivative work under the same terms. This statement is a good-faith reading of
verifiable facts, not a legal opinion.

### Reproduced from the original for backward compatibility

These identifiers and behaviours are intentionally preserved so that sites
upgrading from Quick Adsense keep working. They are **not** to be renamed:

- Option keys: `quick_adsense_settings`, `quick_adsense_2_options`,
  `quick_adsense_2_options_bak`, `quick_adsense_schema_version` (new),
  `quick_adsense_migration_backup` (new).
- Legacy stats / ads.txt keys owned by the original plugin:
  `quick_adsense_onpost_ad_{1..10}_stats`, `quick_adsense_adstxt_*`.
- Content control tokens: `NoAds`, `OffDef`, `OffWidget`, `OffBegin`,
  `OffMiddle`, `OffEnd`, `OffAfMore`, `OffBfLastPara`, `Ads1`–`Ads10`,
  `CusAds1`–`CusAds10`, `RndAds`, and their aliases.
- Historical widget identifier label `AdsWidget%d (Quick Adsense)` (WordPress
  derives the stored widget id from this string).
- The `\QuickAdsense\` PHP namespace and `@package QuickAdsense` doc tag
  (internal; kept stable for the generated class map).

## Bundled third-party code

| Component | License | Location |
|---|---|---|
| Composer generated autoloader | MIT | `vendor/composer/LICENSE` |

No other third-party runtime code is bundled. (Quick Adsense 2.8.7 shipped
Mobile_Detect and a GeoIP library; this rebuild does not.)

## Trademarks

"AdSense" and "Google AdSense" are trademarks of Google LLC. RevGuard for
AdSense is an independent project and is not affiliated with, endorsed by, or
sponsored by Google LLC.

## Open action item

The `Contributors:` field in `readme.txt` is a placeholder
(`your-wordpress-org-username`). Replace it with the real WordPress.org account
name(s) of the current maintainer before submitting to the plugin directory.
Do not list `namithjawahar` there — that credit belongs in this file and in
`readme.txt`'s `== Credits ==` section, not in the contributor list of the new
plugin.
