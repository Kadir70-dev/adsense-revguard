<?php
/**
 * View renderer contract.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Contracts;

interface ViewRendererInterface {

	/**
	 * Render a named view with an explicit context.
	 *
	 * @param array<string, mixed> $context View data.
	 */
	public function render( string $view, array $context = array() ): void;
}
