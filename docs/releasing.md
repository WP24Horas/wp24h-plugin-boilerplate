# Release process

The project ships releases as a clean ZIP built from the repository root and filtered by `.distignore`.

## Before releasing

Keep these values aligned:

- `Version` in `wp24h-plugin-boilerplate.php`.
- `WP24H_PLUGIN_BOILERPLATE_VERSION` in `wp24h-plugin-boilerplate.php`.
- `Stable tag` in `readme.txt`.
- The matching version section in `CHANGELOG.md`.

Until the release is actually published, keep release notes under `## [Unreleased]`. Create the dated `## [1.0.0] - YYYY-MM-DD` section only after every gate passes and immediately before creating the immutable tag.

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

## Build and verify the distributable

Use the release packaging contract instead of building and inspecting the ZIP as unrelated steps:

```bash
composer release:package
```

This command runs:

1. `release:build` — creates `dist/wp24h-plugin-boilerplate.zip` from `.distignore`;
2. `release:verify` — verifies the package top-level directory, required runtime files and the absence of forbidden development/tooling paths.

The build script uses an isolated temporary directory and cleans it automatically. The ZIP verifier intentionally avoids Bash-only helpers such as `mapfile`, keeping the verification path compatible with Bash 3.2 environments as well as newer Bash versions.

You can still run the phases individually when debugging:

```bash
composer release:build
composer release:verify
```

## Default release path

The default release path is local-first and explicit:

1. complete the local quality and generator checks;
2. validate the plugin in a disposable WordPress installation;
3. run `composer release:package`;
4. install and activate that exact ZIP in a clean WordPress instance;
5. finalize the `CHANGELOG.md` entry with the actual release date;
6. create the immutable version tag only after the validated commit is final;
7. create the GitHub Release explicitly and attach the exact verified ZIP.

Creating or pushing a tag does **not** trigger GitHub Actions automatically.

## Optional GitHub release workflow

The `Release` workflow is `workflow_dispatch` only. It exists as an optional deliberate release tool when GitHub Actions usage is desired.

When run manually with a semantic version such as `1.0.0`, it validates version metadata, installs dependencies, runs `composer check`, runs `composer scaffold:smoke`, builds **and verifies** the distribution ZIP, then publishes or updates the corresponding GitHub Release asset.

Do not use the workflow as a substitute for the documented runtime and distribution gates. In particular, the first `v1.0.0` should only be published after the release checklist issue is complete.
