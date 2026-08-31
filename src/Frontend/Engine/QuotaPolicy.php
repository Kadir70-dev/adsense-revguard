<?php
/**
 * Per-content ad quota.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Frontend\Engine;

use QuickAdsense\Contracts\RequestContextInterface;
use QuickAdsense\Frontend\Token\TokenParser;
use QuickAdsense\Settings\Settings;

final class QuotaPolicy {

	public function __construct(
		private readonly TokenParser $tokens,
	) {}

	public function quota( string $content, Settings $settings, RequestContextInterface $request ): int {
		$quota = $settings->int( 'max_ads_per_page' );
		if ( ! $this->tokens->has( $content, 'OffWidget' ) ) {
			for ( $i = 1; $i <= 10; ++$i ) {
				if ( $request->isActiveWidget( $i ) ) {
					--$quota;
				}
			}
		}

		return max( 0, $quota );
	}
}
