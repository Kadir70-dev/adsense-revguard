<?php
/**
 * Immutable settings value object.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Settings;

/**
 * A read-only, typed view over the plugin configuration.
 *
 * Instances are immutable. Use withDefaults() to obtain a Settings where absent
 * keys fall back to Defaults; the overlay is in-memory only and never changes
 * what is persisted (so it cannot alter legacy behaviour).
 */
final class Settings {

	/**
	 * @param array<string, mixed> $data The settings data.
	 */
	public function __construct(
		private readonly array $data,
	) {}

	/**
	 * Build a Settings with defaults overlaid beneath the given stored values.
	 *
	 * @param array<string, mixed> $stored Raw stored settings.
	 */
	public static function withDefaults( array $stored ): self {
		return new self( array_replace( Defaults::all(), $stored ) );
	}

	/**
	 * Whether a key is present.
	 */
	public function has( string $key ): bool {
		return array_key_exists( $key, $this->data );
	}

	/**
	 * Raw value for a key, or $default when absent.
	 */
	// phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.defaultFound -- Public named-argument compatibility.
	public function get( string $key, mixed $default = null ): mixed {
		return $this->data[ $key ] ?? $default;
	}

	/**
	 * Boolean view of a key. Legacy stores '1'/'0' strings; PHP treats '0' and
	 * '' as false, which is exactly the intended interpretation.
	 */
	public function bool( string $key ): bool {
		return (bool) ( $this->data[ $key ] ?? false );
	}

	/**
	 * Integer view of a key.
	 */
	public function int( string $key ): int {
		return (int) ( $this->data[ $key ] ?? 0 );
	}

	/**
	 * String view of a key.
	 */
	public function string( string $key ): string {
		$value = $this->data[ $key ] ?? '';

		return is_scalar( $value ) ? (string) $value : '';
	}

	/**
	 * The full data as an array.
	 *
	 * @return array<string, mixed>
	 */
	public function toArray(): array {
		return $this->data;
	}
}
