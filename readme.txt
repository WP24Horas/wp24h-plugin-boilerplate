=== WP24H Plugin Boilerplate ===
Contributors: asllanmaciel
Tags: boilerplate, development, modular, settings, rest-api
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A modular and configurable foundation for professional WordPress plugins.

== Description ==

WP24H Plugin Boilerplate provides a secure and extensible starting point with:

* Configurable feature modules.
* A Settings API administration screen.
* Public read-only and protected REST API examples.
* Shortcode, dashboard widget, admin notice and Site Health examples.
* Capability checks, validation, sanitization and escaping patterns.
* Internationalization and safe uninstall behavior.
* PHPCS, PHPStan and PHPUnit development tooling.
* A local plugin scaffolder that renames runtime identity safely.
* A module generator that creates a module class and matching unit test.
* Local smoke validation for the generated plugin and module workflow.

This repository is intended as a development starter. Use the scaffolder to create a derived plugin with its own slug, namespace, text domain and ownership metadata before distribution.

== Installation ==

1. Clone the repository for development.
2. Run `composer install`.
3. Run `composer check` to validate plugin code, tests and retained CLI tooling.
4. Run `composer scaffold:smoke` to validate the project-generation flow.
5. Optionally start the local WordPress environment with `wp-env`.
6. Activate the plugin and open Settings > WP24H Boilerplate.

== Changelog ==

= 1.0.0 =
* Initial public version of the boilerplate.

