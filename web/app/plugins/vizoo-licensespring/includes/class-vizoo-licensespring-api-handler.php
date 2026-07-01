<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'call_api.php';

class Vizoo_LicenseSpring_API_Handler
{
    public static function set_deal_id($license, $deal_id)
    {
        $new_metadata = $license->get_raw_metadata();
        if (empty($new_metadata)) {
            $new_metadata['web'] = ['renewal' => []];
        }
        if (empty($new_metadata['web'])) {
            $new_metadata['web']['renewal'] = [];
        }
        $new_metadata['web']['renewal']['dealId'] = $deal_id;
        Vizoo_LicenseSpring_API_Handler::edit_license_metadata($license->get_id(), $new_metadata);
        $license->set_raw_metadata($new_metadata);
    }

    public static function set_expiration_notification_sent($license)
    {
        $new_metadata = $license->get_raw_metadata();
        if (empty($new_metadata)) {
            $new_metadata['web'] = ['renewal' => []];
        }
        if (empty($new_metadata['web'])) {
            $new_metadata['web']['renewal'] = [];
        }
        $new_metadata['web']['renewal']['expirationNotificationSent'] = $license->get_formatted_renewal_date('c');
        Vizoo_LicenseSpring_API_Handler::edit_license_metadata($license->get_id(), $new_metadata);
    }

    public static function set_expiration_reminder_sent($license)
    {
        $new_metadata = $license->get_raw_metadata();
        if (empty($new_metadata)) {
            $new_metadata['web'] = ['renewal' => []];
        }
        if (empty($new_metadata['web'])) {
            $new_metadata['web']['renewal'] = [];
        }
        $new_metadata['web']['renewal']['expirationReminderSent'] = $license->get_formatted_renewal_date('c');
        Vizoo_LicenseSpring_API_Handler::edit_license_metadata($license->get_id(), $new_metadata);
    }

    public static function set_expiration_ignored($license)
    {
        $new_metadata = $license->get_raw_metadata();
        if (empty($new_metadata)) {
            $new_metadata['web'] = ['renewal' => []];
        }
        if (empty($new_metadata['web'])) {
            $new_metadata['web']['renewal'] = [];
        }
        $new_metadata['web']['renewal']['expirationReminderIgnored'] = $license->get_formatted_renewal_date('c');
        Vizoo_LicenseSpring_API_Handler::edit_license_metadata($license->get_id(), $new_metadata);
    }

    public static function set_cancellation_sent($license)
    {
        $new_metadata = $license->get_raw_metadata();
        if (empty($new_metadata)) {
            $new_metadata['web'] = ['renewal' => []];
        }
        if (empty($new_metadata['web'])) {
            $new_metadata['web']['renewal'] = [];
        }
        $new_metadata['web']['renewal']['cancellationRequestSent'] = $license->get_formatted_renewal_date('c');
        Vizoo_LicenseSpring_API_Handler::edit_license_metadata($license->get_id(), $new_metadata);
    }

    public static function edit_license_metadata($license_id, $metadata)
    {
        self::get_licensespring_response('PATCH', "licenses/$license_id", [], [
            'metadata' => json_encode($metadata),
        ]);
    }

    public static function get_license($license_id, $company_id)
    {
        $raw_license = self::get_licensespring_response('GET', "licenses/$license_id", [], '');
        $license =  new Vizoo_LicenseSpring_License($raw_license);
        if ($license->get_company_id() !== $company_id) {
            http_response_code(401);
            exit;
        }
        return $license;
    }

    public static function get_company_licenses($company_id)
    {
        $open_renewal_deals = Vizoo_Pipedrive_API_Handler::get_open_renewal_deals();

        $licenses = self::get_licenses($open_renewal_deals);

        return array_filter($licenses, fn ($license) => $license->get_company_id() === $company_id);
    }

    public static function get_licenses($open_renewal_deals = [])
    {
        $results = [];
        $moreResults = true;
        $page = 0;
        while ($moreResults) {
            $pagedLicenses = self::get_licensespring_response('GET', 'licenses', [
                'limit' => '100',
                'offset' => (string)($page * 100),
                'order_by' => '-created_at',
            ], '');
            $licenses = array_map(fn ($raw_license) => new Vizoo_LicenseSpring_License($raw_license, $open_renewal_deals), $pagedLicenses['results']);
            $results = array_merge($results, $licenses);
            $moreResults = $pagedLicenses['next'] !== null;
            $page += 1;
        }
        return $results;
    }

    private static function get_licensespring_response($method, $target, $query_parameters, $body)
    {
        $url = getenv('VIZOO_LICENSESPRING_API_ADDRESS') . urldecode($target) . '?' . http_build_query($query_parameters);

        $headers = [
            'Authorization: Api-Key ' . getenv('VIZOO_LICENSESPRING_API_KEY'),
            'Content-Type: application/json',
        ];

        [$response_code, $response] = call_api($url, $headers, json_encode($body), $method);

        if ($response_code !== 200 && $response_code !== 201) {
            error_log("Unexpected response code returned from LicenseSpring: $response_code");
        }
        return json_decode($response, true);
    }
}
