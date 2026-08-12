<?php
/**
 * Plugin orchestrator.
 *
 * @package WP24H\PluginBoilerplate
 */

namespace WP24H\PluginBoilerplate\Core;

use WP24H\PluginBoilerplate\Admin\SettingsPage;
use WP24H\PluginBoilerplate\Contracts\Module;
use WP24H\PluginBoilerplate\Modules\AdminNoticeModule;
use WP24H\PluginBoilerplate\Modules\DashboardWidgetModule;
use WP24H\PluginBoilerplate\Modules\RestApiModule;
use WP24H\PluginBoilerplate\Modules\ShortcodeModule;
use WP24H\PluginBoilerplate\Modules\SiteHealthModule;
use WP24H\PluginBoilerplate\Support\Options;

final class Plugin {
	private static ?self $instance = null;

	private bool $booted = false;

	private Options $options;

	/**
	 * @var array<string, Module>
	 */
	private array $modules = array();

	private function __construct() {
		$this->options = new Options();
	}

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function activate(): void {
		if ( false === get_option( Options::KEY, false ) ) {
			add_option( Options::KEY, Options::defaults() );
		}
	}

	public function boot(): void {
		if ( $this->booted ) {
			return;
		}

		$this->booted  = true;
		$this->modules = $this->build_modules();

		if ( is_admin() ) {
			( new SettingsPage( $this->options, $this->modules ) )->register();
		}

		foreach ( $this->modules as $module ) {
			if ( $this->options->module_enabled( $module->id() ) ) {
				$module->register();
			}
		}

		load_plugin_textdomain(
			'wp24h-plugin-boilerplate',
			false,
			dirname( plugin_basename( WP24H_PLUGIN_BOILERPLATE_FILE ) ) . '/languages'
		);

		/**
		 * Fires after the plugin and enabled modules have booted.
		 *
		 * @param self $plugin Plugin instance.
		 */
		do_action( 'wp24h_plugin_boilerplate_loaded', $this );
	}

	/**
	 * @return array<string, Module>
	 */
	private function build_modules(): array {
		$modules = array(
			new ShortcodeModule( $this->options ),
			new RestApiModule( $this->options ),
			new DashboardWidgetModule( $this->options ),
			new AdminNoticeModule( $this->options ),
			new SiteHealthModule(),
		);

		/**
		 * Filters available modules before they are indexed and registered.
		 *
		 * @param array<int, mixed> $modules Candidate module instances.
		 */
		$modules = apply_filters( 'wp24h_plugin_boilerplate_modules', $modules );
		$indexed = array();

		foreach ( $modules as $module ) {
			if ( $module instanceof Module ) {
				$indexed[ $module->id() ] = $module;
			}
		}

		return $indexed;
	}
}
