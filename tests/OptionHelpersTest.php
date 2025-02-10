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

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'schema', $result );
		$this->assertArrayHasKey( 'default', $result );
		$this->assertIsArray( $result['schema'] );
		$this->assertIsArray( $result['default'] );
	}

	public function test_sanitize_option_value(): void {
		$this->assertIsArray( OptionHelpers::sanitize( null, '' ) );
		$this->assertIsArray( OptionHelpers::sanitize( array(), '' ) );
	}
}
