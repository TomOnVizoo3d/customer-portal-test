<?php

require_once 'vizoo_customers.env';

require_once 'PHPMailer/Exception.php';
require_once 'PHPMailer/SMTP.php';
require_once 'PHPMailer/PHPMailer.php';

function main()
{
    $raw_licenses = get_all_licenses();
    $licenses = array_map('convert_license', $raw_licenses);
    $corrupt_licenses = array_filter($licenses, function ($license) {
        if ($license['license_type'] !== 'CLIENT') {
            return false;
        }
        return !is_numeric($license['weclapp_customer_number']) ||
            !is_numeric($license['renewal_price']) ||
            !is_numeric($license['company_id']) ||
            empty($license['renewal_currency']) ||
            empty($license['licensee']);
    });
    $licenses_per_manager = array_reduce($corrupt_licenses, 'group_licenses_by_manager', []);
    send_emails($licenses_per_manager);
}

function get_all_licenses()
{
    $results = [];
    $moreResults = true;
    $page = 0;
    while ($moreResults) {
        $pagedLicenses = get_licensespring_response('GET', 'licenses', [
            'limit' => '100',
            'offset' => (string)($page * 100),
            'order_by' => '-created_at',
        ], '');
        $results = array_merge($results, $pagedLicenses['results']);
        $moreResults = $pagedLicenses['next'] !== null;
        $page += 1;
    }
    return $results;
}

function get_licensespring_response($method, $target, $query_parameters, $body)
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

function group_licenses_by_manager($carry, $license)
{
    $manager_email = $license['manager_email'];
    if (!isset($carry[$manager_email])) {
        $carry[$manager_email] = [$license];
    } else {
        array_push($carry[$manager_email], $license);
    }
    return $carry;
}

function convert_license($license)
{
    $sales_manager_mappings = [
        'Renate' => 'renate.eder@vizoo3d.com',
        'Martin' => 'martin.semsch@vizoo3d.com',
        'Andrew' => 'andrew.bougie@vizoo3d.com',
    ];
    $default_manager = 'Renate';

    $renewal_date = $license['validity_period'];

    $metadata = $license['metadata'];
    $renewal_price = @$metadata['web']['renewalAmount'];
    $renewal_currency = @$metadata['web']['renewalCurrency'];
    $licensee = @$metadata['web']['company'];
    $company_id = @$metadata['web']['pipedriveOrganizationId'];
    $weclapp_customer_number = @$metadata['web']['weclappCustomerNumber'];
    $tier = @$metadata['web']['tier'];

    $manager = @$metadata['web']['manager'];
    if (empty($manager)) {
        $manager = $default_manager;
    }
    $manager_email = $sales_manager_mappings[$manager];

    return [
        'id' => $license['id'],
        'email' => $license['customer']['email'],
        'renewal_price' => $renewal_price,
        'renewal_currency' => $renewal_currency,
        'licensee' => $licensee,
        'company_id' => $company_id,
        'license_type' => $tier,
        'manager_email' => $manager_email,
        'renewal_date' => $renewal_date,
        'weclapp_customer_number' => $weclapp_customer_number,
    ];
}

function get_email_body($licenses): string
{
    $body = "Some subscriptions managed by you require attention: They are missing information needed for license renewal.\n\n";
    $body = array_reduce($licenses, function ($carry, $license) {
        $carry .= sprintf(
            "- %d (licensee: %s, pipedrive id: %s, renewal date: %s, weclapp customer number: %s, price: %d, currency: %s): %s\n",
            $license['id'],
            empty($license['licensee']) ? '- missing -' : $license['licensee'],
            !is_numeric($license['company_id']) ? '- missing/invalid -' : $license['company_id'],
            $license['renewal_date'],
            !is_numeric($license['weclapp_customer_number']) ? '- missing/invalid -' : $license['weclapp_customer_number'],
            !is_numeric($license['renewal_price']) ? '- missing/invalid -' : $license['renewal_price'],
            empty($license['renewal_currency']) ? '- missing -' : $license['renewal_currency'],
            $license['email']
        );
        return $carry;
    }, $body);
    $body .= "\n\n(this is an automatically created report)";
    return $body;
}

function send_emails($licenses_per_manager)
{
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = getenv('VIZOO_MAIL_SMTP');
    $mail->SMTPAuth = getenv('VIZOO_MAIL_AUTH');
    $mail->SMTPKeepAlive = true;
    $mail->Port = getenv('VIZOO_MAIL_PORT');
    $mail->Username = getenv('VIZOO_MAIL_USERNAME');
    $mail->Password = getenv('VIZOO_MAIL_PASSWORD');
    $mail->setFrom(getenv('VIZOO_MAIL_FROM'), 'Vizoo LicenseGuard');

    $mail->Subject = 'Some xTex subscriptions require attention';

    foreach ($licenses_per_manager as $email => $licenses) {
        $mail->Body = get_email_body($licenses);

        try {
            $mail->addAddress($email);
        } catch (Exception $e) {
            printf('Invalid address skipped: %s', $email);
            continue;
        }

        try {
            $mail->send();
            printf('Message sent to: %s', $email);
        } catch (Exception $e) {
            printf('Mailer Error (%s) %s', $email, $mail->ErrorInfo);
            $mail->getSMTPInstance()->reset();
        }

        $mail->clearAddresses();
    }

    $mail->smtpClose();
}

function call_api($url, $headers, $body, $method = null)
{
    $channel = curl_init();
    curl_setopt($channel, CURLOPT_URL, $url);
    if (!empty($headers)) {
        curl_setopt($channel, CURLOPT_HTTPHEADER, $headers);
    }
    curl_setopt($channel, CURLOPT_RETURNTRANSFER, 1);
    if ($method === null) {
        $method = $_SERVER['REQUEST_METHOD'];
    }
    curl_setopt($channel, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($channel, CURLOPT_POSTFIELDS, $body);

    $response = curl_exec($channel);

    $httpcode = curl_getinfo($channel, CURLINFO_RESPONSE_CODE);
    curl_close($channel);

    return [(int) $httpcode, $response];
}

main();
