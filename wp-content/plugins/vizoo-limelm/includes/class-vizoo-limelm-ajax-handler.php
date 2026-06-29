<?php

/**
 * The handler class for AJAX calls to this plugin.
 *
 * The class handling the AJAX requests of the frontend site of the page. If the user has
 * JavaScript enabled he can request details of the licenses he owns, deactivate
 * activations and send renewal requests via AJAX which makes the usage of this plugin
 * much faster.
 *
 * @since 1.0.0
 */
class Vizoo_LimeLM_AJAX_Handler
{
    /**
     * Defines all functions available through an AJAX request.
     *
     * Add action hooks, so that actions requested are handled by the correct function in
     * this class.
     *
     * @since    1.0.0
     * @access   public
     */
    public static function define_actions()
    {
        add_action('wp_ajax_vizoo_limelm_get_licenses', ['Vizoo_LimeLM_AJAX_Handler', 'get_licenses']);
        add_action('wp_ajax_vizoo_limelm_get_renewal_form', ['Vizoo_LimeLM_AJAX_Handler', 'get_renewal_form']);
        add_action('wp_ajax_vizoo_limelm_get_cancellation_form', ['Vizoo_LimeLM_AJAX_Handler', 'get_cancellation_form']);
        add_action('wp_ajax_vizoo_limelm_migrate_license', ['Vizoo_LimeLM_AJAX_Handler', 'migrate_license']);
        add_action('wp_ajax_vizoo_limelm_cancel_license', ['Vizoo_LimeLM_AJAX_Handler', 'cancel_license']);
        add_action('wp_ajax_vizoo_limelm_deactivate_activation', ['Vizoo_LimeLM_AJAX_Handler', 'deactivate_activation']);
        add_action('wp_ajax_vizoo_limelm_check_licenses', ['Vizoo_LimeLM_AJAX_Handler', 'check_licenses']);
        add_action('wp_ajax_nopriv_vizoo_limelm_check_licenses', ['Vizoo_LimeLM_AJAX_Handler', 'check_licenses']);
    }

    /**
     * Validates the AJAX request.
     *
     * Checks whether the AJAX referer is correct and the user is capable of executing the
     * requested action. Exits if check is negative.
     *
     * @since    1.0.0
     * @access   private
     * @param    string               $action             The action that shall be performed.
     */
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

    /**
     * Gets all licenses of the user's organization.
     *
     * Fetches all licenses of the user's organization from the API handler and returns the
     * filled out template.
     *
     * @since    1.0.0
     * @access   private
     * @return   string               $action             The action that shall be performed.
     */
    public static function get_licenses()
    {
        self::validateRequest('vizoo_limelm_get_licenses');

        $organization_id = get_user_meta(get_current_user_id(), 'vizoo_organization', true);
        if (empty($organization_id)) {
            echo 'You are not registered in an organization.';
            exit;
        }

        $licenses = Vizoo_LimeLM_API_Handler::get_licenses($organization_id);

        if (empty($licenses)) {
            echo 'No licenses found.';
            exit;
        }

        foreach ($licenses as $license) {
            Vizoo_LimeLM_Template_Handler::render_license($license);
        }
        exit;
    }

    /**
     * Gets the renewal form for a specific license.
     *
     * Fetches all licenses of the user's organization from the API handler and returns the
     * filled out template.
     *
     * @since    1.0.0
     * @access   private
     * @return   string               $action             The action that shall be performed.
     */
    public static function get_renewal_form()
    {
        self::validateRequest('vizoo_limelm_get_renewal_form');

        $license_id = $_POST['vizoo_limelm_license_id'];
        if (empty($license_id)) {
            exit;
        }

        $organization_id = get_user_meta(get_current_user_id(), 'vizoo_organization', true);
        if (empty($organization_id)) {
            exit;
        }

        $license = Vizoo_LimeLM_API_Handler::get_license($license_id, $organization_id);

        Vizoo_LimeLM_Template_Handler::render_license_migration($license);

        exit;
    }

