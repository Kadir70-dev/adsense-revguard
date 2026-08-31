<?php
/**
 * In-content ad renderer.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Frontend;

use QuickAdsense\Contracts\HtmlPolicyInterface;
use QuickAdsense\Contracts\RequestContextInterface;
use QuickAdsense\Settings\Settings;

final class AdRenderer {

	public function __construct(
		private readonly HtmlPolicyInterface $ad_policy,
	) {}

	public function render( int $slot, Settings $settings, RequestContextInterface $request ): string {
		if ( $slot < 1 ) {
			return '';
		}

		$styles    = array(
			'',
			'float: left; margin: %1$dpx %1$dpx %1$dpx 0;',
			'float: none; margin:%1$dpx 0 %1$dpx 0; text-align:center;',
			'float: right; margin:%1$dpx 0 %1$dpx %1$dpx;',
			'float: none; margin:0px;',
		);
		$alignment = $settings->int( 'onpost_ad_' . $slot . '_alignment' );
		$style     = sprintf( $styles[ $alignment ] ?? '', $settings->int( 'onpost_ad_' . $slot . '_margin' ) );

		return '<div class="' . md5( $request->siteUrl() ) . '" data-index="' . $slot . '" style="' . $style . '">' . "\n"
			. $this->ad_policy->filter( $settings->string( 'onpost_ad_' . $slot . '_content' ) ) . "\n</div>\n";
	}
}
