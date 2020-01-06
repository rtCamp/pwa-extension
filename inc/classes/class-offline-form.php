<?php
/**
 * Plugin offline comment class.
 *
 * @package rt-pwa-extensions
 */

namespace RT\PWA\Inc;

use \RT\PWA\Inc\Traits\Singleton;

/**
 * Class Plugin
 */
class Offline_Form {

	use Singleton;

	/**
	 * Construct method.
	 */
	protected function __construct() {

		$this->setup_hooks();

	}

	/**
	 * Setup actions/filters
	 *
	 * @return void
	 */
	protected function setup_hooks() {

		add_action( 'wp_front_service_worker', array( $this, 'offline_form_service_worker' ), 11 );

	}

	/**
	 * Register service worker script for offline form submit.
	 *
	 * @param $scripts
	 */
	public function offline_form_service_worker( $scripts ) {

		$scripts->register(
			'offline-form-submit', // Handle.
			array(
				'src'  => [ $this, 'get_offline_form_script' ],
				'deps' => array(), // Dependency.
			)
		);

	}

	/**
	 * Get offline-form script.
	 *
	 * @return string
	 */
	public function get_offline_form_script() {
		$sw_script = file_get_contents( RT_PWA_EXTENSIONS_PATH . '/js/offline-form.js' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$sw_script = preg_replace( '#/\*\s*global.+?\*/#', '', $sw_script );

		// Replace with offline template URLs.
		return str_replace(
			array(
				'ERROR_OFFLINE_URL',
				'ERROR_500_URL'
			),
			array(
				wp_service_worker_json_encode( add_query_arg( 'wp_error_template', 'offline', home_url( '/' ) ) ),
				wp_service_worker_json_encode( add_query_arg( 'wp_error_template', '500', home_url( '/' ) ) )
			),
			$sw_script
		);
	}

}
