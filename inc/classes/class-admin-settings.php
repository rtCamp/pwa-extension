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
	 * Setting page slug.
	 *
	 * @var string
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
		$args = array(
			'type'              => 'string',
			'description'       => esc_html__( 'Form routes for offline form submission.', 'rt-pwa-extensions' ),
			'sanitize_callback' => array( $this, 'validate_setting' ),
		);

		register_setting( $this->page_slug, 'rt_pwa_extension_options', $args );
		add_settings_section( 'pwa-extension-setting-options', esc_html__( 'Form Routes Options', 'rt-pwa-extensions' ), array( $this, 'callback_pwa_setting_section' ), 'options-general.php' );
		add_settings_field( 'pwa-extension-routes-input', esc_html__( 'Form Routes', 'rt-pwa-extensions' ), array( $this, 'callback_url_input' ), 'options-general.php', 'pwa-extension-setting-options' );
	}

	/**
	 * Setting section callback.
	 */
	public function callback_pwa_setting_section() {
		esc_html_e( 'Add your form routes for offline form submission.', 'rt-pwa-extensions' );
	}

	/**
	 * Setting Field callback.
	 */
	public function callback_url_input() {
		$value = get_option( 'rt_pwa_extension_options' );
		printf( '<textarea name="rt_pwa_extension_options" rows="5">%1$s</textarea>', esc_textarea( $value ) );
		printf( '<p><i>%1$s</i></p>', esc_html__( 'Add multiple routes in separate new line.', 'rt-pwa-extensions' ) );
	}

	/**
	 * Register plugin option page.
	 */
	public function register_options_page() {
		add_options_page( esc_html__( 'PWA Extension Settings', 'rt-pwa-extensions' ), esc_html__( 'PWA Extension Settings', 'rt-pwa-extensions' ), 'manage_options', $this->page_slug, array( $this, 'options_page' ) );
	}

	/**
	 * Settings page form.
	 */
	public function options_page() {

		// User Require Capability to edit page.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'rt-pwa-extensions' ) );
		}

		?>
		<div>
			<h1><?php esc_html_e( 'PWA Extension Settings', 'rt-pwa-extensions' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( $this->page_slug );
				do_settings_sections( 'options-general.php' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Validate setting input.
	 *
	 * @param string $input input string.
	 *
	 * @return string.
	 */
	public function validate_setting( $input ) {

		$validated = sanitize_textarea_field( $input );

		// Set error message when invalid.
		if ( $validated !== $input ) {
			$type    = 'error';
			$message = esc_html__( 'Invalid \'Form Route\'. Please enter valid form routes.', 'rt-pwa-extensions' );
			add_settings_error(
				'rt_pwa_extension_options',
				'pwa-extension-invalid-route',
				$message,
				$type
			);
		}

		if ( $validated === $input ) {
			$routes      = explode( PHP_EOL, $input );
			$white_space = false;
			foreach ( $routes as $route ) {
				if ( false !== strpos( $route, ' ' ) ) {
					$white_space = true;
					break;
				}
			}

			// Set error message for white spaces.
			if ( true === $white_space ) {
				add_settings_error(
					'rt_pwa_extension_options',
					'pwa-extension-white-space-not-allowed',
					esc_html__( 'White space are not allowed in \'Form Routes\'.', 'rt-pwa-extensions' ),
					'error'
				);
			}
		}

		return $validated;
	}
}
