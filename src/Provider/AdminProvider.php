<?php
/**
 * Admin service provider.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Provider;

use QuickAdsense\Admin\Controls\ControlFactory;
use QuickAdsense\Admin\Menu;
use QuickAdsense\Admin\SettingsPage;
use QuickAdsense\Container;
use QuickAdsense\Contracts\ServiceProviderInterface;
use QuickAdsense\Contracts\SettingsRepositoryInterface;
use QuickAdsense\Contracts\ViewRendererInterface;
use QuickAdsense\Infrastructure\View\PhpViewRenderer;

final class AdminProvider implements ServiceProviderInterface {

	public function register( Container $container ): void {
		$container->singleton(
			ViewRendererInterface::class,
			static fn (): ViewRendererInterface => new PhpViewRenderer( ADSENSE_REVGUARD_PATH . 'views' )
		);
		$container->singleton( ControlFactory::class, static fn (): ControlFactory => new ControlFactory() );
		$container->singleton(
			SettingsPage::class,
			static fn ( Container $c ): SettingsPage => new SettingsPage(
				$c->get( SettingsRepositoryInterface::class ),
				$c->get( ViewRendererInterface::class ),
				$c->get( ControlFactory::class )
			)
		);
		$container->singleton(
			Menu::class,
			static fn ( Container $c ): Menu => new Menu( $c->get( SettingsPage::class ) )
		);
	}

	public function boot( Container $container ): void {
		add_action( 'admin_menu', array( $container->get( Menu::class ), 'register' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueueAssets' ) );
		add_action(
			'admin_post_adsense_revguard_save_settings',
			static function () use ( $container ): void {
				$input = array();
				$nonce = '';

				// phpcs:ignore WordPress.Security.NonceVerification.Missing -- The nonce must be read before it can be verified below.
				if ( isset( $_POST[ SettingsPage::NONCE_NAME ] ) ) {
					// phpcs:ignore WordPress.Security.NonceVerification.Missing -- The nonce must be read before it can be verified below.
					$nonce = sanitize_text_field( wp_unslash( $_POST[ SettingsPage::NONCE_NAME ] ) );
				}

				if ( ! wp_verify_nonce( $nonce, SettingsPage::NONCE_ACTION ) ) {
					$container->get( SettingsPage::class )->save( array(), $nonce );
					return;
				}

				if ( isset( $_POST['adsense_revguard_settings'] ) && is_array( $_POST['adsense_revguard_settings'] ) ) {
					// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- SettingsSanitizer validates each known field.
					$input = wp_unslash( $_POST['adsense_revguard_settings'] );
				}

				$saved = $container->get( SettingsPage::class )->save( $input, $nonce );
				$url   = add_query_arg(
					'adsense_revguard_notice',
					$saved ? 'saved' : 'error',
					admin_url( 'admin.php?page=adsense-revguard&tab=settings' )
				);

				wp_safe_redirect( $url );
				exit;
			}
		);
	}

	/**
	 * Enqueue the admin UI stylesheet, scoped to the AdSense RevGuard screen only.
	 */
	public static function enqueueAssets( string $hook_suffix ): void {
		if ( 'toplevel_page_adsense-revguard' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'adsense-revguard-admin',
			ADSENSE_REVGUARD_URL . 'assets/css/admin.css',
			array(),
			ADSENSE_REVGUARD_VERSION
		);
	}
}
