<?php
/**
 * Configure Manifest.
 *
 * @package rt-pwa-extensions
 */

namespace RT\PWA\Inc;

use \RT\PWA\Inc\Traits\Singleton;

/**
 * PWA configuration
 */
class Manifest {

	use Singleton;

	/**
	 * Initalize class
	 */
	protected function __construct() {

		add_filter( 'web_app_manifest', array( $this, 'filter_web_app_manifest' ) );

	}

	/**
	 * Filter PWA manifest
	 *
	 * @param array $manifest PWA manifest.
	 *
	 * @return array
	 */
	public function filter_web_app_manifest( $manifest ) {

		if ( empty( $manifest ) || ! is_array( $manifest ) ) {
			return $manifest;
		}

		$icon_sizes = array( '72', '96', '128', '144', '152', '192', '384', '512' );

		foreach ( $icon_sizes as $icon_size ) {

			$manifest['icons'][] = array(
				'src'   => sprintf( '%1$s/assets/img/icon-%2$sx%2$s.png', get_template_directory_uri(), $icon_size ),
				'sizes' => sprintf( '%1$sx%1$s', $icon_size ),
			);

		}

		$manifest['display'] = 'standalone';

		return $manifest;

	}

}
