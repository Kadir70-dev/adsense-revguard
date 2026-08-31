# AdSense RevGuard

AdSense RevGuard is a WordPress ad-management plugin that inserts Google AdSense
or any arbitrary ad markup into your site's content at configurable positions,
without requiring theme edits.

- **Current version:** 3.0.0
- **License:** GPL-2.0-or-later
- **Text domain:** `adsense-revguard`

## Features

- Configurable maximum ads per page.
- Ten in-content ad slots.
- Placement controls for the beginning, middle, and end of a post, after the
  `<!--more-->` tag, and before a chosen paragraph or image.
- Display-condition controls (post types, front page, archives, logged-in users,
  and similar).
- Explicit and random ad-placement control tokens (for example `NoAds`,
  `OffDef`, `Ads1`–`Ads10`, `RndAds`).
- Header and footer embed-code output.
- Capability- and nonce-protected settings, with sanitisation, escaping, and an
  output-time HTML policy applied to stored ad markup.

## Requirements

| Requirement       | Minimum |
|-------------------|---------|
| WordPress         | 6.0     |
| PHP               | 8.2     |
| Tested up to (WP) | 7.0     |

The plugin ships with a generated Composer PSR-4 autoloader in `vendor/`. No
third-party runtime packages are bundled.

## Installation

1. Download or clone this repository into
   `wp-content/plugins/adsense-revguard/`.
2. Activate **AdSense RevGuard** from the WordPress Plugins screen.
3. Open **AdSense RevGuard** in the admin menu and configure ad slots,
   positions, and display conditions.

## Upgrading from Quick Adsense 2.8.7

Version 3.0.0 is a compatibility-focused rebuild. On activation it migrates and
preserves supported legacy configuration into the existing
`quick_adsense_settings` option, and existing content control tokens continue to
work. Back up your site and database before upgrading.

## Repository layout

```
adsense-revguard.php      Plugin bootstrap (environment guard + autoloader + composition root)
uninstall.php             Uninstall cleanup entry point
readme.txt                WordPress.org-format readme
LICENSE.md                Licensing summary and legacy attribution
CHANGELOG.md              Release notes
assets/                   Admin CSS
src/                      Namespaced plugin source (PSR-4)
views/                    Admin page templates
vendor/                   Generated Composer autoloader (committed; required at runtime)
```

## Licensing and attribution

AdSense RevGuard is free software distributed under the GNU General Public
License, version 2 or (at your option) any later version. See
[`LICENSE.md`](LICENSE.md) and <https://www.gnu.org/licenses/gpl-2.0.html>.

Version 3.0.0 is an independently maintained rebuild by **Abdul Kadir**. It
carries forward the placement engine and configuration behaviour of the original
GPL-licensed **Quick Adsense 2.8.7** project; that attribution is retained for
GPL license compliance, and legacy option keys, control tokens, and widget
identifiers are preserved so existing installations upgrade cleanly.
