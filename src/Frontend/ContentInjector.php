<?php
/**
 * the_content orchestration.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Frontend;

use QuickAdsense\Contracts\RequestContextInterface;
use QuickAdsense\Contracts\SettingsRepositoryInterface;
use QuickAdsense\Frontend\Engine\InsertionContext;
use QuickAdsense\Frontend\Engine\PositionEngine;
use QuickAdsense\Frontend\Engine\QuotaPolicy;
use QuickAdsense\Frontend\Token\TokenParser;

final class ContentInjector {

	public function __construct(
		private readonly SettingsRepositoryInterface $settings,
		private readonly RequestContextInterface $request,
		private readonly TokenParser $tokens,
		private readonly QuotaPolicy $quota,
		private readonly PositionEngine $positions,
	) {}

	public function inject( string $content ): string {
		$settings = $this->settings->get();
		if ( ! $this->isActive( $content, $this->settings->raw() ) ) {
			return $this->tokens->strip( $content );
		}

		$slots = array();
		for ( $i = 1; $i <= 10; ++$i ) {
			if ( '' !== $settings->string( 'onpost_ad_' . $i . '_content' ) ) {
				$slots[] = $i;
			}
		}
		if ( array() === $slots ) {
			return $this->tokens->strip( $content );
		}
		// Legacy random assignment includes slot 100 as an empty ad container.
		// Keeping it preserves both the seeded shuffle and rendered output.
		$slots[] = 100;

		$context = new InsertionContext(
			0,
			$slots,
			0,
			$this->quota->quota( $content, $settings, $this->request )
		);
		if ( ! $context->hasQuota() ) {
			return $this->tokens->strip( $content );
		}

		return $this->positions->inject( $content, $settings, $this->request, $context );
	}

	/**
	 * Apply display conditions from the raw saved option.
	 *
	 * @param array<string, mixed> $stored Raw saved settings.
	 */
	private function isActive( string $content, array $stored ): bool {
		if ( $this->request->isFeed() || $this->tokens->has( $content, 'NoAds' ) || $this->tokens->has( $content, 'OffAds' ) ) {
			return false;
		}

		return ! (
			( $this->request->isSingle() && ! $this->isEnabled( $stored, 'enable_on_posts' ) )
			|| ( $this->request->isPage() && ! $this->isEnabled( $stored, 'enable_on_pages' ) )
			|| ( $this->request->isHome() && ! $this->isEnabled( $stored, 'enable_on_homepage' ) )
			|| ( $this->request->isCategory() && ! $this->isEnabled( $stored, 'enable_on_categories' ) )
			|| ( $this->request->isTag() && ! $this->isEnabled( $stored, 'enable_on_tags' ) )
			|| (
				$this->request->isArchive()
				&& ! $this->request->isCategory()
				&& ! $this->request->isTag()
				&& ! $this->isEnabled( $stored, 'enable_on_archives' )
			)
			|| ( $this->request->isUserLoggedIn() && $this->isEnabled( $stored, 'disable_for_loggedin_users' ) )
		);
	}

	/**
	 * Interpret legacy checkbox representations safely.
	 *
	 * @param array<string, mixed> $stored Raw saved settings.
	 */
	private function isEnabled( array $stored, string $key ): bool {
		$value = $stored[ $key ] ?? false;

		return true === $value
			|| 1 === $value
			|| ( is_string( $value ) && in_array( strtolower( $value ), array( '1', 'on', 'yes', 'true' ), true ) );
	}
}
