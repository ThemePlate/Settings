<?php

/**
 * @package ThemePlate
 */

namespace Tests;

use ThemePlate\Core\Helper\Form;
use ThemePlate\Settings\OptionBox;
use WP_Screen;
use WP_UnitTestCase;

class OptionBoxTest extends WP_UnitTestCase {
	private OptionBox $option_box;

	public function setUp(): void {
		$this->option_box = new OptionBox( 'Test' );
	}

	public function test_firing_create_actually_add_hooks(): void {
		$this->option_box->create();

		$this->assertSame( 10, has_action( 'current_screen', array( $this->option_box, 'maybe_wanted_page' ) ) );
	}

	public function test_adding_location_fires_more_hooks(): void {
		$pages = array( 'page1', 'page2' );

		foreach ( $pages as $page ) {
			$this->option_box->location( $page );
		}

		$this->option_box->create();

		$this->assertSame( count( $pages ), did_action( 'register_setting' ) );

		foreach ( $pages as $page ) {
			$this->assertSame( 10, has_filter( 'default_option_' . $page, '__return_empty_array' ) );
			$this->assertSame( 10, has_filter( 'sanitize_option_' . $page, array( $this->option_box, 'sanitize_option' ) ) );
			$this->assertSame( 10, has_action( 'themeplate_settings_' . $page . '_normal', array( $this->option_box, 'layout_postbox' ) ) );
		}
	}

	public function for_enqueue_assets_only_on_wanted_screens(): array {
		// phpcs:disable WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
		return array(
			'with not a custom page set and not in a custom screen' => array(
				'options-general.php',
				'options-general.php',
				false,
			),
			'with a custom page set but not in a custom screen' => array(
				'tester',
				'options-general.php',
				false,
			),
			'with a custom page set but not in a wanted screen' => array(
				'tester',
				'toplevel_page_tester-sub',
				false,
			),
			'with a custom page set and in the wanted screen' => array(
				'tester',
				'toplevel_page_tester',
				true,
			),
		);
		// phpcs:disable WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
	}

	/**
	 * @dataProvider for_enqueue_assets_only_on_wanted_screens
	 */
	public function test_enqueue_assets_only_on_wanted_screens( string $page, string $hook_name, bool $has_action ): void {
		$this->option_box->location( $page )->maybe_wanted_page( WP_Screen::get( $hook_name ) );

		$output = has_action( 'admin_enqueue_scripts', array( Form::class, 'enqueue_assets' ) );

		if ( $has_action ) {
			$this->assertSame( 10, $output );
		} else {
			$this->assertFalse( $output );
		}
	}

	public function test_sanitize_option_value(): void {
		$this->assertIsArray( $this->option_box->sanitize_option( null, 'test' ) );
		$this->assertIsArray( $this->option_box->sanitize_option( array(), 'test' ) );
	}
}
