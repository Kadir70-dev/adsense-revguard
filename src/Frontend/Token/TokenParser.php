<?php
/**
 * Content control-token parser.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Frontend\Token;

final class TokenParser {

	public function __construct(
		private readonly TokenAliasMap $aliases,
	) {}

	public function has( string $content, string $token ): bool {
		$tokens = $this->aliases->aliases()[ $token ] ?? array( $token );

		foreach ( $tokens as $candidate ) {
			if ( str_contains( $content, '<!--' . $candidate . '-->' ) ) {
				return true;
			}
		}

		return false;
	}

	public function strip( string $content, bool $trim_only = false ): string {
		$tokens = array_merge(
			array(
				'EmptyClear',
				'RndAds',
				'NoAds',
				'OffDef',
				'OffAds',
				'OffWidget',
				'OffBegin',
				'OffMiddle',
				'OffEnd',
				'CusRnd',
			),
			$this->aliases->all()
		);

		for ( $i = 1; $i <= 10; ++$i ) {
			$tokens[] = 'CusAds' . $i;
			$tokens[] = 'Ads' . $i;
		}

		foreach ( $tokens as $token ) {
			$comment = '<!--' . $token . '-->';
			if ( $trim_only ) {
				$content = str_replace( '<p>' . $comment . '</p>', $comment, $content );
			} else {
				$content = str_replace( array( '<p>' . $comment . '</p>', $comment ), '', $content );
			}
		}

		if ( ! $trim_only ) {
			$content = str_replace( array( '##QA-TP1##', '##QA-TP2##' ), array( '<p></p>', '<p>&nbsp;</p>' ), $content );
		}

		return $content;
	}
}
