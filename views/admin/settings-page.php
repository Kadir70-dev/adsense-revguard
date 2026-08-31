<?php
/**
 * Main admin settings view.
 *
 * @package QuickAdsense
 *
 * @var array<string, mixed> $context
 */

use QuickAdsense\Admin\Controls\ControlFactory;
use QuickAdsense\Admin\SettingsPage;
use QuickAdsense\Settings\Settings;

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Included inside PhpViewRenderer::render(), so these variables are method-local.

$settings = $context['settings'];
$controls = $context['controls'];
$notice   = (string) $context['notice'];
$tab      = (string) $context['tab'];

if ( ! $settings instanceof Settings || ! $controls instanceof ControlFactory ) {
	return;
}

$ad_options = array( '0' => __( 'Random ad', 'adsense-revguard' ) );
for ( $ad_index = 1; $ad_index <= 10; $ad_index++ ) {
	$ad_options[ (string) $ad_index ] = sprintf(
		/* translators: %d: ad slot number. */
		__( 'Ad %d', 'adsense-revguard' ),
		$ad_index
	);
}

$alignment_options = array(
	'1' => __( 'Left', 'adsense-revguard' ),
	'2' => __( 'Center', 'adsense-revguard' ),
	'3' => __( 'Right', 'adsense-revguard' ),
	'4' => __( 'None', 'adsense-revguard' ),
);
?>
<div class="wrap qa-wrap">
	<?php require __DIR__ . '/partials/page-header.php'; ?>

	<?php if ( 'saved' === $notice ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html__( 'Settings saved.', 'adsense-revguard' ); ?></p></div>
	<?php elseif ( 'error' === $notice ) : ?>
		<div class="notice notice-error"><p><?php echo esc_html__( 'Settings could not be saved.', 'adsense-revguard' ); ?></p></div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="adsense_revguard_save_settings">
		<?php wp_nonce_field( SettingsPage::NONCE_ACTION, SettingsPage::NONCE_NAME ); ?>

		<div class="qa-card">
		<h2><?php echo esc_html__( 'General settings', 'adsense-revguard' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="adsense_revguard_max_ads_per_page"><?php echo esc_html__( 'Maximum ads per page', 'adsense-revguard' ); ?></label></th>
				<td>
					<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ControlFactory escapes every dynamic value. ?>
					<?php echo $controls->number( 'max_ads_per_page', $settings->int( 'max_ads_per_page' ), 0, 10 ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php echo esc_html__( 'Global ad styling', 'adsense-revguard' ); ?></th>
				<td>
					<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ControlFactory escapes every dynamic value. ?>
					<?php echo $controls->checkbox( 'onpost_enable_global_style', $settings->bool( 'onpost_enable_global_style' ), __( 'Use one alignment and margin for all post ads', 'adsense-revguard' ) ); ?>
					<p>
						<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ControlFactory escapes every dynamic value. ?>
						<?php echo $controls->select( 'onpost_global_alignment', $settings->string( 'onpost_global_alignment' ), $alignment_options ); ?>
						<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ControlFactory escapes every dynamic value. ?>
						<?php echo $controls->number( 'onpost_global_margin', $settings->int( 'onpost_global_margin' ), 0, 1000 ); ?>
						<?php echo esc_html__( 'pixels margin', 'adsense-revguard' ); ?>
					</p>
				</td>
			</tr>
		</table>
		</div>

		<div class="qa-card">
		<h2><?php echo esc_html__( 'Position settings', 'adsense-revguard' ); ?></h2>
		<table class="form-table" role="presentation">
			<?php
			$fixed_positions = array(
				'beginning_of_post' => __( 'Beginning of post', 'adsense-revguard' ),
				'middle_of_post'    => __( 'Middle of post', 'adsense-revguard' ),
				'end_of_post'       => __( 'End of post', 'adsense-revguard' ),
				'after_more_tag'    => __( 'After the More tag', 'adsense-revguard' ),
				'before_last_para'  => __( 'Before the last paragraph', 'adsense-revguard' ),
			);
			foreach ( $fixed_positions as $position_key => $position_label ) :
				$enabled_key = 'enable_position_' . $position_key;
				$ad_key      = 'ad_' . $position_key;
				?>
				<tr>
					<th scope="row"><?php echo esc_html( $position_label ); ?></th>
					<td>
						<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ControlFactory escapes every dynamic value. ?>
						<?php echo $controls->checkbox( $enabled_key, $settings->bool( $enabled_key ), __( 'Enable', 'adsense-revguard' ) ); ?>
						<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ControlFactory escapes every dynamic value. ?>
						<?php echo $controls->select( $ad_key, $settings->string( $ad_key ), $ad_options ); ?>
					</td>
				</tr>
			<?php endforeach; ?>

			<?php for ( $position_index = 1; $position_index <= 3; $position_index++ ) : ?>
				<?php
				$enabled_key = 'enable_position_after_para_option_' . $position_index;
				$ad_key      = 'ad_after_para_option_' . $position_index;
				$count_key   = 'position_after_para_option_' . $position_index;
				$jump_key    = 'enable_jump_position_after_para_option_' . $position_index;
				?>
				<tr>
					<th scope="row">
						<?php
						echo esc_html(
							sprintf(
								/* translators: %d: position rule number. */
								__( 'After paragraph rule %d', 'adsense-revguard' ),
								$position_index
							)
						);
						?>
					</th>
					<td>
						<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ControlFactory escapes every dynamic value. ?>
						<?php echo $controls->checkbox( $enabled_key, $settings->bool( $enabled_key ), __( 'Enable', 'adsense-revguard' ) ); ?>
						<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ControlFactory escapes every dynamic value. ?>
						<?php echo $controls->select( $ad_key, $settings->string( $ad_key ), $ad_options ); ?>
						<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ControlFactory escapes every dynamic value. ?>
						<?php echo $controls->number( $count_key, $settings->int( $count_key ), 1, 100000 ); ?>
						<?php echo esc_html__( 'paragraphs', 'adsense-revguard' ); ?>
						<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ControlFactory escapes every dynamic value. ?>
						<?php echo $controls->checkbox( $jump_key, $settings->bool( $jump_key ), __( 'Count from the end', 'adsense-revguard' ) ); ?>
					</td>
				</tr>
			<?php endfor; ?>

			<tr>
				<th scope="row"><?php echo esc_html__( 'After image rule', 'adsense-revguard' ); ?></th>
				<td>
					<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ControlFactory escapes every dynamic value. ?>
					<?php echo $controls->checkbox( 'enable_position_after_image_option_1', $settings->bool( 'enable_position_after_image_option_1' ), __( 'Enable', 'adsense-revguard' ) ); ?>
					<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ControlFactory escapes every dynamic value. ?>
					<?php echo $controls->select( 'ad_after_image_option_1', $settings->string( 'ad_after_image_option_1' ), $ad_options ); ?>
					<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ControlFactory escapes every dynamic value. ?>
					<?php echo $controls->number( 'position_after_image_option_1', $settings->int( 'position_after_image_option_1' ), 1, 100000 ); ?>
					<?php echo esc_html__( 'images', 'adsense-revguard' ); ?>
					<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ControlFactory escapes every dynamic value. ?>
					<?php echo $controls->checkbox( 'enable_jump_position_after_image_option_1', $settings->bool( 'enable_jump_position_after_image_option_1' ), __( 'Count from the end', 'adsense-revguard' ) ); ?>
				</td>
			</tr>
		</table>
		</div>

		<div class="qa-card">
		<h2><?php echo esc_html__( 'Display conditions', 'adsense-revguard' ); ?></h2>
		<fieldset>
			<?php
			$display_conditions = array(
				'enable_on_posts'              => __( 'Posts', 'adsense-revguard' ),
				'enable_on_pages'              => __( 'Pages', 'adsense-revguard' ),
				'enable_on_homepage'           => __( 'Homepage', 'adsense-revguard' ),
				'enable_on_categories'         => __( 'Category archives', 'adsense-revguard' ),
				'enable_on_archives'           => __( 'Other archives', 'adsense-revguard' ),
				'enable_on_tags'               => __( 'Tag archives', 'adsense-revguard' ),
				'enable_all_possible_ads'      => __( 'Show all possible ads when explicitly requested', 'adsense-revguard' ),
				'disable_for_loggedin_users'   => __( 'Disable ads for logged-in users', 'adsense-revguard' ),
				'disable_widgets_on_homepage'  => __( 'Disable ad widgets on the homepage', 'adsense-revguard' ),
			);
			foreach ( $display_conditions as $condition_key => $condition_label ) :
				?>
				<p>
					<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ControlFactory escapes every dynamic value. ?>
					<?php echo $controls->checkbox( $condition_key, $settings->bool( $condition_key ), $condition_label ); ?>
				</p>
			<?php endforeach; ?>
		</fieldset>
		</div>

		<div class="qa-card">
		<h2><?php echo esc_html__( 'Ad slots 1–10', 'adsense-revguard' ); ?></h2>
		<?php for ( $ad_index = 1; $ad_index <= 10; $ad_index++ ) : ?>
			<?php
			$content_key   = 'onpost_ad_' . $ad_index . '_content';
			$alignment_key = 'onpost_ad_' . $ad_index . '_alignment';
			$margin_key    = 'onpost_ad_' . $ad_index . '_margin';
			?>
			<details>
				<summary>
					<?php
					echo esc_html(
						sprintf(
							/* translators: %d: ad slot number. */
							__( 'Ad slot %d', 'adsense-revguard' ),
							$ad_index
						)
					);
					?>
				</summary>
				<p>
					<label for="<?php echo esc_attr( 'adsense_revguard_' . $content_key ); ?>"><?php echo esc_html__( 'Ad code', 'adsense-revguard' ); ?></label>
					<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ControlFactory escapes every dynamic value. ?>
					<?php echo $controls->textarea( $content_key, $settings->string( $content_key ) ); ?>
				</p>
				<p>
					<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ControlFactory escapes every dynamic value. ?>
					<?php echo $controls->select( $alignment_key, $settings->string( $alignment_key ), $alignment_options ); ?>
					<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ControlFactory escapes every dynamic value. ?>
					<?php echo $controls->number( $margin_key, $settings->int( $margin_key ), 0, 1000 ); ?>
					<?php echo esc_html__( 'pixels margin', 'adsense-revguard' ); ?>
				</p>
			</details>
		<?php endfor; ?>
		</div>

		<div class="qa-card">
		<h2><?php echo esc_html__( 'Header and footer code', 'adsense-revguard' ); ?></h2>
		<p><?php echo esc_html__( 'Code is stored raw for compatibility and filtered by the approved ad-code policy only when output.', 'adsense-revguard' ); ?></p>
		<p>
			<label for="adsense_revguard_header_embed_code"><?php echo esc_html__( 'Header code', 'adsense-revguard' ); ?></label>
			<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ControlFactory escapes every dynamic value. ?>
			<?php echo $controls->textarea( 'header_embed_code', $settings->string( 'header_embed_code' ), 5 ); ?>
		</p>
		<p>
			<label for="adsense_revguard_footer_embed_code"><?php echo esc_html__( 'Footer code', 'adsense-revguard' ); ?></label>
			<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ControlFactory escapes every dynamic value. ?>
			<?php echo $controls->textarea( 'footer_embed_code', $settings->string( 'footer_embed_code' ), 5 ); ?>
		</p>
		</div>

		<div class="qa-card">
		<h2><?php echo esc_html__( 'Control-token help', 'adsense-revguard' ); ?></h2>
		<p><code>&lt;!--NoAds--&gt;</code>, <code>&lt;!--OffDef--&gt;</code>, <code>&lt;!--OffBegin--&gt;</code>, <code>&lt;!--OffMiddle--&gt;</code>, <code>&lt;!--OffEnd--&gt;</code>, <code>&lt;!--OffAfMore--&gt;</code>, <code>&lt;!--OffBfMore--&gt;</code>, <code>&lt;!--Ads1--&gt;</code> … <code>&lt;!--Ads10--&gt;</code>, <code>&lt;!--RndAds--&gt;</code></p>
		</div>

		<?php submit_button( __( 'Save settings', 'adsense-revguard' ) ); ?>
	</form>

	<?php require __DIR__ . '/partials/page-footer.php'; ?>
</div>
<?php // phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
