# Changelog

## 3.0.0 — RC1

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
- Added PHPUnit, PHPStan, PHPCS, golden-master, and real-WordPress integration coverage.

No Pro features, Gutenberg blocks, widgets, ads.txt UI, or release-channel integrations are included in RC1.
