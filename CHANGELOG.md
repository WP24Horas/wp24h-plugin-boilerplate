# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Initial public WP24H Plugin Boilerplate baseline.
- Composer-based development tooling.
- WordPress Coding Standards via PHPCS.
- Static analysis with PHPStan.
- PHPUnit + Brain Monkey test support.
- Local WordPress development support with `wp-env`.
- Security, contribution and community guidance.
- Safe local scaffolder for generating a new plugin with a custom name, slug, PSR-4 namespace, Composer package and constant prefix.
- Scaffold documentation with deterministic replacement and safety rules.
- `composer scaffold -- ...` as the recommended plugin creation flow.
- Structural scaffold smoke test covering generated identity and removal of boilerplate-only tooling.
- Optional full scaffold smoke mode that runs `composer install` and `composer check` inside the generated plugin.
- Module generator exposed through `composer make:module -- ...`, with automatic namespace/text-domain detection and source/test generation.
- Scaffold smoke coverage for the retained module generator and overwrite protection.
- PHP syntax lint for retained CLI tooling under `bin/` and `scripts/`.
- Generator hardening smoke covering comment-breaking labels, invalid header metadata, literal replacement-like URLs, Unicode and generated PHP syntax.
- Optional Site Health diagnostics module with direct runtime baseline checks.
- Unit coverage for Site Health registration and result shape.
- Optional protected REST POST example with capability checks, argument validation and sanitization.
- Unit coverage for protected REST registration, authorization, validation and sanitization.
- REST API guide explaining public read-only versus protected administrative routes.
- Optional `--plugin-uri` support for explicit generated-project metadata.
- Ownership-neutral generated `SECURITY.md` and `readme.txt` guidance.
- Reproducible local release packaging commands: `release:build`, `release:verify` and `release:package`.
- Structural ZIP verification for required files, top-level layout and forbidden development tooling.

### Fixed

- Module labels can no longer terminate or corrupt generated DocBlocks through `*/` or line breaks.
- Scaffold plugin names/authors reject control characters and comment-breaking metadata before generation.
- Explicit Plugin URI values are rendered literally instead of being interpreted as `preg_replace()` replacement backreferences.
- Release builds use isolated temporary directories with cleanup instead of a shared fixed path.
- Release ZIP verification no longer depends on Bash `mapfile`, keeping the script compatible with Bash 3.2 environments such as older/default macOS installations.

### Changed

- Scaffold identity replacement now covers kebab-case, snake_case, REST namespace, PSR-4 namespace and constant prefixes to reduce collision risk.
- Generated plugins no longer inherit the boilerplate `composer.lock` or generator-only Composer commands.
- Generated plugins retain the module generator, tooling syntax lint and generator hardening check as part of their development experience.
- Generated plugin headers omit `Plugin URI` when no explicit project URL is supplied instead of inventing a WP24Horas repository URL.
- Scaffold smoke coverage verifies explicit and omitted plugin URI behavior plus neutral security/readme metadata.
- `composer check` is now a self-contained plugin contract: PHPCS, PHPStan, PHPUnit, retained-tooling syntax lint and generator hardening.
- Boilerplate-only scaffold validation remains an explicit `composer scaffold:smoke` command so generated Composer scripts never contain dead references.
- Release validation runs `composer check`, scaffold smoke and ZIP verification explicitly.
- The release workflow is manual-only; creating a version tag no longer consumes GitHub Actions automatically.
- Customization guidance prefers scaffold-first generation over manual search/replace.
- Production ZIP excludes generator and development tooling.
- README documents Site Health, protected REST, module generation and local validation commands.

`v1.0.0` will be created only after the documented clean-checkout, generator, WordPress runtime and distribution gates pass. At that point this Unreleased section will be finalized as the first versioned release entry with the actual release date.
