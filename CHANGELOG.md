# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Safe local scaffolder for generating a new plugin with a custom name, slug, PSR-4 namespace, Composer package and constant prefix.
- Scaffold documentation with deterministic replacement and safety rules.
- `composer scaffold -- ...` as the recommended plugin creation flow.
- Structural scaffold smoke test covering generated identity and removal of boilerplate-only tooling.
- Optional full scaffold smoke mode that runs `composer install` and `composer check` inside the generated plugin.
- Optional Site Health diagnostics module with direct runtime baseline checks.
- Unit coverage for Site Health registration and result shape.
- Optional `--plugin-uri` support for explicit generated-project metadata.
- Ownership-neutral generated `SECURITY.md` guidance.

### Changed

- Scaffold identity replacement now covers kebab-case, snake_case, REST namespace, PSR-4 namespace and constant prefixes to reduce collision risk.
- Generated plugins no longer inherit the boilerplate `composer.lock` or generator-only Composer commands.
- Generated plugin headers omit `Plugin URI` when no explicit project URL is supplied instead of inventing a WP24Horas repository URL.
- Scaffold smoke coverage now verifies explicit and omitted plugin URI behavior plus neutral security metadata.
- `composer check` now includes the structural scaffold smoke test.
- Customization guidance now prefers scaffold-first generation over manual search/replace.
- Production ZIP excludes the scaffolder tooling.
- README documents the optional Site Health module and scaffold validation commands.

## [1.0.0] - 2026-08-12

### Added

- Initial public version of the WP24Horas Plugin Boilerplate.
- Composer-based development tooling.
- WordPress Coding Standards via PHPCS.
- Static analysis with PHPStan.
- Local WordPress development support with wp-env.
- GitHub issue and pull request templates.
- GitHub Actions workflows for continuous integration.
- Security and contribution guidelines.
