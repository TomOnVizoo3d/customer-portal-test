<?php

if (!defined('ABSPATH')) {
    exit;
}

class Vizoo_LicenseSpring_AJAX_Handler
{
    public static function define_actions()
    {
        add_action('wp_ajax_vizoo_licensespring_get_licenses', ['Vizoo_LicenseSpring_AJAX_Handler', 'get_licenses']);
        add_action('wp_ajax_vizoo_licensespring_get_renewal_form', ['Vizoo_LicenseSpring_AJAX_Handler', 'get_renewal_form']);
        add_action('wp_ajax_vizoo_licensespring_get_cancellation_form', ['Vizoo_LicenseSpring_AJAX_Handler', 'get_cancellation_form']);
        add_action('wp_ajax_vizoo_licensespring_renew_license', ['Vizoo_LicenseSpring_AJAX_Handler', 'renew_license']);
        add_action('wp_ajax_vizoo_licensespring_cancel_license', ['Vizoo_LicenseSpring_AJAX_Handler', 'cancel_license']);
        add_action('wp_ajax_vizoo_licensespring_check_licenses', ['Vizoo_LicenseSpring_AJAX_Handler', 'check_licenses']);
        add_action('wp_ajax_nopriv_vizoo_licensespring_check_licenses', ['Vizoo_LicenseSpring_AJAX_Handler', 'check_licenses']);
    }

    public static function get_licenses()
    {
        self::validateRequest('vizoo_licensespring_get_licenses');

        $organization_id = get_user_meta(get_current_user_id(), 'vizoo_organization', true);
        if (empty($organization_id)) {
            echo 'You are not registered in an organization.';
            exit;
        }

        $licenses = Vizoo_LicenseSpring_API_Handler::get_company_licenses($organization_id);

        if (empty($licenses)) {
            echo 'No licenses found.';
            exit;
        }

        foreach ($licenses as $license) {
            Vizoo_LicenseSpring_Template_Handler::render_license($license);
        }
        exit;
    }

    public static function get_renewal_form()
    {
        self::validateRequest('vizoo_licensespring_get_renewal_form');

        $license_id = $_POST['vizoo_licensespring_license_id'];
        if (empty($license_id)) {
            exit;
        }

        $organization_id = get_user_meta(get_current_user_id(), 'vizoo_organization', true);
        if (empty($organization_id)) {
            exit;
        }

        $license = Vizoo_LicenseSpring_API_Handler::get_license($license_id, $organization_id);

        Vizoo_LicenseSpring_Template_Handler::render_license_renewal($license);

        exit;
    }

    public static function get_cancellation_form()
    {
        self::validateRequest('vizoo_licensespring_get_cancellation_form');

        $license_id = $_POST['vizoo_licensespring_license_id'];
        if (empty($license_id)) {
            exit;
        }

        $organization_id = get_user_meta(get_current_user_id(), 'vizoo_organization', true);
        if (empty($organization_id)) {
            exit;
        }

        $license = Vizoo_LicenseSpring_API_Handler::get_license($license_id, $organization_id);

        Vizoo_LicenseSpring_Template_Handler::render_license_cancellation($license);

        exit;
    }

