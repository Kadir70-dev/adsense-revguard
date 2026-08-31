# License

RevGuard for AdSense is free software licensed under the GNU General Public
License, version 2, or (at your option) any later version (`GPL-2.0-or-later`).

You may redistribute and/or modify this software under the terms of the GNU
General Public License as published by the Free Software Foundation.

This software is distributed in the hope that it will be useful, but WITHOUT
ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

The complete license text is available at:
https://www.gnu.org/licenses/old-licenses/gpl-2.0.html

## Copyright

* Copyright (C) 2026 Abdul Kadir — rebuild, architecture, and continued
  maintenance of this plugin (version 3.0.0 onward).
* Portions Copyright (C) namithjawahar / Quick Adsense — original plugin design,
  the content-placement engine behaviour, and the legacy option/token/widget
  identifiers this plugin reproduces for backward compatibility.

Copyright in this project is additive: the notice above does not replace or
diminish the original author's copyright in the code and design this work
derives from.

## Derivative-work attribution

RevGuard for AdSense is a derivative work of the GPL-licensed **Quick Adsense**
WordPress plugin.

| | |
|---|---|
| Original project | Quick Adsense — https://quickadsense.com/ |
| Original author | namithjawahar (Author URI: https://smartlogix.co.in/) |
| Original WordPress.org contributor slug | `namithjawahar` |
| Referenced version | 2.8.7 (2022) |
| Original license | GNU General Public License, version 2 or later. The 2.8.7 release did not ship an explicit `License:` header, but WordPress.org hosting requires all plugins to be GPLv2-or-later compatible, and the 2.8.7 package bundled GPL-licensed components. |

### What was carried forward from the original

* The ad-placement model (beginning / middle / end / after-more / before-last-
  paragraph / after-paragraph / after-image positions; up to ten ad slots;
  maximum-ads-per-page cap).
* The content control tokens (`NoAds`, `OffDef`, `OffWidget`, `Ads1`–`Ads10`,
  `RndAds`, and the position-disable aliases).
* The legacy option keys (`quick_adsense_settings`, `quick_adsense_2_options`,
  `quick_adsense_2_options_bak`), the historical widget identifiers, and the
  migration detection logic — all reproduced so existing installations upgrade
  without data loss.

### What is new in this work

* A rebuilt, namespaced PHP 8.2 codebase (PSR-4, dependency container, service
  providers, contracts) that shares no source files with the original.
* Guarded activation/deactivation/uninstall, versioned and reversible
  migrations, an explicit editable-settings allowlist, nonce + capability
  enforcement, and an output-time HTML policy for stored ad markup.

## Third-party components

This distribution bundles only the generated Composer autoloader
(`vendor/composer/`), which is licensed under the MIT License; its license text
is included at `vendor/composer/LICENSE`. Unlike Quick Adsense 2.8.7, this
plugin does **not** bundle Mobile_Detect, a GeoIP library, or any other
third-party runtime code.

## Trademarks

"AdSense" and "Google AdSense" are trademarks of Google LLC. This plugin is not
affiliated with, endorsed by, or sponsored by Google LLC; the term is used only
to describe compatibility.
