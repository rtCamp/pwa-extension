<?php
/**
 * Plugin Name: rt PWA Push notifications
 * Description: Push notifications
 * Author: rtCamp, chandrapatel, pradeep910
 * Author URI: https://rtcamp.com/?utm_source=rt-pwa-extensions-plugin
 * Version: 1.0.2
 *
 * @package rt-pwa-extensions
 */

use RT\PWA\Inc\Web_Push;
use RT\PWA\Inc\Admin;

define( 'RT_PWA_EXTENSIONS_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'RT_PWA_EXTENSIONS_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

// phpcs:disable WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant
require_once RT_PWA_EXTENSIONS_PATH . '/inc/helpers/autoloader.php';
// phpcs:enable WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant


Admin::get_instance();

register_activation_hook( __FILE__, array( Web_Push::get_instance(), 'susbcription_data_table' ) );
