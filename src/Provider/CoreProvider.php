<?php
/**
 * Core service provider.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Provider;

use QuickAdsense\Container;
use QuickAdsense\Contracts\OptionStoreInterface;
use QuickAdsense\Contracts\ServiceProviderInterface;
use QuickAdsense\Contracts\SettingsRepositoryInterface;
use QuickAdsense\Contracts\TransientStoreInterface;
use QuickAdsense\Infrastructure\WordPress\WpOptionStore;
use QuickAdsense\Infrastructure\WordPress\WpTransientStore;
use QuickAdsense\Infrastructure\Lifecycle\Activator;
use QuickAdsense\Infrastructure\Lifecycle\Deactivator;
use QuickAdsense\Migration\LegacyCompatibilityLoader;
use QuickAdsense\Migration\MigrationRunner;
use QuickAdsense\Migration\SchemaVersion;
use QuickAdsense\Settings\SettingsRepository;
use QuickAdsense\Settings\SettingsSanitizer;

/**
 * Wires the settings and data layer: stores, settings repository, and the
 * migration runner.
 */
final class CoreProvider implements ServiceProviderInterface {

	public function register( Container $container ): void {
		$container->singleton(
			OptionStoreInterface::class,
			static fn (): OptionStoreInterface => new WpOptionStore()
		);

		$container->singleton(
			TransientStoreInterface::class,
			static fn (): TransientStoreInterface => new WpTransientStore()
		);

		$container->singleton(
			SettingsSanitizer::class,
			static fn (): SettingsSanitizer => new SettingsSanitizer()
		);

		$container->singleton(
			SettingsRepositoryInterface::class,
			static fn ( Container $c ): SettingsRepositoryInterface => new SettingsRepository(
				$c->get( OptionStoreInterface::class ),
				$c->get( SettingsSanitizer::class ),
			)
		);

		$container->singleton(
			LegacyCompatibilityLoader::class,
			static fn (): LegacyCompatibilityLoader => new LegacyCompatibilityLoader()
		);

		$container->singleton(
			MigrationRunner::class,
			static fn ( Container $c ): MigrationRunner => new MigrationRunner(
				$c->get( OptionStoreInterface::class ),
				$c->get( LegacyCompatibilityLoader::class )->steps(),
				SchemaVersion::CURRENT,
			)
		);

		$container->singleton(
			Activator::class,
			static fn ( Container $c ): Activator => new Activator( $c->get( MigrationRunner::class ) )
		);

		$container->singleton(
			Deactivator::class,
			static fn (): Deactivator => new Deactivator()
		);
	}

	public function boot( Container $container ): void {
		// One autoloaded version read per request; writes occur only when pending.
		$runner = $container->get( MigrationRunner::class );
		if ( ! $runner->run() ) {
			$message = $runner->lastError()?->getMessage() ?? 'Unknown migration failure';
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Required lifecycle observability.
			error_log( 'RevGuard for AdSense migration failed and was rolled back: ' . $message );
		}
	}
}
