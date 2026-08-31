<?php
/**
 * Admin About tab: product description, rebuild rationale, compatibility, security.
 *
 * @package QuickAdsense
 *
 * @var array<string, mixed> $context
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Included inside PhpViewRenderer::render(), so these variables are method-local.

$tab           = (string) $context['tab'];
$qa_plugin_ver = defined( 'REVGUARD_FOR_ADSENSE_VERSION' ) ? REVGUARD_FOR_ADSENSE_VERSION : '3.0.0';
?>
<div class="wrap qa-wrap">
	<?php require __DIR__ . '/partials/page-header.php'; ?>

	<div class="qa-card">
		<h2><?php echo esc_html__( 'What RevGuard for AdSense does', 'revguard-for-adsense' ); ?></h2>
		<p class="qa-card-subtitle">
			<?php echo esc_html__( 'RevGuard for AdSense inserts Google AdSense or any ad code into your WordPress content at configurable positions, without theme edits.', 'revguard-for-adsense' ); ?>
		</p>
	</div>

	<div class="qa-card">
		<p class="qa-section-title"><?php echo esc_html__( 'Why this rebuild exists', 'revguard-for-adsense' ); ?></p>
		<p>
			<?php echo esc_html__( 'Version 3.0.0 is a compatibility-focused rebuild on a modern, namespaced PHP architecture. It preserves existing legacy configuration and content control tokens while bringing the codebase up to current WordPress and PHP standards.', 'revguard-for-adsense' ); ?>
		</p>
	</div>

	<div class="qa-grid">
		<div class="qa-tile">
			<p class="qa-tile-label"><?php echo esc_html__( 'PHP compatibility', 'revguard-for-adsense' ); ?></p>
			<p class="qa-tile-value"><span class="qa-dot"></span> <?php echo esc_html__( 'PHP 8.2+', 'revguard-for-adsense' ); ?></p>
		</div>
		<div class="qa-tile">
			<p class="qa-tile-label"><?php echo esc_html__( 'WordPress compatibility', 'revguard-for-adsense' ); ?></p>
			<p class="qa-tile-value"><span class="qa-dot"></span> <?php echo esc_html__( 'WordPress 6.0+', 'revguard-for-adsense' ); ?></p>
		</div>
		<div class="qa-tile">
			<p class="qa-tile-label"><?php echo esc_html__( 'Current version', 'revguard-for-adsense' ); ?></p>
			<p class="qa-tile-value"><?php echo esc_html( $qa_plugin_ver ); ?></p>
		</div>
	</div>

	<div class="qa-card">
		<p class="qa-section-title"><?php echo esc_html__( 'Security highlights', 'revguard-for-adsense' ); ?></p>
		<ul>
			<li><?php echo esc_html__( 'Settings changes require the manage_options capability and a verified nonce.', 'revguard-for-adsense' ); ?></li>
			<li><?php echo esc_html__( 'Stored ad and embed code is filtered through an approved ad-code policy at every output boundary.', 'revguard-for-adsense' ); ?></li>
			<li><?php echo esc_html__( 'No telemetry and no third-party API calls are made by the plugin.', 'revguard-for-adsense' ); ?></li>
		</ul>
	</div>

	<div class="qa-card">
		<p class="qa-section-title"><?php echo esc_html__( 'Migration highlights', 'revguard-for-adsense' ); ?></p>
		<ul>
			<li><?php echo esc_html__( 'Existing settings from earlier versions are detected and migrated automatically on activation.', 'revguard-for-adsense' ); ?></li>
			<li><?php echo esc_html__( 'Unknown legacy option keys already stored are retained rather than discarded.', 'revguard-for-adsense' ); ?></li>
			<li><?php echo esc_html__( 'Deactivating the plugin preserves your settings; only an explicit Delete removes plugin-owned data.', 'revguard-for-adsense' ); ?></li>
		</ul>
	</div>

	<?php require __DIR__ . '/partials/page-footer.php'; ?>
</div>
<?php // phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
