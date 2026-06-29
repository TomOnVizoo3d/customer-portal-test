<?php

/**
 * Vizoo GmbH Receive Webhook Requests
 *
 * Receive webhook requests from pipedrive and update Vizoo's customer portal
 * (wordpress page).
 *
 * Only organizations reviewed by a Vizoo co-worker and marked as 'Confirmed
 * for Database' are sent to the database.
 *
 * For documentation of pipedrive Webhooks, go to:
 * https://webhooks-manager.pipedrive.com/web/#/documentation
 *
 *
 * Content:
 * 1. Initialize
 * 2. Handle incoming POST requests
 * 3. Functions reading the wordpress database
 * 4. Functions modifying the wordpress database
 * 5. Logging and emailing functions
 * 6. Functions for parsing the pipedrive request
 *
 */

/**
 * 1. Initialize
 */

// init error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/errs.txt');

$reference_id = substr(md5(rand()), 0, 7);

// report mail, where success and error mails should be sent to
$report_mail = 'info@vizoo3d.com';

require_once 'vizoo_customers.env';
require_once 'PHPMailer/Exception.php';
require_once 'PHPMailer/SMTP.php';
require_once 'PHPMailer/PHPMailer.php';

// the api keys of fields can be found under 'Customize fields' in pipedrive
$custom_field_definitions_person = [
    'd8d9ec3964d384586c3d5bfca17581d734ff87f1' => [
        'name' => 'confirmed',
        'options' => [
            '16' => 'Yes',
            '22' => 'Yes (silent)',
        ],
    ],
    'ac0f6a3158cc8abfd08d082c28f883bcd3def3cf' => [
        'name' => 'role',
        'options' => [
            '20' => 'User',
            '21' => 'LicenseManager',
        ],
    ],
];

$custom_field_definitions_organization = [
    'dd6673c0508069aee54b3a5badc9a19d9127bae0' => [
        'name' => 'confirmed',
        'options' => [
            '18' => 'Yes',
        ],
    ],
    '3e46eecb7290e77ffe0b3c2116d04cac802ffb49' => [
        'name' => 'relationship',
        'options' => [
            '2' => 'Customer',
            '3' => 'Partner',
            '4' => 'Reseller',
            '23' => 'Customer (via Reseller)',
            '24' => 'Press',
            '223' => 'Service Customer (no portal access)',
        ],
    ],
    'fade8eb9b767f3a34d84cbfeb4e3f78427c13d87' => [
        'name' => 'website',
        'options' => null,
    ],
];

enum PipedriveActions: string
{
    case CREATE = 'create';
    case CHANGE = 'change';
    case DELETE = 'delete';
}

