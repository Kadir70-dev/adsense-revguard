<?php
/**
 * Shared admin header: branding, badges, and tab navigation.
 *
 * @package QuickAdsense
 *
 * @var string $tab Active tab slug.
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Included inside PhpViewRenderer::render(), so these variables are method-local.

$qa_tabs = array(
	'home'     => __( 'Home', 'adsense-revguard' ),
	'settings' => __( 'Ad Settings', 'adsense-revguard' ),
	'about'    => __( 'About', 'adsense-revguard' ),
	'pro'      => __( 'Upcoming Pro', 'adsense-revguard' ),
);
?>
<div class="qa-header">
	<div class="qa-brand">
		<span class="qa-brand-mark" aria-hidden="true">
			<span class="dashicons dashicons-money-alt"></span>
		</span>
		<div>
			<p class="qa-brand-name"><?php echo esc_html__( 'AdSense RevGuard', 'adsense-revguard' ); ?></p>
			<p class="qa-brand-tagline"><?php echo esc_html__( 'Ad placement, made simple.', 'adsense-revguard' ); ?></p>
		</div>
	</div>
	<div class="qa-badges">
		<span class="qa-badge qa-badge-version">
			<?php
			echo esc_html(
				sprintf(
					/* translators: %s: plugin version number. */
					__( 'v%s', 'adsense-revguard' ),
					defined( 'ADSENSE_REVGUARD_VERSION' ) ? ADSENSE_REVGUARD_VERSION : '3.0.0'
				)
			);
			?>
		</span>
		<span class="qa-badge qa-badge-free"><?php echo esc_html__( 'Free', 'adsense-revguard' ); ?></span>
		<a class="qa-badge qa-badge-pro" href="<?php echo esc_url( add_query_arg( 'tab', 'pro' ) ); ?>">
			<?php echo esc_html__( 'Pro Coming Soon', 'adsense-revguard' ); ?>
		</a>
	</div>
</div>

<nav class="qa-nav" aria-label="<?php echo esc_attr__( 'AdSense RevGuard sections', 'adsense-revguard' ); ?>">
	<?php foreach ( $qa_tabs as $qa_tab_key => $qa_tab_label ) : ?>
		<a
			href="<?php echo esc_url( add_query_arg( 'tab', $qa_tab_key, admin_url( 'admin.php?page=adsense-revguard' ) ) ); ?>"
			class="<?php echo $tab === $qa_tab_key ? 'is-active' : ''; ?>"
		>
			<?php echo esc_html( $qa_tab_label ); ?>
		</a>
	<?php endforeach; ?>
</nav>
<?php // phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
