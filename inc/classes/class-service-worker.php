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

		add_filter( 'wp_service_worker_navigation_caching_strategy', array( $this, 'filter_wp_service_worker_navigation_caching_strategy' ) );

		add_filter( 'wp_service_worker_navigation_caching_strategy_args', array( $this, 'filter_wp_service_worker_navigation_caching_strategy_args' ) );

		add_action( 'wp_front_service_worker', array( $this, 'cache_images' ) );
		add_action( 'wp_front_service_worker', array( $this, 'cache_theme_assets' ) );
		add_action( 'wp_front_service_worker', array( $this, 'cache_gutenberg_assets' ) );
		add_action( 'wp_front_service_worker', array( $this, 'enable_offline_google_analytics' ) );

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

		return \WP_Service_Worker_Caching_Routes::STRATEGY_NETWORK_FIRST;

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

	/**
	 * Cache theme assets with runtime network-first caching strategy.
	 * This includes both the parent theme and child theme.
	 *
	 * @see https://gist.github.com/westonruter/1a63d052beb579842461f6ad837715fb#file-basic-site-caching-php-L43-L67
	 *
	 * @param \WP_Service_Worker_Scripts $scripts Instance to register service worker behavior with.
	 *
	 * @return void
	 */
	public function cache_theme_assets( \WP_Service_Worker_Scripts $scripts ) {

		$theme_directory_uri_patterns = array(
			preg_quote( trailingslashit( get_template_directory_uri() ), '/' ),
		);

		// @codeCoverageIgnoreStart
		// Ignoring because not able to mock this condition.
		if ( get_template() !== get_stylesheet() ) {
			$theme_directory_uri_patterns[] = preg_quote( trailingslashit( get_stylesheet_directory_uri() ), '/' );
		}
		// @codeCoverageIgnoreEnd


		$scripts->caching_routes()->register(
			'^(' . implode( '|', $theme_directory_uri_patterns ) . ').*',
			array(
				'strategy'  => \WP_Service_Worker_Caching_Routes::STRATEGY_NETWORK_FIRST,
				'cacheName' => 'theme-assets',
				'plugins'   => array(
					'expiration' => array(
						'maxEntries' => 25, // Limit the cached entries to the number of files loaded over network, e.g. JS, CSS, and PNG.
					),
				),
			)
		);

	}

	/**
	 * Cache Gutenberg block-library assets with runtime network-first caching strategy.
	 *
	 * @see https://gist.github.com/westonruter/1a63d052beb579842461f6ad837715fb#file-basic-site-caching-php-L43-L67
	 *
	 * @param \WP_Service_Worker_Scripts $scripts Instance to register service worker behavior with.
	 *
	 * @return void
	 */
	public function cache_gutenberg_assets( \WP_Service_Worker_Scripts $scripts ) {

		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		if ( is_plugin_active( 'gutenberg/gutenberg.php' ) ) {
			$block_library_path = '/wp-content/plugins/gutenberg/.*\.(?:css|js)(\?.*)?$';
		} else {
			$block_library_path = '/wp-includes/css/dist/block-library/.*\.(?:css|js)(\?.*)?$';
		}

		$scripts->caching_routes()->register(
			$block_library_path,
			array(
				'strategy'  => \WP_Service_Worker_Caching_Routes::STRATEGY_NETWORK_FIRST,
				'cacheName' => 'block-library-assets',
				'plugins'   => array(
					'expiration' => array(
						'maxEntries' => 25, // Limit the cached entries to the number of files loaded over network, e.g. JS, CSS, and PNG.
					),
				),
			)
		);
	}

	/**
	 * Enables offline google analytics.
	 *
	 * @param \WP_Service_Worker_Scripts $scripts Instance to register service worker behavior with.
	 *
	 * @return void
	 */
	public function enable_offline_google_analytics( \WP_Service_Worker_Scripts $scripts ) {

		$scripts->register(
			'offline-google-analytics',
			array(
				'src' => sprintf( '%s/assets/js/offline-analytics.js', untrailingslashit( RT_PWA_EXTENSIONS_URL ) ),
			)
		);

	}

}
