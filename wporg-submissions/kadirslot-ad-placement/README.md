# KadirSlot Ad Placement — WordPress.org submission v1.1.1

This folder tracks the WordPress.org resubmission of the former **RevGuard Free** plugin (slug `revguard-free`). It is a **separate** codebase from the repository's `RevGuard for AdSense v3.0.0` default branch.

- Proposed name: **KadirSlot Ad Placement**
- Requested new slug and text domain: `kadirslot-ad-placement`
- Tested package: `kadirslot-ad-placement-1.1.1.zip`
- Package SHA-256: `71a9ffd00be153d6149d722f4aa096f61ab1aa1122234ae1ecca09c9d236e18d`
- Local checks: PHP syntax passes, 60 PHP test assertions passed (PHP 8.4).
- Unverified: official Plugin Check, full WordPress installation and WordPress.org review approval.

**Compatibility:** Existing `revguard_free_settings` option keys, `revguard_ad` shortcode and RevGuard Pro detection intentionally remain unchanged. Changing these could break upgrades.

The new plugin slug must be explicitly requested in the **original** WordPress.org review email thread before approval.

Do **not** merge this directory over the existing root-level `RevGuard for AdSense v3.0.0` plugin or publish both plugin files in a single WordPress installable package.