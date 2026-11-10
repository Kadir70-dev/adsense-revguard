=== KadirSlot Ad Placement ===
Contributors: abdulkadirco
Tags: adsense insertion, ad placement, adsense shortcode, responsive ads, wordpress ads
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 8.2
Stable tag: 1.1.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Insert responsive Google AdSense ads in WordPress posts and pages with 3 ad slots, automatic placements and easy shortcodes.

== Description ==
KadirSlot Ad Placement is a lightweight Google AdSense insertion plugin for WordPress publishers, bloggers and small content websites. Add up to three responsive AdSense display ad units to posts and pages without editing your theme.

Enter your existing Google AdSense publisher ID once, then enter each ad unit ID. KadirSlot builds the standard responsive Google AdSense markup from validated IDs. Place ads automatically, or use [kadirslot_ad id="1"] in your content. The older [revguard_ad id="1"] shortcode still works for sites upgrading from RevGuard Free.

KadirSlot does not track visitors or collect visitor identifiers on your server. Loading actual AdSense ads connects visitors' browsers to Google, which may collect data as described under External services. An approved AdSense account is required.

== Who is this plugin for? ==

* WordPress bloggers and publishers using Google AdSense display ads.
* Site owners who want simple manual or automatic ad placement without editing PHP templates.
* Publishers who need a small, focused alternative to a full ad-management platform.
* Existing RevGuard Free users who want to retain compatible saved settings and old shortcodes.

== Features ==

* Three individually enabled Google AdSense display ad slots.
* Automatic ad insertion at the beginning, middle or end of single posts and pages, or after a chosen paragraph.
* Manual ad units using [kadirslot_ad id="1"], [kadirslot_ad id="2"] or [kadirslot_ad id="3"].
* Backward compatibility for the existing [revguard_ad id="1"] shortcode.
* Responsive AdSense units generated from validated publisher and ad-unit IDs.
* Option to avoid loading a duplicate AdSense script if your theme or another plugin already loads it.
* No ads in RSS feeds. Disabled slots do not display ads.

== How to insert an AdSense ad ==

1. Sign in to Google AdSense and obtain your publisher ID and display ad unit IDs.
2. Go to Settings > KadirSlot Ad Placement in your WordPress admin.
3. Add your publisher ID and an ad unit ID, then enable that slot.
4. Choose Beginning, Middle, End, After paragraph, or Manual.
5. For manual placement, insert [kadirslot_ad id="1"] into a WordPress Shortcode block or compatible post content.

This plugin is for Google AdSense display ad units only. It does not support arbitrary HTML/JavaScript, ad network rotation, or revenue guarantees. Follow Google AdSense policies and regional consent requirements.

== External services ==
This plugin connects visitors' browsers to Google AdSense to display ads.

* What is loaded: Google's AdSense script, https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js, with your publisher ID as the client parameter. Google then serves the ads into the generated ad units.
* When: only on front-end pages that display at least one enabled KadirSlot Ad Placement slot with a saved publisher ID and ad unit ID. Nothing is loaded in the admin area, in feeds or on pages without a KadirSlot Ad Placement ad. If "Load the Google AdSense script" is turned off, the plugin outputs only the ad units and relies on your theme or another plugin to load Google's script.
* What is sent: your AdSense publisher ID and ad unit IDs, plus the data a browser sends with any request to Google, such as the visitor's IP address, user agent, the page URL and Google cookies. Google may set or read cookies to serve and measure ads. Your server does not send any data.
* Provider: Google AdSense, by Google LLC. Terms: https://policies.google.com/terms and https://www.google.com/adsense/new/localized-terms. Privacy policy: https://policies.google.com/privacy. AdSense program policies: https://support.google.com/adsense/answer/48182.

KadirSlot Ad Placement is independently developed and is not affiliated with or endorsed by Google LLC. Google AdSense is a trademark of Google LLC.

You are responsible for following the AdSense program policies and for any consent required in your region before ads are shown.

