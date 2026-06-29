<?php

if (!defined('ABSPATH')) {
    exit;
}

class Vizoo_LicenseSpring_Template_Handler
{
    public const TEMPLATE_PATH = __DIR__ . '/assets/templates/';

    public static function render_license($license)
    {
        include self::TEMPLATE_PATH . 'license.php';
    }

    public static function render_license_renewal($license)
    {
        include self::TEMPLATE_PATH . 'license-renewal.php';
    }

    public static function render_license_cancellation($license)
    {
        include self::TEMPLATE_PATH . 'license-cancellation.php';
    }

    public static function get_license_overview($licenses = [])
    {
        ob_start();
        include self::TEMPLATE_PATH . 'license-overview.php';
        return ob_get_clean();
    }

    public static function render_success()
    {
        include self::TEMPLATE_PATH . 'success.php';
    }

    public static function render_nonce_fail()
    {
        include self::TEMPLATE_PATH . 'nonce-fail.php';
    }

    public static function render_renewal_notification_ticket($license)
    {
        ob_start();
        include self::TEMPLATE_PATH . 'tickets/license-expires-soon.php';
        return ob_get_clean();
    }

    public static function render_renewal_notification_ticket_title($license)
    {
        ob_start();
        include self::TEMPLATE_PATH . 'tickets/license-expires-soon-title.php';
        return ob_get_clean();
    }

    public static function render_renewal_request_ticket($license, $license_period, $license_comment, $license_price)
    {
        ob_start();
        include self::TEMPLATE_PATH . 'tickets/license-renewal-request.php';
        return ob_get_clean();
    }

    public static function render_renewal_request_ticket_title($license)
    {
        ob_start();
        include self::TEMPLATE_PATH . 'tickets/license-renewal-request-title.php';
        return ob_get_clean();
    }

    public static function render_renewal_notification_email($license)
    {
        ob_start();
        include self::TEMPLATE_PATH . 'emails/license-expires-soon.php';
        return ob_get_clean();
    }

    public static function render_renewal_notification_email_title($license)
    {
        ob_start();
        include self::TEMPLATE_PATH . 'emails/license-expires-soon-title.php';
        return ob_get_clean();
    }

    public static function render_renewal_notification_reminder_email($license)
    {
        ob_start();
        include self::TEMPLATE_PATH . 'emails/license-expires-soon-reminder.php';
        return ob_get_clean();
    }

    public static function render_renewal_notification_reminder_email_title($license)
    {
        ob_start();
        include self::TEMPLATE_PATH . 'emails/license-expires-soon-reminder-title.php';
        return ob_get_clean();
    }

    public static function get_contact_img_path($managerName)
    {
        switch ($managerName) {
            case "Andrew Bougie":
                return esc_url(plugin_dir_url(__FILE__) . "../includes/assets/img/" .  "andrew_bougie.jpeg");
                break;
            case "Renate Eder":
                return esc_url(plugin_dir_url(__FILE__) . "../includes/assets/img/" .  "renate_eder.jpeg");
                break;
            case "Martin Semsch":
                return esc_url(plugin_dir_url(__FILE__) . "../includes/assets/img/" .  "martin_semsch.jpeg");
                break;
            default:
                return esc_url(plugin_dir_url(__FILE__) . "../includes/assets/img/" .  "user_regular.svg");
                break;
        }
    }
}
