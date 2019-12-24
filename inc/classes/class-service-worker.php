<?php
/**
 * Configure serivice worker.
 *
 * @package rt-pwa-extensions
 */

namespace RT\PWA\Inc;

use \RT\PWA\Inc\Traits\Singleton;

/**
 * PWA configuration
 */
class Service_Worker {

	use Singleton;

	/**
	 * Initalize class
	 */
	protected function __construct() {

		// Enable service worker integrations.
		add_filter( 'wp_service_worker_integrations_enabled', '__return_true' );

		// This is to opt-in to a caching strategy for navigation requests.
		add_filter( 'wp_service_worker_navigation_preload', '__return_false' );

		add_filter( 'wp_service_worker_navigation_caching_strategy', array( $this, 'filter_wp_service_worker_navigation_caching_strategy' ) );

		add_filter( 'wp_service_worker_navigation_caching_strategy_args', array( $this, 'filter_wp_service_worker_navigation_caching_strategy_args' ) );

		add_action( 'wp_front_service_worker', array( $this, 'cache_images' ) );

	}

	/**
	 * Filters caching strategy used for frontend navigation requests.
	 *
	 * Change service worker navigation caching strategy to fastest first.
	 *
	 * @param string $caching_strategy Caching strategy to use for frontend navigation requests.
	 *
	 * @return string
	 */
	public function filter_wp_service_worker_navigation_caching_strategy( $caching_strategy ) {

		return \WP_Service_Worker_Caching_Routes::STRATEGY_STALE_WHILE_REVALIDATE;

	}

	/**
	 * Filters the caching strategy args used for frontend navigation requests.
	 *
	 * @param array $caching_strategy_args Caching strategy args.
	 *
	 * @return array
	 */
	public function filter_wp_service_worker_navigation_caching_strategy_args( $caching_strategy_args ) {

		if ( ! is_array( $caching_strategy_args ) ) {
			$caching_strategy_args = array();
		}

		$caching_strategy_args['cacheName']                           = 'pages';
		$caching_strategy_args['plugins']['expiration']['maxEntries'] = 50;

		return $caching_strategy_args;

	}

	/**
	 * Cache images
	 *
	 * @param \WP_Service_Worker_Scripts $scripts Instance to register service worker behavior with.
	 *
	 * @return void
	 */
	public function cache_images( \WP_Service_Worker_Scripts $scripts ) {

		$scripts->caching_routes()->register(
			'/wp-content/.*\.(?:png|gif|jpg|jpeg|svg|webp)(\?.*)?$',
			array(
				'strategy'  => \WP_Service_Worker_Caching_Routes::STRATEGY_CACHE_FIRST,
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

}
