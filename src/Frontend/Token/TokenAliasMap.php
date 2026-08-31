<?php
/**
 * Control-token aliases.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Frontend\Token;

final class TokenAliasMap {

	/**
	 * @return array<string, array<int, string>>
	 */
	public function aliases(): array {
		return array(
			'OffAfMore'     => array( 'OffAfMore', 'OffBfMore' ),
			'OffBfLastPara' => array( 'OffBfLastPara', 'OffAfLastPara' ),
		);
	}

	/**
	 * @return array<int, string>
	 */
	public function all(): array {
		return array_values( array_unique( array_merge( ...array_values( $this->aliases() ) ) ) );
	}
}
