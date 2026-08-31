=== RevGuard for AdSense ===
Contributors: abdulkadirco
Tags: adsense, advertising, ads, monetization
Requires at least: 6.0
Tested up to: 7.1
Stable tag: 3.0.0
Requires PHP: 8.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Insert advertising code into WordPress content at configurable positions while preserving existing legacy settings.

== Description ==

RevGuard for AdSense provides configurable advertising placement without requiring theme edits. It is a rebuilt, independently maintained continuation of the classic "Quick Adsense" plugin, focused on backward compatibility for existing installations.

Version 3.0.0 is a compatibility-focused rebuild. Existing legacy configuration is migrated into the preserved `quick_adsense_settings` option, and existing content control tokens remain supported.

This plugin is not affiliated with, endorsed by, or sponsored by Google LLC. "AdSense" and "Google AdSense" are trademarks of Google LLC and are used here only to describe compatibility.

Included in this release:

* Maximum ads-per-page configuration.
* Ten in-content ad slots.
* Beginning, middle, end, More-tag, paragraph, and image placement controls.
* Display-condition controls.
* Explicit and random ad control tokens.
* Header and footer embed code.
* Capability and nonce protected settings updates.
* Output-time filtering for stored advertising markup.

Ad and embed code is stored as entered by an administrator and filtered at each approved output boundary. Only trusted administrators with the `manage_options` capability can change plugin settings.

== Installation ==

1. In WordPress, open Plugins > Add New > Upload Plugin.
2. Select the RevGuard for AdSense ZIP file and choose Install Now.
3. Activate RevGuard for AdSense.
4. Open RevGuard for AdSense in the WordPress admin menu.
5. Configure ad slots, positions, and display conditions, then save.

For upgrades from legacy Quick Adsense 2.8.7, back up the site and database first. Install the new version, then deactivate and delete the old "Quick Adsense" plugin only after confirming your settings migrated. The activation process preserves and migrates supported legacy settings automatically. If the original Quick Adsense plugin is still installed, both plugins share the `quick_adsense_settings` option; run only one of them at a time.

== Frequently Asked Questions ==

= Will my existing legacy settings be preserved? =

Yes. Version 3.0.0 preserves the legacy settings option and migrates supported older option formats. Unknown keys already stored in the settings array are retained when the settings page is saved.

= Is ad code stored unchanged? =

Yes. Administrators commonly need to store scripts, iframes, and verification markup. Code remains raw at rest and is filtered through the plugin's advertising HTML policy when rendered.

= Does the plugin send any data to Google or other third parties? =

No. RevGuard for AdSense itself makes no external network requests: it has no telemetry, no analytics, no license checks, and no remote API calls. However, the advertising and embed code that an administrator enters is output on the site, and that administrator-selected code may load third-party resources or services (for example Google AdSense). The site owner remains responsible for the code and services they configure and for meeting any applicable privacy or consent obligations.

= Which content tokens are supported? =

Existing tokens including `NoAds`, `OffDef`, `Ads1` through `Ads10`, `RndAds`, and the established position-disable aliases remain supported.

= Are settings deleted when the plugin is deactivated? =

No. Deactivation preserves settings. Choosing Delete from the WordPress Plugins screen runs the uninstall cleanup, which removes only data owned by this plugin. Shared and original-plugin option keys are preserved when the original "Quick Adsense" plugin is detected or when data ownership is ambiguous.

= Does this release include Gutenberg blocks, widgets, or an ads.txt manager? =

No. This release is limited to the compatibility-focused core, the settings interface, and the approved frontend output.

== Upgrade Notice ==

= 3.0.0 =

Major compatibility-focused rebuild requiring PHP 8.2 and WordPress 6.0. Back up your site before upgrading from Quick Adsense 2.8.7. Existing supported settings are migrated and preserved.

== Changelog ==

= 3.0.0 =

* Rebuilt the plugin on PHP 8.2 with Composer PSR-4 autoloading.
* Added guarded activation, deactivation, uninstall, and versioned migrations.
* Ported the legacy content-placement engine while preserving placement and control-token behavior.
* Added a modern settings page using preserved option keys.
* Added nonce, capability, sanitization, escaping, and output-policy controls.
* Added header and footer embed-code integration.
* Preserved legacy control tokens and configuration behavior.
* Uninstall no longer deletes option keys that may belong to a separately
  installed original "Quick Adsense" plugin.

== Credits ==

RevGuard for AdSense is a derivative work of the GPL-licensed "Quick Adsense"
WordPress plugin.

* Original plugin: Quick Adsense (https://quickadsense.com/)
* Original author / WordPress.org contributor: namithjawahar (https://smartlogix.co.in/)
* Referenced original version: 2.8.7 (2022)
* Original license: GNU General Public License, version 2 or later
  (WordPress.org hosting requires GPLv2-or-later compatibility; the 2.8.7
  package did not carry an explicit License header).
* Rebuild, new architecture, and continued maintenance (2026): Abdul Kadir.

The original placement-engine behaviour and the legacy option keys, content
control tokens, and widget identifiers are retained so existing Quick Adsense
installations upgrade cleanly. See LICENSE.md for full derivative-work detail.
