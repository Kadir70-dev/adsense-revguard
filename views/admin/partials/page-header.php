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
	'home'     => __( 'Home', 'revguard-for-adsense' ),
	'settings' => __( 'Ad Settings', 'revguard-for-adsense' ),
	'about'    => __( 'About', 'revguard-for-adsense' ),
	'pro'      => __( 'Upcoming Pro', 'revguard-for-adsense' ),
);
?>
<div class="qa-header">
	<div class="qa-brand">
		<span class="qa-brand-mark" aria-hidden="true">
			<span class="dashicons dashicons-money-alt"></span>
		</span>
		<div>
			<p class="qa-brand-name"><?php echo esc_html__( 'RevGuard for AdSense', 'revguard-for-adsense' ); ?></p>
			<p class="qa-brand-tagline"><?php echo esc_html__( 'Ad placement, made simple.', 'revguard-for-adsense' ); ?></p>
		</div>
	</div>
	<div class="qa-badges">
		<span class="qa-badge qa-badge-version">
			<?php
			echo esc_html(
				sprintf(
					/* translators: %s: plugin version number. */
					__( 'v%s', 'revguard-for-adsense' ),
					defined( 'REVGUARD_FOR_ADSENSE_VERSION' ) ? REVGUARD_FOR_ADSENSE_VERSION : '3.0.0'
				)
			);
			?>
		</span>
		<span class="qa-badge qa-badge-free"><?php echo esc_html__( 'Free', 'revguard-for-adsense' ); ?></span>
		<a class="qa-badge qa-badge-pro" href="<?php echo esc_url( add_query_arg( 'tab', 'pro' ) ); ?>">
			<?php echo esc_html__( 'Pro Coming Soon', 'revguard-for-adsense' ); ?>
		</a>
	</div>
</div>

<nav class="qa-nav" aria-label="<?php echo esc_attr__( 'RevGuard for AdSense sections', 'revguard-for-adsense' ); ?>">
	<?php foreach ( $qa_tabs as $qa_tab_key => $qa_tab_label ) : ?>
		<a
			href="<?php echo esc_url( add_query_arg( 'tab', $qa_tab_key, admin_url( 'admin.php?page=revguard-for-adsense' ) ) ); ?>"
			class="<?php echo $tab === $qa_tab_key ? 'is-active' : ''; ?>"
		>
			<?php echo esc_html( $qa_tab_label ); ?>
		</a>
	<?php endforeach; ?>
</nav>
<?php // phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
