=== RevGuard for AdSense ===
Contributors: quickadsense
Tags: adsense, advertising, ads, monetization
Requires at least: 6.0
Tested up to: 7.0
Stable tag: 3.0.0
Requires PHP: 8.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Insert advertising code into WordPress content at configurable positions while preserving existing legacy settings.

== Description ==

RevGuard for AdSense provides configurable advertising placement without requiring theme edits.

Version 3.0.0 is a compatibility-focused rebuild. Existing legacy configuration is migrated into the preserved `quick_adsense_settings` option, and existing content control tokens remain supported.

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

For upgrades from legacy version 2.8.7, back up the site and database first. Install the new version over the existing plugin. The activation process preserves and migrates supported legacy settings automatically.

== Frequently Asked Questions ==

= Will my existing legacy settings be preserved? =

Yes. Version 3.0.0 preserves the legacy settings option and migrates supported older option formats. Unknown keys already stored in the settings array are retained when the settings page is saved.

= Is ad code stored unchanged? =

Yes. Administrators commonly need to store scripts, iframes, and verification markup. Code remains raw at rest and is filtered through the plugin's advertising HTML policy when rendered.

= Which content tokens are supported? =

Existing tokens including `NoAds`, `OffDef`, `Ads1` through `Ads10`, `RndAds`, and the established position-disable aliases remain supported.

= Are settings deleted when the plugin is deactivated? =

No. Deactivation preserves settings. Choosing Delete from the WordPress Plugins screen runs the uninstall cleanup and removes data owned by the plugin.

= Does this release include Gutenberg blocks or Pro features? =

No. RC1 is limited to the compatibility-focused core, settings interface, and approved frontend output.

== Upgrade Notice ==

= 3.0.0 =

Major compatibility-focused rebuild requiring PHP 8.2 and WordPress 6.0. Back up your site before upgrading from 2.8.7. Existing supported settings are migrated and preserved.

== Changelog ==

= 3.0.0 =

* Rebuilt the plugin on PHP 8.2 with Composer PSR-4 autoloading.
* Added guarded activation, deactivation, uninstall, and versioned migrations.
* Ported the legacy content-placement engine with golden-master parity coverage.
* Added a modern settings page using preserved option keys.
* Added nonce, capability, sanitization, escaping, and output-policy controls.
* Added header and footer embed-code integration.
* Preserved legacy control tokens and configuration behavior.
