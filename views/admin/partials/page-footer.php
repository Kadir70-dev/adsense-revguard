<?php
/**
 * Shared admin footer: documentation and support placeholders.
 *
 * @package QuickAdsense
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="qa-footer">
	<span>
		<?php
		echo esc_html(
			sprintf(
				/* translators: %s: plugin version number. */
				__( 'RevGuard for AdSense v%s', 'revguard-for-adsense' ),
				defined( 'REVGUARD_FOR_ADSENSE_VERSION' ) ? REVGUARD_FOR_ADSENSE_VERSION : '3.0.0'
			)
		);
		?>
	</span>
</div>
