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
		public function __construct() {
			$this->acu_init();
		}

		public function acu_init() {
			add_action( 'wp_head', array( $this, 'acu_render_canonical_url' ) );
		}

		public function acu_render_canonical_url() {
			echo '<!-- Advance Canonical URL -->
			<link rel="canonical" content="' . get_bloginfo( 'url' ) . '' . $_SERVER['REQUEST_URI'] . '">
			<!-- Advance Canonical URL -->';
		}
	}
}

$ACU = new advance_canonical_url();
