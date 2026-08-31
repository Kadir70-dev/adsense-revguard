<?php
/**
 * Composition root for RevGuard for AdSense.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense;

use QuickAdsense\Contracts\ServiceProviderInterface;
use QuickAdsense\Infrastructure\Lifecycle\Activator;
use QuickAdsense\Infrastructure\Lifecycle\Deactivator;
use QuickAdsense\Provider\CoreProvider;
use QuickAdsense\Provider\AdminProvider;
use QuickAdsense\Provider\FrontendProvider;

/**
 * The plugin's composition root and singleton entry point.
 *
 * Task-001 scaffold: constructs the service container and exposes the boot
 * lifecycle. Service providers, migrations, settings, and the ad-insertion
 * engine are registered here in subsequent tasks per ARCHITECTURE_V2.md
 * (§6.1 bootstrap sequence). No runtime behaviour is registered yet, so
 * activating this scaffold changes nothing observable on the site.
 */
final class Plugin {

	/**
	 * The single shared instance.
	 */
	private static ?self $instance = null;

	/**
	 * Whether boot() has already run (idempotency guard).
	 */
	private bool $booted = false;

	/**
	 * Whether service factories have been registered.
	 */
	private bool $services_registered = false;

	/**
	 * Whether WordPress lifecycle hooks have been registered.
	 */
	private bool $lifecycle_hooks_registered = false;

	/**
	 * Provider instances shared by activation and normal boot.
	 *
	 * @var array<int, ServiceProviderInterface>|null
	 */
	private ?array $provider_instances = null;

	/**
	 * The service container.
	 */
	public readonly Container $container;

	/**
	 * @param string $version Plugin version.
	 * @param string $file    Absolute path to the main plugin file.
	 * @param string $path    Absolute path to the plugin directory (trailing slash).
	 * @param string $url     URL to the plugin directory (trailing slash).
	 */
	private function __construct(
		public readonly string $version,
		public readonly string $file,
		public readonly string $path,
		public readonly string $url,
	) {
		$this->container = new Container();
	}

	/**
	 * Retrieve (and lazily create) the shared plugin instance.
	 *
	 * Relies on the REVGUARD_FOR_ADSENSE_* constants defined in the bootstrap file.
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self(
				version: REVGUARD_FOR_ADSENSE_VERSION,
				file: REVGUARD_FOR_ADSENSE_FILE,
				path: REVGUARD_FOR_ADSENSE_PATH,
				url: REVGUARD_FOR_ADSENSE_URL,
			);
		}

		return self::$instance;
	}

	/**
	 * Boot the plugin. Safe to call multiple times; only the first call runs.
	 *
	 * All providers are registered (services bound) before any is booted (hooks
	 * registered), so cross-provider dependencies resolve regardless of order.
	 */
	public function boot(): void {
		if ( $this->booted ) {
			return;
		}

		$this->registerServices();

		foreach ( $this->providers() as $provider ) {
			$provider->boot( $this->container );
		}

		$this->booted = true;
	}

	/**
	 * Register activation and deactivation callbacks exactly once.
	 */
	public function registerLifecycleHooks(): void {
		if ( $this->lifecycle_hooks_registered ) {
			return;
		}

		register_activation_hook( $this->file, array( $this, 'activate' ) );
		register_deactivation_hook( $this->file, array( $this, 'deactivate' ) );
		$this->lifecycle_hooks_registered = true;
	}

	/**
	 * Activation entry point. Safe to invoke repeatedly.
	 */
	public function activate(): void {
		$this->registerServices();
		$this->container->get( Activator::class )->activate();
	}

	/**
	 * Deactivation entry point. Never removes user data.
	 */
	public function deactivate(): void {
		$this->registerServices();
		$this->container->get( Deactivator::class )->deactivate();
	}

	/**
	 * Whether the plugin has completed booting.
	 */
	public function isBooted(): bool {
		return $this->booted;
	}

	/**
	 * The plugin's service providers, in registration order.
	 *
	 * Additional bounded-context providers (Admin, Frontend, Stats, AdsTxt,
	 * Widgets, Editor) are added here in later phases per ARCHITECTURE_V2.md §2.
	 *
	 * @return array<int, ServiceProviderInterface>
	 */
	private function providers(): array {
		return $this->provider_instances ??= array(
			new CoreProvider(),
			new AdminProvider(),
			new FrontendProvider(),
		);
	}

	/**
	 * Register all service factories exactly once.
	 */
	private function registerServices(): void {
		if ( $this->services_registered ) {
			return;
		}

		foreach ( $this->providers() as $provider ) {
			$provider->register( $this->container );
		}

		$this->services_registered = true;
	}
}
