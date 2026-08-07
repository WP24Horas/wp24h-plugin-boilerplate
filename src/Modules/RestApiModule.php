<?php
/**
 * REST API module.
 *
 * @package WP24H\PluginBoilerplate
 */

namespace WP24H\PluginBoilerplate\Modules;

use WP24H\PluginBoilerplate\Contracts\Module;
use WP24H\PluginBoilerplate\Support\Options;
use WP_REST_Request;
use WP_REST_Response;

final class RestApiModule implements Module {
	public function __construct( private Options $options ) {}

	public function id(): string {
		return 'rest_api';
	}

	public function label(): string {
		return __( 'Example REST endpoint', 'wp24h-plugin-boilerplate' );
	}

	public function description(): string {
		return __( 'Exposes a read-only message endpoint under a configurable namespace.', 'wp24h-plugin-boilerplate' );
	}

	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'register_route' ) );
	}

	public function register_route(): void {
		register_rest_route(
			(string) $this->options->get( 'rest_namespace', 'wp24h-boilerplate/v1' ),
			'/message',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'respond' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	public function respond( WP_REST_Request $request ): WP_REST_Response {
		unset( $request );

		return new WP_REST_Response(
			array(
				'headline' => (string) $this->options->get( 'headline', '' ),
				'message'  => (string) $this->options->get( 'message', '' ),
				'version'  => WP24H_PLUGIN_BOILERPLATE_VERSION,
			),
			200
		);
	}
}
