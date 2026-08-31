<?php
/**
 * Admin dashboard (Home tab): status overview and quick actions.
 *
 * @package QuickAdsense
 *
 * @var array<string, mixed> $context
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Included inside PhpViewRenderer::render(), so these variables are method-local.

$tab            = (string) $context['tab'];
$qa_wp_version  = get_bloginfo( 'version' );
$qa_php_version = PHP_VERSION;
$qa_plugin_ver  = defined( 'ADSENSE_REVGUARD_VERSION' ) ? ADSENSE_REVGUARD_VERSION : '3.0.0';
?>
<div class="wrap qa-wrap">
	<?php require __DIR__ . '/partials/page-header.php'; ?>

	<div class="qa-grid">
		<div class="qa-tile">
			<p class="qa-tile-label"><?php echo esc_html__( 'Plugin status', 'adsense-revguard' ); ?></p>
			<p class="qa-tile-value"><span class="qa-dot"></span> <?php echo esc_html__( 'Active', 'adsense-revguard' ); ?></p>
		</div>
		<div class="qa-tile">
			<p class="qa-tile-label"><?php echo esc_html__( 'Version', 'adsense-revguard' ); ?></p>
			<p class="qa-tile-value"><?php echo esc_html( $qa_plugin_ver ); ?></p>
		</div>
		<div class="qa-tile">
			<p class="qa-tile-label"><?php echo esc_html__( 'PHP compatibility', 'adsense-revguard' ); ?></p>
			<p class="qa-tile-value"><span class="qa-dot"></span> <?php echo esc_html( $qa_php_version ); ?></p>
		</div>
		<div class="qa-tile">
			<p class="qa-tile-label"><?php echo esc_html__( 'WordPress compatibility', 'adsense-revguard' ); ?></p>
			<p class="qa-tile-value"><span class="qa-dot"></span> <?php echo esc_html( $qa_wp_version ); ?></p>
		</div>
	</div>

	<div class="qa-card">
		<h2><?php echo esc_html__( 'Welcome to AdSense RevGuard', 'adsense-revguard' ); ?></h2>
		<p class="qa-card-subtitle">
			<?php echo esc_html__( 'A quick, flexible way to insert Google AdSense or any ad code into your WordPress site.', 'adsense-revguard' ); ?>
		</p>
		<div class="qa-cta-row">
			<a class="qa-btn qa-btn-primary" href="<?php echo esc_url( add_query_arg( 'tab', 'settings', admin_url( 'admin.php?page=adsense-revguard' ) ) ); ?>">
				<?php echo esc_html__( 'Configure Ads', 'adsense-revguard' ); ?>
			</a>
			<a class="qa-btn qa-btn-secondary" href="<?php echo esc_url( add_query_arg( 'tab', 'pro', admin_url( 'admin.php?page=adsense-revguard' ) ) ); ?>">
				<?php echo esc_html__( 'View Pro Features', 'adsense-revguard' ); ?>
			</a>
		</div>
	</div>

	<div class="qa-card">
		<p class="qa-section-title"><?php echo esc_html__( 'Quick summary', 'adsense-revguard' ); ?></p>
		<div class="qa-grid">
			<div class="qa-tile">
				<p class="qa-tile-label"><?php echo esc_html__( 'Ad slots available', 'adsense-revguard' ); ?></p>
				<p class="qa-tile-value"><?php echo esc_html__( '10', 'adsense-revguard' ); ?></p>
			</div>
			<div class="qa-tile">
				<p class="qa-tile-label"><?php echo esc_html__( 'Placement rules', 'adsense-revguard' ); ?></p>
				<p class="qa-tile-value"><?php echo esc_html__( 'Beginning, middle, end & more', 'adsense-revguard' ); ?></p>
			</div>
			<div class="qa-tile">
				<p class="qa-tile-label"><?php echo esc_html__( 'Data protection', 'adsense-revguard' ); ?></p>
				<p class="qa-tile-value"><span class="qa-dot"></span> <?php echo esc_html__( 'Capability & nonce checked', 'adsense-revguard' ); ?></p>
			</div>
		</div>
	</div>

	<?php require __DIR__ . '/partials/page-footer.php'; ?>
</div>
<?php // phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