/**
 * 2. Handle incoming POST request
 */

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        vizoo_final_msg('invalid request', 400);
    }

    $body = file_get_contents('php://input');
    $request = json_decode($body, 1);

    if (is_null($request)) {
        vizoo_final_msg('empty request body', 400);
    }

    if (empty($request['meta']['action']) || empty($request['meta']['entity'])) {
        vizoo_final_msg('wrong request body', 400);
    }

    // Report success to pipedrive early, so we don't run into a timeout.
    ob_start();
    echo " ";
    http_response_code(200);
    header('Connection: close');
    header('Content-Length: ' . ob_get_length());
    ob_end_flush();
    @ob_flush();
    flush();
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    }

    $task = $request['meta']['action'];
    $object_type = $request['meta']['entity'];

    $object_id = $request['meta']['id'];
    $attempt_count = $request['meta']['attempt'];
    $formatted_time = date('Y-m-d H:i:s');

    vizoo_msg("incoming request: $object_type.$task | id #$object_id | attempt $attempt_count | $formatted_time");

    switch ($object_type) {
        case 'organization':
            $custom_field_definitions = $custom_field_definitions_organization;
            break;
        case 'person':
            $custom_field_definitions = $custom_field_definitions_person;
            break;
        default:
            vizoo_final_msg('no change in database needed (no person or organization)');
    }

    $previous_custom_fields_array = $request['previous']['custom_fields'];
    $custom_fields_array = $request['data']['custom_fields'];
    $custom_fields_previous = vizoo_extract_custom_fields($custom_field_definitions, $previous_custom_fields_array);
    $custom_fields_data = vizoo_extract_custom_fields($custom_field_definitions, $custom_fields_array);

    // setting defaults to prevent 'possible undefined warnings'
    $custom_fields = [];
    $visible = false;
    $visible_previous = false;

    // check whether the 'Confirmed for Database'-field is set
    // and if so, get the relevant object for the database entry
    if ($task == PipedriveActions::CREATE->value && !empty($custom_fields_data['confirmed'])) {
        // if the object was created and is directly confirmed for database, create it
        $object = $request['data'];
        $custom_fields = $custom_fields_data;
        $visible = ($custom_fields['confirmed'] == 'Yes');
    } elseif ($task === PipedriveActions::CHANGE->value && !empty($custom_fields_data['confirmed']) && !array_key_exists('confirmed', $custom_fields_previous)) {
        // if the object previously was confirmed for database and still is, we have to update it
        $object = $request['data'];
        $custom_fields = $custom_fields_data;
        $visible = ($custom_fields['confirmed'] == 'Yes');
        $visible_previous = ($custom_fields['confirmed'] == 'Yes') && !array_key_exists('confirmed', $custom_fields_previous);
    } elseif ($task == PipedriveActions::CHANGE->value && !empty($custom_fields_data['confirmed']) && empty($custom_fields_previous['confirmed'])) {
        // if the object previously wasn't confirmed for database, but was updated and is now confirmed for
        // database we have to create it (not update it)
        $task = PipedriveActions::CREATE->value;
        $object = $request['data'];
        $custom_fields = $custom_fields_data;
        $visible = ($custom_fields['confirmed'] == 'Yes');
    } elseif ($task == PipedriveActions::CHANGE->value && empty($custom_fields_data['confirmed']) && !empty($custom_fields_previous['confirmed'])) {
        // if the object previously was confirmed for database, but was updated and is not confirmed for
        // database anymore we have to delete it (not update it)
        $task = PipedriveActions::DELETE->value;
        $object = $request['previous'];
        $custom_fields = $custom_fields_previous;
    } elseif ($task == PipedriveActions::DELETE->value && !empty($request['previous'])) {
        $object = $request['previous'];
    } else {
        // if nothing is applicable we have nothing to do here
        vizoo_final_msg('no change in database needed');
    }
    vizoo_msg('change in database necessary: ' . $object_type . '.' . $task . ', loading wordpress');

    // import wordpress
    require_once __DIR__ . '/../wp-load.php';

    if ($object_type == 'organization') {
        // fetch information about the organization
        $pd_org_id = $object['id'] ?? $request['data']['id'];
        $pd_org_name = $object['name'];
        $pd_org_website = $custom_fields['website'] ?: '';
        $pd_org_relation = $custom_fields['relationship'] ?: 'Customer (via Reseller)';

        vizoo_msg("organization details: id #$pd_org_id | name '$pd_org_name' | website '$pd_org_website' | relation '$pd_org_relation'");

        if ($task == PipedriveActions::CREATE->value) {
            // create an organization into the wordpress database
            vizoo_create_organization($pd_org_id, $pd_org_name, $pd_org_website, $pd_org_relation);
        } elseif ($task == PipedriveActions::CHANGE->value) {
            // update an organization in the wordpress database
            vizoo_update_organization($pd_org_id, $pd_org_name, $pd_org_website, $pd_org_relation);
        } elseif ($task == PipedriveActions::DELETE->value) {
            // delete the organization
            vizoo_delete_organization($pd_org_id);
        }
    } elseif ($object_type == 'person') {
        // fetch information about the person
        $pd_person_id = $object['id'] ?? $request['data']['id'];
        $pd_person_name = $object['name'];
        $pd_person_mail = $object['emails'][0]['value'];
        $pd_person_org_id = $object['org_id'];
        $pd_person_role = $custom_fields['role'];

        vizoo_msg("person details: id #$pd_person_id | name '$pd_person_name' | mail '$pd_person_mail' | organization id #$pd_person_org_id");

        if ($task == PipedriveActions::CREATE->value) {
            // insert the user into the database and get back the id
            $db_person_id = vizoo_create_user($pd_person_id, $pd_person_mail, $pd_person_name, $pd_person_org_id, $pd_person_role, $visible);
        } elseif ($task == PipedriveActions::CHANGE->value) {
            // update the user
            vizoo_update_user($pd_person_id, $pd_person_mail, $pd_person_name, $pd_person_role, $visible, $visible_previous);
        } elseif ($task == PipedriveActions::DELETE->value) {
            // and delete the user
            vizoo_delete_user($pd_person_id);
        }
    }
    // we're done
    vizoo_final_msg('finished actions');
} catch (Exception $e) {
    vizoo_send_error_mail('Synchronization script failed', 'There was an error executing the synchronization script:\n' . $e->__toString());
    vizoo_final_msg('exception was thrown: ' . $e->__toString());
}

