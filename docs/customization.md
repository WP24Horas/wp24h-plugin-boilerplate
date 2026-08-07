# Customization checklist

Use this checklist before shipping a plugin derived from the boilerplate.

## Identity

- Rename the plugin folder and main file.
- Replace `WP24H Plugin Boilerplate` in the plugin header and documentation.
- Replace the `wp24h-plugin-boilerplate` text domain everywhere.
- Replace `WP24H\PluginBoilerplate` with a unique PSR-4 namespace.
- Rename constants beginning with `WP24H_PLUGIN_BOILERPLATE_`.
- Rename the option key and public hooks to prevent collisions.

## Product behavior

- Remove example modules not required by the product.
- Add one class per capability and implement the `Module` contract.
- Keep expensive work out of constructors; register behavior through hooks.
- Define explicit REST permission callbacks.
- Add nonces and capability checks to every state-changing action.
- Decide whether uninstall should preserve or remove user data.

## Release quality

- Update supported WordPress and PHP versions.
- Add integration or end-to-end coverage for user-visible workflows.
- Run `composer check`.
- Test activation without a generated `vendor/` directory.
- Generate translations and a distributable ZIP without development files.
- Replace repository URLs, security contact and author information.

