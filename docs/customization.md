# Customization checklist

Use this checklist before shipping a plugin derived from the boilerplate.

## Recommended starting point

Prefer the built-in scaffolder instead of manually renaming the base:

```bash
composer scaffold -- \
  --name="Acme Orders" \
  --slug=acme-orders \
  --namespace="Acme\\Orders" \
  --vendor=acme \
  --target="../acme-orders"
```

See [`scaffolding.md`](scaffolding.md) for all options and safety rules.

## Identity

The scaffolder handles the deterministic identity changes below:

- plugin name;
- plugin folder target;
- main plugin filename;
- text domain / slug;
- PSR-4 namespace;
- constants beginning with the boilerplate prefix;
- Composer package name.

After generation, review manually:

- plugin description and product-specific URLs;
- author and author URL when not supplied to the scaffolder;
- repository URLs, security contact and contribution links;
- option keys and public hooks when the plugin needs product-specific names;
- README and `readme.txt` product copy.

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
- Search for `wp24h-plugin-boilerplate`, `WP24H\\PluginBoilerplate` and `WP24H_PLUGIN_BOILERPLATE` before the first release; none should remain unless deliberately documented as provenance.
- Inspect the generated ZIP before publishing.