/**
 * 3. Functions reading the wordpress database
 */

function vizoo_get_user_id_by_pipedrive_id($pd_person_id)
{
    vizoo_msg('calling: vizoo_get_user_id_by_pipedrive_id( ' . $pd_person_id . ' )');
    global $wpdb;
    $result = $wpdb->get_var('SELECT user_id FROM ' . $wpdb->prefix . 'users_pipedrive WHERE pipedrive_id = "' . $pd_person_id . '"');
    vizoo_msg(' -> ' . print_r($result, true), false);
    return $result;
}

function vizoo_get_pipedrive_id_by_user_id($db_person_id)
{
    vizoo_msg('calling: vizoo_get_pipedrive_id_by_user_id( ' . $db_person_id . ' )');
    global $wpdb;
    $result = $wpdb->get_var('SELECT pipedrive_id FROM ' . $wpdb->prefix . 'users_pipedrive WHERE user_id = "' . $db_person_id . '"');
    vizoo_msg(' -> ' . print_r($result, true), false);
    return $result;
}

function vizoo_get_organization_by_pipedrive_id($pd_org_id)
{
    vizoo_msg('calling: vizoo_get_organization_by_pipedrive_id( ' . $pd_org_id . ' )');
    global $wpdb;
    $result = $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . 'organizations WHERE org_pipedrive_id = "' . $pd_org_id . '"', ARRAY_A);
    vizoo_msg(' -> ' . print_r($result, true), false);
    return $result;
}

function vizoo_get_organization_by_id($db_org_id)
{
    vizoo_msg('calling: vizoo_get_organization_by_id( ' . $db_org_id . ' )');
    global $wpdb;
    $result = $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . 'organizations WHERE ID = "' . $db_org_id . '"', ARRAY_A);
    vizoo_msg(' -> ' . print_r($result, true), false);
    return $result;
}

if (!function_exists('vizoo_get_organization_of_user')) {
    function vizoo_get_organization_of_user($db_person_id)
    {
        vizoo_msg('calling: vizoo_get_organization_of_user( ' . $db_person_id . ' )');
        global $wpdb;
        $result = $wpdb->get_var('SELECT org_id FROM ' . $wpdb->prefix . 'organizations_relationships WHERE user_id = "' . $db_person_id . '"');
        vizoo_msg(' -> ' . print_r($result, true), false);
        return $result;
    }
}

function vizoo_get_relation_by_groupname($groupname)
{
    vizoo_msg('calling: vizoo_get_relation_by_groupname( \'' . $groupname . '\' )');
    global $wpdb;
    $result = $wpdb->get_var('SELECT ID FROM ' . $wpdb->prefix . 'uam_accessgroups WHERE groupname = "' . $groupname . '"');
    vizoo_msg(' -> ' . print_r($result, true), false);
    return $result;
}

