<?php
/**
 * Shortcode module.
 *
 * @package WP24H\PluginBoilerplate
 */

namespace WP24H\PluginBoilerplate\Modules;

use WP24H\PluginBoilerplate\Contracts\Module;
use WP24H\PluginBoilerplate\Support\Options;

final class ShortcodeModule implements Module {
	public function __construct( private Options $options ) {}

	public function id(): string {
		return 'shortcode';
	}

	public function label(): string {
		return __( 'Example shortcode', 'wp24h-plugin-boilerplate' );
	}

	public function description(): string {
		return __( 'Registers [wp24h_boilerplate] with configurable content.', 'wp24h-plugin-boilerplate' );
	}

	public function register(): void {
		add_shortcode( 'wp24h_boilerplate', array( $this, 'render' ) );
	}

	/**
	 * @param array<string, mixed> $attributes Shortcode attributes.
	 */
	public function render( array $attributes = array() ): string {
		$attributes = shortcode_atts(
			array( 'show_headline' => 'yes' ),
			$attributes,
			'wp24h_boilerplate'
		);

		$headline = (string) Options::display_value( 'headline', $this->options->get( 'headline', '' ) );
		$message  = (string) Options::display_value( 'message', $this->options->get( 'message', '' ) );
		$color    = (string) $this->options->get( 'accent_color', '#2271b1' );

		ob_start();
		?>
		<section class="wp24h-boilerplate" style="border-left: 4px solid <?php echo esc_attr( $color ); ?>; padding: 1rem;">
			<?php if ( 'yes' === $attributes['show_headline'] ) : ?>
				<h2><?php echo esc_html( $headline ); ?></h2>
			<?php endif; ?>
			<p><?php echo esc_html( $message ); ?></p>
		</section>
		<?php

		return (string) ob_get_clean();
	}
}

