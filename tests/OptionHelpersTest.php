<?php

/**
 * @package ThemePlate
 */

namespace Tests;

use ThemePlate\Settings\OptionHelpers;
use WP_UnitTestCase;

class OptionHelpersTest extends WP_UnitTestCase {
	public function test_schema_default(): void {
		$result = OptionHelpers::schema_default( 'test' );

		$this->assertArrayHasKey( 'schema', $result );
		$this->assertArrayHasKey( 'default', $result );
		$this->assertIsArray( $result['schema'] );
		$this->assertIsArray( $result['default'] );
	}

	public static function for_schema_default_values(): array {
		return array(
			'empty'   => array( array(), '' ),
			'missing' => array( array( 'key' => 'value' ), '' ),
			'custom'  => array( array( 'default' => 'custom' ), 'custom' ),
			'invalid' => array( (object) array( 'default' => 'value' ), '' ),
		);
	}

	/** @dataProvider for_schema_default_values */
	public function test_schema_default_values( $field, $expected ): void {
		add_filter(
			'themeplate_setting_test_schema',
			fn(): array => array( 'key' => $field )
		);

		$result = OptionHelpers::schema_default( 'test' );

		$this->assertSame( array( 'key' => $expected ), $result['default'] );
	}

	public function test_sanitize_option_value(): void {
		add_filter(
			'themeplate_setting_test_schema',
			fn(): array => array( 'key' => array( 'default' => 'value' ) )
		);

		$this->assertSame( array(), OptionHelpers::sanitize( null, 'test' ) );
		$this->assertSame( array(), OptionHelpers::sanitize( array(), 'test' ) );
		$this->assertSame( array( 'key' => 'value' ), OptionHelpers::sanitize( array( 'key' => '' ), 'test' ) );
		$this->assertSame( array( 'key' => 'custom' ), OptionHelpers::sanitize( array( 'key' => 'custom' ), 'test' ) );
	}
}
