<?php

/**
 * Setup options meta boxes
 *
 * @package ThemePlate
 * @since 0.1.0
 */

namespace ThemePlate\Settings;

use Exception;
use ThemePlate\Core\Form;
use ThemePlate\Core\Helper\Box;
use ThemePlate\Core\Helper\Main;

class Settings {

	private array $config;
	private Form $form;
	private string $page;


	public function __construct( array $config ) {

		$expected = array(
			'id',
			'title',
			'page',
		);

		if ( ! Main::is_complete( $config, $expected ) ) {
			throw new Exception();
		}

		$defaults = array(
			'show_on'  => array(),
			'hide_on'  => array(),
			'context'  => 'normal',
			'priority' => 'default',
		);
		$config   = Main::fool_proof( $defaults, $config );

		$config['object_type'] = 'options';

		$this->config = $config;

		try {
			$this->form = new Form( $config );
		} catch ( Exception $e ) {
			throw new Exception( $e );
		}

		add_action( 'current_screen', array( $this, 'create' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts_styles' ), 11 );

	}


	public function create(): void {

		if ( ! $this->is_valid_screen() ) {
			return;
		}

		$settings = $this->config;
		$section  = $this->page . '_' . $settings['context'];
		$priority = Box::get_priority( $settings );

		add_action( 'themeplate_settings_' . $section, array( $this, 'add' ), $priority );

	}


	public function add(): void {

		$this->form->layout_postbox( $this->page );

	}


	public function scripts_styles(): void {

		if ( ! $this->is_valid_screen() ) {
			return;
		}

		$this->form->enqueue( 'settings' );

	}


	private function is_valid_screen(): bool {

		$screen = get_current_screen();

		if ( null === $screen || false === strpos( $screen->id, '_page_' ) ) {
			return false;
		}

		$page_s = (array) $this->config['page'];
		$sparts = explode( '_page_', $screen->id, 2 );

		foreach ( $page_s as $page ) {
			if ( $sparts[1] === $page ) {
				$this->page = $page;

				return true;
			}
		}

		return false;

	}

}
