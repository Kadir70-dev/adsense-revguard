<?php
/**
 * Frontend service provider.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Provider;

use QuickAdsense\Container;
use QuickAdsense\Contracts\HtmlPolicyInterface;
use QuickAdsense\Contracts\RequestContextInterface;
use QuickAdsense\Contracts\ServiceProviderInterface;
use QuickAdsense\Contracts\SettingsRepositoryInterface;
use QuickAdsense\Frontend\AdRenderer;
use QuickAdsense\Frontend\ContentInjector;
use QuickAdsense\Frontend\EmbedCodes;
use QuickAdsense\Frontend\Engine\PositionEngine;
use QuickAdsense\Frontend\Engine\QuotaPolicy;
use QuickAdsense\Frontend\Token\TokenAliasMap;
use QuickAdsense\Frontend\Token\TokenParser;
use QuickAdsense\Infrastructure\WordPress\WpRequestContext;
use QuickAdsense\Infrastructure\Security\KsesAdPolicy;

final class FrontendProvider implements ServiceProviderInterface {

	public function register( Container $container ): void {
		$container->singleton( RequestContextInterface::class, static fn (): RequestContextInterface => new WpRequestContext() );
		$container->singleton( HtmlPolicyInterface::class, static fn (): HtmlPolicyInterface => new KsesAdPolicy() );
		$container->singleton( TokenAliasMap::class, static fn (): TokenAliasMap => new TokenAliasMap() );
		$container->singleton(
			TokenParser::class,
			static fn ( Container $c ): TokenParser => new TokenParser( $c->get( TokenAliasMap::class ) )
		);
		$container->singleton(
			QuotaPolicy::class,
			static fn ( Container $c ): QuotaPolicy => new QuotaPolicy( $c->get( TokenParser::class ) )
		);
		$container->singleton(
			AdRenderer::class,
			static fn ( Container $c ): AdRenderer => new AdRenderer( $c->get( HtmlPolicyInterface::class ) )
		);
		$container->singleton(
			PositionEngine::class,
			static fn ( Container $c ): PositionEngine => new PositionEngine(
				$c->get( TokenParser::class ),
				$c->get( AdRenderer::class )
			)
		);
		$container->singleton(
			ContentInjector::class,
			static fn ( Container $c ): ContentInjector => new ContentInjector(
				$c->get( SettingsRepositoryInterface::class ),
				$c->get( RequestContextInterface::class ),
				$c->get( TokenParser::class ),
				$c->get( QuotaPolicy::class ),
				$c->get( PositionEngine::class )
			)
		);
		$container->singleton(
			EmbedCodes::class,
			static fn ( Container $c ): EmbedCodes => new EmbedCodes(
				$c->get( SettingsRepositoryInterface::class ),
				$c->get( HtmlPolicyInterface::class )
			)
		);
	}

	public function boot( Container $container ): void {
		add_filter(
			'the_content',
			static fn ( string $content ): string => $container->get( ContentInjector::class )->inject( $content )
		);
		add_action( 'wp_head', array( $container->get( EmbedCodes::class ), 'header' ) );
		add_action( 'wp_footer', array( $container->get( EmbedCodes::class ), 'footer' ) );
	}
}
