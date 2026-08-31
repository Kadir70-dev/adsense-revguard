<?php
/**
 * Legacy value helper.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Migration;

/**
 * Faithful port of the legacy quick_adsense_get_value(): returns the value only
 * when it is set and non-empty, otherwise the default. Used so migration output
 * matches the legacy defaults.php mapping exactly.
 */
final class LegacyValue {

	/**
	 * @param array<array-key, mixed> $data    Source data.
	 * @param string                  $key     Key to read.
	 * @param mixed                   $default Fallback when unset or empty.
	 */
	// phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.defaultFound -- Existing named-argument compatibility.
	public static function get( array $data, string $key, mixed $default = '' ): mixed {
		return ( isset( $data[ $key ] ) && '' !== $data[ $key ] ) ? $data[ $key ] : $default;
	}
}
