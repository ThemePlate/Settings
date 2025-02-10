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


	private function get_schema_default( string $option ): array {

		$default = array();
		$schema  = apply_filters( 'themeplate_setting_' . $option . '_schema', $default );

		if ( ! empty( $schema ) ) {
			$default = array_map(
				function( $field ) {
					return $field['default'];
				},
				$schema
			);
		}

		return compact( 'schema', 'default' );

	}


	public function create(): void {

		$priority = BoxHelper::get_priority( $this->config );

		foreach ( $this->menu_pages as $menu_page ) {
			$section = $menu_page . '_' . $this->config['context'];

			add_filter( 'sanitize_option_' . $menu_page, array( $this, 'sanitize_option' ), 10, 2 );
			add_action( 'themeplate_page_' . $menu_page . '_load', array( FormHelper::class, 'enqueue_assets' ) );
			add_action( 'themeplate_settings_' . $section, array( $this, 'layout_postbox' ), $priority );
			add_filter( 'themeplate_setting_' . $menu_page . '_schema', array( $this, 'build_schema' ), $priority );
		}

		if ( did_action( 'init' ) ) {
			$this->register_setting();
		} else {
			add_action( 'init', array( $this, 'register_setting' ) ); // @codeCoverageIgnore
		}

	}


	public function location( string $page ): self {

		$this->menu_pages[] = $page;

		return $this;

	}


	public function sanitize_option( ?array $value, string $menu_page ): array {

		if ( null === $value ) {
			return array();
		}

		$saved  = BoxHelper::prepare_save( $value );
		$option = $this->get_schema_default( $menu_page );

		array_walk(
			$saved,
			function( &$value, $key, $default ) {
				if ( empty( $value ) ) {
					$value = $default[ $key ];
				}
			},
			$option['default']
		);

		return $saved;

	}


	public function get_config(): Config {

		return new Config( $this->config['data_prefix'], $this->fields );

	}


	public function build_schema( array $data ): array {

		if ( null === $this->fields ) {
			return $data;
		}

		$schema = FieldsHelper::build_schema( $this->fields, $this->config['data_prefix'] );

		return array_merge(
			$data,
			$schema,
		);

	}


	public function register_setting(): void {

		foreach ( $this->menu_pages as $menu_page ) {
			$option = $this->get_schema_default( $menu_page );
			$args   = array(
				'default'      => $option['default'],
				'type'         => 'object',
				'show_in_rest' => array(
					'schema' => array(
						'type'       => 'object',
						'properties' => $option['schema'],
					),
				),
			);

			register_setting( $menu_page, $menu_page, $args );
		}

	}

}
