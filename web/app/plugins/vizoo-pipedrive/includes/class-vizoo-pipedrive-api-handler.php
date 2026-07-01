<?php

/**
 * The handler for the API calls to pipedrive.
 *
 * @link       https://customers.vizoo3d.com
 * @since      1.0.0
 *
 *
 * @package    Vizoo_Pipedrive
 * @subpackage Vizoo_Pipedrive/includes
 */

class Vizoo_Pipedrive_API_Handler
{
    /**
     * Creates a deal in pipedrive.
     *
     * @since     1.0.0
     * @access    public
     * @param     string     $title                  Title of the deal.
     * @param     float      $value                  Value of the deal.
     * @param     string     $currency               Currency of the deal. Accepts a 3-character currency code.
     * @param     integer    $owner_id               ID of the user who will be marked as the owner of this deal.
     * @param     integer    $person_id              ID of the person this deal will be associated with.
     * @param     integer    $org_id                 ID of the organization this deal will be associated with.
     * @param     string     $expected_close_date    The expected close date of the deal. In ISO 8601 format: YYYY-MM-DD.
     * @return    integer                            The ID of the newly created deal.
     */
    public static function create_deal($title, $value, $currency, $owner_id, $person_id, $org_id, $expected_close_date)
    {
        $args = [
            'title' => $title,
            'value' => $value,
            'currency' => $currency,
            'owner_id' => (int) $owner_id,
            'person_id' => (int) $person_id,
            'org_id' => (int)$org_id,
            'pipeline_id' => 5,
            'expected_close_date' => $expected_close_date,
        ];
        $response = self::call_api('POST', 'v2/deals', array_filter($args));
        return $response['id'];
    }

    /**
     * Creates a note in pipedrive.
     *
     * @since     1.0.0
     * @access    public
     * @param     string     $content      Content of the note in HTML format. Will be sanitized on pipedrive.
     * @param     integer    $entity_id    ID of the deal the note will be attached to.
     * @param     string     $type         Entity type, one of lead, deal, person or org.
     * @return    integer                  The ID of the newly created note.
     */
    public static function create_note($content, $entity_id, $type = 'deal')
    {
        $args = [
            'content' => $content,
            "{$type}_id" => $entity_id,
        ];
        $response = self::call_api('POST', 'v1/notes', array_filter($args));
        return $response['id'];
    }

    /**
     * Creates an activity in pipedrive.
     *
     * @since     1.0.0
     * @access    public
     * @param     string      $subject     Subject of the activity.
     * @param     string      $type        Type of the activity. Uses the key_string parameter of ActivityTypes.
     * @param     DateTime    $due_date    The due date of the activity.
     * @param     integer     $owner_id     ID of the user whom the activity will be assigned to.
     * @param     integer     $deal_id     ID of the deal the activity will be linked to.
     * @return    integer                  The ID of the newly created activity.
     */
    public static function create_activity($subject, $type, $due_date, $owner_id, $deal_id, $note = '')
    {
        $args = [
            'subject' => $subject,
            'type' => $type,
            'due_date' => $due_date->format('Y-m-d'),
            'owner_id' => $owner_id,
            'deal_id' => $deal_id,
            'note' => $note,
        ];
        $response = self::call_api('POST', 'v2/activities', array_filter($args));
        return $response['id'];
    }

    /**
     * Gets the user ID by name.
     *
     * @since     1.0.0
     * @access    public
     * @param     string     $name    Name of the user.
     * @return    integer             The ID of the user if found, else 0.
     */
    public static function get_user_id_by_name($name)
    {
        if (empty($name)) {
            return 0;
        }
        $response = self::call_api('GET', 'v1/users', ['term' => $name], 'find');
        return empty($response) ? 0 : $response[0]['id'];
    }

    public static function get_user_by_id($user_id)
    {
        if (empty($user_id)) {
            return 0;
        }
        $response = self::call_api('GET', 'v1/users', [], $user_id);
        return $response;
    }

    public static function add_file_to_deal($filename, $deal_id, $mime_type, $posted_filename)
    {
        $response = self::call_api('POST', 'v1/files', [
            'file' => curl_file_create($filename, $mime_type, $posted_filename),
            'deal_id' => $deal_id,
        ], '', 'multipart/form-data');
        return empty($response['id']) ? 0 : $response['id'];
    }

    public static function get_open_renewal_deals()
    {
        $response = self::call_api('GET', 'v2/deals', ['filter_id' => 262, 'status' => 'open']);
        return array_map(fn ($element): int => $element['id'], $response);
    }

    public static function get_organization_details($organizationId)
    {
        if (empty($organizationId)) {
            return 0;
        }
        $response = self::call_api('GET', 'v2/organizations', [], $organizationId);
        return $response;
    }

    /**
     * Calls the pipedrive API.
     *
     * @since     1.0.0
     * @access    private
     * @param     string     $method     The HTTP request method.
     * @param     string     $element    Element that should be accessed. E.g. 'notes', 'deals', ...
     * @param     array      $args       Arguments of the request.
     * @param     string     $func       Optional. A specific function call added to the request url.
     * @return    array                  The data that is returned by the pipedrive API.
     */
    private static function call_api($method, $element, $args = [], $func = '', $type = 'application/json')
    {
        $url = getenv('VIZOO_PIPEDRIVE_API_ADDRESS') . $element . ($func !== '' ? '/' . $func : '');
        $postfields = [];
        if ($method === 'POST' || $method === 'PUT') {
            $postfields = $args;
            $args = [];
        }

        $args['api_token'] = getenv('VIZOO_PIPEDRIVE_API_TOKEN');
        $url .= '?' . http_build_query($args);

        $channel = curl_init();
        curl_setopt($channel, CURLOPT_URL, $url);
        curl_setopt($channel, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($channel, CURLOPT_CUSTOMREQUEST, $method);
        if (!empty($postfields)) {
            switch ($type) {
                case 'multipart/form-data':
                    curl_setopt($channel, CURLOPT_HTTPHEADER, ['Content-Type: multipart/form-data']);
                    curl_setopt($channel, CURLOPT_POSTFIELDS, $postfields);
                    break;
                case 'application/json':
                default:
                    curl_setopt($channel, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                    curl_setopt($channel, CURLOPT_POSTFIELDS, json_encode($postfields));
                    break;
            }
        }
        $output = curl_exec($channel);
        curl_close($channel);

        $result = json_decode($output, true);

        if (empty($result['success'])) {
            throw new RuntimeException('The pipedrive API returned an error. Output: ' . $output);
        } else {
            return $result['data'];
        }
    }
}
