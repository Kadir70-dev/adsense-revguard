<?php
/**
 * Legacy-compatible string placement engine.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Frontend\Engine;

use QuickAdsense\Contracts\RequestContextInterface;
use QuickAdsense\Frontend\AdRenderer;
use QuickAdsense\Frontend\Token\TokenParser;
use QuickAdsense\Settings\Settings;

final class PositionEngine {

	public function __construct(
		private readonly TokenParser $tokens,
		private readonly AdRenderer $renderer,
	) {}

	public function inject(
		string $content,
		Settings $settings,
		RequestContextInterface $request,
		InsertionContext $context
	): string {
		$content = str_replace( array( '<p></p>', '<p>&nbsp;</p>' ), array( '##QA-TP1##', '##QA-TP2##' ), $content );
		$off_def = $this->tokens->has( $content, 'OffDef' );
		$custom  = array();

		if ( ! $off_def ) {
			$content = $this->placeDefaults( $content, $settings, $request, $custom );
		}

		$content  = '<!--EmptyClear-->' . $content . "\n"
			. '<div style="font-size: 0px; height: 0px; line-height: 0px; margin: 0; padding: 0; clear: both;"></div>';
		$content  = $this->tokens->strip( $content, true );
		$is_many  = ! $request->isSingle() && ! $request->isPage();
		$show_all = $settings->bool( 'enable_all_possible_ads' );

		if ( ! $off_def ) {
			foreach ( $custom as $index => $slot ) {
				if ( ! $show_all && $is_many && ( $index + 1 ) === $context->begin_end_cursor ) {
					continue;
				}
				if ( $this->replace( $content, 'CusAds' . $slot, $slot, $settings, $request, $context ) ) {
					$context->begin_end_cursor = $index + 1;
					if ( ! $context->hasQuota() || ( $is_many && ! $show_all ) ) {
						return $this->tokens->strip( $content );
					}
				}
			}
		}

		if ( $show_all || ! $is_many ) {
			foreach ( array_values( $context->ad_slot_ids ) as $slot ) {
				if ( $this->replace( $content, 'Ads' . $slot, $slot, $settings, $request, $context ) && ! $context->hasQuota() ) {
					return $this->tokens->strip( $content );
				}
			}

			$content = $this->replaceRandom( $content, 'CusRnd', $settings, $request, $context, false );
			if ( $context->hasQuota() ) {
				$content = $this->replaceRandom( $content, 'RndAds', $settings, $request, $context, true );
			}
		}

		return $this->tokens->strip( $content );
	}

	/**
	 * @param array<int, int> $custom Specific slots in placement order.
	 */
	private function placeDefaults(
		string $content,
		Settings $settings,
		RequestContextInterface $request,
		array &$custom
	): string {
		$stand_ins = array(
			'begin'  => $this->standIn( $settings->get( 'ad_beginning_of_post' ), $custom ),
			'middle' => $this->standIn( $settings->get( 'ad_middle_of_post' ), $custom ),
			'end'    => $this->standIn( $settings->get( 'ad_end_of_post' ), $custom ),
			'more'   => $this->standIn( $settings->get( 'ad_after_more_tag' ), $custom ),
			'last'   => $this->standIn( $settings->get( 'ad_before_last_para' ), $custom ),
		);

		for ( $i = 1; $i <= 3; ++$i ) {
			$stand_ins[ 'para' . $i ] = $this->standIn( $settings->get( 'ad_after_para_option_' . $i ), $custom );
		}
		$stand_ins['image'] = $this->standIn( $settings->get( 'ad_after_image_option_1' ), $custom );

		if ( $settings->bool( 'enable_position_middle_of_post' ) && ! $this->tokens->has( $content, 'OffMiddle' ) ) {
			$content = $this->placeMiddle( $content, $stand_ins['middle'] );
		}
		if ( $settings->bool( 'enable_position_after_more_tag' ) && ! $this->tokens->has( $content, 'OffAfMore' ) ) {
			$content = str_replace( '<span id="more-' . $request->postId() . '"></span>', $this->comment( $stand_ins['more'] ), $content );
		}
		if ( $settings->bool( 'enable_position_beginning_of_post' ) && ! $this->tokens->has( $content, 'OffBegin' ) ) {
			$content = $this->comment( $stand_ins['begin'] ) . $content;
		}
		if ( $settings->bool( 'enable_position_end_of_post' ) && ! $this->tokens->has( $content, 'OffEnd' ) ) {
			$content .= $this->comment( $stand_ins['end'] );
		}
		if ( $settings->bool( 'enable_position_before_last_para' ) && ! $this->tokens->has( $content, 'OffBfLastPara' ) ) {
			$content = $this->placeBeforeLast( $content, $stand_ins['last'] );
		}
		for ( $i = 1; $i <= 3; ++$i ) {
			if ( $settings->bool( 'enable_position_after_para_option_' . $i ) ) {
				$content = $this->placeAfter(
					$content,
					'</p>',
					$settings->int( 'position_after_para_option_' . $i ),
					$stand_ins[ 'para' . $i ],
					$settings->bool( 'enable_jump_position_after_para_option_' . $i )
				);
			}
		}
		if ( $settings->bool( 'enable_position_after_image_option_1' ) ) {
			$content = $this->placeAfterImage(
				$content,
				$settings->int( 'position_after_image_option_1' ),
				$stand_ins['image'],
				$settings->bool( 'enable_jump_position_after_image_option_1' )
			);
		}

		return $content;
	}

	/**
	 * @param array<int, int> $custom Specific slots in placement order.
	 */
	private function standIn( mixed $slot, array &$custom ): string {
		if ( 0 === $slot ) {
			return 'CusRnd';
		}
		$id       = (int) $slot;
		$custom[] = $id;
		return 'CusAds' . $id;
	}

	private function placeMiddle( string $content, string $stand_in ): string {
		if ( substr_count( strtolower( $content ), '</p>' ) < 2 ) {
			return $content;
		}
		$content = str_replace( '</P>', '</p>', $content );
		$parts   = explode( '</p>', $content );
		$cursor  = 0;
		$middle  = strlen( $content ) / 2;
		foreach ( $parts as $index => $part ) {
			$cursor += strlen( $part ) + 4;
			if ( $cursor > $middle ) {
				$previous         = $cursor - strlen( $part );
				$target           = ( $middle - $previous > $cursor - $middle && isset( $parts[ $index + 1 ] ) ) ? $index + 1 : $index;
				$parts[ $target ] = $this->comment( $stand_in ) . $parts[ $target ];
				break;
			}
		}
		return implode( '</p>', $parts );
	}

	private function placeBeforeLast( string $content, string $stand_in ): string {
		$content = str_replace( '<P>', '<p>', $content );
		$parts   = explode( '<p>', $content );
		if ( count( $parts ) > 2 ) {
			$last    = array_pop( $parts );
			$content = implode( '<p>', $parts ) . $this->comment( $stand_in ) . '<p>' . $last;
		}
		return $content;
	}

	private function placeAfter( string $content, string $tag, int $position, string $stand_in, bool $jump ): string {
		if ( '' === $tag ) {
			return $content;
		}
		$content = str_replace( strtoupper( $tag ), $tag, $content );
		$parts   = explode( $tag, $content );
		if ( $position < count( $parts ) ) {
			return implode( $tag, array_slice( $parts, 0, $position ) ) . $tag
				. $this->comment( $stand_in ) . implode( $tag, array_slice( $parts, $position ) );
		}
		return $jump ? implode( $tag, $parts ) . $this->comment( $stand_in ) : $content;
	}

	private function placeAfterImage( string $content, int $position, string $stand_in, bool $jump ): string {
		$content = str_replace( array( '<IMG', '</A>' ), array( '<img', '</a>' ), $content );
		$parts   = explode( '<img', $content );
		if ( $position >= count( $parts ) ) {
			return $content;
		}
		$part = $parts[ $position ];
		if ( $jump && str_contains( $part, '[/caption]' ) && ! str_contains( strtolower( explode( '[/caption]', $part )[0] ), '[caption ' ) ) {
			$parts[ $position ] = preg_replace( '/\\[\\/caption\\]/', '[/caption]' . "\r\n" . $this->comment( $stand_in ) . "\r\n", $part, 1 ) ?? $part;
		} elseif ( str_contains( $part, '</a>' ) && ! str_contains( strtolower( explode( '</a>', $part )[0] ), '<a href' ) ) {
			$parts[ $position ] = preg_replace( '/<\\/a>/', '</a>' . "\r\n" . $this->comment( $stand_in ) . "\r\n", $part, 1 ) ?? $part;
		} else {
			$parts[ $position ] = preg_replace( '/>/', '>' . "\r\n" . $this->comment( $stand_in ) . "\r\n", $part, 1 ) ?? $part;
		}
		return implode( '<img', $parts );
	}

	private function replace(
		string &$content,
		string $token,
		int $slot,
		Settings $settings,
		RequestContextInterface $request,
		InsertionContext $context
	): bool {
		$comment = '<!--' . $token . '-->';
		if ( ! str_contains( $content, $comment ) || ! in_array( $slot, $context->ad_slot_ids, true ) ) {
			return false;
		}
		$content = implode( $this->renderer->render( $slot, $settings, $request ), explode( $comment, $content, 2 ) );
		$context->record( $slot );
		return true;
	}

	private function replaceRandom(
		string $content,
		string $token,
		Settings $settings,
		RequestContextInterface $request,
		InsertionContext $context,
		bool $limited
	): string {
		$count = substr_count( $content, $this->comment( $token ) );
		if ( 0 === $count ) {
			return $content;
		}
		$slots = $context->ad_slot_ids;
		shuffle( $slots );
		if ( $limited ) {
			$slots = array_slice( $slots, 0, max( 0, $context->quota - $context->ads_displayed ) );
		}
		$slot_count = count( $slots );
		while ( $slot_count < $count ) {
			$slots[] = -1;
			++$slot_count;
		}
		shuffle( $slots );
		for ( $i = 0; $i < $count; ++$i ) {
			if ( ! $context->hasQuota() ) {
				break;
			}
			$slot    = array_shift( $slots );
			$comment = '<!--' . $token . '-->';
			$content = implode( $this->renderer->render( (int) $slot, $settings, $request ), explode( $comment, $content, 2 ) );
			if ( -1 !== $slot || ! $limited ) {
				$context->record( (int) $slot );
			}
		}
		return $content;
	}

	private function comment( string $token ): string {
		return '<!--' . $token . '-->';
	}
}
