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

		// Add short name which is required by the PWA standards.
		$manifest['short_name'] = substr( get_bloginfo( 'name' ), 0, 12 );

		// Get the PWA icon id first.
		$icon_id = get_theme_mod( 'rpe_pwa_icon' );
		// Get the site icon id if PWA icon is not set.
		$icon_id = ! empty( $icon_id ) ? $icon_id : get_option( 'site_icon' );
		$icons   = [];
		if ( ! empty( $icon_id ) ) {
			$icon_sizes = [ '72', '96', '128', '144', '152', '192', '384', '512' ];

			// Add icons with different sizes.
			foreach ( $icon_sizes as $icon_size ) {
				$image_size_label = sprintf( 'pwa-icon-%1$d', $icon_size );
				$icon             = wp_get_attachment_image_src( absint( $icon_id ), $image_size_label );
				if ( ! empty( $icon ) ) {
					$icons[] = [
						'src'   => $icon[0],
						'sizes' => sprintf( '%1$sx%1$s', $icon_size ),
						'type'  => get_post_mime_type( $icon_id ),
					];
				}
			}
		}
		$manifest['icons']   = $icons;
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
