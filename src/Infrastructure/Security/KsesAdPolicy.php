<?php
/**
 * Scoped ad-code HTML policy.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Infrastructure\Security;

use QuickAdsense\Contracts\HtmlPolicyInterface;

/**
 * The sole raw-HTML exception for stored advertising and tracker snippets.
 *
 * Ad code is intentionally stored verbatim for backwards compatibility. It is
 * filtered at the output boundary so newly-disallowed markup is never trusted
 * merely because it was saved by an administrator in the past.
 */
final class KsesAdPolicy implements HtmlPolicyInterface {

	public function filter( string $html ): string {
		return wp_kses( $html, $this->allowed() );
	}

	public function allowed(): array {
		$global = array(
			'class'            => true,
			'id'               => true,
			'style'            => true,
			'title'            => true,
			'data-*'           => true,
			'aria-describedby' => true,
			'aria-hidden'      => true,
			'aria-label'       => true,
			'aria-labelledby'  => true,
			'role'             => true,
		);

		return array(
			'a'        => $global + array(
				'href'   => true,
				'target' => true,
				'rel'    => true,
			),
			'div'      => $global,
			'span'     => $global,
			'ins'      => $global,
			'img'      => $global + array(
				'src'     => true,
				'alt'     => true,
				'width'   => true,
				'height'  => true,
				'loading' => true,
			),
			'iframe'   => $global + array(
				'src'             => true,
				'width'           => true,
				'height'          => true,
				'frameborder'     => true,
				'scrolling'       => true,
				'allow'           => true,
				'allowfullscreen' => true,
				'sandbox'         => true,
			),
			'meta'     => $global + array(
				'charset'    => true,
				'content'    => true,
				'http-equiv' => true,
				'itemprop'   => true,
				'name'       => true,
				'property'   => true,
			),
			'link'     => $global + array(
				'as'             => true,
				'crossorigin'    => true,
				'href'           => true,
				'hreflang'       => true,
				'integrity'      => true,
				'media'          => true,
				'referrerpolicy' => true,
				'rel'            => true,
				'sizes'          => true,
				'type'           => true,
			),
			'script'   => array(
				'src'         => true,
				'type'        => true,
				'async'       => true,
				'defer'       => true,
				'crossorigin' => true,
				'data-*'      => true,
			),
			'noscript' => array(),
			'br'       => array(),
		);
	}
}
