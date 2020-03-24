<?php

namespace RT\PWA\Tests;

use RT\PWA\Inc\Manifest;


/**
 * Class Manifest
 *
 * @coversDefaultClass \RT\PWA\Inc\Manifest
 */
class Test_Manifest extends \WP_UnitTestCase {

	/**
	 *
	 * @var \RT\PWA\Inc\Manifest
	 */
	protected $_instance = false;

	/**
	 * This function set the instance for class Manifest.
	 */
	public function setUp(): void {

		$this->_instance = Manifest::get_instance();

	}

	/**
	 * @covers ::__construct
	 */
	public function test_construct() {

		Utility::invoke_method( $this->_instance, '__construct' );

		$this->assertEquals( 10, has_filter( 'web_app_manifest', [ $this->_instance, 'filter_web_app_manifest' ] ) );
	}


	/**
	 * @covers ::filter_web_app_manifest()
	 */
	public function test_filter_web_app_manifest() {

		// Empty manifest.

		$this->assertEmpty( $this->_instance->filter_web_app_manifest( '' ) );

		$icon_sizes = [ '72', '96', '128', '144', '152', '192', '384', '512' ];

		foreach ( $icon_sizes as $icon_size ) {

			$icons[] = [
				'src'   => sprintf( '%1$s/assets/img/icon-%2$sx%2$s.png', get_template_directory_uri(), $icon_size ),
				'sizes' => sprintf( '%1$sx%1$s', $icon_size ),
			];

		}
		$expected_data = [
			'test_key'   => 'test_value',
			'icons'      => $icons,
			'display'    => 'standalone',
			'name' => 'test site',
			'short_name' => 'test site',
		];

		$arg = [
			'test_key' => 'test_value',
			'name'     => 'test site',
		];

		$this->assertEquals( $expected_data, $this->_instance->filter_web_app_manifest( $arg ) );
	}


}
