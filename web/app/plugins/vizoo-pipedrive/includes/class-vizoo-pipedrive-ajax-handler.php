<?php

/**
 * The handler class for AJAX calls to this plugin.
 *
 * The class handling the AJAX requests of the receive module that fetches changes of the
 * pipedrive database.
 *
 * @since 1.0.0
 */
class Vizoo_Pipedrive_AJAX_Handler
{
    /**
     * Defines all functions available through an AJAX request.
     *
     * Add action hooks, so that actions requested are handled by the correct function in
     * this class.
     *
     * @since    1.0.0
     * @access   public
     */
    public static function define_actions()
    {
        add_action('wp_ajax_nopriv_vizoo_pipedrive_add_user', ['Vizoo_Pipedrive_AJAX_Handler', 'add_user']);
        add_action('wp_ajax_nopriv_vizoo_pipedrive_update_user', ['Vizoo_Pipedrive_AJAX_Handler', 'update_user']);
        add_action('wp_ajax_nopriv_vizoo_pipedrive_delete_user', ['Vizoo_Pipedrive_AJAX_Handler', 'delete_user']);
        add_action('wp_ajax_nopriv_vizoo_pipedrive_add_organization', ['Vizoo_Pipedrive_AJAX_Handler', 'add_organization']);
        add_action('wp_ajax_nopriv_vizoo_pipedrive_update_organization', ['Vizoo_Pipedrive_AJAX_Handler', 'update_organization']);
        add_action('wp_ajax_nopriv_vizoo_pipedrive_delete_organization', ['Vizoo_Pipedrive_AJAX_Handler', 'delete_organization']);
    }

    private static function authenticate()
    {
        if (!isset($_POST['auth_key']) || $_POST['auth_key'] != getenv('VIZOO_LIMELM_CHECK_TOKEN')) {
            header('HTTP/1.1 403 Forbidden');
            echo 'Authentification failed.';
            exit;
        }
    }

    /**
     * Inserts a user into the WordPress database.
     *
     * Creates a new user based on the information provided by pipedrive. This method is
     * called when a user is marked as 'Confirmed for Database' (and previously wasn't
     * marked as 'Confirmed for Database').
     *
     * @since     1.0.0
     * @access    public
     */
    public static function add_user()
    {
        self::authenticate();

        $user_id = $_POST['user_id'];
        $user_email = $_POST['user_email'];
        $user_name = $_POST['user_name'];
        $user_organization = $_POST['user_organization'];
        $user_role = $_POST['user_role'];
        $is_visible = $_POST['is_visible'];
        if (!isset($user_id) || !isset($user_email) || !isset($user_name) || !isset($user_organization) || !isset($user_role) || !isset($is_visible)) {
            header('HTTP/1.1 400 Bad Request');
            echo 'Error: Not enough information provided for \'add_user\'.';
            exit;
        }

        $organization = vizoo_pipedrive_get_organization($user_organization);
        if ($organization != null) {
            $relation_id = Vizoo_Pipedrive_Database_Handler::get_relation_by_groupname($organization->relation);
            if ($relation_id != null) {
                if (!vizoo_pipedrive_user_exists($user_email)) {
                    $user_email = sanitize_email($user_email);
                    $user_nicename = sanitize_title($user_email);
                    $user_password = wp_generate_password(8);

                    $wp_user_id = wp_insert_user([
                        'user_pass' => $user_password,
                        'user_login' => $user_email,
                        'user_nicename' => $user_nicename,
                        'user_email' => $user_email,
                        'display_name ' => $user_name,
                        'nickname' => $user_nicename,
                    ]);

                    if (is_numeric($wp_user_id)) {
                        update_user_meta($wp_user_id, 'active', 1);
                        update_user_meta($wp_user_id, 'vizoo_first_login', 1);
                        update_user_meta($wp_user_id, 'vizoo_customerrole', $user_role);
                        update_user_meta($wp_user_id, 'vizoo_organization', $user_organization);
                        update_user_meta($wp_user_id, 'vizoo_pipedrive_id', $user_id);

                        Vizoo_Pipedrive_Database_Handler::set_user_relation($wp_user_id, $relation_id);
                        if ($user_role == 'LicenseManager') {
                            Vizoo_Pipedrive_Database_Handler::set_user_relation($wp_user_id, Vizoo_Pipedrive_Database_Handler::get_relation_by_groupname('License-Admin'));
                        }

                        if ($is_visible) {
                            Vizoo_Pipedrive::send_user_mail($user_name, $user_email, $user_password);
                        }

                        Vizoo_Pipedrive::send_success_mail($user_email, $user_id, $user_name, $organization, $wp_user_id);

                        header('HTTP/1.1 200 OK');
                        echo 'User successfully created.';
                        exit;
                    } else {

                        Vizoo_Pipedrive::send_error_mail('Could not add user', 'The user \'' . $user_name . '\' could not be added to the Customer Portal. WordPress raised this error:' . "\n\n" . print_r($wp_user_id));

                        header('HTTP/1.1 500 Internal Server Error');
                        echo 'Error: Could not add user, WordPress error.' . "\n" . print_r($wp_user_id);
                        exit;
                    }
                } else {

                    Vizoo_Pipedrive::send_error_mail('User already registered', 'The user \'' . $user_name . '\' could not be added to the Customer Portal as the email address is already registered. There might be a duplicate in the pipedrive database.');

                    header('HTTP/1.1 500 Internal Server Error');
                    echo 'Error: User already exists.';
                    exit;
                }
            } else {

                Vizoo_Pipedrive::send_error_mail('No relation found', 'The user \'' . $user_name . '\' could not be added to the Customer Portal as the organization this person is working for (' . $organization->id . ') has no (valid) relation attached to it. Please add a relation to the organization first.');

                header('HTTP/1.1 500 Internal Server Error');
                echo 'Error: Relation could not be found.';
                exit;
            }
        } else {

            Vizoo_Pipedrive::send_error_mail('Organization not in database', 'The user \'' . $user_name . '\' could not be added to the Customer Portal as the organization this person is working for (' . $organization->id . ') is not (yet) registered in the database. Please set the organization \'Confirmed for Database\' first.');

            header('HTTP/1.1 500 Internal Server Error');
            echo 'Error: Organization is not (yet) in the database.';
            exit;
        }
    }

