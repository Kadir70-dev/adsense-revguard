<?php
/**
 * Default settings.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Settings;

/**
 * The canonical default configuration.
 *
 * This is a faithful port of the legacy quick_adsense_get_defaults(): key names,
 * order, and string values are preserved so the golden-master `settings/defaults`
 * snapshot matches byte-for-byte. Do not "improve" the values here — backward
 * compatibility depends on parity.
 */
final class Defaults {

	/**
	 * The complete default settings array.
	 *
	 * @return array<string, mixed>
	 */
	public static function all(): array {
		$settings = array();

		$settings['max_ads_per_page'] = '3';

		$settings['enable_position_beginning_of_post'] = '1';
		$settings['ad_beginning_of_post']              = '1';
		$settings['enable_position_middle_of_post']    = '0';
		$settings['ad_middle_of_post']                 = '0';
		$settings['enable_position_end_of_post']       = '1';
		$settings['ad_end_of_post']                    = '0';

		$settings['enable_position_after_more_tag']   = '0';
		$settings['ad_after_more_tag']                = '0';
		$settings['enable_position_before_last_para'] = '0';
		$settings['ad_before_last_para']              = '0';

		for ( $i = 1; $i <= 3; $i++ ) {
			$settings[ 'enable_position_after_para_option_' . $i ]      = '0';
			$settings[ 'ad_after_para_option_' . $i ]                   = '0';
			$settings[ 'position_after_para_option_' . $i ]             = '1';
			$settings[ 'enable_jump_position_after_para_option_' . $i ] = '0';
		}

		for ( $i = 1; $i <= 1; $i++ ) {
			$settings[ 'enable_position_after_image_option_' . $i ]      = '0';
			$settings[ 'ad_after_image_option_' . $i ]                   = '0';
			$settings[ 'position_after_image_option_' . $i ]             = '1';
			$settings[ 'enable_jump_position_after_image_option_' . $i ] = '0';
		}

		$settings['enable_on_posts'] = '1';
		$settings['enable_on_pages'] = '1';

		$settings['enable_on_homepage']      = '0';
		$settings['enable_on_categories']    = '0';
		$settings['enable_on_archives']      = '0';
		$settings['enable_on_tags']          = '0';
		$settings['enable_all_possible_ads'] = '0';

		$settings['disable_widgets_on_homepage'] = '0';

		$settings['disable_for_loggedin_users'] = '0';

		$settings['enable_quicktag_buttons']             = '1';
		$settings['disable_randomads_quicktag_button']   = '0';
		$settings['disable_disablead_quicktag_buttons']  = '0';
		$settings['disable_positionad_quicktag_buttons'] = '0';

		$settings['onpost_enable_global_style'] = '0';
		$settings['onpost_global_alignment']    = '2';
		$settings['onpost_global_margin']       = '10';

		for ( $i = 1; $i <= 10; $i++ ) {
			$settings[ 'onpost_ad_' . $i . '_content' ]   = '';
			$settings[ 'onpost_ad_' . $i . '_alignment' ] = '2';
			$settings[ 'onpost_ad_' . $i . '_margin' ]    = '10';

			$settings[ 'widget_ad_' . $i . '_content' ] = '';
		}

		return $settings;
	}
}
