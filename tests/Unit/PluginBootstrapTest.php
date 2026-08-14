<?php
/**
 * Plugin bootstrap tests.
 *
 * @package WP24H\PluginBoilerplate\Tests
 */

namespace WP24H\PluginBoilerplate\Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class PluginBootstrapTest extends TestCase {
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_boot_is_registered_on_plugins_loaded(): void {
		Functions\when( 'plugin_dir_path' )->justReturn( dirname( __DIR__, 2 ) . '/' );
		Functions\when( 'plugin_dir_url' )->justReturn( 'https://example.test/plugin/' );
		Functions\expect( 'register_activation_hook' )->once();
		Functions\expect( 'add_action' )
			->once()
			->with( 'plugins_loaded', \Mockery::type( 'callable' ) );

		require dirname( __DIR__, 2 ) . '/wp24h-plugin-boilerplate.php';

		self::assertTrue( defined( 'WP24H_PLUGIN_BOILERPLATE_VERSION' ) );
	}
}
