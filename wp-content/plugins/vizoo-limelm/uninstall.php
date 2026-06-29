<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://customers.vizoo3d.com
 * @since      1.0.0
 *
 * @package    Vizoo_LimeLM
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Exit if uninstall is not called from WordPress.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit('invalid uninstall attemt');
}

// Uninstall process removes Vizoo LimeLM settings from the WordPress database (_options table).
if (WP_UNINSTALL_PLUGIN) {
    if (is_multisite()) {
        global $wpdb;
        $blog_ids = $wpdb->get_col('SELECT blog_id FROM ' . $wpdb->blogs);
        $original_blog_id = get_current_blog_id();

        foreach ($blog_ids as $blog_id) {
            switch_to_blog($blog_id);
            vizoo_limelm_uninstall_options();
        }

        switch_to_blog($original_blog_id);
    } else {
        vizoo_limelm_uninstall_options();
    }
}


/**
 * Compartmentalizes uninstall
 *
 * @since 1.0.0
 */
function vizoo_limelm_uninstall_options()
{
    // Nothing to do here.
}

// End of file.
