<?php

namespace RT\PWA\Tests;

use RT\PWA\Inc\Manifest;
use WP_Google_Login\Tests\Utility;

/**
 * Class Manifest
 *
 * @coversDefaultClass \RT\PWA\Inc\Manifest
 */
class Test_Manifest extends \WP_UnitTestCase {

	/**
	 * This google_auth data member will contain google_auth object.
	 *
	 * @var \RT\PWA\Inc\Manifest
	 */
	protected $_instance = false;

	/**
	 * This function set the instance for class google-auth.
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

		$expected_data = array(
			'test_key' => 'test_value',
			'icons'    =>
			[
				[
					'src'   => '/srv/www/wordpress-develop/public_html/tests/phpunit/includes/../data/themedir1/default/assets/img/icon-72x72.png',
					'sizes' => '72x72',
				],
				[
					'src'   => '/srv/www/wordpress-develop/public_html/tests/phpunit/includes/../data/themedir1/default/assets/img/icon-96x96.png',
					'sizes' => '96x96',
				],
				[
					'src'   => '/srv/www/wordpress-develop/public_html/tests/phpunit/includes/../data/themedir1/default/assets/img/icon-128x128.png',
					'sizes' => '128x128',
				],
				[
					'src'   => '/srv/www/wordpress-develop/public_html/tests/phpunit/includes/../data/themedir1/default/assets/img/icon-144x144.png',
					'sizes' => '144x144',
				],
				[
					'src'   => '/srv/www/wordpress-develop/public_html/tests/phpunit/includes/../data/themedir1/default/assets/img/icon-152x152.png',
					'sizes' => '152x152',
				],
				[
					'src'   => '/srv/www/wordpress-develop/public_html/tests/phpunit/includes/../data/themedir1/default/assets/img/icon-192x192.png',
					'sizes' => '192x192',
				],
				[
					'src'   => '/srv/www/wordpress-develop/public_html/tests/phpunit/includes/../data/themedir1/default/assets/img/icon-384x384.png',
					'sizes' => '384x384',
				],
				[
					'src'   => '/srv/www/wordpress-develop/public_html/tests/phpunit/includes/../data/themedir1/default/assets/img/icon-512x512.png',
					'sizes' => '512x512',
				],
			],
			'display'  => 'standalone',
		);

		$arg = [
			'test_key' => 'test_value',
		];

		$this->assertEquals( $expected_data, $this->_instance->filter_web_app_manifest( $arg ) );
	}


}
