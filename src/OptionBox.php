<?php

/**
 * Setup options meta boxes
 *
 * @package ThemePlate
 * @since 0.1.0
 */

namespace ThemePlate\Settings;

use ThemePlate\Core\Field;
use ThemePlate\Core\Form;
use ThemePlate\Core\Helper\Box;
use ThemePlate\Core\Helper\Form as FormHelper;

class OptionBox extends Form {

	protected array $menu_pages    = array();
	protected string $option_name  = '';
	protected ?array $saved_values = null;


	protected function initialize( array &$config ): void {
	}


	protected function fields_group_key(): string {

		return $this->option_name;

	}


	protected function should_display_field( Field $field ): bool {

		if ( null === $this->saved_values ) {
			$this->saved_values = get_option( $this->option_name );
		}

		return true;

	}

	protected function get_field_value( Field $field ) {

		$prefix = $this->config['data_prefix'];
		$stored = $this->saved_values[ $field->data_key( $prefix ) ] ?? '';

		// phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
		return $stored ?: $field->get_config( 'default' );

	}


	public function create(): void {

		$priority = Box::get_priority( $this->config );

		foreach ( $this->menu_pages as $menu_page ) {
			$section = $menu_page . '_' . $this->config['context'];

			register_setting( $menu_page, $menu_page );
			add_filter( 'default_option_' . $menu_page, '__return_empty_array' );
			add_filter( 'sanitize_option_' . $menu_page, array( $this, 'sanitize_option' ) );
			add_action( 'themeplate_page_' . $menu_page . '_load', array( $this, 'on_wanted_page' ) );
			add_action( 'themeplate_settings_' . $section, array( $this, 'layout_postbox' ), $priority );
		}

	}


	public function location( string $page ): self {

		$this->menu_pages[] = $page;

		return $this;

	}


	public function on_wanted_page( array $config ): void {

		if ( ! in_array( $config['menu_slug'], $this->menu_pages, true ) ) {
			return;
		}

		$this->option_name = $config['menu_slug'];

		add_action( 'admin_enqueue_scripts', array( FormHelper::class, 'enqueue_assets' ) );

	}


	public function sanitize_option( ?array $value ): array {

		if ( null === $value ) {
			return array();
		}

		return Box::prepare_save( $value );

	}

}