    public static function renew_license()
    {
        require_once 'countries.php';

        $license_id = $_POST['vizoo_licensespring_license_renewal_id'];
        self::validateRequest('vizoo_licensespring_confirm_renewal-' . $license_id);
        $license_period = $_POST['vizoo_licensespring_period'];
        $license_sendme = $_POST['vizoo_licensespring_type'];

        $contact_first_name = $_POST['vizoo_licensespring_contact_first_name'];
        $contact_last_name = $_POST['vizoo_licensespring_contact_last_name'];
        $contact_mail = $_POST['vizoo_licensespring_contact_mail'];
        $contact_company = $_POST['vizoo_licensespring_contact_company'];
        $billing_street = $_POST['vizoo_licensespring_billing_street'];
        $billing_city = $_POST['vizoo_licensespring_billing_city'];
        $billing_country = $_POST['vizoo_licensespring_billing_country'];
        if (empty($license_id) || empty($license_period) || empty($license_sendme)) {
            exit;
        }
        if (
            empty($contact_first_name) ||
            empty($contact_last_name) ||
            !filter_var($contact_mail, FILTER_VALIDATE_EMAIL) ||
            empty($contact_company) ||
            empty($billing_street) ||
            empty($billing_city) ||
            empty($billing_country)
        ) {
            exit;
        }

        if (count($_FILES) > 1) {
            exit;
        }
        if (count($_FILES) === 1 && $_FILES[0]["size"] > 5 * 1024 * 1024) {
            exit;
        }
        $license_comment = sprintf(
            "PO number (optional): %s<br />Billing information:<br /><br />%s %s<br />%s<br />%s<br />VAT Number: %s<br /><br />%s<br />%s %s<br />%s<br />%s",
            empty($_POST['vizoo_licensespring_po_number']) ? "not provided" : $_POST['vizoo_licensespring_po_number'],
            $contact_first_name,
            $contact_last_name,
            $contact_mail,
            $contact_company,
            empty($_POST['vizoo_licensespring_contact_vat']) ? 'not provided' : $_POST['vizoo_licensespring_contact_vat'],
            $billing_street,
            empty($_POST['vizoo_licensespring_billing_zip']) ? "" : $_POST['vizoo_licensespring_billing_zip'],
            $billing_city,
            empty($_POST['vizoo_licensespring_billing_state']) ? "" : $_POST['vizoo_licensespring_billing_state'],
            $all_countries[$billing_country]
        );

        $license_price = $_POST['vizoo_licensespring_price'];

        $billing_address = [
            "zipcode" => empty($_POST['vizoo_licensespring_billing_zip']) ? "" : $_POST['vizoo_licensespring_billing_zip'],
            "city" => $billing_city,
            "company" => $contact_company,
            "countryCode" => $billing_country,
            "street1" => $billing_street,
            "firstName" => $contact_first_name,
            "lastName" => $contact_last_name,
            "state" => empty($_POST['vizoo_licensespring_billing_state']) ? "" : $_POST['vizoo_licensespring_billing_state'],
        ];

        $organization_id = get_user_meta(get_current_user_id(), 'vizoo_organization', true);
        $license = Vizoo_LicenseSpring_API_Handler::get_license($license_id, $organization_id);

        $users = vizoo_pipedrive_get_users($license->get_company_id(), true);
        $deal_id = Vizoo_LicenseSpring::send_renewal_request($license, $license_period, $license_comment, $license_price, $license_sendme, $users, $billing_address, $contact_mail);

        if ($deal_id === -1) {
            echo '200';
            exit;
        }
        Vizoo_LicenseSpring_API_Handler::set_deal_id($license, $deal_id);
        echo '200';
        exit;
    }

