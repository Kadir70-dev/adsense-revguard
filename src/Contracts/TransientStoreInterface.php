<?php
/**
 * Transient store contract.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Contracts;

/**
 * Abstraction over short-lived cached storage (WordPress Transients API).
 */
interface TransientStoreInterface {

	/**
	 * Read a transient, returning $default when absent or expired.
	 */
	// phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.defaultFound -- Public named-argument compatibility.
	public function get( string $key, mixed $default = null ): mixed;

	/**
	 * Store a transient with an optional time-to-live in seconds.
	 */
	public function set( string $key, mixed $value, int $ttl = 0 ): bool;

	/**
	 * Delete a transient.
	 */
	public function delete( string $key ): bool;
}
