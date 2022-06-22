<?php

/**
 * Setup options meta boxes
 *
 * @package ThemePlate
 * @since 0.1.0
 */

namespace ThemePlate\Settings;

use ThemePlate\Core\Config;
use ThemePlate\Core\Form;
use ThemePlate\Core\Handler;
use ThemePlate\Core\Helper\BoxHelper;
use ThemePlate\Core\Helper\FieldsHelper;
use ThemePlate\Core\Helper\FormHelper;

class OptionBox extends Form {

	protected string $option_name = '';
	protected array $menu_pages   = array();


	protected function get_handler(): Handler {

		return new OptionHandler();

	}


	protected function initialize( array &$config ): void {
	}


	protected function fields_group_key(): string {

		return $this->option_name;

	}


	protected function maybe_nonce_fields( string $current_id ): void {

		$this->option_name = $current_id;

	}


	public function create(): void {

		$priority = BoxHelper::get_priority( $this->config );

		foreach ( $this->menu_pages as $menu_page ) {
			$section = $menu_page . '_' . $this->config['context'];

			add_filter( 'sanitize_option_' . $menu_page, array( $this, 'sanitize_option' ) );
			add_action( 'themeplate_page_' . $menu_page . '_load', array( FormHelper::class, 'enqueue_assets' ) );
			add_action( 'themeplate_settings_' . $section, array( $this, 'layout_postbox' ), $priority );
			add_filter( 'themeplate_setting_' . $menu_page . '_default', array( $this, 'register_default' ), $priority );
			add_filter( 'themeplate_setting_' . $menu_page . '_properties', array( $this, 'register_properties' ), $priority );
		}

		add_action( 'init', array( $this, 'register_setting' ) );

	}


	public function location( string $page ): self {

		$this->menu_pages[] = $page;

		return $this;

	}


	public function sanitize_option( ?array $value ): array {

		if ( null === $value ) {
			return array();
		}

		return BoxHelper::prepare_save( $value );

	}


	public function get_config(): Config {

		return new Config( $this->config['data_prefix'], $this->fields );

	}


	public function register_default( array $value ): array {

		if ( null === $this->fields ) {
			return $value;
		}

		$default = array();

		foreach ( $this->fields->get_collection() as $field ) {
			$default[ $field->data_key( $this->config['data_prefix'] ) ] = FieldsHelper::get_default_value( $field );
		}

		return array_merge(
			$value,
			$default,
		);

	}


	public function register_properties( array $list ): array {

		if ( null === $this->fields ) {
			return $list;
		}

		$properties = FieldsHelper::build_schema( $this->fields, $this->config['data_prefix'] );

		return array_merge(
			$list,
			$properties,
		);

	}


	public function register_setting(): void {

		foreach ( $this->menu_pages as $menu_page ) {
			$args = array(
				'default'      => apply_filters( 'themeplate_setting_' . $menu_page . '_default', array() ),
				'type'         => 'object',
				'show_in_rest' => array(
					'schema' => array(
						'type'       => 'object',
						'properties' => apply_filters( 'themeplate_setting_' . $menu_page . '_properties', array() ),
					),
				),
			);

			register_setting( $menu_page, $menu_page, $args );
		}

	}

}
