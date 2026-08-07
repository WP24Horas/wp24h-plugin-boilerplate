# Module API

Modules are small, independently activatable capabilities.

Each module implements four methods:

- `id()` returns a stable, machine-readable identifier.
- `label()` returns the translated name shown in settings.
- `description()` explains the capability to an administrator.
- `register()` attaches WordPress hooks only after the module is enabled.

## Example

```php
namespace Vendor\Plugin\Modules;

use WP24H\PluginBoilerplate\Contracts\Module;

final class HealthCheckModule implements Module {
	public function id(): string {
		return 'health_check';
	}

	public function label(): string {
		return __( 'Health check', 'vendor-plugin' );
	}

	public function description(): string {
		return __( 'Adds product-specific diagnostics.', 'vendor-plugin' );
	}

	public function register(): void {
		add_filter( 'site_status_tests', array( $this, 'add_tests' ) );
	}
}
```

Register external modules through `wp24h_plugin_boilerplate_modules`. The settings page discovers them automatically.

Use unique IDs. Changing an ID after release creates a new setting and leaves the old value behind.

