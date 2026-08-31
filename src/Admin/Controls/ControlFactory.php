<?php
/**
 * Escaping-by-default admin form controls.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Admin\Controls;

final class ControlFactory {

	public function checkbox( string $key, bool $checked, string $label ): string {
		return sprintf(
			'<label><input type="checkbox" name="adsense_revguard_settings[%1$s]" value="1"%2$s> %3$s</label>',
			esc_attr( $key ),
			checked( $checked, true, false ),
			esc_html( $label )
		);
	}

	/**
	 * @param array<string, string> $options Select values and labels.
	 */
	public function select( string $key, string $value, array $options ): string {
		$html = sprintf(
			'<select id="adsense_revguard_%1$s" name="adsense_revguard_settings[%1$s]">',
			esc_attr( $key )
		);

		foreach ( $options as $option_value => $label ) {
			$html .= sprintf(
				'<option value="%1$s"%2$s>%3$s</option>',
				esc_attr( $option_value ),
				selected( $value, $option_value, false ),
				esc_html( $label )
			);
		}

		return $html . '</select>';
	}

	public function number( string $key, int $value, int $minimum, int $maximum ): string {
		return sprintf(
			'<input type="number" class="small-text" id="adsense_revguard_%1$s" name="adsense_revguard_settings[%1$s]" value="%2$d" min="%3$d" max="%4$d">',
			esc_attr( $key ),
			$value,
			$minimum,
			$maximum
		);
	}

	public function textarea( string $key, string $value, int $rows = 8 ): string {
		return sprintf(
			'<textarea class="large-text code" id="adsense_revguard_%1$s" name="adsense_revguard_settings[%1$s]" rows="%2$d">%3$s</textarea>',
			esc_attr( $key ),
			$rows,
			esc_textarea( $value )
		);
	}
}
