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
		add_option( 'pwa_extension_option_name', 'This is my option value.' );
		register_setting( 'pwa_extension_group', 'pwa_extension_form_urls', array( $this, 'validate_setting' ) );
	}

	/**
	 * Register plugin option page.
	 */
	public function register_options_page() {
		add_options_page( 'PWA Extension Setting', 'PWA Extension Settings', 'manage_options', $this->page_slug, array( $this, 'options_page' ) );
	}

	/**
	 * Settings page form.
	 */
	public function options_page() {
		?>
		<div>
			<h2><?php esc_html_e( 'PWA Extension Setting', 'pwa-extension' ); ?></h2>
			<form method="post" action="options.php">
				<?php settings_fields( 'pwa_extension_group' ); ?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<label for="pwa_extension_form_urls"><?php esc_html_e( 'Offline Form URL:', 'pwa-extension' ); ?></label>
						</th>
						<td>
							<input type="text" id="pwa_extension_form_urls" name="pwa_extension_form_urls" value="<?php echo esc_attr( get_option( 'pwa_extension_form_urls' ) ); ?>" />
						</td>
					</tr>
				</table>
				<p><?php esc_html_e( 'Note: Use comma(`,`) to separate multiple URLs.', 'pwa-extension' ); ?></p>
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
