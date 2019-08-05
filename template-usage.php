<?php
/**
 * Plugin Name:       Template Usage
 * Description:       Display info on what page templates are currently being used within a WordPress multisite.
 * Version:           1.0.0-beta.1
 * Author:            Joseph Fusco
 * Author URI:        https://github.com/josephfusco/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       template-usage
 * Domain Path:       /languages
 * Network:           true
 */

namespace Template_Usage;

// If this file is called directly, abort.
defined( 'WPINC' ) || die();

/**
 * Autoload the plugin's classes.
 */
require_once __DIR__ . '/inc/autoload.php';

/**
 * Begins execution of the plugin.
 */
add_action(
	'plugins_loaded',
	function () {
		$plugin = new Plugin();
		$plugin->run();
	}
);
