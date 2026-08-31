# Changelog

## 3.0.0

- Rebuilt the plugin for PHP 8.2+ using strict types and Composer PSR-4 autoloading.
- Added graceful PHP, WordPress, and dependency guards.
- Added idempotent activation and versioned legacy-settings migrations with rollback backups.
- Added explicit deactivation and uninstall lifecycle handling.
- Ported the legacy content engine while preserving placement and control-token behavior.
- Removed runtime global state from the modern content engine.
- Added the modern WordPress administration settings interface.
- Added nonce, capability, sanitization, validation, and escaping controls.
- Preserved unknown legacy settings during partial saves.
- Added scoped advertising HTML filtering at all implemented output boundaries.
- Added header and footer embed-code output.
- Uninstall preserves option keys that may belong to a separately installed
  original "Quick Adsense" plugin (ownership-aware cleanup).

This release does not include Pro features, Gutenberg blocks, sidebar widgets,
an ads.txt UI, or release-channel integrations.

## History

Version 3.0.0 is the first release of the rebuilt codebase. It continues the
GPL-licensed "Quick Adsense" plugin (original author: namithjawahar; last
original release 2.8.7, 2022). See LICENSE.md for attribution.
