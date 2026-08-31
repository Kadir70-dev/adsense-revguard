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
 * Ad code is intentionally stored verbatim for backwards compatibility. Non-
 * script markup is filtered through wp_kses() at the output boundary, so markup
 * that later becomes disallowed is never trusted merely because it was saved by
 * an administrator in the past.
 *
 * Two adjustments make wp_kses() safe to use on *real* ad code without
 * corrupting it:
 *
 *  1. The standard AdSense unit is `<ins class="adsbygoogle" style="display:block">`.
 *     wp_kses()'s default `safe_style_css` list has no `display` (or explicit
 *     sizing) property, so it silently strips that style and the ad fails to
 *     render. This class re-adds the layout-only properties AdSense needs, for
 *     the duration of the filter call.
 *
 *  2. Ad snippets ship an inline `<script>` initialiser whose JavaScript body
 *     (`a && b`, `i < n`, `x > 0`) wp_kses() would entity-mangle into broken
 *     code. Ad and embed code can only ever be written by a `manage_options`
 *     administrator (enforced where it is saved), so an inline `<script>` whose
 *     opening tag carries no event handler and no nested tag is preserved
 *     verbatim; everything else is removed *before* wp_kses() runs, so the rest
 *     of the markup is still fully filtered.
 */
final class KsesAdPolicy implements HtmlPolicyInterface {

	/**
	 * Layout-only CSS properties that AdSense/ad markup needs but that
	 * wp_kses()'s default inline-style allow-list omits.
	 *
	 * @var array<int, string>
	 */
	private const EXTRA_SAFE_STYLE_CSS = array(
		'display',
		'width',
		'height',
		'min-width',
		'max-width',
		'min-height',
		'max-height',
		'aspect-ratio',
		'object-fit',
	);

	public function filter( string $html ): string {
		$add_ad_style_props = static function ( array $props ): array {
			return array_values( array_unique( array_merge( $props, self::EXTRA_SAFE_STYLE_CSS ) ) );
		};

		$scripts = array();
		$marker  = 'RGFA-SCRIPT-' . ( function_exists( 'wp_generate_password' ) ? wp_generate_password( 16, false ) : (string) wp_rand() ) . '-';

		// Lift out genuinely inline <script> initialisers (a body, no src) whose
		// opening tag has no event handler or nested tag; wp_kses() still filters
		// everything else, including external <script src> loaders.
		$prepared = (string) preg_replace_callback(
			'#<script\b([^>]*)>(.*?)</script\s*>#is',
			function ( array $matches ) use ( &$scripts, $marker ): string {
				if ( '' === trim( $matches[2] ) || ! $this->scriptOpenTagIsTrusted( $matches[1] ) ) {
					return $matches[0];
				}

				$token             = '<!--' . $marker . count( $scripts ) . '-->';
				$scripts[ $token ] = '<script' . rtrim( $matches[1] ) . '>' . $matches[2] . '</script>';

				return $token;
			},
			$html
		);

		add_filter( 'safe_style_css', $add_ad_style_props );

		try {
			$filtered = wp_kses( $prepared, $this->allowed() );
		} finally {
			remove_filter( 'safe_style_css', $add_ad_style_props );
		}

		return array() === $scripts ? $filtered : strtr( $filtered, $scripts );
	}

	/**
	 * Whether an inline <script> opening tag is safe to pass through untouched:
	 * no inline event handler, no nested tag start, and no `src` (external
	 * loaders stay in the stream so wp_kses() vets their URL).
	 */
	private function scriptOpenTagIsTrusted( string $attributes ): bool {
		if ( 1 === preg_match( '/\bsrc\s*=/i', $attributes ) ) {
			return false;
		}

		return 1 !== preg_match( '/\bon[a-z]+\s*=|</i', $attributes );
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
				'as'            => true,
				'crossorigin'   => true,
				'href'          => true,
				'hreflang'      => true,
				'integrity'     => true,
				'media'         => true,
				'referrerpolicy' => true,
				'rel'           => true,
				'sizes'         => true,
				'type'          => true,
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
