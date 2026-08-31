<?php
/**
 * Settings schema.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Settings;

/**
 * Classifies settings keys into field types. Because the legacy key space is
 * generated (loops of onpost_ad_{i}_*, after_para_option_{n}, per-ad targeting
 * flags), classification is pattern-based rather than an exhaustive list.
 */
final class Schema {

	/**
	 * Determine the field type for a settings key.
	 */
	public static function typeOf( string $key ): FieldType {
		if ( self::isAdCode( $key ) ) {
			return FieldType::AdCode;
		}

		if ( str_contains( $key, '_limit_visitor_country' ) ) {
			return FieldType::Multiselect;
		}

		if ( self::isCheckbox( $key ) ) {
			return FieldType::Checkbox;
		}

		if ( self::isInt( $key ) ) {
			return FieldType::IntValue;
		}

		return FieldType::Text;
	}

	private static function isAdCode( string $key ): bool {
		return str_ends_with( $key, '_content' )
			|| 'header_embed_code' === $key
			|| 'footer_embed_code' === $key;
	}

	private static function isCheckbox( string $key ): bool {
		return str_starts_with( $key, 'enable_' )
			|| str_starts_with( $key, 'disable_' )
			|| str_contains( $key, '_hide_' )
			|| str_ends_with( $key, '_enable_stats' )
			|| 'onpost_enable_global_style' === $key
			|| 'widget_enable_global_style' === $key;
	}

	private static function isInt( string $key ): bool {
		return 'max_ads_per_page' === $key
			|| str_starts_with( $key, 'ad_' )
			|| str_starts_with( $key, 'position_after_' )
			|| str_ends_with( $key, '_alignment' )
			|| str_ends_with( $key, '_margin' );
	}
}
