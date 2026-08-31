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
$qa_plugin_ver  = defined( 'REVGUARD_FOR_ADSENSE_VERSION' ) ? REVGUARD_FOR_ADSENSE_VERSION : '3.0.0';
?>
<div class="wrap qa-wrap">
	<?php require __DIR__ . '/partials/page-header.php'; ?>

	<div class="qa-grid">
		<div class="qa-tile">
			<p class="qa-tile-label"><?php echo esc_html__( 'Plugin status', 'revguard-for-adsense' ); ?></p>
			<p class="qa-tile-value"><span class="qa-dot"></span> <?php echo esc_html__( 'Active', 'revguard-for-adsense' ); ?></p>
		</div>
		<div class="qa-tile">
			<p class="qa-tile-label"><?php echo esc_html__( 'Version', 'revguard-for-adsense' ); ?></p>
			<p class="qa-tile-value"><?php echo esc_html( $qa_plugin_ver ); ?></p>
		</div>
		<div class="qa-tile">
			<p class="qa-tile-label"><?php echo esc_html__( 'PHP compatibility', 'revguard-for-adsense' ); ?></p>
			<p class="qa-tile-value"><span class="qa-dot"></span> <?php echo esc_html( $qa_php_version ); ?></p>
		</div>
		<div class="qa-tile">
			<p class="qa-tile-label"><?php echo esc_html__( 'WordPress compatibility', 'revguard-for-adsense' ); ?></p>
			<p class="qa-tile-value"><span class="qa-dot"></span> <?php echo esc_html( $qa_wp_version ); ?></p>
		</div>
	</div>

	<div class="qa-card">
		<h2><?php echo esc_html__( 'Welcome to RevGuard for AdSense', 'revguard-for-adsense' ); ?></h2>
		<p class="qa-card-subtitle">
			<?php echo esc_html__( 'A quick, flexible way to insert Google AdSense or any ad code into your WordPress site.', 'revguard-for-adsense' ); ?>
		</p>
		<div class="qa-cta-row">
			<a class="qa-btn qa-btn-primary" href="<?php echo esc_url( add_query_arg( 'tab', 'settings', admin_url( 'admin.php?page=revguard-for-adsense' ) ) ); ?>">
				<?php echo esc_html__( 'Configure Ads', 'revguard-for-adsense' ); ?>
			</a>
		</div>
	</div>

	<div class="qa-card">
		<p class="qa-section-title"><?php echo esc_html__( 'Quick summary', 'revguard-for-adsense' ); ?></p>
		<div class="qa-grid">
			<div class="qa-tile">
				<p class="qa-tile-label"><?php echo esc_html__( 'Ad slots available', 'revguard-for-adsense' ); ?></p>
				<p class="qa-tile-value"><?php echo esc_html( (string) 10 ); ?></p>
			</div>
			<div class="qa-tile">
				<p class="qa-tile-label"><?php echo esc_html__( 'Placement rules', 'revguard-for-adsense' ); ?></p>
				<p class="qa-tile-value"><?php echo esc_html__( 'Beginning, middle, end & more', 'revguard-for-adsense' ); ?></p>
			</div>
			<div class="qa-tile">
				<p class="qa-tile-label"><?php echo esc_html__( 'Data protection', 'revguard-for-adsense' ); ?></p>
				<p class="qa-tile-value"><span class="qa-dot"></span> <?php echo esc_html__( 'Capability & nonce checked', 'revguard-for-adsense' ); ?></p>
			</div>
		</div>
	</div>

	<?php require __DIR__ . '/partials/page-footer.php'; ?>
</div>
<?php // phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