function vizoo_user_exists($db_person_mail)
{
    vizoo_msg('calling: vizoo_user_exists( \'' . $db_person_mail . '\' )');
    $result = email_exists($db_person_mail) || username_exists($db_person_mail);
    vizoo_msg(' -> ' . print_r($result, true), false);
    return $result;
}

function vizoo_organization_exists($pd_org_id)
{
    vizoo_msg('calling: vizoo_organization_exists( \'' . $pd_org_id . '\' )');
    global $wpdb;
    $result = $wpdb->get_var('SELECT COUNT(ID) AS count FROM ' . $wpdb->prefix . 'organizations WHERE org_pipedrive_id = "' . $pd_org_id . '"') == '1';
    vizoo_msg(' -> ' . print_r($result, true), false);
    return $result;
}

/**
 * 4. Functions modifying the wordpress database
 */

function vizoo_create_organization($pd_org_id, $pd_org_name, $pd_org_website, $pd_org_relation)
{
    vizoo_msg('calling: vizoo_create_organization( \'' . $pd_org_id . '\', \'' . $pd_org_name . '\', \'' . $pd_org_website . '\', \'' . $pd_org_relation . '\')');
    global $wpdb;

    if (vizoo_organization_exists($pd_org_id)) {
        vizoo_msg('organization already exists, organization not created');
        vizoo_send_error_mail('Organization already exists', 'Someone requested \'' . $pd_org_name . '\' to be created to the database, but the organization is already registered.');
        return false;
    }

    return $wpdb->insert($wpdb->prefix . 'organizations', [
        'org_pipedrive_id' => $pd_org_id,
        'org_name' => $pd_org_name,
        'org_website' => $pd_org_website,
        'org_relation' => $pd_org_relation,
    ]);
}

function vizoo_create_user($pd_person_id, $pd_person_mail, $pd_person_name, $pd_person_org_id, $pd_person_role, $visible)
{
    vizoo_msg('calling: vizoo_create_user( \'' . $pd_person_id . '\', \'' . $pd_person_mail . '\', \'' . $pd_person_name . '\', \'' . $pd_person_org_id . '\', \'' . $pd_person_role . '\', \'' . $visible . '\' )');
    global $wpdb;
    require_once __DIR__ . '/../wp-admin/includes/user.php';

    $db_org = vizoo_get_organization_by_pipedrive_id($pd_person_org_id);
    if ($db_org === null) {
        vizoo_msg('organization not in database, user not created');
        vizoo_send_error_mail('Organization not in database', 'Someone requested \'' . $pd_person_name . '\' to be created to the database, but the organization this person is working for (id: \'' . $pd_person_org_id . '\') is not registered (yet). Please set the organization \'Confirmed for Database\' first.');
        return -1;
    }
    vizoo_msg(print_r($db_org, true));

    $db_rel_id = vizoo_get_relation_by_groupname($db_org['org_relation']);
    if ($db_rel_id === -1) {
        vizoo_msg('no relation found, user not created');
        vizoo_send_error_mail('No relation found', 'Someone requested \'' . $pd_person_name . '\' to be created to the database, but the organization this person is working for (id: \'' . $pd_person_org_id . '\') has no (valid) relation attached to it. Please add a relation to the organization first.');
        return -1;
    }

    if (vizoo_user_exists($pd_person_mail)) {
        vizoo_msg('user already exists, user not created');
        vizoo_send_error_mail('User already exists', 'Someone requested \'' . $pd_person_name . '\' to be created to the database, but the email is already registered. There might be a duplicate in the pipedrive database.');
        return -1;
    }

    $db_person_mail = sanitize_email($pd_person_mail);
    $db_person_nicename = sanitize_title($pd_person_mail);
    $db_person_pass = wp_generate_password(8);

    $new_user_id = wp_insert_user([
        'user_pass' => $db_person_pass,
        'user_login' => $db_person_mail,
        'user_nicename' => $db_person_nicename,
        'user_email' => $db_person_mail,
        'display_name' => $pd_person_name,
        'nickname' => $db_person_nicename,
        'role' => 'subscriber',
    ]);

    if (is_numeric($new_user_id)) {
        $db_person_id = $new_user_id;
    } else {
        vizoo_msg('could not create user, error: ' . $new_user_id->get_error_code());
        vizoo_send_error_mail('Could not create user', 'Someone requested \'' . $pd_person_name . '\' to be created to the database, but something went wrong. Error: ' . $new_user_id->get_error_code() . ' (WordPress Error).');
        return -1;
    }

    update_user_meta($db_person_id, 'active', 1);
    update_user_meta($db_person_id, 'vizoo_first_login', 1);
    update_user_meta($db_person_id, 'vizoo_customerrole', $pd_person_role);

    /* PREPARING FOR V.2 - SMOOTH TRANSITION */
    update_user_meta($db_person_id, 'vizoo_pipedrive_id', $pd_person_id);
    update_user_meta($db_person_id, 'vizoo_organization', $pd_person_org_id);
    /*****************************************/

    $wpdb->insert($wpdb->prefix . 'users_pipedrive', [
        'user_id' => $db_person_id,
        'pipedrive_id' => $pd_person_id,
    ]);
    $wpdb->insert($wpdb->prefix . 'organizations_relationships', [
        'org_id' => $db_org['ID'],
        'user_id' => $db_person_id,
    ]);
    $wpdb->insert($wpdb->prefix . 'uam_accessgroup_to_object', [
        'object_id' => $db_person_id,
        'general_object_type' => '_user_',
        'object_type' => '_user_',
        'group_id' => $db_rel_id,
        'group_type' => 'UserGroup',
    ]);

    if ($pd_person_role == 'LicenseManager') {
        $wpdb->insert($wpdb->prefix . 'uam_accessgroup_to_object', [
            'object_id' => $db_person_id,
            'general_object_type' => '_user_',
            'object_type' => '_user_',
            'group_id' => vizoo_get_relation_by_groupname('License-Admin'),
            'group_type' => 'UserGroup',
        ]);
    }

    vizoo_msg('user created, activated, dependencies set, relation set');

    if ($visible) {
        vizoo_send_user_mail($pd_person_name, $pd_person_mail, $db_person_pass);
    }
    vizoo_send_success_mail($pd_person_mail, $pd_person_id, $pd_person_name, $db_person_id, $db_org);

    return $db_person_id;
}

