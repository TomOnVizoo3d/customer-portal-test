<?php

/**
 * Vizoo FirstLogin
 *
 * This plugin forces users to set their passwords the first time they login. Since users
 * are synchronized using the pipedrive database they do not initialize the account
 * creation, but instead get a random password.
 *
 * @link       https://customers.vizoo3d.com/
 * @since      1.0.0
 * @package    Vizoo_FirstLogin
 *
 * @wordpress-plugin
 * Plugin Name:    Vizoo FirstLogin
 * Description:    A plugin that forces users to set their password the first time they login.
 * Version:        1.0.0
 * Author:         Vizoo3D
 * Author URI:     https://www.vizoo3d.com/
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

function vizoo_check_firstlogin()
{
    // Do not alter AJAX, Cron or WP_CLI requests.
    if ((defined('DOING_AJAX') && DOING_AJAX) || (defined('DOING_CRON') && DOING_CRON) || (defined('WP_CLI') && WP_CLI)) {
        return;
    }

    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $first_login = get_user_meta($user->ID, 'vizoo_first_login', true);

        if ($first_login !== '0') {
            $required_post_args = [
                'vizoo_firstlogin_set',
                'vizoo_firstlogin_pw1',
                'vizoo_firstlogin_pw2',
                'vizoo_firstlogin_nonce',
            ];

            if (count(array_intersect(array_keys($_POST), $required_post_args)) == count($required_post_args)) {
                $nonce = $_POST['vizoo_firstlogin_nonce'];
                $password = $_POST['vizoo_firstlogin_pw1'];
                $password_confirm = $_POST['vizoo_firstlogin_pw2'];

                if (wp_verify_nonce($nonce, 'initial_password_set')) {
                    if (empty($password) || empty($password_confirm)) {
                        $vizoo_firstlogin_err = 'Passwords can\'t be empty.';
                    } elseif ($password != $password_confirm) {
                        $vizoo_firstlogin_err = 'Passwords don\'t match.';
                    } else {
                        $userdata = [
                            'ID' => $user->ID,
                            'user_pass' => $password,
                        ];
                        $user_id = wp_update_user($userdata);
                        update_user_meta($user_id, 'vizoo_first_login', '0');
                        wp_safe_redirect(get_home_url(), 302);
                        exit;
                    }
                } else {
                    $vizoo_firstlogin_err = 'Are you sure you want to do this?';
                }
            }
            require_once plugin_dir_path(__FILE__) . 'includes/templates/welcome-page.php';
            exit;
        }
    }
}
add_action('template_redirect', 'vizoo_check_firstlogin');
