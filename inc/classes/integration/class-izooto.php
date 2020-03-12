<?php
/**
 * Izooto integration with PWA plugin.
 *
 * @package rt-pwa-extensions
 */

namespace RT\PWA\Inc\Integration;

use \RT\PWA\Inc\Traits\Singleton;

/**
 * Izooto integration with PWA plugin.
 */
class Izooto {

	use Singleton;

	/**
	 * Initialization
	 */
	protected function __construct() {

		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		if ( ! is_plugin_active( 'izooto-web-push/izooto.php' ) ) {
			return;
		}

		add_action( 'wp_front_service_worker', array( $this, 'register_izooto_service_worker_script' ) );

		// Remove izooto service worker so it does not conflict with PWA plugin service worker.
		add_action(
			'init',
			function() {
				remove_action( 'parse_request', 'izooto_sdk_files' );
			}
		);

	}

	/**
	 * Register Izooto service workers scripts.
	 *
	 * @param object $scripts scripts object.
	 *
	 * @return void
	 */
	public function register_izooto_service_worker_script( $scripts ) {
		$scripts->register(
			'izooto-workers',
			array(
				'src' => function() {

					require_once WP_PLUGIN_DIR . '/izooto-web-push/includes/class-init.php';

					$obj = new \Init();
					$opfunction = $obj->iz_get_option( 'izooto-settings' );

					// Bail out if izooto not configured correctly.
					if ( empty( $opfunction['sw'] ) ) {
						return;
					}

					return sprintf( 'var izCacheVer = 1; importScripts("%1$s");', esc_url_raw( 'https://' . $opfunction['sw'] ) );
				},
			)
		);

	}

}
