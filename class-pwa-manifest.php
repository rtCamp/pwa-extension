<?php
/**
 * Filters to configure Manifest.
 *
 * @package rt-pwa-extensions
 */

/**
 * PWA configuration
 */
class PWA_Manifest {
	/**
	 * Initalize class
	 */
	public function __construct() {
		add_filter( 'web_app_manifest', [ $this, 'filter_jetpack_pwa_manifest' ] );
		add_action( 'wp_head', [ $this, 'add_pwa_compat_library' ], 20 );
	}
	/**
	 * Filter PWA manifest
	 *
	 * @param array $manifest PWA manifest.
	 *
	 * @return array
	 */
	public function filter_jetpack_pwa_manifest( $manifest ) {
		if ( empty( $manifest ) || ! is_array( $manifest ) ) {
			return $manifest;
		}
		$icon_sizes = [ '72', '96', '128', '144', '152', '192', '384', '512' ];
		foreach ( $icon_sizes as $icon_size ) {
			$manifest['icons'][] = [
				'src'   => sprintf( '%1$s/assets/img/icon-%2$sx%2$s.png', get_template_directory_uri(), $icon_size ),
				'sizes' => sprintf( '%1$sx%1$s', $icon_size ),
			];
		}
		$manifest['display'] = 'standalone';
		return $manifest;
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
}

new PWA_Manifest();
