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
			$img_url     = sprintf( '%1$s/assets/img/icon-%2$sx%2$s.png', get_template_directory_uri(), $icon_size );
			$filter_name = sprintf( 'rt_pwa_extensions_app_icon_%1$s_%1$s', $icon_size );

			$manifest['icons'][] = array(
				/**
				 * Filters web app manifest icon url.
				 *
				 * The Dynamic portion of the hook refers to height and width of the image.
				 *
				 * @since 1.0.3
				 *
				 * @param string $img_url Icon URL.
				 */
				'src'   => apply_filters( $filter_name, $img_url ),
				'sizes' => sprintf( '%1$sx%1$s', $icon_size ),
			);

		}

		$manifest['display'] = 'standalone';

		return $manifest;

	}

}