function vizoo_update_organization($pd_org_id, $db_org_name, $db_org_website, $db_org_relation)
{
    global $wpdb;
    $old_relation = vizoo_get_organization_by_pipedrive_id($pd_org_id)['org_relation'];
    $old_relation_id = vizoo_get_relation_by_groupname($old_relation);

    if ($db_org_relation == '') {
        $db_org_relation = $old_relation;
    }

    $wpdb->update($wpdb->prefix . 'organizations', [
        'org_name' => $db_org_name,
        'org_website' => $db_org_website,
        'org_relation' => $db_org_relation,
    ], [
        'org_pipedrive_id' => $pd_org_id,
    ]);

    $db_org_id = vizoo_get_organization_by_pipedrive_id($pd_org_id)['ID'];

    $affected_users_ids = $wpdb->get_col('SELECT user_id FROM ' . $wpdb->prefix . 'organizations_relationships WHERE org_id = "' . $db_org_id . '"');
    if (!empty($affected_users_ids)) {
        // ..get the id of the accessgroup..
        $relation_id = vizoo_get_relation_by_groupname($db_org_relation);

        // ..prepare the query..
        $query = 'UPDATE ' . $wpdb->prefix . 'uam_accessgroup_to_object SET group_id = "' . $relation_id . '" WHERE group_id = "' . $old_relation_id . '" AND (0=1';

        foreach ($affected_users_ids as $affected_users_id) {
            $query .= ' OR object_id = "' . $affected_users_id . '"';
        }
        $query .= ')';

        // ..and update the accessgroup-assignment
        $wpdb->query($query);
    }
}

