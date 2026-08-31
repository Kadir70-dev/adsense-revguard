<?php
/**
 * WordPress option store.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Infrastructure\WordPress;

use QuickAdsense\Contracts\OptionStoreInterface;
use stdClass;

/**
 * OptionStoreInterface backed by the WordPress Options API.
 */
final class WpOptionStore implements OptionStoreInterface {

	// phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.defaultFound -- Contract signature compatibility.
	public function get( string $key, mixed $default = null ): mixed {
		$sentinel = self::sentinel();
		$value    = get_option( $key, $sentinel );

		return $value === $sentinel ? $default : $value;
	}

	public function update( string $key, mixed $value ): bool {
		return (bool) update_option( $key, $value );
	}

	public function delete( string $key ): bool {
		return (bool) delete_option( $key );
	}

	public function exists( string $key ): bool {
		$sentinel = self::sentinel();

		return get_option( $key, $sentinel ) !== $sentinel;
	}

	/**
	 * A per-process unique marker used to distinguish "absent" from a stored
	 * falsey value (get_option() returns false for both otherwise).
	 */
	private static function sentinel(): stdClass {
		static $sentinel = null;

		return $sentinel ??= new stdClass();
	}
}
