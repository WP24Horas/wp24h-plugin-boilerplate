<?php
/**
 * Protected REST API module.
 *
 * @package WP24H\PluginBoilerplate
 */

namespace WP24H\PluginBoilerplate\Modules;

use WP24H\PluginBoilerplate\Contracts\Module;
use WP24H\PluginBoilerplate\Support\Options;
use WP_REST_Request;
use WP_REST_Response;

final class ProtectedRestModule implements Module {
	public function __construct( private Options $options ) {}

	public function id(): string {
		return 'protected_rest';
	}

	public function label(): string {
		return __( 'Protected REST endpoint', 'wp24h-plugin-boilerplate' );
	}

	public function description(): string {
		return __( 'Demonstrates an authenticated POST endpoint with capability checks, validation and sanitization.', 'wp24h-plugin-boilerplate' );
	}

	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'register_route' ) );
	}

	public function register_route(): void {
		register_rest_route(
			(string) $this->options->get( 'rest_namespace', 'wp24h-boilerplate/v1' ),
			'/protected-message',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'respond' ),
				'permission_callback' => array( $this, 'can_manage' ),
				'args'                => array(
					'message' => array(
						'required'          => true,
						'type'              => 'string',
						'validate_callback' => array( $this, 'validate_message' ),
						'sanitize_callback' => array( $this, 'sanitize_message' ),
					),
				),
			)
		);
	}

	public function can_manage(): bool {
		return current_user_can( 'manage_options' );
	}

	public function validate_message( mixed $value ): bool {
		if ( ! is_string( $value ) ) {
			return false;
		}

		$length = function_exists( 'mb_strlen' ) ? mb_strlen( $value ) : strlen( $value );

		return '' !== trim( $value ) && $length <= 200;
	}

	public function sanitize_message( mixed $value ): string {
		return sanitize_text_field( (string) $value );
	}

	public function respond( WP_REST_Request $request ): WP_REST_Response {
		return new WP_REST_Response(
			array(
				'message' => (string) $request->get_param( 'message' ),
				'version' => WP24H_PLUGIN_BOILERPLATE_VERSION,
			),
			200
		);
	}
}
