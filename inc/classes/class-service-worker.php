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
		add_action( 'wp_front_service_worker', array( $this, 'cache_theme_assets' ) );
		add_action( 'wp_front_service_worker', array( $this, 'cache_gutenberg_assets' ) );
		add_action( 'wp_front_service_worker', array( $this, 'precache_latest_blog_posts' ) );
		add_action( 'wp_front_service_worker', array( $this, 'precache_menu' ) );
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

		if ( get_template() !== get_stylesheet() ) {
			$theme_directory_uri_patterns[] = preg_quote( trailingslashit( get_stylesheet_directory_uri() ), '/' );
		}

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
	 * Pre-Cache latest blog posts
	 *
	 * @param \WP_Service_Worker_Scripts $scripts Instance to register service worker behavior with.
	 *
	 * @return void
	 */
	public function precache_latest_blog_posts( \WP_Service_Worker_Scripts $scripts ) {

		$cache_key = 'rt_pwa_extensions_precache_latest_posts';

		$recent_posts = wp_cache_get( $cache_key );

		if ( empty( $recent_posts ) ) {

			$recent_posts = new \WP_Query(
				array(
					'post_type'              => 'post',
					'post_status'            => 'publish',
					'fields'                 => 'ids',
					'posts_per_page'         => 10,
					'no_found_rows'          => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
				)
			);

			if ( empty( $recent_posts->posts ) || ! is_array( $recent_posts->posts ) ) {
				return;
			}

			$recent_posts = $recent_posts->posts;

			wp_cache_set( $cache_key, $recent_posts, '', 10 * MINUTE_IN_SECONDS );

		}

		foreach ( $recent_posts as $recent_post_id ) {
			$scripts->precaching_routes()->register(
				get_permalink( $recent_post_id ),
				array(
					'revision' => get_bloginfo( 'version' ),
				)
			);
		}

	}

	/**
	 * Pre-Cache menu
	 *
	 * Only precache menu which assigned to any menu locations.
	 *
	 * @param \WP_Service_Worker_Scripts $scripts Instance to register service worker behavior with.
	 *
	 * @return void
	 */
	public function precache_menu( \WP_Service_Worker_Scripts $scripts ) {

		$cache_key = 'rt_pwa_extensions_precache_menu_links';

		$menu_links = wp_cache_get( $cache_key );

		if ( empty( $menu_links ) ) {

			// Get menu locations from source site.
			$menu_locations = get_nav_menu_locations();

			if ( empty( $menu_locations ) || ! is_array( $menu_locations ) ) {
				return;
			}

			$menu_links = array();

			foreach ( $menu_locations as $menu_location => $menu_id ) {

				// If menu location does not have any menu assign then continue.
				if ( empty( $menu_id ) ) {
					continue;
				}

				$menu_items = wp_get_nav_menu_items( $menu_id );

				foreach ( $menu_items as $menu_item ) {

					// Don't precache external links.
					if ( false === strpos( $menu_item->url, home_url() ) ) {
						continue;
					}

					// Don't precache blog page.
					if ( user_trailingslashit( $menu_item->url ) === get_post_type_archive_link( 'post' ) ) {
						continue;
					}

					$menu_links[] = $menu_item->url;

				}
			}

			if ( empty( $menu_links ) || ! is_array( $menu_links ) ) {
				return;
			}

			// Filter out duplicate links.
			$menu_links = array_unique( $menu_links );

			// pre-cache only 10 menu links.
			$menu_links = array_slice( $menu_links, 0, 10 );

			wp_cache_set( $cache_key, $menu_links, '', 10 * MINUTE_IN_SECONDS );

		}

		foreach ( $menu_links as $menu_link ) {
			$scripts->precaching_routes()->register(
				$menu_link,
				array(
					'revision' => get_bloginfo( 'version' ),
				)
			);
		}

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
				'src' => function() {
					return 'workbox.googleAnalytics.initialize();';
				},
			)
		);

	}

}
