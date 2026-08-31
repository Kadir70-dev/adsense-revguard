<?php
/**
 * Migration step: legacy v2.0 (quick_adsense_2_options) to modern settings.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Migration\Steps;

use QuickAdsense\Contracts\MigrationStepInterface;
use QuickAdsense\Contracts\OptionStoreInterface;
use QuickAdsense\Migration\LegacyValue;
use QuickAdsense\Settings\Defaults;
use QuickAdsense\Settings\SettingsRepository;

/**
 * Ports a v2.0 install (single `quick_adsense_2_options` array) into the modern
 * settings array. Faithful reimplementation of the legacy defaults.php mapping,
 * including the v2.0-specific AdsAdsAlignMargin{i} key for alignment. Backs the
 * source up to `quick_adsense_2_options_bak` and removes the original, exactly
 * as the legacy plugin did.
 */
final class MigrateFromV20 implements MigrationStepInterface {

	private const SOURCE_OPTION = 'quick_adsense_2_options';

	private const BACKUP_OPTION = 'quick_adsense_2_options_bak';

	public function appliesTo( OptionStoreInterface $options ): bool {
		$settings = $options->get( SettingsRepository::OPTION );
		if ( is_array( $settings ) && array() !== $settings ) {
			return false;
		}

		$legacy = $options->get( self::SOURCE_OPTION );

		return is_array( $legacy ) && count( $legacy ) > 1;
	}

	public function run( OptionStoreInterface $options ): void {
		$source = $options->get( self::SOURCE_OPTION );
		if ( ! is_array( $source ) ) {
			return;
		}

		$settings = Defaults::all();

		$settings['max_ads_per_page'] = LegacyValue::get( $source, 'AdsDisp', $settings['max_ads_per_page'] );

		$flag_map = array(
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
		foreach ( $flag_map as $legacy_key => $setting_key ) {
			if ( false !== LegacyValue::get( $source, $legacy_key, false ) ) {
				$settings[ $setting_key ] = '1';
			}
		}

		$settings['ad_beginning_of_post'] = LegacyValue::get( $source, 'BegnRnd', $settings['ad_beginning_of_post'] );
		$settings['ad_middle_of_post']    = LegacyValue::get( $source, 'MiddRnd', $settings['ad_middle_of_post'] );
		$settings['ad_end_of_post']       = LegacyValue::get( $source, 'EndiRnd', $settings['ad_end_of_post'] );
		$settings['ad_after_more_tag']    = LegacyValue::get( $source, 'MoreRnd', $settings['ad_after_more_tag'] );
		$settings['ad_before_last_para']  = LegacyValue::get( $source, 'LapaRnd', $settings['ad_before_last_para'] );

		for ( $i = 1; $i <= 3; $i++ ) {
			if ( false !== LegacyValue::get( $source, 'Par' . $i . 'Ads', false ) ) {
				$settings[ 'enable_position_after_para_option_' . $i ] = '1';
			}
			if ( false !== LegacyValue::get( $source, 'Par' . $i . 'Rnd', false ) ) {
				$settings[ 'ad_after_para_option_' . $i ] = '1';
			}
			if ( false !== LegacyValue::get( $source, 'Par' . $i . 'Nup', false ) ) {
				$settings[ 'position_after_para_option_' . $i ] = '1';
			}
			if ( false !== LegacyValue::get( $source, 'Par' . $i . 'Con', false ) ) {
				$settings[ 'enable_jump_position_after_para_option_' . $i ] = '1';
			}
		}

		for ( $i = 1; $i <= 1; $i++ ) {
			if ( false !== LegacyValue::get( $source, 'Img' . $i . 'Ads', false ) ) {
				$settings[ 'enable_position_after_image_option_' . $i ] = '1';
			}
			if ( false !== LegacyValue::get( $source, 'Img' . $i . 'Rnd', false ) ) {
				$settings[ 'ad_after_image_option_' . $i ] = '1';
			}
			if ( false !== LegacyValue::get( $source, 'Img' . $i . 'Nup', false ) ) {
				$settings[ 'position_after_image_option_' . $i ] = '1';
			}
			if ( false !== LegacyValue::get( $source, 'Img' . $i . 'Con', false ) ) {
				$settings[ 'enable_jump_position_after_image_option_' . $i ] = '1';
			}
		}

		for ( $i = 1; $i <= 10; $i++ ) {
			$settings[ 'onpost_ad_' . $i . '_content' ]   = LegacyValue::get( $source, 'AdsCode' . $i, $settings[ 'onpost_ad_' . $i . '_content' ] );
			$settings[ 'onpost_ad_' . $i . '_alignment' ] = LegacyValue::get( $source, 'AdsAdsAlignMargin' . $i, $settings[ 'onpost_ad_' . $i . '_alignment' ] );
			$settings[ 'onpost_ad_' . $i . '_margin' ]    = LegacyValue::get( $source, 'AdsMargin' . $i, $settings[ 'onpost_ad_' . $i . '_margin' ] );
			$settings[ 'widget_ad_' . $i . '_content' ]   = LegacyValue::get( $source, 'WidCode' . $i, $settings[ 'widget_ad_' . $i . '_content' ] );
		}

		$options->update( SettingsRepository::OPTION, $settings );
		$options->update( self::BACKUP_OPTION, $source );
		$options->delete( self::SOURCE_OPTION );
	}

	public function describe(): string {
		return 'Migrate legacy v2.0 options to modern settings';
	}
}
