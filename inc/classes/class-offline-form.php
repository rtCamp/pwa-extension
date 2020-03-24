<?php
/**
 * Offline form submission class.
 *
 * @package rt-pwa-extensions
 */

namespace RT\PWA\Inc;

use \RT\PWA\Inc\Traits\Singleton;

/**
 * Class Offline_Form
 */
class Offline_Form {

	use Singleton;

	/**
	 * Array of error messages.
	 *
	 * @var array
	 */
	private $error_messages;

	/**
	 * Construct method.
	 */
	protected function __construct() {

		$this->error_messages = array(
			'serverOffline' => esc_html__( 'The server appears to be down. Please try again later.', 'rt-pwa-extensions' ),
			'error'         => esc_html__( 'Something prevented the page from being rendered. Please try again.', 'rt-pwa-extensions' ),
			'form'          => esc_html__( 'Your form will be submitted once you are back online!', 'rt-pwa-extensions' ),
		);

		$this->setup_hooks();

	}

	/**
	 * Setup actions/filters
	 *
	 * @return void
	 */
	protected function setup_hooks() {

		add_action( 'wp_front_service_worker', array( $this, 'offline_form_service_worker' ), 10 );

	}

	/**
	 * Register service worker script for offline form submit.
	 *
	 * @param Object $scripts scripts object.
	 */
	public function offline_form_service_worker( $scripts ) {

		$offline_form_sw_script = $this->get_offline_form_script();

		if ( false !== $offline_form_sw_script ) {
			$scripts->register(
				'offline-form-submit', // Handle.
				array(
					'src'  => function() use ( $offline_form_sw_script ) {
						return $offline_form_sw_script;
					},
					'deps' => array(), // Dependency.
				)
			);
		}

	}

	/**
	 * Get offline-form script.
	 *
	 * @return string
	 */
	public function get_offline_form_script() {
		$sw_script = file_get_contents( RT_PWA_EXTENSIONS_PATH . '/assets/js/offline-form.js' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$sw_script = preg_replace( '#/\*\s*global.+?\*/#', '', $sw_script );

		$form_routes_regex = $this->get_form_urls();

		// Bail out if offline form routes are not set.
		if ( false === $form_routes_regex ) {
			return false;
		}

		// Replace with error messages | offline template url |error template url | form routes.
		$sw_script = str_replace(
			array(
				'ERROR_MESSAGES',
				'ERROR_OFFLINE_URL',
				'ERROR_500_URL',
				'FORM_ROUTES',
			),
			array(
				wp_service_worker_json_encode( $this->error_messages ),
				wp_service_worker_json_encode( add_query_arg( 'wp_error_template', 'offline', home_url( '/' ) ) ),
				wp_service_worker_json_encode( add_query_arg( 'wp_error_template', '500', home_url( '/' ) ) ),
				$form_routes_regex,
			),
			$sw_script
		);

		return $sw_script;
	}

	/**
	 * Get all form url regex string.
	 *
	 * @return string
	 */
	private function get_form_urls() {
		$string      = '';
		$form_routes = get_option( 'rt_pwa_extension_options' );
		if ( empty( $form_routes ) || ctype_space( $form_routes ) ) {
			// Return false if no offline form routes are set.
			$string = false;
		} else {
			$routes = explode( PHP_EOL, $form_routes );
			// Create regex string like ( '/contact|/form|/gravity-form' ).
			foreach ( $routes as $route ) {
				$route = preg_replace( '/\s+/', '', $route ); // Remove white spaces.
				if ( empty( $route ) ) {
					continue;
				}
				$string .= str_replace( '/', '\/', $route ) . '|';
			}
			// Remove '|' from end.
			$string = rtrim( $string, '|' );
		}

		return $string;
	}

}
