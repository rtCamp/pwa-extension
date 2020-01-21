<?php
/**
 * Plugin Name: rt PWA Extensions
 * Description: Enabling PWA features like offline caching etc. (requires pwa plugin activated.)
 * Author: rtCamp, chandrapatel, pradeep910
 * Author URI: https://rtcamp.com/?utm_source=rt-pwa-extensions-plugin
 * Version: 1.0.2
 *
 * @package rt-pwa-extensions
 */

use RT\PWA\Inc\Web_Push;

define( 'RT_PWA_EXTENSIONS_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'RT_PWA_EXTENSIONS_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

// phpcs:disable WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant
require_once RT_PWA_EXTENSIONS_PATH . '/inc/helpers/autoloader.php';
// phpcs:enable WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant

/**
 * To load plugin manifest class.
 *
 * @return void
 */
function rt_pwa_extensions_plugin_loader() {
	\RT\PWA\Inc\Plugin::get_instance();
}
rt_pwa_extensions_plugin_loader();

register_activation_hook( __FILE__, array( Web_Push::get_instance(), 'susbcription_data_table' ) );
