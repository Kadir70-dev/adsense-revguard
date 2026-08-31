<?php
/**
 * Admin settings page controller.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Admin;

use QuickAdsense\Admin\Controls\ControlFactory;
use QuickAdsense\Contracts\SettingsRepositoryInterface;
use QuickAdsense\Contracts\ViewRendererInterface;
use QuickAdsense\Settings\FieldType;
use QuickAdsense\Settings\Schema;

final class SettingsPage {

	public const CAPABILITY   = 'manage_options';
	public const NONCE_ACTION = 'revguard_for_adsense_save_settings';
	public const NONCE_NAME   = 'revguard_for_adsense_nonce';

	public function __construct(
		private readonly SettingsRepositoryInterface $settings,
		private readonly ViewRendererInterface $views,
		private readonly ControlFactory $controls,
	) {}

	public function render(): void {
		$notice = '';

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only notice selector after an admin-post redirect.
		if ( isset( $_GET['revguard_for_adsense_notice'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only notice selector after an admin-post redirect.
			$notice = sanitize_key( wp_unslash( $_GET['revguard_for_adsense_notice'] ) );
		}

		$tab = $this->currentTab();

		$this->views->render(
			$this->viewForTab( $tab ),
			array(
				'settings' => $this->settings->get(),
				'controls' => $this->controls,
				'notice'   => $notice,
				'tab'      => $tab,
			)
		);
	}

	/**
	 * The active admin tab, read from the request and constrained to a known set.
	 */
	private function currentTab(): string {
		$tab = 'home';

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only tab selector, no state change.
		if ( isset( $_GET['tab'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only tab selector, no state change.
			$tab = sanitize_key( wp_unslash( $_GET['tab'] ) );
		}

		if ( ! array_key_exists( $tab, self::tabViews() ) ) {
			return 'home';
		}

		return $tab;
	}

	private function viewForTab( string $tab ): string {
		return self::tabViews()[ $tab ];
	}

	/**
	 * @return array<string, string>
	 */
	private static function tabViews(): array {
		return array(
			'home'     => 'admin/home-page',
			'settings' => 'admin/settings-page',
			'about'    => 'admin/about-page',
			'pro'      => 'admin/pro-page',
		);
	}

	/**
	 * Validate and save a settings request.
	 *
	 * @param array<string, mixed> $input Submitted settings.
	 */
	public function save( array $input, string $nonce ): bool {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die(
				esc_html__( 'You are not allowed to manage RevGuard for AdSense settings.', 'revguard-for-adsense' ),
				esc_html__( 'RevGuard for AdSense', 'revguard-for-adsense' ),
				array( 'response' => 403 )
			);
		}

		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			wp_die(
				esc_html__( 'The settings request could not be verified. Please try again.', 'revguard-for-adsense' ),
				esc_html__( 'RevGuard for AdSense', 'revguard-for-adsense' ),
				array( 'response' => 403 )
			);
		}

		$submitted = array_intersect_key( $input, array_flip( self::editableKeys() ) );

		foreach ( self::editableKeys() as $key ) {
			if ( FieldType::Checkbox === Schema::typeOf( $key ) && ! array_key_exists( $key, $submitted ) ) {
				$submitted[ $key ] = '0';
			}
		}

		$this->settings->save( $submitted );

		return true;
	}

	/**
	 * Keys exposed by this settings page.
	 *
	 * @return array<int, string>
	 */
	public static function editableKeys(): array {
		$keys = array(
			'max_ads_per_page',
			'enable_position_beginning_of_post',
			'ad_beginning_of_post',
			'enable_position_middle_of_post',
			'ad_middle_of_post',
			'enable_position_end_of_post',
			'ad_end_of_post',
			'enable_position_after_more_tag',
			'ad_after_more_tag',
			'enable_position_before_last_para',
			'ad_before_last_para',
			'enable_on_posts',
			'enable_on_pages',
			'enable_on_homepage',
			'enable_on_categories',
			'enable_on_archives',
			'enable_on_tags',
			'enable_all_possible_ads',
			'disable_widgets_on_homepage',
			'disable_for_loggedin_users',
			'onpost_enable_global_style',
			'onpost_global_alignment',
			'onpost_global_margin',
			'header_embed_code',
			'footer_embed_code',
		);

		for ( $index = 1; $index <= 3; $index++ ) {
			$keys[] = 'enable_position_after_para_option_' . $index;
			$keys[] = 'ad_after_para_option_' . $index;
			$keys[] = 'position_after_para_option_' . $index;
			$keys[] = 'enable_jump_position_after_para_option_' . $index;
		}

		$keys[] = 'enable_position_after_image_option_1';
		$keys[] = 'ad_after_image_option_1';
		$keys[] = 'position_after_image_option_1';
		$keys[] = 'enable_jump_position_after_image_option_1';

		for ( $index = 1; $index <= 10; $index++ ) {
			$keys[] = 'onpost_ad_' . $index . '_content';
			$keys[] = 'onpost_ad_' . $index . '_alignment';
			$keys[] = 'onpost_ad_' . $index . '_margin';
		}

		return $keys;
	}
}
