<?php
/**
 * Module contract.
 *
 * @package WP24H\PluginBoilerplate
 */

namespace WP24H\PluginBoilerplate\Contracts;

interface Module {
	/**
	 * Stable module identifier used in saved settings.
	 */
	public function id(): string;

	/**
	 * Human-readable module name.
	 */
	public function label(): string;

	/**
	 * Human-readable module description.
	 */
	public function description(): string;

	/**
	 * Register hooks for an enabled module.
	 */
	public function register(): void;
}
