<?php
/**
 * Container exception.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Support;

use RuntimeException;

/**
 * Thrown when the service container is asked for an unregistered service.
 */
final class ContainerException extends RuntimeException {

	/**
	 * Create an exception for an unregistered service id.
	 *
	 * @param string $id The service identifier that could not be resolved.
	 */
	public static function notFound( string $id ): self {
		return new self(
			sprintf( 'RevGuard for AdSense: service "%s" is not registered in the container.', $id )
		);
	}
}
