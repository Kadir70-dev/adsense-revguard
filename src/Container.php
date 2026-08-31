<?php
/**
 * Minimal service container.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense;

use Closure;
use QuickAdsense\Support\ContainerException;

/**
 * A small, dependency-free, PSR-11-style service container.
 *
 * Deliberately minimal (no autowiring, no framework) per ARCHITECTURE_V2.md §2.
 * Providers bind factories during registration; services are resolved lazily.
 */
final class Container {

	/**
	 * Registered service factories, keyed by service id.
	 *
	 * @var array<string, Closure>
	 */
	private array $factories = array();

	/**
	 * Whether each service id should be treated as a shared singleton.
	 *
	 * @var array<string, bool>
	 */
	private array $shared = array();

	/**
	 * Resolved shared instances (and pre-registered instances), keyed by id.
	 *
	 * @var array<string, mixed>
	 */
	private array $resolved = array();

	/**
	 * Bind a factory that produces a new instance on every resolution.
	 *
	 * @param string  $id      Service identifier (typically an interface name).
	 * @param Closure $factory Receives this container and returns the service.
	 */
	public function bind( string $id, Closure $factory ): void {
		$this->factories[ $id ] = $factory;
		$this->shared[ $id ]    = false;
		unset( $this->resolved[ $id ] );
	}

	/**
	 * Bind a factory whose result is created once and shared thereafter.
	 *
	 * @param string  $id      Service identifier.
	 * @param Closure $factory Receives this container and returns the service.
	 */
	public function singleton( string $id, Closure $factory ): void {
		$this->factories[ $id ] = $factory;
		$this->shared[ $id ]    = true;
		unset( $this->resolved[ $id ] );
	}

	/**
	 * Register an already-created instance as a shared service.
	 *
	 * @param string $id       Service identifier.
	 * @param mixed  $instance The instance to share.
	 */
	public function instance( string $id, mixed $instance ): void {
		$this->resolved[ $id ] = $instance;
		$this->shared[ $id ]   = true;
	}

	/**
	 * Whether a service id is registered (as a factory or an instance).
	 */
	public function has( string $id ): bool {
		return isset( $this->factories[ $id ] ) || array_key_exists( $id, $this->resolved );
	}

	/**
	 * Resolve a service by id.
	 *
	 * When called with a class/interface name the return type is inferred as that
	 * type; with any other string it is mixed.
	 *
	 * @template TService of object
	 *
	 * @param class-string<TService>|string $id Service identifier.
	 *
	 * @return ($id is class-string<TService> ? TService : mixed)
	 *
	 * @throws ContainerException When the id is not registered.
	 */
	public function get( string $id ): mixed {
		if ( array_key_exists( $id, $this->resolved ) ) {
			return $this->resolved[ $id ];
		}

		if ( ! isset( $this->factories[ $id ] ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Passed to an exception factory, not output.
			throw ContainerException::notFound( $id );
		}

		$service = ( $this->factories[ $id ] )( $this );

		if ( $this->shared[ $id ] ?? false ) {
			$this->resolved[ $id ] = $service;
		}

		return $service;
	}
}
