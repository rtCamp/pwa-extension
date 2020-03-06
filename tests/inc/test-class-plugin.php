<?php

namespace RT\PWA\Tests;

use RT\PWA\Inc\Plugin;

/**
 * Class Plugin
 *
 * @coversDefaultClass \RT\PWA\Inc\Plugin
 */
class Test_Plugin extends \WP_UnitTestCase {

	/**
	 *
	 * @var \RT\PWA\Inc\Plugin
	 */
	protected $_instance = false;

	/**
	 * This function set the instance for class Plugin.
	 */
	public function setUp(): void {

		$this->_instance = Plugin::get_instance();

	}

	/**
	 * @covers ::__construct
	 * @covers ::setup_hooks
	 */
	public function test_construct() {

		Utility::invoke_method( $this->_instance, '__construct' );

		$this->assertEquals( 20, has_filter( 'wp_head', [ $this->_instance, 'add_pwa_compat_library' ] ) );

		$this->assertEquals( 10, has_filter( 'wp_headers', [ $this->_instance, 'filter_wp_headers' ] ) );
	}

	/**
	 * @covers ::add_pwa_compat_library
	 */
	public function test_add_pwa_compat_library() {
		$output = Utility::buffer_and_return( [ $this->_instance, 'add_pwa_compat_library' ] );

		$this->assertContains( 'https://cdn.jsdelivr.net/npm/pwacompat@2.0.6/pwacompat.min.js', $output );
	}

	public function test_filter_wp_headers() {

		// Assert Non array argument
		$this->assertEquals( 'test', $this->_instance->filter_wp_headers( 'test' ) );

		$data = $this->_instance->filter_wp_headers( [] );
		$this->assertArrayHasKey( 'Strict-Transport-Security', $data );
		$this->assertEquals( 'max-age=3600', $data['Strict-Transport-Security'] );
	}
}
