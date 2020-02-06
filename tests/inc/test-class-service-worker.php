<?php

namespace RT\PWA\Tests;

use RT\PWA\Inc\Service_Worker;

/**
 * Class Service_Worker
 *
 * @coversDefaultClass \RT\PWA\Inc\Service_Worker
 */
class Test_Service_Worker extends \WP_UnitTestCase {

	/**
	 * This google_auth data member will contain google_auth object.
	 *
	 * @var \RT\PWA\Inc\Service_Worker
	 */
	protected $_instance = false;

	/**
	 * This function set the instance for class google-auth.
	 */
	public function setUp(): void {

		$this->_instance = Service_Worker::get_instance();

		$components = array(
			'configuration'      => new \WP_Service_Worker_Configuration_Component(),
			'navigation_routing' => new \WP_Service_Worker_Navigation_Routing_Component(),
			'precaching_routes'  => new \WP_Service_Worker_Precaching_Routes_Component(),
			'caching_routes'     => new \WP_Service_Worker_Caching_Routes_Component(),
		);

		$this->scripts = new \WP_Service_Worker_Scripts( $components );

		add_action( 'init', [ $this, 'register_nav_menus' ] );

	}

	/**
	 * @covers ::__construct
	 */
	public function test_construct() {

		Utility::invoke_method( $this->_instance, '__construct' );

		$hooks = [
			[
				'type'     => 'filter',
				'name'     => 'wp_service_worker_navigation_caching_strategy',
				'priority' => 10,
				'listener' => 'filter_wp_service_worker_navigation_caching_strategy',
			],
			[
				'type'     => 'filter',
				'name'     => 'wp_service_worker_navigation_caching_strategy_args',
				'priority' => 10,
				'listener' => 'filter_wp_service_worker_navigation_caching_strategy_args',
			],
			[
				'type'     => 'action',
				'name'     => 'wp_front_service_worker',
				'priority' => 10,
				'listener' => 'cache_images',
			],
			[
				'type'     => 'action',
				'name'     => 'wp_front_service_worker',
				'priority' => 10,
				'listener' => 'cache_theme_assets',
			],
			[
				'type'     => 'action',
				'name'     => 'wp_front_service_worker',
				'priority' => 10,
				'listener' => 'cache_gutenberg_assets',
			],
			[
				'type'     => 'action',
				'name'     => 'wp_front_service_worker',
				'priority' => 10,
				'listener' => 'precache_latest_blog_posts',
			],
			[
				'type'     => 'action',
				'name'     => 'wp_front_service_worker',
				'priority' => 10,
				'listener' => 'precache_menu',
			],
			[
				'type'     => 'action',
				'name'     => 'wp_front_service_worker',
				'priority' => 10,
				'listener' => 'enable_offline_google_analytics',
			],
		];

		foreach ( $hooks as $hook ) {
			$this->assertEquals(
				$hook['priority'],
				call_user_func( sprintf( 'has_%s', $hook['type'] ), $hook['name'], array( $this->_instance, $hook['listener'] ) ),
				sprintf( '\Artnews\Inc\Archive::_setup_hooks(); failed to register %1$s "%2$s" to %3$s()', $hook['type'], $hook['name'], $hook['listener'] )
			);
		}

		$this->assertEquals( 10, has_filter( 'wp_service_worker_integrations_enabled', '__return_true' ) );
		$this->assertEquals( 10, has_filter( 'wp_service_worker_navigation_preload', '__return_false' ) );
	}

	/**
	 * @covers ::filter_wp_service_worker_navigation_caching_strategy
	 */
	public function test_filter_wp_service_worker_navigation_caching_strategy() {
		$this->assertEquals( 'NetworkFirst', $this->_instance->filter_wp_service_worker_navigation_caching_strategy( '' ) );
	}

	/**
	 * @covers ::filter_wp_service_worker_navigation_caching_strategy_args
	 */
	public function test_filter_wp_service_worker_navigation_caching_strategy_args() {

		$expected_data = [
			'cacheName' => 'pages',
			'plugins'   => [
				'expiration' => [
					'maxEntries' => 50,
				],
			],
		];

		// Test non array argument.
		$this->assertEquals( $expected_data, $this->_instance->filter_wp_service_worker_navigation_caching_strategy_args( 'test' ) );

		$this->assertEquals( $expected_data, $this->_instance->filter_wp_service_worker_navigation_caching_strategy_args( [] ) );
	}

	/**
	 * @covers ::enable_offline_google_analytics
	 */
	public function test_enable_offline_google_analytics() {

		$this->_instance->enable_offline_google_analytics( $this->scripts );

		$this->assertArrayHasKey( 'offline-google-analytics', $this->scripts->registered );

	}


