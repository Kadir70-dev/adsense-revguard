<?php
/**
 * Schema version.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Migration;

/**
 * The data-schema version and the option key that records it.
 *
 * Bump CURRENT whenever a new one-shot migration step must run. The version is a
 * brand-new option that the legacy plugin ignores entirely.
 */
final class SchemaVersion {

	/**
	 * The current schema version this build expects.
	 */
	public const CURRENT = 1;

	/**
	 * Option key that stores the installed schema version.
	 */
	public const OPTION = 'quick_adsense_schema_version';
}
