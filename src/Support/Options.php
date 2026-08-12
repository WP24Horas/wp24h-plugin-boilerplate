<?php
/**
 * Plugin options.
 *
 * @package WP24H\PluginBoilerplate
 */

namespace WP24H\PluginBoilerplate\Support;

final class Options {
	public const KEY = 'wp24h_plugin_boilerplate_settings';

	/**
	 * Return the default configuration.
	 *
	 * @return array<string, mixed>
	 */
	public static function defaults(): array {
		return array(
			'modules'        => array(
				'shortcode'        => true,
				'rest_api'         => true,
				'protected_rest'   => false,
				'dashboard_widget' => false,
				'admin_notice'     => false,
				'site_health'      => false,
			),
			'headline'       => __( 'Built with the WP24H Plugin Boilerplate', 'wp24h-plugin-boilerplate' ),
			'message'        => __( 'Replace this message in Settings → WP24H Boilerplate.', 'wp24h-plugin-boilerplate' ),
			'accent_color'   => '#2271b1',
			'rest_namespace' => 'wp24h-boilerplate/v1',
		);
	}

	/**
	 * Return settings merged with defaults.
	 *
	 * @return array<string, mixed>
	 */
	public function all(): array {
		$stored = get_option( self::KEY, array() );

		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		$settings            = wp_parse_args( $stored, self::defaults() );
		$settings['modules'] = wp_parse_args(
			isset( $stored['modules'] ) && is_array( $stored['modules'] ) ? $stored['modules'] : array(),
			self::defaults()['modules']
		);

		/**
		 * Filters the complete plugin configuration.
		 *
		 * @param array<string, mixed> $settings Plugin settings.
		 */
		return apply_filters( 'wp24h_plugin_boilerplate_settings', $settings );
	}

	/**
	 * Return one setting.
	 *
	 * @return mixed
	 */
	public function get( string $key, mixed $fallback = null ): mixed {
		$settings = $this->all();

		return $settings[ $key ] ?? $fallback;
	}

	/**
	 * Determine whether a module is enabled.
	 */
	public function module_enabled( string $module_id ): bool {
		$modules = $this->get( 'modules', array() );

		return is_array( $modules ) && ! empty( $modules[ $module_id ] );
	}
}
