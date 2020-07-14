<?php

namespace RT\PWA\Tests;

use RT\PWA\Inc\Admin_Settings;

/**
 * Class Admin_Settings
 *
 * @coversDefaultClass \RT\PWA\Inc\Admin_Settings
 */
class Test_Admin_Settings extends \WP_UnitTestCase {

	/**
	 * `Admin_Settings` class instance.
	 *
	 * @var \RT\PWA\Inc\Admin_Settings
	 */
	protected $_instance = false;

	/**
	 * This function set the instance for class Admin_settings.
	 */
	public function setUp(): void {

		$this->_instance = Admin_Settings::get_instance();

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
				'type'     => 'action',
				'name'     => 'admin_init',
				'priority' => 10,
				'listener' => 'register_plugin_settings',
			],
			[
				'type'     => 'action',
				'name'     => 'admin_menu',
				'priority' => 10,
				'listener' => 'register_options_page',
			],
		];

		foreach ( $hooks as $hook ) {
			$this->assertEquals(
				$hook['priority'],
				call_user_func( sprintf( 'has_%s', $hook['type'] ), $hook['name'], array( $this->_instance, $hook['listener'] ) ),
				sprintf( '\RT\PWA\Inc\Admin_Settings::__construct(); failed to register %1$s "%2$s" to %3$s()', $hook['type'], $hook['name'], $hook['listener'] )
			);
		}
	}

	/**
	 * Tests `callback_pwa_setting_section` function.
	 *
	 * @covers ::callback_pwa_setting_section
	 * 
	 * @return void
	 */
	public function test_callback_pwa_setting_section() {

		$output = Utility::buffer_and_return( [ $this->_instance, 'callback_pwa_setting_section' ] );

		$this->assertEquals( 'Add your form routes for offline form submission.', $output );
	}

	/**
	 * Tests `register_options_page` function.
	 *
	 * @covers ::register_options_page()
	 *
	 * @return void
	 */
	public function test_register_options_page() {
		$current_user = get_current_user_id();

		wp_set_current_user( $this->factory()->user->create( array( 'role' => 'administrator' ) ) );

		$this->_instance->register_options_page();

		$admin_page_url = home_url() . '/wp-admin/options-general.php?page=pwa_extension';

		$this->assertEquals(
			$admin_page_url,
			menu_page_url( 'pwa_extension', false ),
			'PWA extension Settings Page was not created'
		);

		wp_set_current_user( $current_user );
	}

	/**
	 * Tests `register_plugin_settings` function.
	 *
	 * @covers ::register_plugin_settings
	 *
	 * @return void
	 */
	public function test_register_plugin_settings() {
		global $new_whitelist_options, $wp_settings_fields;

		$this->_instance->register_plugin_settings();

		$this->assertarrayHasKey(
			'pwa_extension',
			$new_whitelist_options,
			'Option Group pwa_extension has not been created'
		);

		$settings = $new_whitelist_options['pwa_extension'];

		$this->assertCount( 1, $settings, 'The Settings Group amp-admanager-menu 1 setting' );

		$this->assertContains( 'rt_pwa_extension_options',
			$settings,
			sprintf( 'Setting "%1$s" has not been created', 'amp-admanager-menu-settings' )
		);

		$this->assertarrayHasKey(
			'pwa-extension-setting-options',
			$wp_settings_fields['options-general.php'],
			'pwa-extension-setting-options setting section has not been created'
		);

		$this->assertarrayHasKey(
			'pwa-extension-routes-input',
			$wp_settings_fields['options-general.php']['pwa-extension-setting-options'],
			'pwa-extension-routes-input types section settings fields has not been created'
		);

		$this->assertEquals( 'Form Routes', $wp_settings_fields['options-general.php']['pwa-extension-setting-options']['pwa-extension-routes-input']['title']);

	}

	/**
	 * Tests `options_page` function.
	 *
	 * @covers ::options_page
	 *
	 * @return void
	 */
	public function test_options_page() {

		// Test access of user with privileges.
		wp_set_current_user( $this->factory()->user->create( array( 'role' => 'administrator' ) ) );
		$output = Utility::buffer_and_return( [ $this->_instance, 'options_page' ] );
		$this->assertContains( 'PWA Extension Settings', $output );

		// Test access of user without privileges.
		wp_set_current_user( $this->factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$output = Utility::buffer_and_return( [ $this->_instance, 'options_page' ] );
		$this->assertContains( 'You do not have sufficient permissions to access this page.', $output );
	}

	/**
	 * Tests `callback_url_input` function.
	 *
	 * @covers ::callback_url_input
	 *
	 * @return void
	 */
	public function test_callback_url_input() {

		update_option( 'rt_pwa_extension_options', 'test_route' );
		$output = Utility::buffer_and_return( [ $this->_instance, 'callback_url_input' ] );

		$expected_output = '<textarea name="rt_pwa_extension_options" rows="5">test_route</textarea><p><i>Add multiple routes in separate new line.</i></p>';
		$this->assertEquals( $expected_output, $output );

		delete_option( 'rt_pwa_extension_options' );

	}

	/**
	 * Tests `validate_setting` function.
	 *
	 * @covers ::validate_setting
	 *
	 * @return void
	 */
	public function test_validate_setting() {

		global $wp_settings_errors;

		$this->_instance->validate_setting( 'ValidInput' );
		$this->assertNull( $wp_settings_errors );

		$this->_instance->validate_setting( 'Input With Space' );
		$this->assertEquals( 'pwa-extension-white-space-not-allowed', $wp_settings_errors[0]['code'] );

		$this->_instance->validate_setting( '<h1>Invalid Input</h1>' );
		$this->assertEquals( 'pwa-extension-invalid-route', $wp_settings_errors[1]['code'] );

	}

}
