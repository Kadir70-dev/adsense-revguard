<?php
/**
 * Settings sanitiser.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Settings;

/**
 * Validates and coerces raw settings input into a clean, typed array before it
 * is persisted. Unlike the legacy passthrough sanitiser, this enforces types.
 *
 * Ad-code fields are preserved verbatim (raw HTML/JS is required for ad and
 * tracker snippets); output-time filtering is the responsibility of the render
 * layer via the scoped KsesAdPolicy.
 */
final class SettingsSanitizer {

	/**
	 * Sanitise a raw settings input array.
	 *
	 * @param array<string, mixed> $input Raw input.
	 *
	 * @return array<string, mixed> Cleaned settings.
	 */
	public function sanitize( array $input ): array {
		$clean = array();

		foreach ( $input as $key => $value ) {
			$clean[ $key ] = match ( Schema::typeOf( $key ) ) {
				FieldType::Checkbox    => $this->checkbox( $value ),
				FieldType::IntValue    => $this->boundedInt( $key, $value ),
				FieldType::AdCode      => $this->adCode( $value ),
				FieldType::Multiselect => $this->multiselect( $value ),
				FieldType::Text        => $this->text( $value ),
			};
		}

		return $clean;
	}

	private function checkbox( mixed $value ): string {
		$enabled = true === $value
			|| 1 === $value
			|| in_array( strtolower( (string) $value ), array( '1', 'on', 'yes', 'true' ), true );

		return $enabled ? '1' : '0';
	}

	private function boundedInt( string $key, mixed $value ): string {
		$maximum = match ( true ) {
			'max_ads_per_page' === $key,
			str_starts_with( $key, 'ad_' ) => 10,
			str_ends_with( $key, '_alignment' ) => 4,
			str_ends_with( $key, '_margin' ) => 1000,
			str_starts_with( $key, 'position_after_' ) => 100000,
			default => PHP_INT_MAX,
		};

		return (string) min( $maximum, max( 0, (int) $value ) );
	}

	private function adCode( mixed $value ): string {
		return is_string( $value ) ? $value : '';
	}

	private function text( mixed $value ): string {
		// Domain code cannot call wp_strip_all_tags(); WordPress remains behind adapters.
		// phpcs:ignore WordPress.WP.AlternativeFunctions.strip_tags_strip_tags
		return is_scalar( $value ) ? trim( strip_tags( (string) $value ) ) : '';
	}

	/**
	 * @return array<int, string>
	 */
	private function multiselect( mixed $value ): array {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$strings = array_map(
			static fn ( mixed $item ): string => is_scalar( $item ) ? strtoupper( trim( (string) $item ) ) : '',
			$value
		);
		$strings = array_filter(
			$strings,
			static fn ( string $item ): bool => 1 === preg_match( '/^[A-Z]{2}$/', $item )
		);

		return array_values( array_unique( $strings ) );
	}
}
