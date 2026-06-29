<?php

/**
 * The handler for the actual API calls to LimeLM.
 *
 * @link       https://customers.vizoo3d
 * @since      1.0.0
 *
 *
 * @package    Vizoo_LimeLM
 * @subpackage Vizoo_LimeLM/includes
 */

class Vizoo_LimeLM_API_Handler
{
    public static function set_license_renewal_deal($license_id, $deal_id)
    {
        $post_data = [
            'method' => 'limelm.pkey.setDetails',
            'pkey_id' => $license_id,
            'feature_name' => ['RenewalDealId'],
            'feature_value' => [$deal_id],
        ];

        $response = self::get_limelm_response($post_data);
        return $response['stat'] == 'ok' ?: $response['err']['code'];
    }

    /**
     * Informs LimeLM that the user received an expiration notification.
     *
     * Sets the 'ExpirationNotificationSent'-field of the license to the expiration date.
     * This way we can determine whether the user received an expiration notification for
     * the currently approching expiration date.
     *
     * @since    1.0.0
     * @access   public
     * @param    integer    $license_id    The ID of the license.
     * @param    integer    $company_id    The company's id on pipedrive.
     * @param    string     $date          The ISO-formatted date.
     * @return   mixed                     True if successfull, error code on failure.
     */
    public static function set_expiration_notification_sent($license_id, $company_id, $date)
    {
        $license = self::get_license($license_id, $company_id);
        if (!is_a($license, 'Vizoo_LimeLM_License')) {
            return $license;
        }

        $post_data = [
            'method' => 'limelm.pkey.setDetails',
            'pkey_id' => $license_id,
            'feature_name' => ['ExpirationNotificationSent'],
            'feature_value' => [$date],
        ];

        $response = self::get_limelm_response($post_data);
        return $response['stat'] == 'ok' ?: $response['err']['code'];
    }

    /**
     * Informs LimeLM that the user received an expiration reminder.
     *
     * Sets the 'ExpirationReminderSent'-field of the license to the expiration date.
     * This way we can determine whether the user received an expiration reminder for the
     * currently approching expiration date.
     *
     * @since    1.0.0
     * @access   public
     * @param    integer    $license_id    The ID of the license.
     * @param    integer    $company_id    The company's id on pipedrive.
     * @param    string     $date          The ISO-formatted date.
     * @return   mixed                     True if successfull, error code on failure.
     */
    public static function set_expiration_reminder_sent($license_id, $company_id, $date)
    {
        $license = self::get_license($license_id, $company_id);
        if (!is_a($license, 'Vizoo_LimeLM_License')) {
            return $license;
        }

        $post_data = [
            'method' => 'limelm.pkey.setDetails',
            'pkey_id' => $license_id,
            'feature_name' => ['ExpirationReminderSent'],
            'feature_value' => [$date],
        ];

        $response = self::get_limelm_response($post_data);
        return $response['stat'] == 'ok' ?: $response['err']['code'];
    }

    /**
     * Informs LimeLM that the user ignored all expiration reminders.
     *
     * Sets the 'ExpirationReminderIgnored'-field of the license to the expiration date.
     * This way we can determine whether the user ignored an expiration reminder for the
     * currently approching expiration date.
     *
     * @since    1.0.0
     * @access   public
     * @param    integer    $license_id    The ID of the license.
     * @param    integer    $company_id    The company's id on pipedrive.
     * @param    string     $date          The ISO-formatted date.
     * @return   mixed                     True if successfull, error code on failure.
     */
    public static function set_expiration_ignored($license_id, $company_id, $date)
    {
        $license = self::get_license($license_id, $company_id);
        if (!is_a($license, 'Vizoo_LimeLM_License')) {
            return $license;
        }

        $post_data = [
            'method' => 'limelm.pkey.setDetails',
            'pkey_id' => $license_id,
            'feature_name' => ['ExpirationReminderIgnored'],
            'feature_value' => [$date],
        ];

        $response = self::get_limelm_response($post_data);
        return $response['stat'] == 'ok' ?: $response['err']['code'];
    }

    /**
     * Informs LimeLM that the user sent a cancellation request.
     *
     * Sets the 'CancellationRequestSent'-field of the license to the expiration date.
     * This way we can determine whether the user sent a cancellation request for the
     * currently approching expiration date.
     *
     * @since    1.0.0
     * @access   public
     * @param    integer    $license_id    The ID of the license.
     * @param    integer    $company_id    The company's id on pipedrive.
     * @param    string     $date          The ISO-formatted date.
     * @return   mixed                     True if successfull, error code on failure.
     */
    public static function set_cancellation_request_sent($license_id, $company_id, $date)
    {
        $license = self::get_license($license_id, $company_id);
        if (!is_a($license, 'Vizoo_LimeLM_License')) {
            return $license;
        }

        $post_data = [
            'method' => 'limelm.pkey.setDetails',
            'pkey_id' => $license_id,
            'feature_name' => ['CancellationRequestSent'],
            'feature_value' => [$date],
        ];

        $response = self::get_limelm_response($post_data);
        return $response['stat'] == 'ok' ?: $response['err']['code'];
    }