== Installation ==
1. In WordPress, open Plugins > Add New Plugin, search for KadirSlot Ad Placement and choose Install Now; alternatively upload the official ZIP.
2. If the older RevGuard Free plugin is installed, deactivate it. KadirSlot Ad Placement uses the same saved settings and stays paused, without showing ads, while RevGuard Free is still active.
3. Activate KadirSlot Ad Placement.
4. Go to Settings > KadirSlot Ad Placement.
5. Enter your AdSense publisher ID (ca-pub- followed by 16 digits) and, for each slot, the ad unit ID from your AdSense account.
6. Enable a slot and choose a placement, or use [kadirslot_ad id="1"] (legacy [revguard_ad id="1"] remains supported).

== Frequently Asked Questions ==
= Can I paste my own ad code? =
No. For security, version 1.1 accepts only an AdSense publisher ID and numeric ad unit IDs and builds the ad code itself. Other ad networks and custom HTML are not supported.

= What if my theme already loads the AdSense script? =
Turn off "Load the Google AdSense script" in Settings > KadirSlot Ad Placement. KadirSlot Ad Placement also skips its own copy when another plugin has enqueued the same script through WordPress.

= Does KadirSlot protect my Google AdSense account or guarantee earnings? =
No. This plugin only places ads and makes no guarantee about invalid traffic or advertising revenue.

= What happens to settings from RevGuard Free version 1.0? =
On the first load after upgrading, AdSense publisher and ad unit IDs found in each slot's old code are migrated, and the slot keeps its enabled state, placement and paragraph. Slots whose old code contained no AdSense IDs, or a second publisher ID, are disabled. The old code is never displayed again, but it is shown on the settings page so you can copy it until you choose to delete the upgrade report.

= What happens on uninstall? =
Saved settings remain unless manually deleted. Deactivation also preserves configuration.

= Can it run alongside RevGuard Pro? =
Yes. The existing RevGuard Pro integration check remains unchanged; while that companion plugin is active it handles ad rendering and KadirSlot Ad Placement outputs nothing. Saved Free settings are kept for when Pro is deactivated.

= Can I put ads after a specific paragraph? =
Yes. Enable a slot, choose After paragraph, and enter the paragraph number. This automatic insertion works on single WordPress posts and pages.

= Does it work with the WordPress block editor? =
Yes. Use a WordPress Shortcode block for manual placement, or select an automatic placement in the KadirSlot settings. Ad rendering depends on a valid, enabled Google AdSense unit.

= What is the difference between [kadirslot_ad] and [revguard_ad]? =
Both insert an enabled KadirSlot slot by ID. [kadirslot_ad] is the new branded shortcode; [revguard_ad] remains supported so older posts continue to work.

== Changelog ==
= 1.1.3 =
* Added [kadirslot_ad id="1"] as the recommended shortcode; [revguard_ad] remains compatible.
* Expanded installation guidance, features and FAQs for WordPress and Google AdSense users.
* Refined long-tail search tags and listing text without changing ad settings or rendering rules.

= 1.1.2 =
* Fixed a fatal error ("Cannot redeclare") when KadirSlot Ad Placement and the older RevGuard Free plugin were active at the same time. The plugin code now uses its own namespace.
* While RevGuard Free is still active, KadirSlot Ad Placement stays paused so ads are never shown twice.
* Corrupted or unexpected saved values, such as a non-numeric paragraph number, now fall back to safe defaults without PHP warnings.

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
= 1.1.3 =
Adds the branded [kadirslot_ad] shortcode while keeping [revguard_ad] and existing settings working. No reconfiguration is required.

= 1.1.2 =
Fixes a fatal error when the older RevGuard Free plugin is also active, and hardens handling of corrupted settings. Settings and the shortcode are unchanged.

= 1.1.1 =
New name, same stored configuration and shortcode. Custom ad code was already replaced in version 1.1.0.

= 1.1.0 =
Custom ad code is replaced by AdSense publisher and ad unit IDs. Slots without AdSense IDs are disabled; review Settings > KadirSlot Ad Placement after upgrading.