function vizoo_update_user($pd_person_id, $pd_person_mail, $pd_person_name, $pd_person_role, $visible, $visible_previous)
{
    global $wpdb;
    require_once __DIR__ . '/../wp-admin/includes/user.php';

    $db_person_id = vizoo_get_user_id_by_pipedrive_id($pd_person_id);

    if (!($db_person_id > 0)) {
        return;
    }

    $db_person_mail = sanitize_email($pd_person_mail);
    $db_person_nicename = sanitize_title($pd_person_mail);

    if ($visible && !$visible_previous) {
        $db_person_pass = wp_generate_password(8);

        wp_insert_user([
            'ID' => $db_person_id,
            'user_pass' => wp_hash_password($db_person_pass),
            'user_login' => $db_person_mail,
            'user_nicename' => $db_person_nicename,
            'user_email' => $db_person_mail,
            'display_name' => $pd_person_name,
            'nickname' => $db_person_nicename,
            'role' => 'subscriber',
        ]);

        update_user_meta($db_person_id, 'active', 1);
        update_user_meta($db_person_id, 'vizoo_first_login', 1);
    } else {
        wp_update_user([
            'ID' => $db_person_id,
            'user_login' => $db_person_mail,
            'user_nicename' => $db_person_nicename,
            'user_email' => $db_person_mail,
            'display_name' => $pd_person_name,
            'nickname' => $db_person_nicename,
        ]);
    }

    update_user_meta($db_person_id, 'vizoo_customerrole', $pd_person_role);

    if ($pd_person_role == 'LicenseManager') {
        $wpdb->query('INSERT INTO ' . $wpdb->prefix . 'uam_accessgroup_to_object (object_id, general_object_type, object_type, group_id, group_type) VALUES ("' . $db_person_id . '", "_user_", "_user_", "' . vizoo_get_relation_by_groupname('License-Admin') . '", "UserGroup") ON DUPLICATE KEY UPDATE object_id = object_id');
    } else {
        $wpdb->delete($wpdb->prefix . 'uam_accessgroup_to_object', [
            'object_id' => $db_person_id,
            'group_id' => vizoo_get_relation_by_groupname('License-Admin'),
        ]);
    }

    if ($visible && !$visible_previous) {
        vizoo_send_user_mail($pd_person_name, $pd_person_mail, $db_person_pass);
    }
}

function vizoo_delete_organization($pd_org_id)
{
    global $wpdb;
    $org = vizoo_get_organization_by_pipedrive_id($pd_org_id);

    $wpdb->delete($wpdb->prefix . 'organizations', [
        'org_pipedrive_id' => $pd_org_id,
    ]);
    $wpdb->delete($wpdb->prefix . 'organizations_relationships', [
        'org_id' => $org['ID'],
    ]);
}

function vizoo_delete_user($pd_person_id)
{
    global $wpdb;
    require_once __DIR__ . '/../wp-admin/includes/user.php';

    $db_person_id = vizoo_get_user_id_by_pipedrive_id($pd_person_id);
    wp_delete_user($db_person_id);
    $wpdb->delete($wpdb->prefix . 'usermeta', [
        'user_id' => $db_person_id,
    ]);
    $wpdb->delete($wpdb->prefix . 'organizations_relationships', [
        'user_id' => $db_person_id,
    ]);
    $wpdb->delete($wpdb->prefix . 'users_pipedrive', [
        'user_id' => $db_person_id,
    ]);
    $wpdb->delete($wpdb->prefix . 'uam_accessgroup_to_object', [
        'object_id' => $db_person_id,
    ]);
}

/**
 * 5. Logging and emailing functions
 */

function vizoo_msg($message, $eol = true)
{
    global $reference_id;
    if ($eol) {
        $txt = PHP_EOL . "[$reference_id] $message";
    } else {
        $txt = $message;
    }
    file_put_contents(__DIR__ . '/log.txt', $txt, FILE_APPEND | LOCK_EX);
}

function vizoo_final_msg($message, $status = 204)
{
    vizoo_msg($message);
    vizoo_msg('done');
    http_response_code($status);

    die();
}

