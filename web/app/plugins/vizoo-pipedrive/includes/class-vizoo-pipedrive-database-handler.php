<?php

/**
 * The database handler class as wrapper for database access functions.
 *
 * @link          https://customers.vizoo3d
 * @since         1.0.0
 *
 * @package       Vizoo_Pipedrive
 * @subpackage    Vizoo_Pipedrive/includes
 */
class Vizoo_Pipedrive_Database_Handler
{
    /**
     * Constants for the database table names.
     *
     * Define the database table names.
     *
     * @since     1.0.0
     * @access    public
     * @var       string    ORGANIZATIONS_TABLE_NAME             Licenses that won't expire in the time period of the notification threshold.
     * @var       string    ORGANIZATIONS_RELATION_TABLE_NAME    Licenses that will expire in the time period of the notification threshold.
     * @var       string    ORGANIZATIONS_USERS_PIPEDRIVE        Licenses that are expired.
     */
    public const ORGANIZATIONS_TABLE_NAME = 'organizations';
    public const ORGANIZATIONS_RELATION_TABLE_NAME = 'organizations_relationships';
    public const ORGANIZATIONS_USERS_PIPEDRIVE = 'users_pipedrive';

    /**
     * Gets all users of an organization.
     *
     * Retrieves all users that belong to the organizations specified by the id.
     *
     * @since     1.0.0
     * @access    public
     * @param     integer    $organization_id    The organization's pipedrive id.
     * @return    array                          An array of the organization's users. May be empty.
     */
    public static function get_users($organization_id)
    {
        global $wpdb;

        $organization = self::get_organization($organization_id);

        $user_ids = $wpdb->get_col($wpdb->prepare(
            '
        SELECT user_id FROM ' . $wpdb->prefix . self::ORGANIZATIONS_RELATION_TABLE_NAME . '
        WHERE org_id = %d
      ',
            $organization->database_id
        ));

        if (!empty($user_ids)) {
            return get_users([
                'include' => $user_ids,
            ]);
        }
        return [];
    }

    /**
     * Gets an organization from the database.
     *
     * Retrieves the organizations specified by the id from the database.
     *
     * @since     1.0.0
     * @access    public
     * @param     integer                         $organization_id    The organization's pipedrive id.
     * @return    Vizoo_Pipedrive_Organization                        An array of the organization's users. May be empty.
     */
    public static function get_organization($organization_id)
    {
        global $wpdb;

        $query = $wpdb->prepare(
            '
        SELECT * FROM ' . $wpdb->prefix . self::ORGANIZATIONS_TABLE_NAME . '
        WHERE org_pipedrive_id = %d
      ',
            $organization_id
        );

        $row = $wpdb->get_row(
            $wpdb->prepare(
                '
          SELECT * FROM ' . $wpdb->prefix . self::ORGANIZATIONS_TABLE_NAME . '
          WHERE org_pipedrive_id = %d
        ',
                $organization_id
            ),
            ARRAY_A
        );

        if (isset($row['ID'])) {
            return new Vizoo_Pipedrive_Organization($row['org_pipedrive_id'], $row['ID'], $row['org_name'], $row['org_relation'], $row['org_website']);
        }
        return null;
    }

    /**
     * Gets the customer relation id by groupname.
     *
     * Retrieves the correct relation id by the groupname as configured in the User Access
     * Manager plugin. This way we can directly map users to the correct user group as long
     * as the relation set in pipedrive matches a name of usergroups in WordPress.
     *
     * So for example, if we select relationship 'Customer' in pipedrive and there is a
     * usergroup in WordPress called 'Customer' it will automatically be mapped correctly.
     *
     * This means if another relation should be added, a new usergroup has to be added
     * inside WordPress and the relation field in pipedrive needs to be updated
     * accordingly.
     *
     * This plugs in to the table of the User Access Manager plugin.
     *
     * @since     1.0.0
     * @access    public
     * @param     integer    $groupname    The name of the usergroup.
     * @return    integer                  The id of the relation.
     */
    public static function get_relation_by_groupname($groupname)
    {
        global $wpdb;

        return $wpdb->get_var($wpdb->prepare(
            '
        SELECT ID FROM ' . $wpdb->prefix . 'uam_accessgroups
        WHERE groupname = %s
      ',
            $groupname
        ));
    }

