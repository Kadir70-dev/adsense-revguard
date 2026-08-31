<?php
/**
 * WordPress request context adapter.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Infrastructure\WordPress;

use QuickAdsense\Contracts\RequestContextInterface;

final class WpRequestContext implements RequestContextInterface {

	public function isFeed(): bool {
		return is_feed();
	}

	public function isSingle(): bool {
		return is_single();
	}

	public function isPage(): bool {
		return is_page();
	}

	public function isHome(): bool {
		return is_home();
	}

	public function isCategory(): bool {
		return is_category();
	}

	public function isArchive(): bool {
		return is_archive();
	}

	public function isTag(): bool {
		return is_tag();
	}

	public function isUserLoggedIn(): bool {
		return is_user_logged_in();
	}

	public function postId(): int {
		return (int) get_the_ID();
	}

	public function siteUrl(): string {
		return (string) get_bloginfo( 'url' );
	}

	public function isActiveWidget( int $index ): bool {
		// Preserve the historical label because WordPress derives the stored widget ID from it.
		$name = sprintf( 'AdsWidget%d (Quick Adsense)', $index );
		$id   = sanitize_title( str_replace( array( '(', ')' ), '', $name ) );

		// The first argument intentionally remains true for legacy parity.
		// PHPStan's WordPress stub models only the documented callable|false type.
		/** @phpstan-ignore-next-line */
		return (bool) is_active_widget( true, $id );
	}

	public function referer(): string {
		return isset( $_SERVER['HTTP_REFERER'] )
			? sanitize_url( wp_unslash( (string) $_SERVER['HTTP_REFERER'] ) )
			: '';
	}

	public function clientIp(): string {
		return isset( $_SERVER['REMOTE_ADDR'] )
			? sanitize_text_field( wp_unslash( (string) $_SERVER['REMOTE_ADDR'] ) )
			: '';
	}
}