    public static function cancel_license()
    {
        $license_id = $_POST['vizoo_licensespring_license_cancel_id'];
        $license_comment = !empty($_POST['vizoo_licensespring_license_cancel_comment']) ? $_POST['vizoo_licensespring_license_cancel_comment'] : '';
        self::validateRequest('vizoo_licensespring_confirm_cancellation-' . $license_id);

        if (empty($license_id)) {
            exit;
        }

        $organization_id = get_user_meta(get_current_user_id(), 'vizoo_organization', true);
        $license = Vizoo_LicenseSpring_API_Handler::get_license($license_id, $organization_id);

        $deal_id = Vizoo_LicenseSpring::send_cancellation_request($license, $license_comment);

        Vizoo_LicenseSpring_API_Handler::set_deal_id($license, $deal_id);
        Vizoo_LicenseSpring_API_Handler::set_cancellation_sent($license);

        $mails = self::get_contact_emails($license);

        $mail_title = 'Confirmation of xTex Subscription Plan Cancellation';
        $mail_text = sprintf("Hi there,

We are sorry to see you go. This message confirms the receipt of the cancellation request of your xTex license. License details:

ID: %s
Licensee: %s
Expires on: %s

Please note that the xTex software cannot be used after the expiry date anymore.

You can re-subscribe to our xTex license any time. Please contact us on info@vizoo3.com or your reselling partner.

If you have any questions or need support, we are always happy to help.


Kind regards,

Thomas
Vizoo Customer Service", $license->get_id(), $license->get_licensee(), $license->get_formatted_renewal_date());

        wp_mail(
            $mails,
            $mail_title,
            $mail_text,
            ['From: service@customers.vizoo3d.com']
        );

        echo '200';
        exit;
    }

    public static function check_licenses()
    {
        if (!isset($_POST['auth_key']) || $_POST['auth_key'] !== getenv('VIZOO_LICENSESPRING_CHECK_TOKEN')) {
            throw new RuntimeException('Tried to trigger license check with invalid authentication.');
        }

        $licenses = Vizoo_LicenseSpring_API_Handler::get_licenses();

        foreach ($licenses as $license) {
            if ($license->get_tier() !== 'CLIENT') {
                continue;
            }

            if ($license->get_renewal_state() !== Vizoo_LicenseSpring_License::NEEDS_RENEWAL) {
                continue;
            }

            if (!$license->renewal_notification_sent()) {

                // Validate the license.
                if (empty($license->get_company_id())) {
                    Vizoo_Pipedrive::create_missinginfo_deal($license, []);
                    Vizoo_LicenseSpring_API_Handler::set_expiration_notification_sent($license);
                    continue;
                }

                $mails = self::get_contact_emails($license);

                if (empty($license->get_renewal_price()) || empty($license->get_renewal_currency()) || empty($mails)) {
                    Vizoo_Pipedrive::create_missinginfo_deal($license, []);
                    Vizoo_LicenseSpring_API_Handler::set_expiration_notification_sent($license);
                    continue;
                }

                $subject = Vizoo_LicenseSpring_Template_Handler::render_renewal_notification_email_title($license);
                $message = Vizoo_LicenseSpring_Template_Handler::render_renewal_notification_email($license);

                wp_mail($mails, $subject, $message);

                // Set the 'ExpirationNotificationSent'-field for LicenseSpring.
                Vizoo_LicenseSpring_API_Handler::set_expiration_notification_sent($license);
                continue;
            }

            if ($license->should_send_reminder()) {
                if (empty($license->get_company_id())) {
                    Vizoo_Pipedrive::create_missinginfo_deal($license, []);
                    Vizoo_LicenseSpring_API_Handler::set_expiration_reminder_sent($license);
                    continue;
                }

                $users = vizoo_pipedrive_get_users($license->get_company_id(), true);
                $mails = self::get_contact_emails($license);

                if (empty($license->get_renewal_price()) || empty($license->get_renewal_currency()) || empty($mails)) {
                    Vizoo_Pipedrive::create_missinginfo_deal($license, $users);
                    Vizoo_LicenseSpring_API_Handler::set_expiration_reminder_sent($license);
                    continue;
                }

                $subject = Vizoo_LicenseSpring_Template_Handler::render_renewal_notification_reminder_email_title($license);
                $message = Vizoo_LicenseSpring_Template_Handler::render_renewal_notification_reminder_email($license);

                wp_mail($mails, $subject, $message);
                Vizoo_LicenseSpring_API_Handler::set_expiration_reminder_sent($license);
                continue;
            }

            if ($license->should_create_no_response_deal()) {
                $users = vizoo_pipedrive_get_users($license->get_company_id(), true);
                Vizoo_Pipedrive::create_noresponse_deal($license, $users);
                Vizoo_LicenseSpring_API_Handler::set_expiration_ignored($license);
            }
        }

        exit;
    }

    private static function get_contact_emails($license)
    {
        $users = vizoo_pipedrive_get_users($license->get_company_id(), true);
        $mails = array_map(fn ($element): string => $element->user_email, $users);
        $mails[] = $license->get_contact_email();
        return array_values(array_unique($mails));
    }

    private static function validateRequest($action)
    {
        if (!is_user_logged_in()) {
            exit;
        }
        $user = wp_get_current_user();
        $userrole = get_user_meta($user->ID, 'vizoo_customerrole', true);
        if ($userrole != 'LicenseManager' && !current_user_can('edit_pages')) {
            exit;
        }
        check_ajax_referer($action, 'nonce');
    }
}
