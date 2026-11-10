# How to add Google AdSense to WordPress with KadirSlot

**SEO title:** How to Add Google AdSense Ads to WordPress Without Editing Your Theme

**Meta description:** A practical guide to insert responsive Google AdSense ads in WordPress posts and pages using KadirSlot's three slots, automatic placement and shortcodes.

## Before you start

You need an active WordPress site, an approved Google AdSense account, a publisher ID in the `ca-pub-XXXXXXXXXXXXXXXX` format, and the display ad-unit ID(s) from AdSense. Follow AdSense program policies and obtain any legally required visitor consent.

## 1. Install KadirSlot

In your WordPress dashboard open **Plugins → Add New Plugin**. Search for **KadirSlot Ad Placement** and click Install and Activate. The official plugin listing is:

https://wordpress.org/plugins/kadirslot-ad-placement/

## 2. Configure one ad slot

Open **Settings → KadirSlot Ad Placement**. Add your AdSense publisher ID and numeric ad-unit ID. Enable slot 1. Leave other slots disabled until needed.

If the page already loads Google's AdSense JavaScript through a theme or another plugin, turn off **Load the Google AdSense script** to prevent duplicate scripts. The plugin skips its script when it recognizes another WordPress-enqueued copy, but it cannot guarantee detection of every manually embedded script.

## 3. Choose automatic placement

Select one of: Beginning, Middle, End, or After paragraph. After paragraph needs a positive paragraph number; the plugin places the ad on single posts/pages where the content filter runs.

## 4. Or use a shortcode

Choose Manual for the slot. Insert a **Shortcode** block in the WordPress editor and paste:

`[kadirslot_ad id="1"]`

Slot 2: `[kadirslot_ad id="2"]`. Slot 3: `[kadirslot_ad id="3"]`.

Older `[revguard_ad id="1"]` content remains compatible; don't replace working posts urgently.

## 5. Check responsibly

Preview a published post/page with a valid enabled slot. Verify the responsive ad container is in the desired position. Ad fill and earnings are controlled by Google AdSense and may take time; a correctly rendered container does not guarantee an actual paid ad.

## Troubleshooting

- **No unit appears:** Check that the slot is enabled, the publisher ID and numeric ad-unit ID are valid, and the placement or shortcode is present.
- **Site loads AdSense script itself:** Disable KadirSlot's script option and confirm the site supplies Google's script.
- **Old RevGuard Free installed:** Deactivate that plugin before using KadirSlot to avoid overlapping rendering.
- **Do ads appear on every page?** Automatic placement is designed for individual posts/pages, not feeds or arbitrary archive templates.
- **AdSense account needs approval:** KadirSlot does not create or approve accounts and cannot guarantee ad earnings.

**Support:** https://wordpress.org/support/plugin/kadirslot-ad-placement/

**Maintainer note:** Add real screenshots after checking the current admin UI. Do not present mockups as actual UI captures.
