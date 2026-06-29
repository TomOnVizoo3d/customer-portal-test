<?php

/**
 * Failsafe for license renewal checking.
 *
 * This script is called by the Opalstack cron once a week (Monday) at 6:30 AM.
 * It checks all the licenses that are of type CPWM (active maintenance), but
 * are either expired already (PaymentDue in the past) or are missing a
 * PaymentDue date.
 * The respective license managers get a grouped list of all of their licenses
 * that require attention.
 *
 */

require_once 'vizoo_customers.env';

require_once 'PHPMailer/Exception.php';
require_once 'PHPMailer/SMTP.php';
require_once 'PHPMailer/PHPMailer.php';

function main()
{
    $raw_licenses = get_all_licenses();
    $licenses = array_map('convert_licenses', $raw_licenses);
    $corrupt_licenses = array_filter($licenses, function ($license) {
        if ($license['migrated']) {
            return false;
        }
        if ($license['payment_due'] === null || $license['payment_due'] < new DateTimeImmutable('today')) {
            return true;
        }
        if ($license['weclapp_customer_number'] === null) {
            return true;
        }
        return false;
    });
    $licenses_per_manager = array_reduce($corrupt_licenses, 'group_licenses_by_manager', []);
    send_emails($licenses_per_manager);
}

function get_all_licenses()
{
    $channel = curl_init(getenv('VIZOO_LIMELM_API_ADDRESS'));
    $post_data = [
        'method' => 'limelm.pkey.advancedSearch',
        'num' => '999',
        'get_features' => 'true',
        'feature_name' => ['LicenseType', 'LicenseType'],
        'feature_value' => ['CPWM', 'CSWS'],
        'feature_match' => ['exact'],
        'api_key' => getenv('VIZOO_LIMELM_API_KEY'),
        'format' => 'json',
        'nojsoncallback' => '1',
    ];
    curl_setopt($channel, CURLOPT_POST, true);
    curl_setopt($channel, CURLOPT_POSTFIELDS, http_build_query($post_data));
    curl_setopt($channel, CURLOPT_HEADER, 0);
    curl_setopt($channel, CURLOPT_ENCODING, '');
    curl_setopt($channel, CURLOPT_RETURNTRANSFER, true);
    $response = json_decode(curl_exec($channel), true);
    curl_close($channel);
    return $response['pkeys']['pkey'];
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

function convert_licenses($license)
{
    $sales_manager_mappings = [
        'Renate' => 'renate.eder@vizoo3d.com',
        'Martin' => 'martin.semsch@vizoo3d.com',
        'Andrew' => 'andrew.bougie@vizoo3d.com',
    ];
    $default_license_manager = 'Renate';

    $manager = $default_license_manager;
    $payment_due_date = null;
    $weclapp_customer_number = null;
    $license_type = null;
    $migrated = false;
    foreach ($license["features"]["feature"] as $feature) {
        switch ($feature['name']) {
            case 'PaymentDue':
                try {
                    $payment_due_date = new DateTimeImmutable($feature['value']);
                } catch (Exception $e) {
                }
                break;
            case 'LicenseType':
                $license_type = $feature['value'];
                break;
            case 'AccountManager':
                $manager = in_array($feature['value'], array_keys($sales_manager_mappings)) ? $feature['value'] : $default_license_manager;
                break;
            case 'WeclappCustomerNumber':
                if (is_numeric($feature['value'])) {
                    $weclapp_customer_number = $feature['value'];
                }
                break;
            case 'Migrated':
                if ($feature['value'] !== '' && $feature['value'] !== 'DNM') {
                    $migrated = true;
                }
                break;
            default:
                break;
        }
    }
    $manager_email = $sales_manager_mappings[$manager];

    return [
        'key' => $license['key'],
        'id' => $license['id'],
        'email' => $license['email'] ?? '',
        'license_type' => $license_type,
        'manager_email' => $manager_email,
        'payment_due' => $payment_due_date,
        'weclapp_customer_number' => $weclapp_customer_number,
        'migrated' => $migrated,
    ];
}

function get_email_body($licenses): string
{
    $body = "Some licenses managed by you require attention: They are either...\n...missing a PaymentDueDate,\n...already expired but still registered as CPWM (active maintenance),\n...missing a valid weclapp customer number.\n\n";
    $body = array_reduce($licenses, function ($carry, $license) {
        $carry .= sprintf(
            "- %d (%s, payment due: %s, weclapp customer number: %s): %s\n",
            $license['id'],
            $license['license_type'],
            $license['payment_due'] === null ? '- missing -' : date_format($license['payment_due'], 'Y-m-d'),
            $license['weclapp_customer_number'] === null ? '- missing -' : $license['weclapp_customer_number'],
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

    $mail->Subject = 'Some xTex licenses require attention';

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

main();