	/**
	 * @covers ::cache_images
	 */
	public function test_cache_images() {
		$this->_instance->cache_images( $this->scripts );

		$routes = $this->scripts->caching_routes()->get_all();

		$this->assertNotEmpty( $routes );

		$this->assertEquals( '/wp-content/.*\\.(?:png|gif|jpg|jpeg|svg|webp)(\\?.*)?$', $routes[0]['route'] );
	}

	/**
	 * @covers ::cache_theme_assets
	 */
	public function test_cache_theme_assets() {
		$theme_directory_uri_patterns = array(
			preg_quote( trailingslashit( get_template_directory_uri() ), '/' ),
		);

		$this->_instance->cache_theme_assets( $this->scripts );

		$routes = $this->scripts->caching_routes()->get_all();

		$this->assertNotEmpty( $routes );

		$expected_data = '^(' . implode( '|', $theme_directory_uri_patterns ) . ').*';

		$this->assertEquals( $expected_data, $routes[0]['route'] );

	}

	/**
	 * @covers ::cache_gutenberg_assets
	 */
	public function test_cache_gutenberg_assets() {

		// Test for Core gutenberg assets
		$this->_instance->cache_gutenberg_assets( $this->scripts );

		$routes = $this->scripts->caching_routes()->get_all();

		$this->assertNotEmpty( $routes );

		$expected_data = '/wp-includes/css/dist/block-library/.*\.(?:css|js)(\?.*)?$';

		$this->assertEquals( $expected_data, $routes[0]['route'] );

	}

	/**
	 * @covers ::cache_gutenberg_assets
	 */
	public function test_cache_gutenberg_assets_plugin() {
		// Test for gutenberg plugin assets
		$default_plugins = get_option( 'active_plugins' );

		$active_plugin = [
			'gutenberg/gutenberg.php',
		];

		update_option( 'active_plugins', $active_plugin );

		$this->_instance->cache_gutenberg_assets( $this->scripts );

		$routes = $this->scripts->caching_routes()->get_all();

		$this->assertNotEmpty( $routes );

		$expected_data = '/wp-content/plugins/gutenberg/.*\.(?:css|js)(\?.*)?$';

		$this->assertEquals( $expected_data, $routes[0]['route'] );

		update_option( 'active_plugins', $default_plugins );
	}

	/**
	 * @covers ::precache_latest_blog_posts
	 */
	public function test_precache_latest_blog_posts() {

		wp_cache_delete( 'rt_pwa_extensions_precache_latest_posts' );

		// Test with no posts
		$this->_instance->precache_latest_blog_posts( $this->scripts );

		$routes = $this->scripts->precaching_routes()->get_all();

		$this->assertEmpty( $routes );

		// Test with mocking posts
		$post_ids = $this->factory()->post->create_many( 10 );

		$this->_instance->precache_latest_blog_posts( $this->scripts );

		$expected_data = [];

		$post_ids = array_reverse( $post_ids );

		foreach ( $post_ids as $post_id ) {
			$expected_data[] = [
				'url'      => get_permalink( $post_id ),
				'revision' => get_bloginfo( 'version' ),
			];
		}

		$routes = $this->scripts->precaching_routes()->get_all();

		$this->assertEquals( $expected_data, $routes );

	}

	public function test_precache_menu() {

		wp_cache_delete( 'rt_pwa_extensions_precache_menu_links' );

		// Test with no menu locations
		$this->_instance->precache_menu( $this->scripts );

		$routes = $this->scripts->precaching_routes()->get_all();
		$this->assertEquals( [], $routes );

		$menu_name = 'Test Menu 1';
		$menu_id   = wp_create_nav_menu( $menu_name );

		$locations = [
			'test1' => $menu_id,
			'test2' => '',
		];

		set_theme_mod( 'nav_menu_locations', $locations );

		$this->_instance->precache_menu( $this->scripts );

		$routes = $this->scripts->precaching_routes()->get_all();
		$this->assertEquals( [], $routes );

		$this->_set_menu_data( $menu_id );

		$this->_instance->precache_menu( $this->scripts );
		$routes = $this->scripts->precaching_routes()->get_all();

		$expected_data[] = [
			'url'      => home_url( '/test' ),
			'revision' => get_bloginfo( 'version' ),
		];

		$this->assertEquals( $expected_data, $routes );

	}

	public function _set_menu_data( $menu_id ) {

		wp_update_nav_menu_item(
			$menu_id,
			0,
			[
				'menu-item-title'  => 'test',
				'menu-item-url'    => home_url( '/test' ),
				'menu-item-status' => 'publish',
			]
		);

		wp_update_nav_menu_item(
			$menu_id,
			0,
			[
				'menu-item-title'  => 'external link',
				'menu-item-url'    => 'https://www.google.com',
				'menu-item-status' => 'publish',
			]
		);

		wp_update_nav_menu_item(
			$menu_id,
			0,
			[
				'menu-item-title'  => 'Post archive link',
				'menu-item-url'    => get_post_type_archive_link( 'post' ),
				'menu-item-status' => 'publish',
			]
		);
	}

}
