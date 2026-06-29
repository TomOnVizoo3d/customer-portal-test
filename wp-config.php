<?php

require_once 'vizoo_customers.env';

define('ALLOW_UNFILTERED_UPLOADS', true);
define('DISABLE_WP_CRON',          true);

define('WP_DEBUG',    false);
define('WP_DEBUG_LOG', false);
define('DB_NAME',     getenv('DATABASE_NAME'));
define('DB_USER',     getenv('DATABASE_USER'));
define('DB_PASSWORD', getenv('DATABASE_PASSWORD'));
define('DB_HOST',     getenv('DATABASE_HOST'));
define('DB_CHARSET',  'utf8');
define('DB_COLLATE',  '');

define('AUTH_KEY',         getenv('WORDPRESS_AUTH_KEY'));
define('SECURE_AUTH_KEY',  getenv('WORDPRESS_SECURE_AUTH_KEY'));
define('LOGGED_IN_KEY',    getenv('WORDPRESS_LOGGED_IN_KEY'));
define('NONCE_KEY',        getenv('WORDPRESS_NONCE_KEY'));
define('AUTH_SALT',        getenv('WORDPRESS_AUTH_SALT'));
define('SECURE_AUTH_SALT', getenv('WORDPRESS_SECURE_AUTH_SALT'));
define('LOGGED_IN_SALT',   getenv('WORDPRESS_LOGGED_IN_SALT'));
define('NONCE_SALT',       getenv('WORDPRESS_NONCE_SALT'));

define('WP_HOME',    getenv('WORDPRESS_HOME'));
define('WP_SITEURL', getenv('WORDPRESS_SITEURL'));

$table_prefix = 'wp_';

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

require_once ABSPATH . 'wp-settings.php';