    /**
     * Deactivates the activation of a license.
     *
     * Deactivates the activation specified. After deactivation an activation cannot be
     * restored and the user has to activate the software again.
     *
     * The license id and the company id must be provided to check permissions. In an AJAX
     * call the user may alter the request to deactivate activations of licenses that are
     * not accessable to him.
     *
     * @since    1.0.0
     * @access   public
     * @param    integer    $activation_id    The id of the activation.
     * @param    integer    $license_id       The id of the license the activation belongs to.
     * @param    integer    $company_id       The company's id on pipedrive.
     * @return   mixed                        True if successfull, error code on failure.
     */
    public static function deactivate_activation($activation_id, $license_id, $company_id)
    {
        $license = self::get_license($license_id, $company_id, 'id_list');
        if (!is_a($license, 'Vizoo_LimeLM_License')) {
            return $license;
        }
        $license_activations = $license->get_activations();
        if (!in_array($activation_id, $license_activations)) {
            return '403';
        }

        $post_data = [
            'method' => 'limelm.pkey.deactivate',
            'act_id' => $activation_id,
        ];
        $response = self::get_limelm_response($post_data);

        return $response['stat'] == 'ok' ?: $response['err']['code'];
    }

    /**
     * Retrieves a specific license from the LimeLM API.
     *
     * The company id must be provided to check permissions. In an AJAX call the user may
     * alter the request to view licenses that are not accessable to him. Therefore the
     * company id should be set in the serverside AJAX handler to the company id of the
     * currently logged in user.
     *
     * @since    1.0.0
     * @access   public
     * @param    integer    $license_id             The license id.
     * @param    integer    $company_id             The company's id on pipedrive.
     * @param    mixed      $include_activations    Optional. Whether to include activations. May be 'array', 'id_list' or false.
     * @return   mixed                              The license if successfull, error code on failure.
     */
    public static function get_license($license_id, $company_id, $include_activations = false)
    {
        $post_data = [
            'method' => 'limelm.pkey.getDetails',
            'pkey_id' => $license_id,
        ];

        $response = self::get_limelm_response($post_data);

        if ($response['stat'] != 'ok') {
            return $response['err']['code'];
        }

        $license = $response['pkey'];
        $license['id'] = $license_id;

        return self::convert_license($license, $include_activations, $company_id);
    }

    /**
     * Gets all licenses of a company.
     *
     * Fetches all licenses that are registered for a company. A company is registered by
     * filling the company's ID on pipedrive in the 'CompanyID'-field in LimeLM.
     *
     * @since    1.0.0
     * @access   public
     * @param    integer    $company_id    The company's id on pipedrive.
     * @return   mixed                     An array of licenses if successfull, error code on failure.
     */
    public static function get_licenses($company_id)
    {
        $open_renewal_deals = Vizoo_Pipedrive_API_Handler::get_open_renewal_deals();

        $post_data = [
            'method' => 'limelm.pkey.advancedSearch',
            'num' => '999',
            'get_features' => 'true',
            'get_acts' => 'true',
            'feature_name' => ['CompanyId'],
            'feature_value' => [$company_id],
        ];

        $response = self::get_limelm_response($post_data);

        if ($response['stat'] == 'ok') {
            $licenses = [];

            foreach ($response['pkeys']['pkey'] as $license) {
                $licenses[] = self::convert_license($license, 'array', null, $open_renewal_deals);
            }
            return $licenses;
        } else {
            return $response['err']['code'];
        }
    }

    /**
     * Gets all licenses in LimeLM.
     *
     * Fetches all licenses that are registered for any company. A company is registered by
     * filling the company's ID on pipedrive in the 'CompanyID'-field in LimeLM.
     *
     * @since    1.0.0
     * @access   public
     * @return   mixed     An array of licenses if successfull, error code on failure.
     */
    public static function get_all_licenses()
    {
        $post_data = [
            'method' => 'limelm.pkey.advancedSearch',
            'num' => '9999',
            'get_features' => 'true',
            'feature_name' => ['CompanyId'],
            'feature_value' => ['*'],
            'feature_match' => ['wildcard'],
        ];

        $response = self::get_limelm_response($post_data);

        if ($response['stat'] == 'ok') {
            $licenses = [];

            foreach ($response['pkeys']['pkey'] as $license) {
                $licenses[] = self::convert_license($license);
            }
            return $licenses;
        } else {
            return $response['err']['code'];
        }
    }