    public static function get_cancellation_form()
    {
        self::validateRequest('vizoo_limelm_get_cancellation_form');

        $license_id = $_POST['vizoo_limelm_license_id'];
        if (empty($license_id)) {
            exit;
        }

        $organization_id = get_user_meta(get_current_user_id(), 'vizoo_organization', true);
        if (empty($organization_id)) {
            exit;
        }

        $license = Vizoo_LimeLM_API_Handler::get_license($license_id, $organization_id);

        Vizoo_LimeLM_Template_Handler::render_license_cancellation($license);

        exit;
    }

    public static function migrate_license()
    {
        $license_id = $_POST['vizoo_limelm_license_renewal_id'];
        self::validateRequest('vizoo_limelm_confirm_renewal-' . $license_id);
        $special_request = $_POST['vizoo_limelm_special_request'];
        if (empty($license_id)) {
            exit;
        }

        $license_comment = !empty($special_request) ? sprintf(
            "Special request (optional):<br /><br />%s",
            $special_request,
        ) : "";

        $organization_id = get_user_meta(get_current_user_id(), 'vizoo_organization', true);
        $license = Vizoo_LimeLM_API_Handler::get_license($license_id, $organization_id);

        $deal_id = Vizoo_LimeLM::send_migration_request($license, $license_comment);

        if ($deal_id !== -1) {
            $response = Vizoo_LimeLM_API_Handler::set_license_renewal_deal($license_id, $deal_id);
            if ($response !== true) {
                echo $response;
                exit;
            }
        }
        echo '200';
        exit;
    }