    public static function update_user()
    {
        self::authenticate();

        $user_id = $_POST['user_id'];
        $user_email = $_POST['user_email'];
        $user_name = $_POST['user_name'];
        $user_role = $_POST['user_role'];
        $is_visible = $_POST['is_visible'];
        $was_visible = $_POST['was_visible'];

        if (!isset($user_id) || !isset($user_email) || !isset($user_name) || !isset($user_role) || !isset($is_visible) || !isset($was_visible)) {
            header('HTTP/1.1 400 Bad Request');
            echo 'Error: Not enough information for \'update_user\'.';
            exit;
        }

        $wp_user = vizoo_pipedrive_get_user($user_id);
        if ($wp_user == null) {

            Vizoo_Pipedrive::send_error_mail('User not found', 'The user \'' . $user_name . '\' could not be updated as there is no such user in the Customer Portal database. This means that the databases are out of sync, please refer to the Customer Portal Documentation to solve this problem.');

            header('HTTP/1.1 500 Internal Server Error');
            echo 'Error: User not found.';
            exit;
        } else {
            $wp_user_id = $wp_user->ID;
        }

        $user_email = sanitize_email($user_email);
        $user_nicename = sanitize_title($user_email);

        if ($is_visible && !$was_visible) {
            $user_password = wp_generate_password(8);

            wp_insert_user([
                'ID' => $wp_user_id,
                'user_pass' => $user_password,
                'user_login' => $user_email,
                'user_nicename' => $user_nicename,
                'user_email' => $user_email,
                'display_name' => $user_name,
                'nickname' => $user_nicename,
            ]);

            update_user_meta($wp_user_id, 'active', 1);
            update_user_meta($wp_user_id, 'vizoo_first_login', 1);
        } else {
            wp_insert_user([
                'ID' => $wp_user_id,
                'user_login' => $user_email,
                'user_nicename' => $user_nicename,
                'user_email' => $user_email,
                'display_name' => $user_name,
                'nickname' => $user_nicename,
            ]);
        }

        update_user_meta($wp_user_id, 'vizoo_customerrole', $user_role);
        if ($user_role == 'LicenseManager') {
            Vizoo_Pipedrive_Database_Handler::set_user_relation($wp_user_id, Vizoo_Pipedrive_Database_Handler::get_relation_by_groupname('License-Admin'));
        } else {
            Vizoo_Pipedrive_Database_Handler::remove_user_relation($wp_user_id, Vizoo_Pipedrive_Database_Handler::get_relation_by_groupname('License-Admin'));
        }

        if ($is_visible && !$was_visible) {
            Vizoo_Pipedrive::send_user_mail($user_name, $user_email, $user_password);
        }

        header('HTTP/1.1 200 OK');
        echo 'User updated.';
        exit;
    }

