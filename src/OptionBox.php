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

	protected string $option_name = '';
	protected array $menu_pages   = array();
	protected array $saved_values = array();


	protected function initialize( array &$config ): void {
	}


	protected function fields_group_key(): string {

		return $this->option_name;

	}


	protected function maybe_nonce_fields( string $current_id ): void {

		$this->option_name  = $current_id;
		$this->saved_values = get_option( $current_id );

	}


	protected function get_field_value( Field $field, string $current_id ) {

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
			add_action( 'themeplate_page_' . $menu_page . '_load', array( FormHelper::class, 'enqueue_assets' ) );
			add_action( 'themeplate_settings_' . $section, array( $this, 'layout_postbox' ), $priority );
		}

	}


	public function location( string $page ): self {

		$this->menu_pages[] = $page;

		return $this;

	}


	public function sanitize_option( ?array $value ): array {

		if ( null === $value ) {
			return array();
		}

		return Box::prepare_save( $value );

	}

}
