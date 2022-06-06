<?php

/**
 * @package ThemePlate
 */

namespace Tests;

use ThemePlate\Core\Helper\FormHelper;
use ThemePlate\Settings\OptionBox;
use WP_UnitTestCase;

class OptionBoxTest extends WP_UnitTestCase {
	private OptionBox $option_box;

	public function setUp(): void {
		$this->option_box = new OptionBox( 'Test' );
	}

	public function test_firing_create_actually_add_hooks(): void {
		$pages = array( 'page1', 'page2' );

		foreach ( $pages as $page ) {
			$this->option_box->location( $page );
		}

		$this->option_box->create();

		$this->assertSame( count( $pages ), did_action( 'register_setting' ) );

		foreach ( $pages as $page ) {
			$this->assertSame( 10, has_filter( 'default_option_' . $page, '__return_empty_array' ) );
			$this->assertSame( 10, has_filter( 'sanitize_option_' . $page, array( $this->option_box, 'sanitize_option' ) ) );
			$this->assertSame( 10, has_action( 'themeplate_page_' . $page . '_load', array( FormHelper::class, 'enqueue_assets' ) ) );
			$this->assertSame( 10, has_action( 'themeplate_settings_' . $page . '_normal', array( $this->option_box, 'layout_postbox' ) ) );
		}
	}

	public function test_sanitize_option_value(): void {
		$this->assertIsArray( $this->option_box->sanitize_option( null, 'test' ) );
		$this->assertIsArray( $this->option_box->sanitize_option( array(), 'test' ) );
	}
}
