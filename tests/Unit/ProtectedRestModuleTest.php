<?php
/**
 * Protected REST module tests.
 *
 * @package WP24H\PluginBoilerplate\Tests
 */

namespace WP24H\PluginBoilerplate\Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use WP24H\PluginBoilerplate\Modules\ProtectedRestModule;
use WP24H\PluginBoilerplate\Support\Options;

final class ProtectedRestModuleTest extends TestCase {
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_register_hooks_into_rest_api_init(): void {
		$module = new ProtectedRestModule( new Options() );

		Functions\expect( 'add_action' )
			->once()
			->with( 'rest_api_init', array( $module, 'register_route' ) );

		$module->register();
	}

	public function test_permission_callback_requires_manage_options(): void {
		Functions\expect( 'current_user_can' )
			->once()
			->with( 'manage_options' )
			->andReturn( true );

		$module = new ProtectedRestModule( new Options() );

		self::assertTrue( $module->can_manage() );
	}

	public function test_message_validation_rejects_empty_and_oversized_values(): void {
		$module = new ProtectedRestModule( new Options() );

		self::assertFalse( $module->validate_message( '' ) );
		self::assertFalse( $module->validate_message( '   ' ) );
		self::assertFalse( $module->validate_message( array() ) );
		self::assertFalse( $module->validate_message( str_repeat( 'a', 201 ) ) );
		self::assertTrue( $module->validate_message( 'Safe message' ) );
	}

	public function test_message_sanitization_uses_wordpress_api(): void {
		Functions\expect( 'sanitize_text_field' )
			->once()
			->with( '<strong>Hello</strong>' )
			->andReturn( 'Hello' );

		$module = new ProtectedRestModule( new Options() );

		self::assertSame( 'Hello', $module->sanitize_message( '<strong>Hello</strong>' ) );
	}
}
