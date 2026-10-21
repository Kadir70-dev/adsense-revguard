=== KadirSlot Ad Placement ===
Contributors: abdulkadirco
Tags: adsense, ads, advertising, ad insertion, shortcodes
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 8.2
Stable tag: 1.1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Place up to three Google AdSense display units with shortcodes or automatic post and page placements.

== Description ==
KadirSlot Ad Placement shows Google AdSense display ad units on your site. Enter your AdSense publisher ID once and the ad unit ID for each of up to three slots. The plugin builds the official responsive AdSense ad code from those validated IDs. It does not accept custom HTML or JavaScript.

Each slot can be placed automatically at the beginning, middle or end of single posts and pages, or after a chosen paragraph. You can also place a slot manually with the shortcode [revguard_ad id="1"]. Slots are disabled until you enable them, and ads are not added to RSS feeds.

KadirSlot Ad Placement does not track visitors, store visitor identifiers or send any data from your server. You need an approved Google AdSense account; the plugin does not create accounts, verify sites or guarantee ad revenue.

== External services ==
This plugin connects visitors' browsers to Google AdSense to display ads.

* What is loaded: Google's AdSense script, https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js, with your publisher ID as the client parameter. Google then serves the ads into the generated ad units.
* When: only on front-end pages that display at least one enabled KadirSlot Ad Placement slot with a saved publisher ID and ad unit ID. Nothing is loaded in the admin area, in feeds or on pages without a KadirSlot Ad Placement ad. If "Load the Google AdSense script" is turned off, the plugin outputs only the ad units and relies on your theme or another plugin to load Google's script.
* What is sent: your AdSense publisher ID and ad unit IDs, plus the data a browser sends with any request to Google, such as the visitor's IP address, user agent, the page URL and Google cookies. Google may set or read cookies to serve and measure ads. Your server does not send any data.
* Provider: Google AdSense, by Google LLC. Terms: https://policies.google.com/terms and https://www.google.com/adsense/new/localized-terms. Privacy policy: https://policies.google.com/privacy. AdSense program policies: https://support.google.com/adsense/answer/48182.

KadirSlot Ad Placement is independently developed and is not affiliated with or endorsed by Google LLC. Google AdSense is a trademark of Google LLC.

You are responsible for following the AdSense program policies and for any consent required in your region before ads are shown.

== Installation ==
1. Upload the ZIP via Plugins > Add New Plugin > Upload Plugin.
2. If the older RevGuard Free plugin is installed, deactivate it before activating KadirSlot Ad Placement. The existing settings are retained.
3. Activate KadirSlot Ad Placement.
4. Go to Settings > KadirSlot Ad Placement.
5. Enter your AdSense publisher ID (ca-pub- followed by 16 digits) and, for each slot, the ad unit ID from your AdSense account.
6. Enable a slot and choose a placement, or use [revguard_ad id="1"].

== Frequently Asked Questions ==
= Can I paste my own ad code? =
No. For security, version 1.1 accepts only an AdSense publisher ID and numeric ad unit IDs and builds the ad code itself. Other ad networks and custom HTML are not supported.

= My theme already loads the AdSense script. =
Turn off "Load the Google AdSense script" in Settings > KadirSlot Ad Placement. KadirSlot Ad Placement also skips its own copy when another plugin has enqueued the same script through WordPress.

= Does this protect Google AdSense accounts? =
No. This plugin only places ads and makes no guarantee about invalid traffic or advertising revenue.

= What happens to settings from RevGuard Free version 1.0? =
On the first load after upgrading, AdSense publisher and ad unit IDs found in each slot's old code are migrated, and the slot keeps its enabled state, placement and paragraph. Slots whose old code contained no AdSense IDs, or a second publisher ID, are disabled. The old code is never displayed again, but it is shown on the settings page so you can copy it until you choose to delete the upgrade report.

= What happens on uninstall? =
Saved settings remain unless manually deleted. Deactivation also preserves configuration.

= Can it run alongside RevGuard Pro? =
Yes. The existing RevGuard Pro integration check remains unchanged; while that companion plugin is active it handles ad rendering and KadirSlot Ad Placement outputs nothing. Saved Free settings are kept for when Pro is deactivated.

== Changelog ==
= 1.1.1 =
* Renamed plugin and text domain for directory naming compliance; preserves existing settings and the [revguard_ad] shortcode.
* Strengthened validation for invalid typed inputs in the Settings API.

= 1.1.0 =
* Ad slots now take a validated AdSense publisher ID and ad unit IDs and generate the official AdSense markup. Custom HTML and JavaScript are no longer accepted or displayed.
* The Google AdSense script loads only on pages that show an ad, once per page, with an option to turn it off.
* Settings use the WordPress Settings API.
* Ads are no longer added to RSS feeds.
* One-time migration of 1.0 settings, with an upgrade report for slots that could not be migrated.
* Interface strings are translatable.

= 1.0.1 =
* RevGuard Pro compatibility: Free stops rendering ads while Pro is active and keeps its saved settings for downgrade.

= 1.0.0 =
* First free edition: three slots, automatic insertion, shortcode, secure admin form.

== Upgrade Notice ==
= 1.1.1 =
New name, same stored configuration and shortcode. Custom ad code was already replaced in version 1.1.0.

= 1.1.0 =
Custom ad code is replaced by AdSense publisher and ad unit IDs. Slots without AdSense IDs are disabled; review Settings > KadirSlot Ad Placement after upgrading.