    /**
     * Converts the features array into a useable format.
     *
     * Converts the features array from the format of LimeLM to a more useable one. By
     * default LimeLM returns the array of features as an indexed array, not an associative
     * array. This function fixes that.
     *
     *
     * * Example * *
     *
     * By default LimeLM returns something like this:
     *
     * 'features' => {
     *   { 'name' => 'Company',   'value' => 'Vizoo GmbH' },
     *   { 'name' => 'CompanyID', 'value' => '1' },
     *   ...
     * }
     *
     *
     * In this case we need to cycle through each feature and check for the name to be the
     * the feature we want to know.
     * This method converts it into something like this:
     *
     * 'features' => {
     *   'Company'   => 'Vizoo GmbH',
     *   'CompanyID' => '1',
     *   ...
     * }
     *
     *
     * So that we can access the company directly with $features['Company'] now.
     *
     * * End of example * *
     *
     * @since    1.0.0
     * @access   private
     * @param    array      $features    The feature array provided by LimeLM.
     * @return   array                   The formatted feature array.
     */
    private static function convert_features($features)
    {
        $result = [];
        array_walk($features, function ($value, $key) use (&$result) {
            $result[$value['name']] = $value['value'];
        });
        return $result;
    }

    /**
     * Converts the activations array into a useable format.
     *
     * Converts the activations array from the format of LimeLM to a more useable one. By
     * default LimeLM returns the array of activations as an indexed array. This method
     * converts it to an indexed array of Vizoo_LimeLM_Activation objects or alternatively
     * an array containing only the activation's ids (to check whether an activations
     * belongs to a license).
     *
     *
     * * Example * *
     *
     * By default LimeLM returns something like this:
     *
     * 'activations' => {
     *   { 'id' => '123', 'ip' => '1.1.1.1', 'date' => '01-01-1990', ... },
     *   { 'id' => '321', 'ip' => '2.2.2.2', 'date' => '01-01-1990', ... },
     *   ...
     * }
     *
     *
     * This method converts it into something like this (if parameter $to equals 'array'):
     *
     * 'activations' => {
     *   Vizoo_LimeLM_Activation( id, ip, date, ...),
     *   Vizoo_LimeLM_Activation( id, ip, date, ...),
     *   ...
     * }
     *
     *
     * Alternatively this method converts it into something like this (if parameter $to
     * equals 'id_list'; This is used to verify whether an activation belongs to a
     * license):
     *
     * 'activations' => { '123', '321',... }
     *
     *
     * * End of example * *
     *
     * @since    1.0.0
     * @access   private
     * @param    array      $activations    The feature array provided by LimeLM.
     * @param    string     $to             Optional. To which format the activations should be converted to.
     * @return   array                      The formatted activations array.
     */
    private static function convert_activations($activations, $to = 'array')
    {
        $result = [];

        if (isset($activations) && $to != false) {
            if ($to == 'id_list') {
                array_walk($activations, function ($value, $key) use (&$result) {
                    $result[] = $value['id'];
                });
            } elseif ($to == 'array') {
                foreach ($activations as $activation) {
                    $result[] = new Vizoo_LimeLM_Activation($activation);
                }
            }
        }

        return $result;
    }

    /**
     * Converts a license array into a useable format.
     *
     * Converts the license array from the format of LimeLM to a more useable one. By
     * default LimeLM returns the license's arrays as an indexed array. This method
     * converts it to an indexed array of Vizoo_LimeLM_Activation objects or alternatively
     * an array containing only the activation's ids (to check whether an activations
     * belongs to a license).
     *
     * @since    1.0.0
     * @access   private
     * @param    array                   $license                The license array provided by LimeLM.
     * @param    string                  $include_activations    Optional. To which format the activations should be converted to.
     * @return   Vizoo_LimeLM_License                            The license object.
     */
    private static function convert_license($license, $include_activations = false, $company_id = null, $open_renewal_deals = [])
    {
        if ($include_activations === true) {
            $include_activations = 'array';
        }
        if ($include_activations === false || !isset($license['activations'])) {
            $activations = [];
        } else {
            $activations = self::convert_activations($license['activations']['act'], $include_activations);
        }
        $features = self::convert_features($license['features']['feature']);

        if (isset($company_id) && $company_id != $features['CompanyId']) {
            return '403';
        }

        return new Vizoo_LimeLM_License($license, $features, $activations, $open_renewal_deals);
    }

    /**
     * Sends a request to and retrieves data from the API.
     *
     * Sends the data specified as POST request to the LimeLM API. The reponse will be
     * returned.
     *
     * @since    1.0.0
     * @access   private
     * @param    array      $post_data    The POST data that should be sent to the LimeLM API.
     * @return   array                    The reponse from the LimeLM API.
     */
    private static function get_limelm_response($post_data)
    {
        $post_data['api_key'] = getenv('VIZOO_LIMELM_API_KEY');
        $post_data['format'] = 'json';
        $post_data['nojsoncallback'] = '1';

        $post_string = http_build_query($post_data);

        $request = curl_init(getenv('VIZOO_LIMELM_API_ADDRESS'));
        curl_setopt($request, CURLOPT_HEADER, 0);
        curl_setopt($request, CURLOPT_ENCODING, '');
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_POSTFIELDS, $post_string);
        $post_response = curl_exec($request);
        curl_close($request);

        return json_decode($post_response, true);
    }
}
