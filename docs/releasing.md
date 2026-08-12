# Release process

The project ships releases as a clean ZIP built from the repository root and filtered by `.distignore`.

## Before releasing

Keep these values aligned:

- `Version` in `wp24h-plugin-boilerplate.php`.
- `WP24H_PLUGIN_BOILERPLATE_VERSION` in `wp24h-plugin-boilerplate.php`.
- `Stable tag` in `readme.txt`.
- The matching version section in `CHANGELOG.md`.

Run the plugin quality contract and the boilerplate-specific generator smoke separately:

```bash
composer install
composer check
composer scaffold:smoke
```

`composer check` is intentionally self-contained and is retained by generated plugins. `composer scaffold:smoke` belongs only to the boilerplate and validates the complete boilerplate → plugin → module generation path.

For the deepest local proof, including dependency installation and `composer check` inside a generated plugin, run:

```bash
composer scaffold:smoke:full
```

Build the distributable locally:

```bash
bash scripts/build-release.sh
```

The resulting file is `dist/wp24h-plugin-boilerplate.zip`.

## GitHub release

The `Release` workflow supports two safe entry points:

1. Run it manually from GitHub Actions and provide a semantic version such as `1.0.0`.
2. Push a tag matching `v*.*.*`.

Before publishing, the workflow validates version metadata, runs `composer check`, runs `composer scaffold:smoke` and builds the same reproducible ZIP used locally. It then attaches the ZIP to the GitHub Release.

Re-running the workflow for an existing release replaces the ZIP asset instead of creating a duplicate release.