function vizoo_send_mail($from_title, $to, $subject, $message, $reply_to = null)
{
    $mailer = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mailer->isSMTP();
        $mailer->Host = getenv('VIZOO_MAIL_SMTP');
        $mailer->SMTPAuth = (bool) getenv('VIZOO_MAIL_AUTH');
        $mailer->Username = getenv('VIZOO_MAIL_USERNAME');
        $mailer->Password = getenv('VIZOO_MAIL_PASSWORD');
        $mailer->Port = (int) getenv('VIZOO_MAIL_PORT');
        $mailer->setFrom(getenv('VIZOO_MAIL_FROM'), $from_title);

        $mailer->addAddress($to);
        $mailer->Subject = $subject;
        $mailer->Body = $message;

        if (isset($reply_to)) {
            $mailer->addReplyTo($reply_to[0], $reply_to[1]);
        }

        $mailer->send();
    } catch (PHPMailer\PHPMailer\Exception $e) {
        vizoo_final_msg('mail could not be sent to ' . $to, 500);
    }
}

function vizoo_send_error_mail($subject, $message)
{
    global $report_mail;

    $text =
        'There was an error changing something in the customer database:' . "\n\n" .
        $message;

    vizoo_send_mail('Vizoo Error Report', $report_mail, '[Error report] ' . $subject, $text);
}

function vizoo_send_success_mail($pd_person_mail, $pd_person_id, $pd_person_name, $db_person_id, $db_org)
{
    global $report_mail;

    $text =
        'The user \'' . $pd_person_name . '\' was successfully registered at the Customer Portal!' . "\n\n" .
        'Details of registration:' . "\n" .
        'Name: ' . $pd_person_name . "\n" .
        'Mail: ' . $pd_person_mail . "\n" .
        'Pipedrive-ID: ' . $pd_person_id . "\n" .
        'Customer-Portal-ID: ' . $db_person_id . "\n" .
        'Organization: ' . $db_org['org_name'] . ' (Pipedrive-ID: ' . $db_org['org_pipedrive_id'] . ' / Customer-Portal-ID: ' . $db_org['ID'] . ')' . "\n" .
        'Role: ' . $db_org['org_relation'];

    vizoo_send_mail('Vizoo Report', $report_mail, '[Report] User was successfully registered at the Customer Portal', $text);
}

function vizoo_send_user_mail($name, $mail, $pass)
{

    $text =
        'Hello ' . $name . ',' . "\n\n" .
        'We have setup your account for the Vizoo Customer Portal! You now have access to resources such as our knowledge base, software downloads and your xTex license information.' . "\n\n" .
        'You may visit the portal at https://customers.vizoo3d.com and use the following credentials:' . "\n\n" .
        'Username: ' . $mail . "\n" .
        'Password: ' . $pass . "\n\n" .
        '(Please change your password after signing in for the first time)' . "\n\n\n" .
        'Kind regards,' . "\n" .
        'Vizoo Customer Service' . "\n\n" .
        '(This is an automatically generated email)';

    vizoo_send_mail('Vizoo GmbH', $mail, 'Your Vizoo Customer Portal Registration', $text, ['info@vizoo3d.com', 'Vizoo GmbH']);
}

/**
 * 6. Functions for parsing the pipedrive request
 */
function vizoo_extract_custom_fields(array $custom_field_definitions, ?array $custom_field_array)
{
    $result = [];
    if (empty($custom_field_array)) {
        return $result;
    }
    foreach ($custom_field_array as $field_name => $field_value) {
        $nested_id = $field_value['id'];
        if (!array_key_exists($field_name, $custom_field_definitions)) {
            continue;
        }
        $custom_field_definition = $custom_field_definitions[$field_name];
        $field_name = $custom_field_definition['name'];
        if ($custom_field_definition['options'] === null || !array_key_exists($nested_id, $custom_field_definition['options'])) {
            $result[$field_name] = $nested_id;
            continue;
        }
        $result[$field_name] = $custom_field_definition['options'][$nested_id];
    }
    return $result;
}
