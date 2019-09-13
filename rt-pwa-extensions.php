<?php
/**
 * Plugin Name: rt PWA Extensions
 * Description: Enabling PWA features like offline caching etc. (requires pwa plugin activated.)
 * Author: rtCamp, chandrapatel, pradeep910, dharmin
 * Author URI: https://rtcamp.com/?utm_source=rt-pwa-extensions-plugin
 * Version: 1.0
 *
 * @package rt-pwa-extensions
 */

// Include PWA Manifest changes.
require __DIR__ . '/class-pwa-manifest.php';

// Enable service worker integrations.
add_filter( 'wp_service_worker_integrations_enabled', '__return_true' );

// This is to opt-in to a caching strategy for navigation requests.
add_filter( 'wp_service_worker_navigation_preload', '__return_false' );

// Change service worker navigation caching strategy to fastest first.
add_filter(
	'wp_service_worker_navigation_caching_strategy',
	function() {
		return WP_Service_Worker_Caching_Routes::STRATEGY_STALE_WHILE_REVALIDATE;
	}
);

// Cache pages.
add_filter(
	'wp_service_worker_navigation_caching_strategy_args',
	function( $args ) {
		$args['cacheName']                           = 'pages';
		$args['plugins']['expiration']['maxEntries'] = 50;
		return $args;
	}
);

// Cache images.
add_action(
	'wp_front_service_worker',
	function( \WP_Service_Worker_Scripts $scripts ) {
		$scripts->caching_routes()->register(
			'/wp-content/.*\.(?:png|gif|jpg|jpeg|svg|webp)(\?.*)?$',
			array(
				'strategy'  => WP_Service_Worker_Caching_Routes::STRATEGY_CACHE_FIRST,
				'cacheName' => 'images',
				'plugins'   => array(
					'expiration' => array(
						'maxEntries'    => 50,
						'maxAgeSeconds' => 60 * 60 * 24,
					),
				),
			)
		);
	}
);

/**
 * Adds an HSTS header to the response.
 *
 * @param array $headers The headers to filter.
 * @return array $headers The filtered headers.
 */
add_filter(
	'wp_headers',
	function( $headers ) {
		$headers['Strict-Transport-Security'] = 'max-age=3600'; // Or another max-age.
		return $headers;
	}
);


// Register different image sizes for icons.
$icon_sizes = [ '72', '96', '128', '144', '152', '192', '384', '512' ];

foreach ( $icon_sizes as $size ) {
	// Generete a image size label: pwa-icon-<size>.
	$image_size_label = sprintf( 'pwa-icon-%1$d', $size );
	add_image_size( $image_size_label, absint( $size ), absint( $size ) );
}

// Add Customizer settings to select PWA Icon.
add_action(
	'customize_register',
	function( $wp_customize ) {
		$wp_customize->add_setting(
			'rpe_pwa_icon',
			array(
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Cropped_Image_Control(
				$wp_customize,
				'cropped_image',
				array(
					'section'     => 'title_tagline',
					'label'       => __( 'PWA Icon' ),
					'flex_width'  => true, // Allow any width, making the specified value recommended.
					'flex_height' => true, // Require the resulting image to be exactly as tall as the height attribute.
					'width'       => 512,
					'height'      => 512,
					'priority'    => 16,
					'settings'    => 'rpe_pwa_icon',
				)
			)
		);
	}
);

// Regenerate all thumbnail of the current site id image with new sizes.
register_activation_hook(
	__FILE__,
	function() {

		$site_icon_id = get_option( 'site_icon' );

		if ( ! empty( $site_icon_id ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-includes/pluggable.php';
			$metadata = wp_generate_attachment_metadata( $site_icon_id, get_attached_file( $site_icon_id ) );
			wp_update_attachment_metadata( $site_icon_id, $metadata );
		}

	}
);
