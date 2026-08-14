<?php
/**
 * Dashboard widget module.
 *
 * @package WP24H\PluginBoilerplate
 */

namespace WP24H\PluginBoilerplate\Modules;

use WP24H\PluginBoilerplate\Contracts\Module;
use WP24H\PluginBoilerplate\Support\Options;

final class DashboardWidgetModule implements Module {
	public function __construct( private Options $options ) {}

	public function id(): string {
		return 'dashboard_widget';
	}

	public function label(): string {
		return __( 'Dashboard widget', 'wp24h-plugin-boilerplate' );
	}

	public function description(): string {
		return __( 'Adds a configurable example card to the WordPress dashboard.', 'wp24h-plugin-boilerplate' );
	}

	public function register(): void {
		add_action( 'wp_dashboard_setup', array( $this, 'add_widget' ) );
	}

	public function add_widget(): void {
		wp_add_dashboard_widget(
			'wp24h_plugin_boilerplate_widget',
			(string) Options::display_value( 'headline', $this->options->get( 'headline', '' ) ),
			array( $this, 'render' )
		);
	}

	public function render(): void {
		echo '<p>' . esc_html( (string) Options::display_value( 'message', $this->options->get( 'message', '' ) ) ) . '</p>';
	}
}
