<?php
/**
 * Migration step: legacy v1.x (individual options) to modern settings.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Migration\Steps;

use QuickAdsense\Contracts\MigrationStepInterface;
use QuickAdsense\Contracts\OptionStoreInterface;
use QuickAdsense\Settings\Defaults;
use QuickAdsense\Settings\SettingsRepository;

/**
 * Ports a v1.x install (dozens of individual options keyed off `AdsDisp`) into
 * the modern settings array. Faithful reimplementation of the legacy
 * defaults.php mapping — note that, unlike v2.0, v1.x reads the position flags
 * with a direct get_option() default rather than an existence check, and uses
 * `AdsAlign{i}` (not `AdsAdsAlignMargin{i}`) for alignment. Removes only the
 * `AdsDisp` marker, exactly as the legacy plugin did.
 */
final class MigrateFromV1x implements MigrationStepInterface {

	/**
	 * Legacy option keys whose mere presence enables a boolean setting.
	 *
	 * @var array<string, string>
	 */
	private const PRESENCE_FLAGS = array(
		'BegnAds' => 'enable_position_beginning_of_post',
		'MiddAds' => 'enable_position_middle_of_post',
		'EndiAds' => 'enable_position_end_of_post',
		'MoreAds' => 'enable_position_after_more_tag',
		'LapaAds' => 'enable_position_before_last_para',
		'AppPost' => 'enable_on_posts',
		'AppPage' => 'enable_on_pages',
		'AppHome' => 'enable_on_homepage',
		'AppCate' => 'enable_on_categories',
		'AppArch' => 'enable_on_archives',
		'AppTags' => 'enable_on_tags',
		'AppMaxA' => 'enable_all_possible_ads',
		'AppSide' => 'disable_widgets_on_homepage',
		'AppLogg' => 'disable_for_loggedin_users',
		'QckTags' => 'enable_quicktag_buttons',
		'QckRnds' => 'disable_randomads_quicktag_button',
		'QckOffs' => 'disable_disablead_quicktag_buttons',
		'QckOfPs' => 'disable_positionad_quicktag_buttons',
	);

	public function appliesTo( OptionStoreInterface $options ): bool {
		$settings = $options->get( SettingsRepository::OPTION );
		if ( is_array( $settings ) && array() !== $settings ) {
			return false;
		}

		$legacy = $options->get( 'quick_adsense_2_options' );
		if ( is_array( $legacy ) && count( $legacy ) > 1 ) {
			return false;
		}

		return in_array(
			$options->get( 'AdsDisp' ),
			array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10' ),
			true
		);
	}

	public function run( OptionStoreInterface $options ): void {
		$settings = Defaults::all();

		$settings['max_ads_per_page'] = $this->opt( $options, 'AdsDisp', $settings['max_ads_per_page'] );

		foreach ( self::PRESENCE_FLAGS as $legacy_key => $setting_key ) {
			if ( false !== $options->get( $legacy_key, false ) ) {
				$settings[ $setting_key ] = '1';
			}
		}

		$settings['ad_beginning_of_post'] = $this->opt( $options, 'BegnRnd', $settings['ad_beginning_of_post'] );
		$settings['ad_middle_of_post']    = $this->opt( $options, 'MiddRnd', $settings['ad_middle_of_post'] );
		$settings['ad_end_of_post']       = $this->opt( $options, 'EndiRnd', $settings['ad_end_of_post'] );
		$settings['ad_after_more_tag']    = $this->opt( $options, 'MoreRnd', $settings['ad_after_more_tag'] );
		$settings['ad_before_last_para']  = $this->opt( $options, 'LapaRnd', $settings['ad_before_last_para'] );

		for ( $i = 1; $i <= 3; $i++ ) {
			$settings[ 'enable_position_after_para_option_' . $i ]      = $this->opt( $options, 'Par' . $i . 'Ads', $settings[ 'enable_position_after_para_option_' . $i ] );
			$settings[ 'ad_after_para_option_' . $i ]                   = $this->opt( $options, 'Par' . $i . 'Rnd', $settings[ 'ad_after_para_option_' . $i ] );
			$settings[ 'position_after_para_option_' . $i ]             = $this->opt( $options, 'Par' . $i . 'Nup', $settings[ 'position_after_para_option_' . $i ] );
			$settings[ 'enable_jump_position_after_para_option_' . $i ] = $this->opt( $options, 'Par' . $i . 'Con', $settings[ 'enable_jump_position_after_para_option_' . $i ] );
		}

		for ( $i = 1; $i <= 1; $i++ ) {
			$settings[ 'enable_position_after_image_option_' . $i ]      = $this->opt( $options, 'Img' . $i . 'Ads', $settings[ 'enable_position_after_image_option_' . $i ] );
			$settings[ 'ad_after_image_option_' . $i ]                   = $this->opt( $options, 'Img' . $i . 'Rnd', $settings[ 'ad_after_image_option_' . $i ] );
			$settings[ 'position_after_image_option_' . $i ]             = $this->opt( $options, 'Img' . $i . 'Nup', $settings[ 'position_after_image_option_' . $i ] );
			$settings[ 'enable_jump_position_after_image_option_' . $i ] = $this->opt( $options, 'Img' . $i . 'Con', $settings[ 'enable_jump_position_after_image_option_' . $i ] );
		}

		for ( $i = 1; $i <= 10; $i++ ) {
			$settings[ 'onpost_ad_' . $i . '_content' ]   = $this->opt( $options, 'AdsCode' . $i, $settings[ 'onpost_ad_' . $i . '_content' ] );
			$settings[ 'onpost_ad_' . $i . '_alignment' ] = $this->opt( $options, 'AdsAlign' . $i, $settings[ 'onpost_ad_' . $i . '_alignment' ] );
			$settings[ 'onpost_ad_' . $i . '_margin' ]    = $this->opt( $options, 'AdsMargin' . $i, $settings[ 'onpost_ad_' . $i . '_margin' ] );
			$settings[ 'widget_ad_' . $i . '_content' ]   = $this->opt( $options, 'WidCode' . $i, $settings[ 'widget_ad_' . $i . '_content' ] );
		}

		$options->update( SettingsRepository::OPTION, $settings );
		$options->delete( 'AdsDisp' );
	}

	public function describe(): string {
		return 'Migrate legacy v1.x options to modern settings';
	}

	/**
	 * Mirror of get_option($key, $default): the stored value when present, else
	 * the default.
	 */
	// phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.defaultFound -- Mirrors get_option terminology.
	private function opt( OptionStoreInterface $options, string $key, mixed $default ): mixed {
		$value = $options->get( $key, false );

		return false !== $value ? $value : $default;
	}
}
