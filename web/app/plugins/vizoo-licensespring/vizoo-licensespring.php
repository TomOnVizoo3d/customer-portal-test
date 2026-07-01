<?php

/**
 * Vizoo LicenseSpring Integration
 *
 * This plugin adds support for LicenseSpring licenses directly in WordPress.
 * Users can see their licenses on a license page if they have the rights to do
 * so. They can optionally request a renewal if the license is about to expire.
 *
 * @link              https://customers.vizoo3d.com/
 * @since             1.0.0
 * @package           Vizoo_LicenseSpring
 *
 * @wordpress-plugin
 * Plugin Name:       Vizoo LicenseSpring Integration
 * Description:       A plugin that enables managing of LicenseSpring licenses.
 * Version:           1.0.0
 * Author:            Vizoo GmbH
 * Author URI:        https://www.vizoo3d.com/
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

define('VIZOO_LICENSESPRING_VERSION', '1.0.0');

function activate_vizoo_licensespring()
{
    // nothing to do
}

function deactivate_vizoo_licensespring()
{
    // nothing to do
}

register_activation_hook(__FILE__, 'activate_vizoo_licensespring');
register_deactivation_hook(__FILE__, 'deactivate_vizoo_licensespring');


require plugin_dir_path(__FILE__) . 'includes/class-vizoo-licensespring.php';

function run_vizoo_licensespring()
{
    $plugin = new Vizoo_LicenseSpring();
    $plugin->run();
}
run_vizoo_licensespring();
