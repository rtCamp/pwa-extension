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
	 * `Service_Worker` class instance.
	 *
	 * @var \RT\PWA\Inc\Service_Worker
	 */
	protected $_instance = false;

	/**
	 * This function set the instance for class Service_Worker.
	 *
	 * @return void
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
	 * Tests class construct.
	 *
	 * @covers ::__construct
	 *
	 * @return void
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
				'listener' => 'enable_offline_google_analytics',
			],
		];

		foreach ( $hooks as $hook ) {
			$this->assertEquals(
				$hook['priority'],
				call_user_func( sprintf( 'has_%s', $hook['type'] ), $hook['name'], array( $this->_instance, $hook['listener'] ) ),
				sprintf( '\RT\PWA\Inc\Service_Worker::__construct(); failed to register %1$s "%2$s" to %3$s()', $hook['type'], $hook['name'], $hook['listener'] )
			);
		}

		$this->assertEquals( 10, has_filter( 'wp_service_worker_integrations_enabled', '__return_true' ) );
	}

	/**
	 * Tests `filter_wp_service_worker_navigation_caching_strategy` function.
	 *
	 * @covers ::filter_wp_service_worker_navigation_caching_strategy
	 *
	 * @return void
	 */
	public function test_filter_wp_service_worker_navigation_caching_strategy() {
		$this->assertEquals( 'NetworkFirst', $this->_instance->filter_wp_service_worker_navigation_caching_strategy( '' ) );
	}

	/**
	 * Tests `filter_wp_service_worker_navigation_caching_strategy_args` function.
	 *
	 * @covers ::filter_wp_service_worker_navigation_caching_strategy_args
	 *
	 * @return void
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
	 * Tests `enable_offline_google_analytics` function.
	 *
	 * @covers ::enable_offline_google_analytics
	 *
	 * @return void
	 */
	public function test_enable_offline_google_analytics() {

		$this->_instance->enable_offline_google_analytics( $this->scripts );

		$this->assertArrayHasKey( 'offline-google-analytics', $this->scripts->registered );

	}


	/**
	 * Tests `cache_images` function.
	 *
	 * @covers ::cache_images
	 *
	 * @return void
	 */
	public function test_cache_images() {
		$this->_instance->cache_images( $this->scripts );

		$routes = $this->scripts->caching_routes()->get_all();

		$this->assertNotEmpty( $routes );

		$this->assertEquals( '/wp-content/.*\\.(?:png|gif|jpg|jpeg|svg|webp)(\\?.*)?$', $routes[0]['route'] );
	}

	/**
	 * Tests `cache_theme_assets` function.
	 *
	 * @covers ::cache_theme_assets
	 *
	 * @return void
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
	 * Tests `cache_gutenberg_assets` function.
	 *
	 * @covers ::cache_gutenberg_assets
	 *
	 * @return void
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
	 * Tests `cache_gutenberg_assets` function.
	 *
	 * @covers ::cache_gutenberg_assets
	 *
	 * @return void
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

}
