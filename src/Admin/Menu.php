<?php
/**
 * WordPress admin menu registration.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Admin;

final class Menu {

	public function __construct(
		private readonly SettingsPage $page,
	) {}

	public function register(): void {
		add_menu_page(
			__( 'AdSense RevGuard', 'adsense-revguard' ),
			__( 'AdSense RevGuard', 'adsense-revguard' ),
			SettingsPage::CAPABILITY,
			'adsense-revguard',
			array( $this->page, 'render' ),
			'dashicons-money-alt'
		);
	}
}
