<?php

/**
 * Plugin Name: Advance Canonical URL
 * Description: A WordPress plugin to avoid duplicate content throughout the website with advance settings.
 * Plugin URI: https://github.com/usmanaliqureshi/advance-canonical-url
 * Author: Usman Ali Qureshi
 * Author URI: https://www.usmanaliqureshi.com
 * Contributors: usmanaliqureshi
 * Version: 1.0.0
 * License: GPL2
 * Text Domain: acu
 * Domain Path: /languages/
 */

/**
 * Intruders aren't allowed.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'advance_canonical_url' ) ) {
	class advance_canonical_url {

		private $options;

		public function __construct() {
			$this->acu_init();
		}

		public function acu_init() {

			register_activation_hook( __FILE__, array( $this, 'acu_activation' ) );
			register_deactivation_hook( __FILE__, array( $this, 'acu_deactivation' ) );

			if ( is_admin() ) {
				add_action( 'admin_menu', array( $this, 'acu_plugin_page' ) );
				add_action( 'admin_init', array( $this, 'acu_settings_page_init' ) );
				add_action( 'update_option_acu_options', array( $this, 'acu_update_options' ), 10, 2 );
			}

			add_action( 'wp_head', array( $this, 'acu_render_canonical_url' ) );
		}

		public function acu_activation() {
			$this->options    = get_option( 'acu_options' );
			$canonical_method = ( $this->options['canonical_method'] ? $this->options['canonical_method'] : 'advance' );

			if ( ! isset( $this->options['canonical_method'] ) ) {
				$defaults = array(
					'canonical_method' => $canonical_method
				);
				update_option( 'acu_options', $defaults );
			}
		}

		public function acu_deactivation() {
			delete_option( 'acu_options' );
		}

		public function acu_plugin_page() {
			add_options_page(
				'Advance Canonical Settings',
				'Advance Canonical Settings',
				'manage_options',
				'advance_canonical_settings',
				array( $this, 'acu_settings_page' )
			);
		}

		public function acu_settings_page() {
			?>

			<div class="wrap">

				<form id="acu_form" class="acu_form" method="post" action="options.php">

					<?php
					settings_fields( 'acu_option_group' );
					do_settings_sections( 'acu-setting-admin' );
					submit_button();
					?>

				</form>

			</div>

			<?php
		}

		public function acu_settings_page_init() {
			register_setting(
				'acu_option_group',
				'acu_options',
				array( $this, 'acu_sanitize_and_validate' )
			);
			add_settings_section(
				'settings_advance_canonical',
				__( 'Advance Canonical Settings', 'acu' ),
				array( $this, 'acu_section_information' ),
				'acu-setting-admin'
			);
			add_settings_field(
				'canonical_method',
				__( 'Canonical Method', 'acu' ),
				array( $this, 'select_canonical_method' ),
				'acu-setting-admin',
				'settings_advance_canonical'
			);
		}

		public function acu_section_information() {
			?>

			<h4><?php esc_html_e( 'Select your desired settings', 'acu' ); ?></h4>

			<?php
		}

		public function select_canonical_method() {
			$this->options = get_option( 'acu_options' );
			?>

			<select id="canonical_method" name="acu_options[canonical_method]">

				<option
					value="basic" <?php echo isset( $this->options['canonical_method'] ) ? ( selected( $this->options['canonical_method'], 'basic', false ) ) : ( '' ); ?>>

					<?php esc_html_e( 'Basic', 'acu' ); ?>

				</option>

				<option
					value="advance" <?php echo isset( $this->options['canonical_method'] ) ? ( selected( $this->options['canonical_method'], 'advance', false ) ) : ( '' ); ?>>

					<?php esc_html_e( 'Advance', 'acu' ); ?>

				</option>

			</select>

			<p class="acu-description"><?php esc_html_e( 'Choose the method to display canonical url throughout your website. If you
                choose Advance then each post, page and custom post will have its own canonical url setting.', 'acu' ); ?></p>

			<?php
		}

		public function acu_sanitize_and_validate( $acu_input ) {
			$acu_new_input = array();
			if ( isset( $acu_input['canonical_method'] ) ) {
				$acu_method_valid_values = array(
					'basic',
					'advance',
				);
				if ( in_array( $acu_input['canonical_method'], $acu_method_valid_values ) ) {
					$acu_new_input['canonical_method'] = sanitize_text_field( $acu_input['canonical_method'] );
				} else {
					wp_die( "Invalid selection for Canonical Method, please go back and try again." );
				}
			}
		}

		public function acu_render_canonical_url() {
			echo '<!-- Advance Canonical URL -->
			<link rel="canonical" content="' . get_bloginfo( 'url' ) . '' . $_SERVER['REQUEST_URI'] . '">
			<!-- Advance Canonical URL -->';
		}
	}
}

$ACU = new advance_canonical_url();
