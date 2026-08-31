<?php
/**
 * Admin Upcoming Pro tab: presentation-only locked feature cards.
 *
 * No Pro functionality is implemented here — display only.
 *
 * @package QuickAdsense
 *
 * @var array<string, mixed> $context
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Included inside PhpViewRenderer::render(), so these variables are method-local.

$tab = (string) $context['tab'];

$qa_pro_features = array(
	array(
		'icon'  => 'dashicons-tag',
		'title' => __( 'Advanced Targeting', 'revguard-for-adsense' ),
		'desc'  => __( 'Target ads by category, tag, author, or custom rule sets.', 'revguard-for-adsense' ),
	),
	array(
		'icon'  => 'dashicons-chart-bar',
		'title' => __( 'Analytics', 'revguard-for-adsense' ),
		'desc'  => __( 'See impressions and clicks per ad slot, right in your dashboard.', 'revguard-for-adsense' ),
	),
	array(
		'icon'  => 'dashicons-randomize',
		'title' => __( 'A/B Testing', 'revguard-for-adsense' ),
		'desc'  => __( 'Run multiple ad variants and compare performance automatically.', 'revguard-for-adsense' ),
	),
	array(
		'icon'  => 'dashicons-clock',
		'title' => __( 'Scheduling', 'revguard-for-adsense' ),
		'desc'  => __( 'Show or hide ads automatically on a date and time schedule.', 'revguard-for-adsense' ),
	),
	array(
		'icon'  => 'dashicons-smartphone',
		'title' => __( 'Device Targeting', 'revguard-for-adsense' ),
		'desc'  => __( 'Serve different ad code to desktop, tablet, and mobile visitors.', 'revguard-for-adsense' ),
	),
	array(
		'icon'  => 'dashicons-admin-site-alt3',
		'title' => __( 'Geo Targeting', 'revguard-for-adsense' ),
		'desc'  => __( 'Target ad placements by visitor country or region.', 'revguard-for-adsense' ),
	),
	array(
		'icon'  => 'dashicons-sos',
		'title' => __( 'Priority Support', 'revguard-for-adsense' ),
		'desc'  => __( 'Faster response times from the RevGuard for AdSense support team.', 'revguard-for-adsense' ),
	),
);
?>
<div class="wrap qa-wrap">
	<?php require __DIR__ . '/partials/page-header.php'; ?>

	<div class="qa-card">
		<h2><?php echo esc_html__( 'Upcoming Pro features', 'revguard-for-adsense' ); ?></h2>
		<p class="qa-card-subtitle">
			<?php echo esc_html__( 'A preview of what is planned for RevGuard for AdSense Pro. These features are not yet available and nothing below changes how the free plugin works today.', 'revguard-for-adsense' ); ?>
		</p>
	</div>

	<div class="qa-pro-grid">
		<?php foreach ( $qa_pro_features as $qa_feature ) : ?>
			<div class="qa-pro-card">
				<span class="qa-pro-lock dashicons dashicons-lock" aria-hidden="true"></span>
				<span class="qa-pro-card-icon">
					<span class="dashicons <?php echo esc_attr( $qa_feature['icon'] ); ?>" aria-hidden="true"></span>
				</span>
				<h3><?php echo esc_html( $qa_feature['title'] ); ?></h3>
				<p><?php echo esc_html( $qa_feature['desc'] ); ?></p>
				<span class="qa-pro-soon"><?php echo esc_html__( 'Coming soon', 'revguard-for-adsense' ); ?></span>
			</div>
		<?php endforeach; ?>
	</div>

	<?php require __DIR__ . '/partials/page-footer.php'; ?>
</div>
<?php // phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
