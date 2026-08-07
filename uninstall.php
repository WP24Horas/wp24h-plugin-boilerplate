<?php
/**
 * Uninstall cleanup.
 *
 * Settings are preserved by default. Define WP24H_PLUGIN_BOILERPLATE_REMOVE_DATA
 * as true before uninstalling if the generated plugin should remove its data.
 *
 * @package WP24H\PluginBoilerplate
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

if ( defined( 'WP24H_PLUGIN_BOILERPLATE_REMOVE_DATA' ) && WP24H_PLUGIN_BOILERPLATE_REMOVE_DATA ) {
	delete_option( 'wp24h_plugin_boilerplate_settings' );
}
