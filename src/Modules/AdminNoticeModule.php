<?php
/**
 * Admin notice module.
 *
 * @package WP24H\PluginBoilerplate
 */

namespace WP24H\PluginBoilerplate\Modules;

use WP24H\PluginBoilerplate\Contracts\Module;
use WP24H\PluginBoilerplate\Support\Options;

final class AdminNoticeModule implements Module {
	public function __construct( private Options $options ) {}

	public function id(): string {
		return 'admin_notice';
	}

	public function label(): string {
		return __( 'Admin notice', 'wp24h-plugin-boilerplate' );
	}

	public function description(): string {
		return __( 'Shows the configured message to administrators as a dismissible notice.', 'wp24h-plugin-boilerplate' );
	}

	public function register(): void {
		add_action( 'admin_notices', array( $this, 'render' ) );
	}

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="notice notice-info is-dismissible">
			<p><strong><?php echo esc_html( (string) Options::display_value( 'headline', $this->options->get( 'headline', '' ) ) ); ?></strong></p>
			<p><?php echo esc_html( (string) Options::display_value( 'message', $this->options->get( 'message', '' ) ) ); ?></p>
		</div>
		<?php
	}
}

