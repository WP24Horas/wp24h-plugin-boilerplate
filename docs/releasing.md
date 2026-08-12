# Release process

The project ships releases as a clean ZIP built from the repository root and filtered by `.distignore`.

## Before releasing

Keep these values aligned:

- `Version` in `wp24h-plugin-boilerplate.php`.
- `WP24H_PLUGIN_BOILERPLATE_VERSION` in `wp24h-plugin-boilerplate.php`.
- `Stable tag` in `readme.txt`.
- The matching version section in `CHANGELOG.md`.

Run the full quality suite:

```bash
composer install
composer check
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

Before publishing, the workflow validates version metadata, runs the full Composer quality suite and builds the same reproducible ZIP used locally. It then attaches the ZIP to the GitHub Release.

Re-running the workflow for an existing release replaces the ZIP asset instead of creating a duplicate release.
