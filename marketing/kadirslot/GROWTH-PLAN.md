# KadirSlot — 10,000 genuine active installations growth plan

**Baseline:** Fewer than 10 active installations and no public reviews at the time of this audit (10 Nov 2026). Version 1.1.3 has been published to WordPress.org SVN. **10,000 is a goal, not a forecast or guarantee.** Distinguish *downloads* from WordPress.org's *active installations*.

## Positioning

**One-liner:** Responsive Google AdSense ads for WordPress, with three straightforward slots and optional automatic placements.

**Ideal audience:** Small WordPress publishers with approved AdSense accounts who want the simplest safe ad setup, not a comprehensive ad-server suite.

**Differentiation:** Minimal settings, known/validated publisher and ad-slot IDs, responsive code, manual shortcode or beginning/middle/end/after-paragraph placements, preserved old shortcodes. Do not claim revenue improvements, invalid-click protection, arbitrary HTML support, ad rotation, or integrations not built.

## Priority 0 — Quality and discoverability (first two weeks)

- [x] Ship shortcode alias and improved installation/FAQ wording (1.1.3).
- [x] Prepare original plugin banner and icon source under creative/. Use WordPress.org SVN root assets, not plugin ZIP.
- [ ] Publish final banner/icon assets only after rendering and visual QA.
- [ ] Capture **real, sanitized** screenshots of Settings page, three slots, placement menu and shortcode block. Don't submit reconstructed UI screenshots. Update readme Screenshots section with the matching captions; release only after tests.
- [ ] Real WordPress install check with PHP 8.2+, block editor, common caching theme and conflict check. Check script loaded once and no AdSense output on disabled slots.
- [ ] Verify plugin header, support URLs, docs and accessibility; do not broaden PHP compatibility merely by editing the requirement — test first.

## Acquisition channels — ethically, without spam

- Publish one helpful how-to guide and two short demos: install, first responsive unit, and choosing automatic vs manual placement. Videos should show actual plugin UI.
- Create a simple documentation/landing page on owned domain, canonical install CTA to WordPress.org, with setup steps, FAQ, changelog and support link.
- Answer relevant WordPress.org support questions and participate in appropriate WordPress communities with useful answers. Mention the plugin only where it is relevant and within community rules.
- Publish at most one genuine launch announcement per opted-in audience/community. No unsolicited DMs, mass commenting, paid review campaigns, fake installs or misleading comparisons.
- Invite feedback from real users after successful setup, without pressuring or incentivizing five-star reviews. Respect WordPress.org policies.
- Document and resolve support issues promptly; each solved friction becomes a guide or tested improvement.

## Measurement

Track weekly: live version, WordPress.org reported active installs, downloads (if available), review count and rating, unresolved support topics, tutorial visits and legitimate install clicks. WordPress.org active install counts are rounded/bucketed and can lag; do not interpolate false precision.

## Milestones (planning targets, not promises)

| Stage | Meaningful target | Key gate |
| --- | --- | --- |
| Month 1 | First 25 active installs | First-use setup works without support |
| Month 2–3 | 100–300 active installs | Useful documentation, initial organic referrals |
| Month 4–6 | 500–1,500 active installs | Real feedback/reviews and consistently low support burden |
| Month 12+ | 2,000+ active installs | Repeatable organic discovery, improved conversion |
| Long-term | 10,000 genuine active installs | Distribution at scale, retention and product-market fit |

The market is crowded. These stages are stretch goals; growth may take much longer or stop below target. Reassess every 30 days based on evidence.

## Content backlog

1. **How to add AdSense ads to WordPress without editing theme files** — tutorial, screenshots, video.
2. **WordPress AdSense shortcode placement guide** — how to use [kadirslot_ad id="1"] in the block editor.
3. **Google AdSense script loaded twice?** — troubleshooting the setting; avoid claims that it catches every possible script inclusion.
4. **Before/after/paragraph placement guide** — a precise example with limitations.
5. **Migrating from RevGuard Free** — explain preserved settings and legacy shortcode.

## Release discipline

- WordPress.org SVN is the actual deployment target. A WordPress.org profile → GitHub connection does not publish plugin code.
- Current release workflow is manual and credential-gated. Never put SVN passwords, tokens, or publisher IDs into source control or public logs.
- Credentials exposed in chats must be rotated. Automate only with safe GitHub Actions repository secrets.
- Do not change working ad output, legacy compatibility or licensing solely to improve marketing metrics.
