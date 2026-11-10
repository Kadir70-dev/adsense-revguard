# KadirSlot Pro — launch decision memo

**Recommendation (10 Nov 2026):** Build a small private beta in Dec 2026; target a public Pro 1.0 launch in **Jan–Feb 2027** only if functionality, interoperability, license delivery, security and real customer demand are validated. Don't delay until 10,000 free active installs, and don't rush to sell an untested plugin.

## Pro value proposition

Free remains useful forever: 3 AdSense display slots, basic automatic placements, shortcodes, existing compatibility and bug fixes. No artificial trial expiry or removal of free functions.

Pro is a separately distributed paid add-on (outside WordPress.org plugin SVN) offering **additional genuinely new functionality**:
1. Additional reusable ad slots beyond 3.
2. Conditional display controls: post type, category, page include/exclude, mobile/desktop *layout* selection (not identity or consent tracking).
3. Time scheduling and rule priority with deterministic display order.
4. Placement templates and import/export (non-sensitive config; mask or omit IDs when sharing).
5. Safe ad preview/diagnostics, duplicate script checks and testing modes without promising invalid traffic prevention.

Later, after validation: A/B tests for placement layouts, performance insights where permitted and legally compliant, and reusable ad groups. Be especially careful with Google AdSense consent, invalid-traffic rules and non-deceptive placement.

## Paid plan suggestion (USD, annual)

- 1 site: $39/year.
- 5 sites: $79/year.
- Unlimited sites: $149/year.

Prices are hypotheses; test willingness-to-pay with beta customers and do not advertise as final until the product and payment flow exist.

## Go/no-go checklist

- [ ] 10–20 real beta testers; at least 5 actively configure a Pro-only feature.
- [ ] Fully working install, update and rollback on real WordPress installations.
- [ ] Security audit of every admin form and rule engine (caps, nonces, sanitization/escaping).
- [ ] PHP/WP compatibility matrix; no duplicate ads and no lost settings on upgrades.
- [ ] GPL-compatible distribution and external license server/payment integration, with privacy and support policies.
- [ ] Clear separation between free and Pro; honor WordPress.org guidelines against trialware and admin hijacking.
- [ ] Refund terms, installation documentation and support response process.
- [ ] Pilot customers confirm at least one feature is worth paying for.

## Business milestones

- Under 50 active free installs: focus product stability, documentation and customer discovery. Pro is beta-only.
- 50–300 installs with engaged testers: launch may be viable if retention and demand are real.
- 300+ installs: use actual conversion and support data to improve messaging, pricing and the roadmap.

**No revenue promise:** 10,000 free installs are not 10,000 paying customers. At 1% adoption, 10,000 active installs would correspond to ~100 paying sites, but conversion is not known and multi-site plans complicate revenue.
