<?php
/**
 * Plugin Name: WP24H Plugin Boilerplate
 * Plugin URI:  https://github.com/WP24Horas/wp24h-plugin-boilerplate
 * Description: A production-ready, modular WordPress plugin starter with configurable features.
 * Version:     1.0.0
 * Author:      WP24Horas
 * Author URI:  https://wp24horas.com.br
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp24h-plugin-boilerplate
 * Domain Path: /languages
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * Tested up to: 7.0
 *
 * @package WP24H\PluginBoilerplate
 */

defined( 'ABSPATH' ) || exit;

define( 'WP24H_PLUGIN_BOILERPLATE_VERSION', '1.0.0' );
define( 'WP24H_PLUGIN_BOILERPLATE_FILE', __FILE__ );
define( 'WP24H_PLUGIN_BOILERPLATE_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP24H_PLUGIN_BOILERPLATE_URL', plugin_dir_url( __FILE__ ) );

$wp24h_autoload = WP24H_PLUGIN_BOILERPLATE_PATH . 'vendor/autoload.php';

if ( file_exists( $wp24h_autoload ) ) {
	require $wp24h_autoload;
} else {
	spl_autoload_register(
		static function ( string $class_name ): void {
			$prefix = 'WP24H\\PluginBoilerplate\\';

			if ( 0 !== strpos( $class_name, $prefix ) ) {
				return;
			}

			$relative_class = substr( $class_name, strlen( $prefix ) );
			$file           = WP24H_PLUGIN_BOILERPLATE_PATH . 'src/' . str_replace( '\\', '/', $relative_class ) . '.php';

			if ( file_exists( $file ) ) {
				require $file;
			}
		}
	);
}

register_activation_hook( __FILE__, array( WP24H\PluginBoilerplate\Core\Plugin::class, 'activate' ) );

add_action(
	'plugins_loaded',
	static function (): void {
		WP24H\PluginBoilerplate\Core\Plugin::instance()->boot();
	}
);