    public static function cancel_license()
    {
        $license_id = $_POST['vizoo_limelm_license_cancel_id'];
        $license_comment = !empty($_POST['vizoo_limelm_license_cancel_comment']) ? $_POST['vizoo_limelm_license_cancel_comment'] : '';
        self::validateRequest('vizoo_limelm_confirm_cancellation-' . $license_id);

        if (empty($license_id)) {
            exit;
        }

        $organization_id = get_user_meta(get_current_user_id(), 'vizoo_organization', true);
        $license = Vizoo_LimeLM_API_Handler::get_license($license_id, $organization_id);

        $deal_id = Vizoo_LimeLM::send_cancellation_request($license, $license_comment);

        $response = Vizoo_LimeLM_API_Handler::set_license_renewal_deal($license_id, $deal_id);
        Vizoo_LimeLM_API_Handler::set_cancellation_request_sent($license_id, $organization_id, $license->get_formatted_renewal_date('c'));

        $users = vizoo_pipedrive_get_users($license->get_company_id(), true);
        $mails = array_map(fn ($element): string => $element->user_email, $users);

        if ($license->get_license_type_code() === 'CSWS') {
            $mail_title = 'Confirmation of xTex Maintenance Plan Cancellation';
            $mail_text = sprintf("Hi there,

We are sorry to see you go. This message confirms the receipt of the cancellation request of your xTex maintenance plan. The maintenance plan of the license below will been cancelled:

ID: %s
Key: %s
Licensee: %s
Expires on: %s

Please note that without the software maintenance, xTex software cannot be updated to a new version and no more customer portal and support ticket system access will be granted.

Perhaps our maintenance plan will be of interest to you in the future, please read term 4.3 Renewal of Terminated Contract of the Software Maintenance and Support Contract attached and contact us for a customized offer.

If you have any questions or need support, we are always happy to help.


Kind regards,
Thomas
Vizoo Customer Service", $license->get_id(), $license->get_key(), $license->get_licensee(), $license->get_formatted_renewal_date());
        } else {
            $mail_title = 'Confirmation of xTex Subscription Plan Cancellation';
            $mail_text = sprintf("Hi there,

We are sorry to see you go. This message confirms the receipt of the cancellation request of your xTex subscription plan. The subscription plan of the license below will been cancelled:

ID: %s
Key: %s
Licensee: %s
Expires on: %s

Please note that your xTex license can't be used after the expiry date anymore.

You can re-subscribe to our xTex license any time. Please contact us on info@vizoo3.com or your reselling partner.

If you have any questions or need support, we are always happy to help.


Kind regards,

Thomas
Vizoo Customer Service", $license->get_id(), $license->get_key(), $license->get_licensee(), $license->get_formatted_renewal_date());
        }

        wp_mail(
            $mails,
            $mail_title,
            $mail_text,
            ['From: service@customers.vizoo3d.com'],
            [trailingslashit(wp_upload_dir('2022/08')['path']) . '2022-08-09_Software_Maintenance_and_Support_Contract_EN.pdf']
        );

        if ($response === true) {
            echo '200';
        } else {
            echo $response;
        }
        exit;
    }

    public static function deactivate_activation()
    {
        $activation_id = $_POST['vizoo_limelm_activation_deactivation_id'];
        self::validateRequest('vizoo_limelm_deactivate_activation-' . $activation_id);
        $license_id = $_POST['vizoo_limelm_activation_license_id'];

        $organization_id = get_user_meta(get_current_user_id(), 'vizoo_organization', true);
        if (empty($organization_id)) {
            exit;
        }

        $response = Vizoo_LimeLM_API_Handler::deactivate_activation($activation_id, $license_id, $organization_id);
        if ($response === true) {
            echo 'Deactivated';
        } else {
            echo $response;
        }
        exit;
    }

    /**
     * Checks all LimeLM licenses for their renewal status.
     *
     * If a license expires soon, the license admins of the organization managing the
     * license will receive an email notification. The email contains a link to the
     * customer portal if it's possible to send a renewal request online or a notice to
     * contact us at support@vizoo3d.com if not (e.g. if no renewal price is set).
     *
     * There will be no notifications for licenses that have no payment due date.
     *
     * After the notification email was sent the 'ExpirationNotificationSent'-field will be
     * set on LimeLM.
     *
     * @since     1.0.0
     * @access    public
     */
    public static function check_licenses()
    {
        if (!isset($_POST['auth_key']) || $_POST['auth_key'] !== getenv('VIZOO_LIMELM_CHECK_TOKEN')) {
            throw new RuntimeException('Tried to trigger license check with invalid authentication.');
        }
        $txt = date('Y-m-d H:i:s') . ' checking licenses' . "\n";

        $licenses = Vizoo_LimeLM_API_Handler::get_all_licenses();
        $txt .= count($licenses) . ' licenses found (with a company attached to it)' . "\n";

        $count = 0;
        $count_reminder = 0;
        $count_noresponse = 0;
        $create_tickets = '';
        $send_mails = '';

        foreach ($licenses as $license) {
            if ($license->is_migrated()) {
                continue;
            }
            if (in_array($license->get_license_type_code(), ['INT', 'CPCM', 'CRNT', 'TMP'])) {
                continue;
            }

            if (!$license->has_payment_due()) {
                continue;
            }

            if ($license->get_renewal_state() !== Vizoo_LimeLM_License::NEEDS_RENEWAL) {
                continue;
            }

            if (!$license->renewal_notification_sent()) {
                $count++;

                // Validate the license.
                if (empty($license->get_company_id())) {
                    Vizoo_Pipedrive::create_missinginfo_deal($license, []);
                    Vizoo_LimeLM_API_Handler::set_expiration_notification_sent($license->get_id(), $license->get_company_id(), $license->get_formatted_renewal_date('c'));
                    $create_tickets .= 'create missinginfo deal for license ' . $license->get_id() . ' (no company id)' . "\n";
                    continue;
                }

                $users = vizoo_pipedrive_get_users($license->get_company_id(), true);
                $mails = array_map(fn ($element): string => $element->user_email, $users);

                if (empty($license->get_renewal_price()) || empty($license->get_renewal_currency()) || empty($mails)) {
                    Vizoo_Pipedrive::create_missinginfo_deal($license, []);
                    Vizoo_LimeLM_API_Handler::set_expiration_notification_sent($license->get_id(), $license->get_company_id(), $license->get_formatted_renewal_date('c'));
                    $create_tickets .= 'create missinginfo deal for license ' . $license->get_id() . ' (no price/currency/mail)' . "\n";
                    continue;
                }

                $subject = Vizoo_LimeLM_Template_Handler::render_renewal_notification_email_title($license);
                $message = Vizoo_LimeLM_Template_Handler::render_renewal_notification_email($license);

                wp_mail($mails, $subject, $message);
                $send_mails .= '[#' . $license->get_id() . '] to [' . implode(', ', $mails) . ']: ' . $license->get_days_left() . ' days left, ' . ($license->get_renewal_state() != Vizoo_LimeLM_License::NOT_RENEWABLE ? 'at customer portal' : 'per email (no price/currency set in limelm)') . "\n";

                // Set the 'ExpirationNotificationSent'-field for LimeLM.
                Vizoo_LimeLM_API_Handler::set_expiration_notification_sent($license->get_id(), $license->get_company_id(), $license->get_formatted_renewal_date('c'));
                continue;
            }

            if ($license->should_send_reminder()) {
                $count_reminder++;
                if (empty($license->get_company_id())) {
                    Vizoo_Pipedrive::create_missinginfo_deal($license, []);
                    Vizoo_LimeLM_API_Handler::set_expiration_reminder_sent($license->get_id(), $license->get_company_id(), $license->get_formatted_renewal_date('c'));
                    continue;
                }

                $users = vizoo_pipedrive_get_users($license->get_company_id(), true);
                $mails = array_map(fn ($element): string => $element->user_email, $users);

                if (empty($license->get_renewal_price()) || empty($license->get_renewal_currency()) || empty($mails)) {
                    Vizoo_Pipedrive::create_missinginfo_deal($license, $users);
                    Vizoo_LimeLM_API_Handler::set_expiration_reminder_sent($license->get_id(), $license->get_company_id(), $license->get_formatted_renewal_date('c'));
                    continue;
                }

                $subject = Vizoo_LimeLM_Template_Handler::render_renewal_notification_reminder_email_title($license);
                $message = Vizoo_LimeLM_Template_Handler::render_renewal_notification_reminder_email($license);

                wp_mail($mails, $subject, $message);
                $send_mails .= '[#' . $license->get_id() . '] to [' . implode(', ', $mails) . ']: ' . $license->get_days_left() . ' days left, ' . ($license->get_renewal_state() != Vizoo_LimeLM_License::NOT_RENEWABLE ? 'at customer portal' : 'per email (no price/currency set in limelm)') . '[REMINDER]' . "\n";
                Vizoo_LimeLM_API_Handler::set_expiration_reminder_sent($license->get_id(), $license->get_company_id(), $license->get_formatted_renewal_date('c'));
                continue;
            }

            if ($license->should_create_no_response_deal()) {
                $users = vizoo_pipedrive_get_users($license->get_company_id(), true);
                $count_noresponse++;
                $create_tickets .= 'create noresponse deal for license ' . $license->get_id() . "\n";
                Vizoo_Pipedrive::create_noresponse_deal($license, $users);
                Vizoo_LimeLM_API_Handler::set_expiration_ignored($license->get_id(), $license->get_company_id(), $license->get_formatted_renewal_date('c'));
            }
        }

        $txt .= $count . ' licenses that expire soon, no renewal notification sent (yet) and have a due date..' . "\n\n";
        $txt .= $count_reminder . ' reminder' . "\n\n";
        $txt .= $count_noresponse . ' noresponse' . "\n\n";
        $txt .= 'send mails to: ' . "\n";
        $txt .= $send_mails . "\n";
        $txt .= 'create tickets of: ' . "\n";
        $txt .= $create_tickets . "\n\n";

        print_r($txt);
        file_put_contents(ABSPATH . '/rx/checklog.txt', $txt, FILE_APPEND | LOCK_EX);
        exit;
    }
}
