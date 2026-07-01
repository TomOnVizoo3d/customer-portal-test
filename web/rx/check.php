<?php

/**
 * Proxy-script for license renewal checking.
 *
 * This script is called by the OpalStack cron once a day at 2:30 AM.
 * https://linuxize.com/post/scheduling-cron-jobs-with-crontab/
 *
 * It forwards the request to WordPress AJAX where the plugins handle checking
 * all licenses for their expiration status.
 * https://codex.wordpress.org/AJAX_in_Plugins
 *
 */

require_once 'vizoo_customers.env';

// LimeLM licenses

$verbose = fopen('php://temp', 'w+');
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => ('https://customers.vizoo3d.com/wp-admin/admin-ajax.php'),
    CURLOPT_POST => true,
    CURLOPT_HEADER => false,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_VERBOSE => true,
    CURLOPT_STDERR => $verbose,
    CURLOPT_POSTFIELDS => http_build_query(
        [
            'action' => 'vizoo_limelm_check_licenses',
            'auth_key' => getenv('VIZOO_LIMELM_CHECK_TOKEN'),
        ]
    ),
]);

$result = curl_exec($curl);
curl_close($curl);
rewind($verbose);
print_r(stream_get_contents($verbose) . "\n");
print_r($result . "\n");

// LicenseSpring licenses
$verbose_licensespring = fopen('php://temp', 'w+');
$curl_licensespring = curl_init();

curl_setopt_array($curl_licensespring, [
    CURLOPT_URL => ('https://customers.vizoo3d.com/wp-admin/admin-ajax.php'),
    CURLOPT_POST => true,
    CURLOPT_HEADER => false,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_VERBOSE => true,
    CURLOPT_STDERR => $verbose_licensespring,
    CURLOPT_POSTFIELDS => http_build_query(
        [
            'action' => 'vizoo_licensespring_check_licenses',
            'auth_key' => getenv('VIZOO_LICENSESPRING_CHECK_TOKEN'),
        ]
    ),
]);

$result_licensespring = curl_exec($curl_licensespring);
curl_close($curl_licensespring);
rewind($verbose_licensespring);
print_r(stream_get_contents($verbose_licensespring) . "\n");
print_r($result_licensespring . "\n");
