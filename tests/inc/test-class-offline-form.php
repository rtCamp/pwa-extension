<?php

namespace RT\PWA\Tests;

use RT\PWA\Inc\Offline_Form;

/**
 * Class Offline_Form
 *
 * @coversDefaultClass \RT\PWA\Inc\Offline_Form
 */
class Test_Offline_Form extends \WP_UnitTestCase {

	/**
	 * `Offline_Form` class instance.
	 *
	 * @var \RT\PWA\Inc\Offline_Form
	 */
	protected $_instance = false;

	/**
	 * This function set the instance for class Offline_Form.
	 *
	 * @return void
	 */
	public function setUp(): void {

		$this->_instance = Offline_Form::get_instance();

		$components = array(
			'configuration'      => new \WP_Service_Worker_Configuration_Component(),
			'navigation_routing' => new \WP_Service_Worker_Navigation_Routing_Component(),
			'precaching_routes'  => new \WP_Service_Worker_Precaching_Routes_Component(),
			'caching_routes'     => new \WP_Service_Worker_Caching_Routes_Component(),
		);

		$this->scripts = new \WP_Service_Worker_Scripts( $components );

	}

	/**
	 * Tests class construct.
	 *
	 * @covers ::__construct
	 * @covers ::setup_hooks
	 *
	 * @return void
	 */
	public function test_construct() {

		Utility::invoke_method( $this->_instance, '__construct' );

		$error_messages = Utility::get_property( $this->_instance, 'error_messages' );
		$expected_data  = [
			'serverOffline' => esc_html__( 'The server appears to be down. Please try again later.', 'rt-pwa-extensions' ),
			'error'         => esc_html__( 'Something prevented the page from being rendered. Please try again.', 'rt-pwa-extensions' ),
			'form'          => esc_html__( 'Your form will be submitted once you are back online!', 'rt-pwa-extensions' ),
		];
		$this->assertEquals( $expected_data, $error_messages );

		$hooks = [
			[
				'type'     => 'filter',
				'name'     => 'wp_front_service_worker',
				'priority' => 10,
				'listener' => 'offline_form_service_worker',
			],
		];

		foreach ( $hooks as $hook ) {
			$this->assertEquals(
				$hook['priority'],
				call_user_func( sprintf( 'has_%s', $hook['type'] ), $hook['name'], array( $this->_instance, $hook['listener'] ) ),
				sprintf( '\RT\PWA\Inc\Offline_Form::__construct(); failed to register %1$s "%2$s" to %3$s()', $hook['type'], $hook['name'], $hook['listener'] )
			);
		}
	}

	/**
	 * Tests `offline_form_service_worker` function.
	 * 
	 * @covers ::offline_form_service_worker
	 *
	 * @return void
	 */
	public function test_offline_form_service_worker() {

		$old_option = get_option( 'rt_pwa_extension_options' );

		$this->_instance->offline_form_service_worker( $this->scripts );
		$this->assertArrayNotHasKey( 'offline-form-submit', $this->scripts->registered );

		$option = 'test1' . PHP_EOL . 'test2' . PHP_EOL . 'test3';
		update_option( 'rt_pwa_extension_options', $option );

		$this->_instance->offline_form_service_worker( $this->scripts );
		$this->assertArrayHasKey( 'offline-form-submit', $this->scripts->registered );

		update_option( 'rt_pwa_extension_options', $old_option );
	}

	/**
	 * Tests `get_form_urls` function.
	 *
	 * @covers ::get_form_urls
	 *
	 * @return void
	 */
	public function test_get_form_urls() {

		// Testing Empty Routes
		$this->assertFalse( Utility::invoke_method( $this->_instance, 'get_form_urls' ) );

		// Testing else condition
		$option = 'test1 abc' . PHP_EOL . 'test2' . PHP_EOL . ' ' . PHP_EOL . 'test3';
		update_option( 'rt_pwa_extension_options', $option );
		$this->assertEquals( 'test1abc|test2|test3', Utility::invoke_method( $this->_instance, 'get_form_urls' ) );

		delete_option( 'rt_pwa_extension_options' );

	}

	/**
	 * Tests `get_offline_form_script` function.
	 *
	 * @covers ::get_offline_form_script
	 *
	 * @return void
	 */
	public function test_get_offline_form_script() {
		$old_option = get_option( 'rt_pwa_extension_options' );

		$expected_offline_url = home_url( '/' ) . '?wp_error_template=offline';
		$expected_500_url     = home_url( '/' ) . '?wp_error_template=500';

		$this->assertFalse( $this->_instance->get_offline_form_script() );

		$expected_form_routes = 'test1|test2|test3';

		$option = 'test1' . PHP_EOL . 'test2' . PHP_EOL . 'test3';
		update_option( 'rt_pwa_extension_options', $option );

		$this->assertContains( $expected_offline_url, $this->_instance->get_offline_form_script() );
		$this->assertContains( $expected_500_url, $this->_instance->get_offline_form_script() );
		$this->assertContains( $expected_form_routes, $this->_instance->get_offline_form_script() );

		update_option( 'rt_pwa_extension_options', $old_option );

	}

}
