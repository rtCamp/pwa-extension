<?php

namespace RT\PWA\Inc;

use \RT\PWA\Inc\Traits\Singleton;
use \Minishlink\WebPush\WebPush;
use \Minishlink\WebPush\Subscription;

class Admin {
	use Singleton;

	public function __construct() {

		add_action( 'admin_menu', array( $this, 'books_options_page' ) );
		add_action( 'admin_init', array( $this, 'books_settings_init' ) );

	}

	/**
	 * Creates settings page in admin menu
	 */
	public function books_options_page() {
		add_submenu_page(
			'options-general.php',
			esc_html__( 'rt PWA Settings', 'rt-pwa' ),
			esc_html__( 'rt PWA settings', 'rt-pwa' ),
			'manage_options',
			'pwa_settings',
			array( $this, 'options_page_html' )
		);
	}

	/**
	 * Renders settings html
	 */
	public function options_page_html() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$private_key = get_option( 'vapid_private_key' ) ?? '';
		$public_key  = get_option( 'vapid_public_key' ) ?? '';
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<form action="options.php" method="post">
				<h2> Push Notification Settings</h2>
				<?php settings_fields( 'web_push' ); ?>
				<table>
					<tr>
						<th>
							<label for="private_key">Private Key(base64 encoded)</label>
						</th>
						<td>
							<textarea id="private_key" name="vapid_private_key"><?php echo esc_html( $private_key ); ?></textarea>
						</td>
					</tr>
					<tr>
						<th>
							<label for="public_key">Public Key(base64 encoded)</label>
						</th>
						<td>
							<textarea id="public_key" name="vapid_public_key"><?php echo esc_html( $public_key ); ?></textarea>
						</td>
					</tr>
				</table>

				<?php submit_button( 'Save Settings' ); ?>
			</form>

		</div>
		<?php
	}

	/**
	 * Initializes settings fields on settings page
	 */
	public function books_settings_init() {
		register_setting( 'web_push', 'vapid_private_key' );
		register_setting( 'web_push', 'vapid_public_key' );
	}

}
