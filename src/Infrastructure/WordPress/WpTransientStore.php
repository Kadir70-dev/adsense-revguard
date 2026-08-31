<?php
/**
 * WordPress transient store.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Infrastructure\WordPress;

use QuickAdsense\Contracts\TransientStoreInterface;

/**
 * TransientStoreInterface backed by the WordPress Transients API.
 */
final class WpTransientStore implements TransientStoreInterface {

	// phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.defaultFound -- Contract signature compatibility.
	public function get( string $key, mixed $default = null ): mixed {
		$value = get_transient( $key );

		return false === $value ? $default : $value;
	}

	public function set( string $key, mixed $value, int $ttl = 0 ): bool {
		return (bool) set_transient( $key, $value, $ttl );
	}

	public function delete( string $key ): bool {
		return (bool) delete_transient( $key );
	}
}
