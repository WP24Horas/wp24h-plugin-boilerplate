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

## Default release path

The default release path is local-first and explicit:

1. complete the local quality and generator checks;
2. validate the plugin in a disposable WordPress installation;
3. build and inspect the local ZIP;
4. create the immutable version tag only after the validated commit is final;
5. create the GitHub Release explicitly and attach the validated ZIP.

Creating or pushing a tag does **not** trigger GitHub Actions automatically.

## Optional GitHub release workflow

The `Release` workflow is `workflow_dispatch` only. It exists as an optional deliberate release tool when GitHub Actions usage is desired.

When run manually with a semantic version such as `1.0.0`, it validates version metadata, installs dependencies, runs `composer check`, runs `composer scaffold:smoke`, builds the distribution ZIP and publishes or updates the corresponding GitHub Release asset.

Do not use the workflow as a substitute for the documented runtime and distribution gates. In particular, the first `v1.0.0` should only be published after the release checklist issue is complete.
