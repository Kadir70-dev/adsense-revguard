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
				__( 'AdSense RevGuard v%s', 'adsense-revguard' ),
				defined( 'ADSENSE_REVGUARD_VERSION' ) ? ADSENSE_REVGUARD_VERSION : '3.0.0'
			)
		);
		?>
	</span>
</div>
