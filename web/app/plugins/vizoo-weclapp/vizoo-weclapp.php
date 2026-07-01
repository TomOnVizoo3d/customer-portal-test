<?php

/**
 * Vizoo weclapp Integration
 *
 * This plugin adds weclapp integration into WordPress.
 *
 * @link       https://customers.vizoo3d.com/
 * @since      1.0.0
 * @package    Vizoo_weclapp
 *
 * @wordpress-plugin
 * Plugin Name:    Vizoo weclapp
 * Description:    A plugin that enables quotation creation through weclapp.
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
define('VIZOO_WECLAPP_VERSION', '1.0.0');

/**
 * The core plugin class.
 */
require plugin_dir_path(__FILE__) . 'includes/class-vizoo-weclapp.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_vizoo_weclapp()
{
    $plugin = new Vizoo_Weclapp();
    $plugin->run();
}
run_vizoo_weclapp();