    public static function delete_user()
    {
        self::authenticate();
        require_once ABSPATH . '/wp-admin/includes/user.php';

        $user_id = $_POST['user_id'];

        if (!isset($user_id)) {
            header('HTTP/1.1 400 Bad Request');
            echo 'Error: Not enough information for \'delete_user\'.';
            exit;
        }

        $wp_user = vizoo_pipedrive_get_user($user_id);
        if ($wp_user != null) {
            $wp_user_id = $wp_user->ID;
        } else {
            header('HTTP/1.1 500 Internal Server Error');
            echo 'Error: User not found.';
            exit;
        }
        wp_delete_user($wp_user_id);

        Vizoo_Pipedrive_Database_Handler::remove_user_relation($wp_user_id);

        header('HTTP/1.1 200 OK');
        echo 'User deleted.';
        exit;
    }

    public static function add_organization()
    {
        self::authenticate();

        $organization_id = $_POST['organization_id'];
        $organization_name = $_POST['organization_name'];
        $organization_website = $_POST['organization_website'] ?: '';
        $organization_relation = $_POST['organization_relation'];

        if (!isset($organization_id) || !isset($organization_name) || !isset($organization_relation)) {
            header('HTTP/1.1 400 Bad Request');
            echo 'Error: Not enough information provided for \'add_organization\'.';
            exit;
        }

        if (!Vizoo_Pipedrive_Database_Handler::organization_exists($organization_id)) {
            Vizoo_Pipedrive_Database_Handler::insert_organization($organization_id, $organization_name, $organization_website, $organization_relation);

            header('HTTP/1.1 200 OK');
            echo 'Organization created.';
            exit;
        } else {

            Vizoo_Pipedrive::send_error_mail('Organization already registered', 'The organization \'' . $organization_name . '\' could not be added to the Customer Portal as there is already an organization with that id.');

            header('HTTP/1.1 500 Internal Server Error');
            echo 'Error: Organization is already registered in the database.';
            exit;
        }
    }

    public static function update_organization()
    {
        self::authenticate();

        $organization_id = $_POST['organization_id'];
        $organization_name = $_POST['organization_name'];
        $organization_website = $_POST['organization_website'] ?: '';
        $organization_relation = $_POST['organization_relation'];

        if (!isset($organization_id) || !isset($organization_name) || !isset($organization_relation)) {
            header('HTTP/1.1 400 Bad Request');
            echo 'Error: Not enough information provided for \'update_organization\'.';
            exit;
        }

        $organization = Vizoo_Pipedrive_Database_Handler::get_organization($organization_id);

        if ($organization != null) {
            if (isset($organization_relation)) {
                Vizoo_Pipedrive_Database_Handler::update_organization($organization_id, $organization_name, $organization_website, $organization_relation);

                // If the relation changed, we have to update the users too.
                if ($organization->relation != $organization_relation) {
                    $relation_id = Vizoo_Pipedrive_Database_Handler::get_relation_by_groupname($organization_relation);

                    foreach (Vizoo_Pipedrive_Database_Handler::get_users($organization_id) as $user) {
                        Vizoo_Pipedrive_Database_Handler::update_user_relation($user->ID, $relation_id, $organization->relation);
                    }
                }

                header('HTTP/1.1 200 OK');
                echo 'Organization updated.';
            } else {

                Vizoo_Pipedrive::send_error_mail('No relation found', 'The organization \'' . $organization_name . '\' could not be updated as there is no relation set in the new version. The organization was not updated, please change the organization\'s relation so that databases are in sync again.');

                header('HTTP/1.1 500 Internal Server Error');
                echo 'Error: No relation set for update.';
                exit;
            }
        } else {

            Vizoo_Pipedrive::send_error_mail('Organization not found', 'The organization \'' . $organization_name . '\' could not be updated as there is no such organization registered in the Customer Portal database. This means that the databases are out of sync, please refer to the Customer Portal Documentation to solve this problem.');

            header('HTTP/1.1 500 Internal Server Error');
            echo 'Error: Organization that doesn\'t exist should be updated.';
            exit;
        }
    }

    /**
     * If an organization is not anymore 'Confirmed for Database'.
     *
     * This will not remove any users.
     */
    public static function delete_organization()
    {
        self::authenticate();

        $organization_id = $_POST['organization_id'];

        if (!isset($organization_id)) {
            header('HTTP/1.1 400 Bad Request');
            echo 'Error: Not enough information provided for \'delete_organization\'.';
            exit;
        }

        print_r(Vizoo_Pipedrive_Database_Handler::get_organization($organization_id));

        if (Vizoo_Pipedrive_Database_Handler::get_organization($organization_id) != null) {
            Vizoo_Pipedrive_Database_Handler::delete_organization($organization_id);

            header('HTTP/1.1 200 OK');
            echo 'Organization deleted.';
        } else {
            header('HTTP/1.1 500 Internal Server Error');
            echo 'Error: Organization not found.';
            exit;
        }
    }
}
