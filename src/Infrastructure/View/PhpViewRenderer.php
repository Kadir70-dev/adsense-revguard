<?php
/**
 * PHP view renderer.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Infrastructure\View;

use QuickAdsense\Contracts\ViewRendererInterface;
use RuntimeException;

final class PhpViewRenderer implements ViewRendererInterface {

	public function __construct(
		private readonly string $view_path,
	) {}

	public function render( string $view, array $context = array() ): void {
		$file = $this->view_path . '/' . ltrim( $view, '/' ) . '.php';

		if ( ! is_file( $file ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception message is not rendered as output.
			throw new RuntimeException( 'View not found: ' . $view );
		}

		require $file;
	}
}
