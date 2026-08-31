<?php
/**
 * Option store contract.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Contracts;

/**
 * Abstraction over persistent key/value option storage (WordPress Options API in
 * production, an in-memory fake in tests).
 */
interface OptionStoreInterface {

	/**
	 * Read an option, returning $default when it does not exist.
	 */
	// phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.defaultFound -- Public named-argument compatibility.
	public function get( string $key, mixed $default = null ): mixed;

	/**
	 * Create or update an option. Returns true on success.
	 */
	public function update( string $key, mixed $value ): bool;

	/**
	 * Delete an option. Returns true on success.
	 */
	public function delete( string $key ): bool;

	/**
	 * Whether an option exists (distinct from holding a falsey value).
	 */
	public function exists( string $key ): bool;
}
