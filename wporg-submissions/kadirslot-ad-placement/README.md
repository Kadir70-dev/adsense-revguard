# KadirSlot Ad Placement — WordPress.org submission

This folder tracks the WordPress.org resubmission of the former **RevGuard Free** plugin (slug `revguard-free`). It is a **separate** codebase from the repository's `RevGuard for AdSense v3.0.0` default branch.

- Proposed name: **KadirSlot Ad Placement**
- Requested new slug and text domain: `kadirslot-ad-placement` (not yet approved or reserved)
- WordPress.org contributor: `abdulkadirco`
- Current package: `kadirslot-ad-placement-1.1.2.zip`
- Package SHA-256: `608ec18991373168e81d932207165feecf54cb94f7b0927edb083b47c6090143` (6 files; rebuilt identically by `tools/build-zip.sh`)
- Previous package: `kadirslot-ad-placement-1.1.1.zip` (SHA-256 `71a9ffd0…6e18d`), superseded.

## Layout

| Path | Purpose |
| --- | --- |
| `source/` | Exact plugin source shipped in the ZIP |
| `tools/build-zip.sh` | Builds the ZIP from an explicit file allowlist, checks header/constant/readme versions match |
| `tests/run-unit.sh` | Unit tests (WordPress stubbed, every PHP warning fails) plus same-process coexistence tests |
| `tests/integration/` | Offline Docker WordPress stack: WP-CLI, real HTTP, Plugin Check |
| `tests/fixtures/` | Older builds used by the coexistence and upgrade tests (RevGuard Free 1.0.1, 1.1.0; KadirSlot 1.1.1) |
| `tests/results/1.1.2/` | Output of the runs that validated the 1.1.2 ZIP |

## Running the tests

```sh
tools/build-zip.sh
tests/run-unit.sh                                    # host PHP
PHP="docker run --rm -v $PWD:$PWD php:8.2-cli php" tests/run-unit.sh
# extra/ holds plugin-check.zip (2.1.0) and optional revguard-pro-*.zip packages; they are not committed.
tests/integration/run.sh kadirslot-ad-placement-1.1.2.zip /path/to/wp-cli.phar extra/ artifacts/ [wp-6.0-core-dir]
```

## 1.1.2 results

- Unit: 227 assertions passed on PHP 8.2.35 and 8.3.6 (source and extracted ZIP).
- Integration, WordPress 7.1.3 / PHP 8.3: 148 passed, 0 failed, including RevGuard Pro 1.0.1 and 1.1.0-rc2 in both activation orders.
- Integration, WordPress 6.0 / PHP 8.3: 112 passed, 0 failed (Pro needs 6.5+ and Plugin Check needs 6.3+, so both are skipped there).
- Plugin Check 2.1.0 on the installed ZIP: no errors or warnings in all 34 checks, including experimental and the 5 runtime checks.

**Compatibility:** Existing `revguard_free_settings` option keys, the `revguard_ad` shortcode and RevGuard Pro detection intentionally remain unchanged. Changing these could break upgrades. Since 1.1.2 the code uses the `KadirSlotAdPlacement` namespace, so it can be active next to RevGuard Free without a fatal error; while an older build is active, KadirSlot stays passive.

The new plugin slug must be explicitly requested in the **original** WordPress.org review email thread before approval.

Do **not** merge this directory over the existing root-level `RevGuard for AdSense v3.0.0` plugin or publish both plugin files in a single WordPress installable package.
