<?php
/**
 * Plugin Name: Disable WP 2FA Locally
 * Description: Automatically disable WP 2FA in local Docker environment
 */

if (getenv('IS_LOCAL') === 'true' || getenv('WP_ENV') === 'development') {
    add_filter('option_active_plugins', function ($plugins) {
        $plugin_to_disable = 'wp-2fa/wp-2fa.php';
        $key = array_search($plugin_to_disable, $plugins);
        if ($key !== false) {
            unset($plugins[$key]);
        }
        return array_values($plugins);
    });
}