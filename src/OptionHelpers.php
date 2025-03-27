<?php

/**
 * Setup options meta boxes
 *
 * @package ThemePlate
 * @since 0.1.0
 */

namespace ThemePlate\Settings;

use ThemePlate\Core\Helper\BoxHelper;

class OptionHelpers {

	public static function schema_default( string $menu_page ): array {

		$schema  = (array) apply_filters( 'themeplate_setting_' . $menu_page . '_schema', array() );
		$default = array_map(
			static function ( $field ) {
				if ( ! is_array( $field ) || empty( $field['default'] ) ) {
					return '';
				}

				return $field['default'];
			},
			$schema
		);

		return compact( 'schema', 'default' );

	}


	public static function sanitize( ?array $value, string $menu_page ): array {

		if ( null === $value ) {
			return array();
		}

		$saved  = BoxHelper::prepare_save( $value );
		$option = self::schema_default( $menu_page );

		array_walk(
			$saved,
			function ( &$value, $key, array $option_default ): void {
				if ( empty( $value ) ) {
					$value = $option_default[ $key ];
				}
			},
			$option['default']
		);

		return $saved;

	}

}
