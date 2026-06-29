<?php

/**
 * Vizoo Pipedrive Integration
 *
 * This plugin adds pipedrive integration into WordPress. Users are automatically
 * synchronized with the pipedrive database (if the 'Confirmed for Database')-field is
 * set.
 *
 * @link       https://customers.vizoo3d.com/
 * @since      1.0.0
 * @package    Vizoo_Pipedrive
 *
 * @wordpress-plugin
 * Plugin Name:    Vizoo Pipedrive
 * Description:    A plugin that enables synchronizing of pipedrive users.
 * Version:        1.0.0
 * Author:         Vizoo3D
 * Author URI:     https://www.vizoo3d.com/
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Current version of the plugin.
 * Follows semantic versioning as defined at https://semver.org
 */
define('VIZOO_PIPEDRIVE_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 */
function activate_vizoo_pipedrive()
{
    // Nothing to do here (yet)..
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_vizoo_pipedrive()
{
    // Nothing to do here (yet)..
}

register_activation_hook(__FILE__, 'activate_vizoo_pipedrive');
register_deactivation_hook(__FILE__, 'deactivate_vizoo_pipedrive');

/**
 * The core plugin class.
 */
require plugin_dir_path(__FILE__) . 'includes/class-vizoo-pipedrive.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_vizoo_pipedrive()
{
    $plugin = new Vizoo_Pipedrive();
    $plugin->run();
}
run_vizoo_pipedrive();
