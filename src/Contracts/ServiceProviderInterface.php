<?php
/**
 * Service provider contract.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Contracts;

use QuickAdsense\Container;

/**
 * A bounded-context wiring unit. register() binds services into the container;
 * boot() registers WordPress hooks. All providers are registered before any is
 * booted.
 */
interface ServiceProviderInterface {

	/**
	 * Bind services into the container. Must not touch WordPress.
	 */
	public function register( Container $container ): void;

	/**
	 * Register hooks / run boot-time wiring.
	 */
	public function boot( Container $container ): void;
}
