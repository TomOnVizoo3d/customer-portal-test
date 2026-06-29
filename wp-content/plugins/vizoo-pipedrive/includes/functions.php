<?php

/**
 * This file contains all the function that are callable throughout WordPress.
 *
 * The functions defined in this file are publicly accessable all throughout WordPress,
 * so they are useable in the theme, in templates or in other plugins.
 * The functions are aliases for static functions of the classes of this plugin.
 *
 * @link          https://customers.vizoo3d
 * @since         1.0.0
 *
 * @package       Vizoo_Pipedrive
 * @subpackage    Vizoo_Pipedrive/includes
 */

/**
 * Gets all users of an organization.
 *
 * Returns all users that are part of the organization specified. May also only return
 * those users that are license admins.
 *
 * @since     1.0.0
 * @param     integer    $organization_id    The organization's id.
 * @param     boolean    $license_admins     Optional. Whether to only return license admins.
 * @return    array                          Array of found users.
 */
function vizoo_pipedrive_get_users($organization_id, $license_admins = false)
{
    $users = [];
    if ($license_admins) {
        $users = get_users([
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'vizoo_organization',
                    'value' => $organization_id,
                    'compare' => '=',
                ],
                [
                    'key' => 'vizoo_customerrole',
                    'value' => 'LicenseManager',
                    'compare' => '=',
                ],
            ],
        ]);
    } else {
        $users = get_users([
            'meta_key' => 'vizoo_organization',
            'meta_value' => $organization_id,
        ]);
    }
    return $users;
}

/**
 * Gets the organization specified by the id.
 *
 * @since     1.0.0
 * @param     integer                         $organization_id    The organization's id.
 * @return    Vizoo_Pipedrive_Organization                        The organization object.
 */
function vizoo_pipedrive_get_organization($organization_id)
{
    return Vizoo_Pipedrive_Database_Handler::get_organization($organization_id);
}

/**
 * Gets the organization of a user.
 *
 * @since     1.0.0
 * @param     integer                         $user_id    Optional. The user's id, if not specified the currently logged in user is used.
 * @return    Vizoo_Pipedrive_Organization                The organization object.
 */
function vizoo_pipedrive_get_organization_of_user($user_id = 0)
{
    if ($user_id == 0 && !is_user_logged_in()) {
        return;
    } elseif ($user_id == 0) {
        $user_id = get_current_user_id();
    }

    $organization_id = get_user_meta($user_id, 'vizoo_organization', true);
    return Vizoo_Pipedrive_Database_Handler::get_organization($organization_id);
}

function vizoo_pipedrive_get_current_user_organization()
{
    return vizoo_pipedrive_get_organization_of_user(0);
}

/**
 * Gets a user by the pipedrive id.
 *
 * @since     1.0.0
 * @param     integer    $pipedrive_id    The user's pipedrive id.
 * @return    WP_User                     The user.
 */
function vizoo_pipedrive_get_user($pipedrive_id)
{
    $args = [
        'meta_key' => 'vizoo_pipedrive_id',
        'meta_value' => $pipedrive_id,
    ];
    $users = get_users($args);
    if (!empty($users)) {
        return $users[0];
    }
    return null;
}

/**
 * Checks whether a user is already registered.
 *
 * @since     1.0.0
 * @param     string     $user_email    The user's email address.
 * @return    boolean                   Whether there is already a user registered with that email address.
 */
function vizoo_pipedrive_user_exists($user_email)
{
    return email_exists($user_email) || username_exists($user_email);
}
