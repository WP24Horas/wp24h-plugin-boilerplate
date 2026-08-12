# Module API

Modules are small, independently activatable capabilities.

Each module implements four methods:

- `id()` returns a stable, machine-readable identifier.
- `label()` returns the translated name shown in settings.
- `description()` explains the capability to an administrator.
- `register()` attaches WordPress hooks only after the module is enabled.

## Generate a module

The recommended path is to use the local module generator:

```bash
composer make:module -- \
  --class=AuditLogModule \
  --id=audit_log \
  --label="Audit log" \
  --description="Registers audit-log hooks."
```

The generator:

- infers the root PSR-4 namespace from `composer.json`;
- infers the plugin text domain from the main plugin header;
- creates `src/Modules/<Class>.php`;
- creates `tests/Unit/<Class>Test.php`;
- never overwrites an existing source or test file.

It is intentionally retained in plugins produced by the main scaffolder, so the same workflow continues after the project is created.

After generation, add the real WordPress hooks inside `register()` and register the module in `Core\Plugin::build_modules()` or through the public module filter.

## Manual example

You can still create modules manually when the generated skeleton is not appropriate:

```php
namespace Vendor\Plugin\Modules;

use Vendor\Plugin\Contracts\Module;

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

For the boilerplate itself, external modules are registered through `wp24h_plugin_boilerplate_modules`. In a scaffolded plugin, that hook is renamed deterministically from the plugin slug.

Use unique IDs. Changing an ID after release creates a new setting and leaves the old value behind.
