<?php
/**
 * Settings repository.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Settings;

use QuickAdsense\Contracts\OptionStoreInterface;
use QuickAdsense\Contracts\SettingsRepositoryInterface;

/**
 * Reads and writes the plugin settings under the legacy option key.
 *
 * Reads are memoised for the request and never write back, so merely loading
 * settings cannot alter what is stored (and therefore cannot change legacy
 * behaviour). Persisting only happens via save(), which validates first.
 */
final class SettingsRepository implements SettingsRepositoryInterface {

	/**
	 * The legacy option key — preserved for backward compatibility.
	 */
	public const OPTION = 'quick_adsense_settings';

	private ?Settings $cache = null;

	/**
	 * @var array<string, mixed>|null
	 */
	private ?array $raw_cache = null;

	public function __construct(
		private readonly OptionStoreInterface $options,
		private readonly SettingsSanitizer $sanitizer,
	) {}

	public function get(): Settings {
		return $this->cache ??= Settings::withDefaults( $this->raw() );
	}

	public function raw(): array {
		if ( null !== $this->raw_cache ) {
			return $this->raw_cache;
		}

		$stored = $this->options->get( self::OPTION, array() );
		$raw    = array();

		if ( is_array( $stored ) ) {
			foreach ( $stored as $key => $value ) {
				$raw[ (string) $key ] = $value;
			}
		}

		$this->raw_cache = $raw;

		return $this->raw_cache;
	}

	public function save( array $input ): Settings {
		$clean  = $this->sanitizer->sanitize( $input );
		$stored = array_replace( $this->raw(), $clean );
		$this->options->update( self::OPTION, $stored );

		$this->raw_cache = $stored;
		$this->cache     = Settings::withDefaults( $stored );

		return $this->cache;
	}
}
