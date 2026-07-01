<?php

if (!defined('ABSPATH')) {
    exit;
}

class Vizoo_LicenseSpring
{
    private function load_dependencies()
    {
        require_once plugin_dir_path(__FILE__) . 'class-vizoo-licensespring-api-handler.php';
        require_once plugin_dir_path(__FILE__) . 'class-vizoo-licensespring-ajax-handler.php';
        require_once plugin_dir_path(__FILE__) . 'class-vizoo-licensespring-template-handler.php';
        require_once plugin_dir_path(__FILE__) . 'class-vizoo-licensespring-license.php';
    }

    private function define_hooks()
    {
        wp_register_style('vizoo_licensespring_style', plugin_dir_url(__FILE__) . 'assets/css/vizoo-licensespring.css');
        wp_register_script('vizoo_licensespring_script', plugin_dir_url(__FILE__) . 'assets/js/vizoo-licensespring.js');

        add_shortcode('vizoo_licensespring_overview', [$this, 'do_shortcode']);

        add_filter('the_posts', [$this, 'enqueue_style']);

        Vizoo_LicenseSpring_AJAX_Handler::define_actions();
    }

    public function enqueue_style($posts)
    {
        if (empty($posts) || !is_page()) {
            return $posts;
        }
        $shortcode_found = false;
        foreach ($posts as $post) {
            if (stripos($post->post_content, '[vizoo_licensespring_overview]') !== false) {
                $shortcode_found = true;
                break;
            }
        }
        if ($shortcode_found) {
            wp_enqueue_style('vizoo_licensespring_style');
        }
        return $posts;
    }

    public function do_shortcode()
    {
        if (!is_user_logged_in()) {
            return;
        }

        $user_id = get_current_user_id();
        $user_role = get_user_meta(wp_get_current_user()->ID, 'vizoo_customerrole', true);
        $organization_id = get_user_meta($user_id, 'vizoo_organization', true);

        if (!$user_role == 'LicenseManager') {
            return;
        }

        if (empty($organization_id)) {
            echo 'Error: You are not registered in an organization.';
            return;
        }

        $licenses = [];
        wp_enqueue_script('vizoo_licensespring_script');

        return Vizoo_LicenseSpring_Template_Handler::get_license_overview($licenses);
    }

    public static function send_renewal_request($license, $license_period, $license_comment, $license_price, $sendme, $users = [], $address = [], $contact_mail = "")
    {
        $renewal_date = $license->get_renewal_date();
        $new_renewal_date = clone $renewal_date;
        $new_renewal_date->modify('+' . $license_period . ' years');
        $deal_id = -1;

        if ($sendme == 'invoice') {
            $deal_id = Vizoo_Pipedrive::create_invoice_request_deal($license, $license->get_renewal_price(), $license_period, $license_price, $license_comment, $users);
            $mail_title = 'Your Order - xTex Renewal - Vizoo GmbH';
            $mail_text = "Hi there,

Thank you for your request. We will process it as soon as possible.

Your expiration date will be updated in a few days. You can check your license status here: https://customers.vizoo3d.com/licenses/

Our accounting will send you the invoice via email near the expiry date.

Please do not hesitate to contact us if you have any questions.


Kind regards,

Thomas

Your friendly Vizoo Customer Service";
            wp_mail($contact_mail, $mail_title, $mail_text, ['From: service@customers.vizoo3d.com']);
        } elseif ($sendme == 'quotation') {
            $customer_number = $license->get_weclapp_customer_number();
            if ($customer_number === "0") {
                Vizoo_Pipedrive::create_quotation_request_deal($license, $license->get_renewal_price(), $license_period, $license_price, $license_comment, $users);
                $mail_title = 'Your quotation request - xTex Renewal - Vizoo GmbH';
                $mail_text = "Hi there,

Thank you for your request. We will process it as soon as possible.

A sales representative will send you the quotation via email in the next few days.

Please do not hesitate to contact us if you have any questions.


Kind regards,

Thomas

Your friendly Vizoo Customer Service";
                wp_mail($contact_mail, $mail_title, $mail_text, ['From: service@customers.vizoo3d.com']);
            } elseif ($customer_number === null) {
                Vizoo_Pipedrive::create_missinginfo_deal($license, $users);
            } else {
                Vizoo_Weclapp::create_quotation($license, $address, $contact_mail, $license_period);
            }
        }

        $txt = date('Y-m-d H:i:s') . ' > renewal requested for license #' . $license->get_id() . ' (pipedrive deal id: ' . $deal_id . ')';
        file_put_contents(ABSPATH . '/rx/renewalrequestlog.txt', $txt, FILE_APPEND | LOCK_EX);

        if ($deal_id === -1 || empty($_FILES['vizoo-licensespring-license-renewal-po-file']) || $_FILES['vizoo-licensespring-license-renewal-po-file']['error'] !== UPLOAD_ERR_OK) {
            return $deal_id;
        }

        Vizoo_Pipedrive::upload_attachment($deal_id, $_FILES['vizoo-licensespring-license-renewal-po-file']);
        return $deal_id;
    }

    public static function send_cancellation_request($license, $license_comment)
    {
        return Vizoo_Pipedrive::create_cancellation_request_deal($license, $license_comment);
    }

    public function run()
    {
        $this->load_dependencies();
        $this->define_hooks();
    }
}
