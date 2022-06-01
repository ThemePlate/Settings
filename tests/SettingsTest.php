<?php

/**
 * @package ThemePlate
 */

namespace Tests;

use ThemePlate\Settings\Settings;
use WP_UnitTestCase;

class SettingsTest extends WP_UnitTestCase {
	public function test_actions(): void {
		$config = array(
			'id'     => 'test',
			'page'   => 'test-page',
			'title'  => 'Tester',
			'fields' => array(
				'test' => array(),
			),
		);

		$settings = new Settings( $config );

		$this->assertSame( 10, has_action( 'current_screen', array( $settings, 'create' ) ) );
		$this->assertSame( 11, has_action( 'admin_enqueue_scripts', array( $settings, 'scripts_styles' ) ) );
	}
}