    /**
     * Checks whether an organization exists.
     *
     * Checks whether the organization with a specific id is registered at the Customer
     * Portal.
     *
     * @since     1.0.0
     * @access    public
     * @param     integer    $organization_id    The organization's id.
     * @return    boolean                        Whether or not the organization is registered.
     */
    public static function organization_exists($organization_id)
    {
        global $wpdb;

        return $wpdb->get_var($wpdb->prepare(
            '
        SELECT COUNT(ID) FROM ' . $wpdb->prefix . self::ORGANIZATIONS_TABLE_NAME . '
        WHERE org_pipedrive_id = %d
      ',
            $organization_id
        )) == '1';
    }

    /**
     * Sets the user's relation.
     *
     * Inserts an entry to the User Access Manager table, so that the user is now in the
     * specified group.
     *
     * @since     1.0.0
     * @access    public
     * @param     integer    $user_id        The user's id.
     * @param     integer    $relation_id    The relation id.
     */
    public static function set_user_relation($user_id, $relation_id)
    {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'uam_accessgroup_to_object',
            [
                'object_id' => $user_id,
                'general_object_type' => '_user_',
                'object_type' => '_user_',
                'group_id' => $relation_id,
                'group_type' => 'UserGroup',
            ]
        );
    }

    /**
     * Updates an existing user relation.
     *
     * Change the user relation to the one specified. This is needed if the organizations
     * relation was changed in pipedrive.
     *
     * @since     1.0.0
     * @access    public
     * @param     integer    $user_id            The user's id.
     * @param     integer    $relation_id        The relation id.
     * @param     integer    $old_relation_id    The old relation id.
     */
    public static function update_user_relation($user_id, $relation_id, $old_relation_id)
    {
        global $wpdb;

        $query = $wpdb->prepare(
            '
        UPDATE ' . $wpdb->prefix . 'uam_accessgroup_to_object
        SET group_id = %d
        WHERE group_id = %d AND object_id = %d
      ',
            $relation_id,
            $old_relation_id,
            $user_id
        );

        $wpdb->query($query);
    }

    /**
     * Removes an existing user relation.
     *
     * Remove the relation between a user and a user group. This is needed if users are not
     * longer license admins.
     *
     * @since     1.0.0
     * @access    public
     * @param     integer    $user_id            The user's id.
     * @param     integer    $relation_id        Optional. The relation id. If none given, delete all relations.
     */
    public static function remove_user_relation($user_id, $relation_id = false)
    {
        global $wpdb;

        if ($relation_id == false) {
            $wpdb->delete($wpdb->prefix . 'uam_accessgroup_to_object', [
                'object_id' => $user_id,
            ]);
        } else {
            $wpdb->delete($wpdb->prefix . 'uam_accessgroup_to_object', [
                'object_id' => $user_id,
                'group_id' => $relation_id,
            ]);
        }
    }

    /**
     * Inserts an organization into the WordPress database.
     *
     * Inserts an entry to the User Access Manager table, so that the user is now in the
     * specified group.
     *
     * @since     1.0.0
     * @access    public
     * @param     integer    $organization_id          The organization's id in pipedrive.
     * @param     string     $organization_name        The organization's name.
     * @param     string     $organization_website     The organization's website. May be empty.
     * @param     string     $organization_relation    The organization's relation.
     */
    public static function insert_organization($organization_id, $organization_name, $organization_website, $organization_relation)
    {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . self::ORGANIZATIONS_TABLE_NAME,
            [
                'org_pipedrive_id' => $organization_id,
                'org_name' => $organization_name,
                'org_website' => $organization_website,
                'org_relation' => $organization_relation,
            ]
        );
    }

    /**
     * Updates an organization in the WordPress database.
     *
     * Updates an organization registered in the WordPress database.
     *
     * @since     1.0.0
     * @access    public
     * @param     integer    $organization_id          The organization's id in pipedrive.
     * @param     string     $organization_name        The organization's name.
     * @param     string     $organization_website     The organization's website. May be empty.
     * @param     string     $organization_relation    The organization's relation.
     */
    public static function update_organization($organization_id, $organization_name, $organization_website, $organization_relation)
    {
        global $wpdb;

        $wpdb->update(
            $wpdb->prefix . self::ORGANIZATIONS_TABLE_NAME,
            [
                'org_name' => $organization_name,
                'org_website' => $organization_website,
                'org_relation' => $organization_relation,
            ],
            [
                'org_pipedrive_id' => $organization_id,
            ]
        );
    }

    public static function delete_organization($organization_id)
    {
        global $wpdb;

        $wpdb->delete(
            $wpdb->prefix . self::ORGANIZATIONS_TABLE_NAME,
            [
                'org_pipedrive_id' => $organization_id,
            ]
        );
    }
}
