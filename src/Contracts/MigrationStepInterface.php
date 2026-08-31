<?php
/**
 * Migration step contract.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Contracts;

/**
 * A single, idempotent migration step. The runner executes the first step whose
 * appliesTo() returns true (mirroring the legacy if/elseif detection chain).
 */
interface MigrationStepInterface {

	/**
	 * Whether this step should handle the current option state.
	 */
	public function appliesTo( OptionStoreInterface $options ): bool;

	/**
	 * Perform the migration against the option store.
	 */
	public function run( OptionStoreInterface $options ): void;

	/**
	 * Short human-readable description (for logging / reporting).
	 */
	public function describe(): string;
}
