<?php
/**
 * Site Health diagnostics module.
 *
 * @package WP24H\PluginBoilerplate
 */

namespace WP24H\PluginBoilerplate\Modules;

use WP24H\PluginBoilerplate\Contracts\Module;

final class SiteHealthModule implements Module {
	public function id(): string {
		return 'site_health';
	}

	public function label(): string {
		return __( 'Site Health diagnostics', 'wp24h-plugin-boilerplate' );
	}

	public function description(): string {
		return __( 'Adds a small direct Site Health test demonstrating plugin runtime diagnostics.', 'wp24h-plugin-boilerplate' );
	}

	public function register(): void {
		add_filter( 'site_status_tests', array( $this, 'register_test' ) );
	}

	/**
	 * Register one direct Site Health test.
	 *
	 * @param array<string, mixed> $tests Existing tests.
	 * @return array<string, mixed>
	 */
	public function register_test( array $tests ): array {
		if ( ! isset( $tests['direct'] ) || ! is_array( $tests['direct'] ) ) {
			$tests['direct'] = array();
		}

		$tests['direct']['wp24h_plugin_boilerplate_runtime'] = array(
			'label' => __( 'WP24H Boilerplate runtime', 'wp24h-plugin-boilerplate' ),
			'test'  => array( $this, 'test_runtime' ),
		);

		return $tests;
	}

	/**
	 * Report the runtime baseline used by the plugin.
	 *
	 * @return array<string, mixed>
	 */
	public function test_runtime(): array {
		$wp_version = (string) get_bloginfo( 'version' );
		$php_ok     = version_compare( PHP_VERSION, '8.1', '>=' );
		$wp_ok      = version_compare( $wp_version, '6.5', '>=' );
		$status     = $php_ok && $wp_ok ? 'good' : 'recommended';

		return array(
			'label'       => $php_ok && $wp_ok
				? __( 'The plugin runtime meets the documented baseline', 'wp24h-plugin-boilerplate' )
				: __( 'The plugin runtime is below the documented baseline', 'wp24h-plugin-boilerplate' ),
			'status'      => $status,
			'badge'       => array(
				'label' => __( 'WP24H Boilerplate', 'wp24h-plugin-boilerplate' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				esc_html(
					sprintf(
						/* translators: 1: plugin version, 2: WordPress version, 3: PHP version. */
						__( 'Plugin %1$s is running on WordPress %2$s and PHP %3$s.', 'wp24h-plugin-boilerplate' ),
						WP24H_PLUGIN_BOILERPLATE_VERSION,
						$wp_version,
						PHP_VERSION
					)
				)
			),
			'actions'     => '',
			'test'        => 'wp24h_plugin_boilerplate_runtime',
		);
	}
}
