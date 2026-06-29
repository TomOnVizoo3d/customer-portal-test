<?php

/**
 * Vizoo LimeLM Integration
 *
 * This plugin adds support for LimeLM licenses directly in WordPress. Users can see
 * their licenses on a license page if they have the rights to do so. They can optionally
 * deactivate existing activations or request a renewal if the license is about to
 * expire.
 *
 * @link              https://customers.vizoo3d.com/
 * @since             1.0.0
 * @package           Vizoo_LimeLM
 *
 * @wordpress-plugin
 * Plugin Name:       Vizoo LimeLM Integration
 * Description:       A plugin that enables managing of licenses through LimeLM.
 * Version:           1.0.0
 * Author:            Vizoo3D
 * Author URI:        https://www.vizoo3d.com/
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Current version of the plugin.
 * Follows semantic versioning as defined at https://semver.org
 */
define('VIZOO_LIMELM_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 */
function activate_vizoo_limelm()
{
    // nothing to do here (yet)..
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_vizoo_limelm()
{
    // nothing to do here (yet)..
}

register_activation_hook(__FILE__, 'activate_vizoo_limelm');
register_deactivation_hook(__FILE__, 'deactivate_vizoo_limelm');

/**
 * The core plugin class.
 */
require plugin_dir_path(__FILE__) . 'includes/class-vizoo-limelm.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_vizoo_limelm()
{
    $plugin = new Vizoo_LimeLM();
    $plugin->run();
}
run_vizoo_limelm();
