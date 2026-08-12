<?php
/**
 * Site Health module tests.
 *
 * @package WP24H\PluginBoilerplate\Tests
 */

namespace WP24H\PluginBoilerplate\Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use WP24H\PluginBoilerplate\Modules\SiteHealthModule;

final class SiteHealthModuleTest extends TestCase {
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		if ( ! defined( 'WP24H_PLUGIN_BOILERPLATE_VERSION' ) ) {
			define( 'WP24H_PLUGIN_BOILERPLATE_VERSION', '1.0.0' );
		}

		Functions\when( 'esc_html' )->alias(
			static fn ( string $text ): string => $text
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_register_hooks_into_site_health_tests(): void {
		$module = new SiteHealthModule();

		Functions\expect( 'add_filter' )
			->once()
			->with( 'site_status_tests', array( $module, 'register_test' ) );

		$module->register();
	}

	public function test_register_test_adds_direct_runtime_check(): void {
		$module = new SiteHealthModule();
		$tests  = $module->register_test( array() );

		self::assertArrayHasKey( 'direct', $tests );
		self::assertArrayHasKey( 'wp24h_plugin_boilerplate_runtime', $tests['direct'] );
		self::assertSame(
			array( $module, 'test_runtime' ),
			$tests['direct']['wp24h_plugin_boilerplate_runtime']['test']
		);
	}

	public function test_runtime_result_has_wordpress_site_health_shape(): void {
		Functions\expect( 'get_bloginfo' )
			->once()
			->with( 'version' )
			->andReturn( '6.8' );

		$result = ( new SiteHealthModule() )->test_runtime();

		self::assertSame( 'good', $result['status'] );
		self::assertSame( 'wp24h_plugin_boilerplate_runtime', $result['test'] );
		self::assertSame( 'WP24H Boilerplate', $result['badge']['label'] );
		self::assertArrayHasKey( 'description', $result );
	}
}
