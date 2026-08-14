<?php
/**
 * Settings screen.
 *
 * @package WP24H\PluginBoilerplate
 */

namespace WP24H\PluginBoilerplate\Admin;

use WP24H\PluginBoilerplate\Contracts\Module;
use WP24H\PluginBoilerplate\Support\Options;

final class SettingsPage {
	private Options $options;

	/**
	 * @var array<string, Module>
	 */
	private array $modules;

	/**
	 * @param array<string, Module> $modules Available modules.
	 */
	public function __construct( Options $options, array $modules ) {
		$this->options = $options;
		$this->modules = $modules;
	}

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_filter(
			'plugin_action_links_' . plugin_basename( WP24H_PLUGIN_BOILERPLATE_FILE ),
			array( $this, 'add_action_link' )
		);
	}

	public function add_page(): void {
		add_options_page(
			__( 'WP24H Plugin Boilerplate', 'wp24h-plugin-boilerplate' ),
			__( 'WP24H Boilerplate', 'wp24h-plugin-boilerplate' ),
			'manage_options',
			'wp24h-plugin-boilerplate',
			array( $this, 'render' )
		);
	}

	public function register_settings(): void {
		register_setting(
			'wp24h_plugin_boilerplate',
			Options::KEY,
			array(
				'type'              => 'array',
				'default'           => Options::defaults(),
				'sanitize_callback' => array( $this, 'sanitize' ),
			)
		);

		add_settings_section(
			'wp24h_modules',
			__( 'Feature modules', 'wp24h-plugin-boilerplate' ),
			static function (): void {
				echo '<p>' . esc_html__( 'Enable only the capabilities your generated plugin needs.', 'wp24h-plugin-boilerplate' ) . '</p>';
			},
			'wp24h-plugin-boilerplate'
		);

		foreach ( $this->modules as $module ) {
			add_settings_field(
				'wp24h_module_' . $module->id(),
				$module->label(),
				array( $this, 'render_module_field' ),
				'wp24h-plugin-boilerplate',
				'wp24h_modules',
				array( 'module' => $module )
			);
		}

		add_settings_section(
			'wp24h_content',
			__( 'Shared content', 'wp24h-plugin-boilerplate' ),
			'__return_false',
			'wp24h-plugin-boilerplate'
		);

		$this->add_text_field( 'headline', __( 'Headline', 'wp24h-plugin-boilerplate' ) );
		$this->add_text_field( 'message', __( 'Message', 'wp24h-plugin-boilerplate' ), 'textarea' );
		$this->add_text_field( 'accent_color', __( 'Accent color', 'wp24h-plugin-boilerplate' ), 'color' );
		$this->add_text_field( 'rest_namespace', __( 'REST namespace', 'wp24h-plugin-boilerplate' ) );
	}

	/**
	 * @param array<string, mixed> $input Raw input.
	 * @return array<string, mixed>
	 */
	public function sanitize( array $input ): array {
		$defaults = Options::defaults();
		$modules  = array();

		foreach ( $this->modules as $module ) {
			$modules[ $module->id() ] = ! empty( $input['modules'][ $module->id() ] );
		}

		$namespace = isset( $input['rest_namespace'] ) ? sanitize_text_field( $input['rest_namespace'] ) : $defaults['rest_namespace'];
		$namespace = trim( $namespace, '/' );

		if ( ! preg_match( '#^[a-z0-9-]+/v[1-9][0-9]*$#', $namespace ) ) {
			add_settings_error(
				Options::KEY,
				'invalid_rest_namespace',
				__( 'The REST namespace must follow the format vendor-name/v1.', 'wp24h-plugin-boilerplate' )
			);
			$namespace = $defaults['rest_namespace'];
		}

		$accent_color = sanitize_hex_color( $input['accent_color'] ?? '' );

		if ( ! $accent_color ) {
			$accent_color = $defaults['accent_color'];
		}

		return array(
			'modules'        => $modules,
			'headline'       => sanitize_text_field( $input['headline'] ?? $defaults['headline'] ),
			'message'        => sanitize_textarea_field( $input['message'] ?? $defaults['message'] ),
			'accent_color'   => $accent_color,
			'rest_namespace' => $namespace,
		);
	}

	/**
	 * @param array{module: Module} $args Field arguments.
	 */
	public function render_module_field( array $args ): void {
		$module   = $args['module'];
		$settings = $this->options->all();
		$enabled  = ! empty( $settings['modules'][ $module->id() ] );
		?>
		<label>
			<input
				type="checkbox"
				name="<?php echo esc_attr( Options::KEY ); ?>[modules][<?php echo esc_attr( $module->id() ); ?>]"
				value="1"
				<?php checked( $enabled ); ?>
			/>
			<?php echo esc_html( $module->description() ); ?>
		</label>
		<?php
	}

	/**
	 * @param array{key: string, type: string} $args Field arguments.
	 */
	public function render_text_field( array $args ): void {
		$key       = $args['key'];
		$type      = $args['type'];
		$value     = (string) Options::display_value( $key, $this->options->get( $key, '' ) );
		$name      = Options::KEY . '[' . $key . ']';
		$css_class = 'regular-text';

		if ( 'textarea' === $type ) {
			printf(
				'<textarea class="large-text" rows="4" name="%1$s">%2$s</textarea>',
				esc_attr( $name ),
				esc_textarea( $value )
			);
			return;
		}

		printf(
			'<input class="%1$s" type="%2$s" name="%3$s" value="%4$s" />',
			esc_attr( $css_class ),
			esc_attr( $type ),
			esc_attr( $name ),
			esc_attr( $value )
		);
	}

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'WP24H Plugin Boilerplate', 'wp24h-plugin-boilerplate' ); ?></h1>
			<p><?php echo esc_html__( 'Use this screen as the configuration foundation for your generated plugin.', 'wp24h-plugin-boilerplate' ); ?></p>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'wp24h_plugin_boilerplate' );
				do_settings_sections( 'wp24h-plugin-boilerplate' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * @param string[] $links Plugin action links.
	 * @return string[]
	 */
	public function add_action_link( array $links ): array {
		array_unshift(
			$links,
			'<a href="' . esc_url( admin_url( 'options-general.php?page=wp24h-plugin-boilerplate' ) ) . '">' .
			esc_html__( 'Settings', 'wp24h-plugin-boilerplate' ) . '</a>'
		);

		return $links;
	}

	private function add_text_field( string $key, string $label, string $type = 'text' ): void {
		add_settings_field(
			'wp24h_' . $key,
			$label,
			array( $this, 'render_text_field' ),
			'wp24h-plugin-boilerplate',
			'wp24h_content',
			array(
				'key'  => $key,
				'type' => $type,
			)
		);
	}
}
