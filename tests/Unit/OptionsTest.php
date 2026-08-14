<?php
/**
 * Options tests.
 *
 * @package WP24H\PluginBoilerplate\Tests
 */

namespace WP24H\PluginBoilerplate\Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use WP24H\PluginBoilerplate\Support\Options;

final class OptionsTest extends TestCase {
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		Functions\when( 'wp_parse_args' )->alias(
			static fn ( array $args, array $defaults ): array => array_merge( $defaults, $args )
		);
		Functions\when( 'apply_filters' )->alias(
			static fn ( string $hook_name, mixed $value ): mixed => $value
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_defaults_enable_only_safe_public_examples(): void {
		$defaults = Options::defaults();

		self::assertTrue( $defaults['modules']['shortcode'] );
		self::assertTrue( $defaults['modules']['rest_api'] );
		self::assertFalse( $defaults['modules']['protected_rest'] );
		self::assertFalse( $defaults['modules']['dashboard_widget'] );
		self::assertFalse( $defaults['modules']['admin_notice'] );
		self::assertFalse( $defaults['modules']['site_health'] );
	}

	public function test_default_content_is_raw_until_presented(): void {
		$defaults = Options::defaults();

		self::assertSame( 'Built with the WP24H Plugin Boilerplate', $defaults['headline'] );
		self::assertSame( 'Replace this message in Settings → WP24H Boilerplate.', $defaults['message'] );
		self::assertSame( $defaults['headline'], Options::display_value( 'headline', $defaults['headline'] ) );
		self::assertSame( $defaults['message'], Options::display_value( 'message', $defaults['message'] ) );
	}

	public function test_saved_module_values_override_defaults(): void {
		Functions\expect( 'get_option' )
			->times( 3 )
			->with( Options::KEY, array() )
			->andReturn(
				array(
					'modules' => array(
						'shortcode' => false,
					),
				)
			);

		$options = new Options();

		self::assertFalse( $options->module_enabled( 'shortcode' ) );
		self::assertTrue( $options->module_enabled( 'rest_api' ) );
		self::assertFalse( $options->module_enabled( 'protected_rest' ) );
	}
}
