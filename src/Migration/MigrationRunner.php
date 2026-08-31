<?php
/**
 * Migration runner.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Migration;

use QuickAdsense\Contracts\MigrationStepInterface;
use QuickAdsense\Contracts\OptionStoreInterface;
use Throwable;

/**
 * Version-gated, one-shot migration orchestrator.
 *
 * Before any step runs, a backup of the affected legacy option keys is taken so
 * the migration is fully reversible via rollback(). The first step whose
 * appliesTo() returns true is executed (mirroring the legacy if/elseif chain).
 * Running is idempotent: once the stored version reaches the target, run() is a
 * no-op.
 */
final class MigrationRunner {

	/**
	 * Option key holding the reversible pre-migration backup.
	 */
	public const BACKUP_OPTION = 'quick_adsense_migration_backup';

	/**
	 * Legacy option keys snapshotted before migration, enabling rollback.
	 *
	 * @var array<int, string>
	 */
	private const BACKED_UP_KEYS = array(
		'quick_adsense_settings',
		'quick_adsense_2_options',
		'quick_adsense_2_options_bak',
		'AdsDisp',
	);

	private ?Throwable $last_error = null;

	/**
	 * @param OptionStoreInterface              $options       Option store.
	 * @param array<int, MigrationStepInterface> $steps         Ordered steps.
	 * @param int                               $target_version Version to reach.
	 */
	public function __construct(
		private readonly OptionStoreInterface $options,
		private readonly array $steps,
		private readonly int $target_version = SchemaVersion::CURRENT,
	) {}

	/**
	 * The installed schema version (0 when never migrated).
	 */
	public function currentVersion(): int {
		return (int) $this->options->get( SchemaVersion::OPTION, 0 );
	}

	/**
	 * Whether a migration is pending.
	 */
	public function needsMigration(): bool {
		return $this->currentVersion() < $this->target_version;
	}

	/**
	 * Run the pending migration once. Safe to call repeatedly.
	 */
	public function run(): bool {
		if ( ! $this->needsMigration() ) {
			return true;
		}

		$this->last_error = null;

		try {
			$this->backup();

			foreach ( $this->steps as $step ) {
				if ( $step->appliesTo( $this->options ) ) {
					$step->run( $this->options );
					break;
				}
			}

			$this->options->update( SchemaVersion::OPTION, $this->target_version );
		} catch ( Throwable $error ) {
			$this->last_error = $error;
			$this->rollback();

			return false;
		}

		return true;
	}

	/**
	 * The last migration failure, when run() returned false.
	 */
	public function lastError(): ?Throwable {
		return $this->last_error;
	}

	/**
	 * Revert to the pre-migration state captured by the last backup.
	 */
	public function rollback(): void {
		$backup = $this->options->get( self::BACKUP_OPTION );

		if ( is_array( $backup ) ) {
			foreach ( $backup as $key => $entry ) {
				if ( ! is_array( $entry ) ) {
					continue;
				}

				if ( true === ( $entry['exists'] ?? false ) ) {
					$this->options->update( (string) $key, $entry['value'] ?? null );
				} else {
					$this->options->delete( (string) $key );
				}
			}
		}

		$this->options->delete( self::BACKUP_OPTION );
		$this->options->delete( SchemaVersion::OPTION );
	}

	/**
	 * Snapshot the affected keys once, before any step mutates them.
	 */
	private function backup(): void {
		if ( $this->options->exists( self::BACKUP_OPTION ) ) {
			return;
		}

		$snapshot = array();

		foreach ( self::BACKED_UP_KEYS as $key ) {
			$exists           = $this->options->exists( $key );
			$snapshot[ $key ] = array(
				'exists' => $exists,
				'value'  => $exists ? $this->options->get( $key ) : null,
			);
		}

		$this->options->update( self::BACKUP_OPTION, $snapshot );
	}
}
