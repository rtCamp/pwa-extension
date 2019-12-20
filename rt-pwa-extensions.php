<?php
/**
 * Plugin Name: rt PWA Extensions
 * Description: Enabling PWA features like offline caching etc. (requires pwa plugin activated.)
 * Author: rtCamp, chandrapatel, pradeep910
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

if ( in_array( 'izooto-web-push/izooto.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
	require __DIR__ . '/class-izooto-integration.php';
}
