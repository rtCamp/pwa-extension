<?php
/**
 * Admin Settings Page class
 *
 * @package rt-pwa-extensions
 */

namespace RT\PWA\Inc;

use \RT\PWA\Inc\Traits\Singleton;

/**
 * Class Admin_Settings
 */
class Admin_Settings {
	use Singleton;

	/**
	 * @var string settings page slug.
	 */
	private $page_slug = 'pwa_extension';

	/**
	 * Admin_Settings constructor.
	 */
	protected function __construct() {
		// Setup hooks.
		add_action( 'admin_init', array( $this, 'register_plugin_settings' ) );
		add_action( 'admin_menu', array( $this, 'register_options_page' ) );
	}

	/**
	 * Register plugin setting.
	 */
	public function register_plugin_settings() {
		register_setting( $this->page_slug, 'rt_pwa_extension_options', array( $this, 'validate_setting' ) );
		add_settings_section( 'pwa-extension-setting-options', __( 'Form URL Options', 'pwa-extension' ), array( $this, 'callback_pwa_setting_section' ), 'options-general.php' );
		add_settings_field( 'pwa-extension-routes-input', __( 'Form Routes', 'pwa-extension' ), array( $this, 'callback_url_input' ), 'options-general.php', 'pwa-extension-setting-options' );
	}

	/**
	 * Setting section callback.
	 */
	public function callback_pwa_setting_section() {
		esc_html_e( 'Add your form routes for offline form submission.' );
	}

	/**
	 * Setting Field callback.
	 */
	public function callback_url_input() {
		$value = get_option( 'rt_pwa_extension_options' );
		printf( '<textarea name="rt_pwa_extension_options" rows="5">%1$s</textarea>', esc_html( $value ) );
	}

	/**
	 * Register plugin option page.
	 */
	public function register_options_page() {
		add_options_page( __( 'PWA Extension Settings', 'pwa-extension' ), __( 'PWA Extension Settings', 'pwa-extension' ), 'manage_options', $this->page_slug, array( $this, 'options_page' ) );
	}

	/**
	 * Settings page form.
	 */
	public function options_page() {
		?>
		<div>
			<h1><?php esc_html_e( 'PWA Extension Settings', 'pwa-extension' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( $this->page_slug ); ?>
				<?php do_settings_sections( 'options-general.php' ); ?>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Validate setting input.
	 *
	 * @param $input string
	 *
	 * @return array.
	 */
	public function validate_setting( $input ) {

		$validated = sanitize_text_field( $input );

		// Set error message when invalid.
		if ( $validated !== $input ) {
			$type    = 'error';
			$message = __( 'Invalid Input', 'pwa-extension' );
			add_settings_error(
				'pwa_extension_form_urls',
				esc_attr( 'settings_updated' ),
				$message,
				$type
			);
		}

		return $validated;
	}
}
