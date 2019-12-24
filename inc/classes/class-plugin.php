<?php
/**
 * Plugin manifest class.
 *
 * @package rt-pwa-extensions
 */

namespace RT\PWA\Inc;

use \RT\PWA\Inc\Traits\Singleton;
use \RT\PWA\Inc\Manifest;
use \RT\PWA\Inc\Service_Worker;
use \RT\PWA\Inc\Integration\Izooto;

/**
 * Class Plugin
 */
class Plugin {

	use Singleton;

	/**
	 * Construct method.
	 */
	protected function __construct() {

		$this->setup_hooks();

		Manifest::get_instance();
		Service_Worker::get_instance();

		// Other plugins integration.
		Izooto::get_instance();

	}

	/**
	 * Setup actions/filters
	 *
	 * @return void
	 */
	protected function setup_hooks() {

		add_action( 'wp_head', array( $this, 'add_pwa_compat_library' ), 20 );

		add_filter( 'wp_headers', array( $this, 'filter_wp_headers' ) );

	}

	/**
	 * Add pwa compat library
	 *
	 * @return void
	 */
	public function add_pwa_compat_library() {
		?>
		<script
			async
			src="https://cdn.jsdelivr.net/npm/pwacompat@2.0.6/pwacompat.min.js"
			integrity="sha384-GOaSLecPIMCJksN83HLuYf9FToOiQ2Df0+0ntv7ey8zjUHESXhthwvq9hXAZTifA"
			crossorigin="anonymous">
		</script>
		<?php
	}

	/**
	 * Adds an HSTS header to the response.
	 *
	 * @param array $headers Associative array of headers to be sent.
	 *
	 * @return array
	 */
	public function filter_wp_headers( $headers ) {

		if ( ! is_array( $headers ) ) {
			return $headers;
		}

		$headers['Strict-Transport-Security'] = 'max-age=3600';

		return $headers;

	}

}
