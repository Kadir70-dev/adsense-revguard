<?php
/**
 * Header and footer embed-code output.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Frontend;

use QuickAdsense\Contracts\HtmlPolicyInterface;
use QuickAdsense\Contracts\SettingsRepositoryInterface;

final class EmbedCodes {

	public function __construct(
		private readonly SettingsRepositoryInterface $settings,
		private readonly HtmlPolicyInterface $policy,
	) {}

	public function header(): void {
		$this->output( 'header_embed_code' );
	}

	public function footer(): void {
		$this->output( 'footer_embed_code' );
	}

	private function output( string $key ): void {
		$code = $this->settings->get()->string( $key );

		if ( '' !== $code ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Filtered by the approved KsesAdPolicy at this ad-code sink.
			echo $this->policy->filter( $code );
		}
	}
}
