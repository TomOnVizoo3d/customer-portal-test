<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * The template handler for this plugin.
 *
 * The class providing functions to render things to the output buffer. These functions
 * are including the template files under the template path (defined in this class).
 *
 * This way we can seperate program logic (pure PHP) and displayment (some PHP + HTML).
 *
 * Every function in this class is static, so they can be accessed without having to
 * instantiate it.
 *
 * @since      1.0.0
 * @package    Vizoo_LimeLM
 * @subpackage Vizoo_LimeLM/includes
 */
class Vizoo_LimeLM_Template_Handler
{
    /**
     * The base path of the templates.
     *
     * @since    1.0.0
     * @access   public
     * @var      string    TEMPLATE_PATH    The path where the templates are located.
     */
    public const TEMPLATE_PATH = __DIR__ . '/assets/templates/';

    /**
     * Renders a license.
     *
     * This is used in the license overview page to render a single license into the page.
     *
     * @since    1.0.0
     * @access   public
     * @param    LimeLM_License    $license    The license object.
     */
    public static function render_license($license)
    {
        include self::TEMPLATE_PATH . 'license.php';
    }

    /**
     * Renders the license renewal popup for a specified license.
     *
     * @since    1.0.0
     * @access   public
     * @param    LimeLM_License    $license    The license object.
     */
    public static function render_license_migration($license)
    {
        include self::TEMPLATE_PATH . 'license-migration.php';
    }

    public static function render_license_cancellation($license)
    {
        include self::TEMPLATE_PATH . 'license-cancellation.php';
    }

    /**
     * Renders license overview page.
     *
     * @since    1.0.0
     * @access   public
     * @param    LimeLM_License    $license    The license object.
     */
    public static function get_license_overview($licenses = [])
    {
        ob_start();
        include self::TEMPLATE_PATH . 'license-overview.php';
        return ob_get_clean();
    }

    /**
     * Renders the success message, when a user's renewal request was successfully sent.
     *
     * @since    1.0.0
     * @access   public
     * @param    LimeLM_License    $license    The license object.
     */
    public static function render_success()
    {
        include self::TEMPLATE_PATH . 'success.php';
    }

    /**
     * Renders the error site, when the nonce could not be validated.
     *
     * This happens if someone tries to spoof around. Nonces protect from access to
     * critial functions (such as sending a renewal request or deactivating an activation)
     * from just a link.
     * For further information see: https://codex.wordpress.org/WordPress_Nonces.
     *
     * @since    1.0.0
     * @access   public
     * @param    LimeLM_License    $license    The license object.
     */
    public static function render_nonce_fail()
    {
        include self::TEMPLATE_PATH . 'nonce-fail.php';
    }

    /**
     * Renders the content of the ticket created when a license expires.
     *
     * If a license is about to expire, there is a ticket created on the JIRA helpdesk.
     * This template defines the contents of that ticket.
     *
     * @since    1.0.0
     * @access   public
     * @param    LimeLM_License    $license    The license that is about to expire.
     */
    public static function render_renewal_notification_ticket($license)
    {
        ob_start();
        include self::TEMPLATE_PATH . 'tickets/license-expires-soon.php';
        return ob_get_clean();
    }

    /**
     * Renders the title of the ticket created when a license expires.
     *
     * If a license is about to expire, there is a ticket created on the JIRA helpdesk.
     * This template defines the title of that ticket.
     *
     * @since    1.0.0
     * @access   public
     * @param    LimeLM_License    $license    The license that is about to expire.
     */
    public static function render_renewal_notification_ticket_title($license)
    {
        ob_start();
        include self::TEMPLATE_PATH . 'tickets/license-expires-soon-title.php';
        return ob_get_clean();
    }

    /**
     * Renders the content of the ticket created when a user sent a renewal request.
     *
     * If a license is about to expire, the user may send a renewal request. In this case
     * there will be created a ticket on the JIRA helpdesk.
     * This template defines the contents of that ticket.
     *
     * @since    1.0.0
     * @access   public
     * @param    LimeLM_License    $license                The license that is about to expire.
     * @param    integer           $license_period         The timeperiod the user wants to extend the license.
     * @param    integer           $license_activations    The amount of activations the user wants to renew.
     * @param    string            $license_comment        An additional comment the user has written.
     * @param    string            $license_price          The price that was proposed to the user.
     */
    public static function render_renewal_request_ticket($license, $license_period, $license_comment, $license_price)
    {
        ob_start();
        include self::TEMPLATE_PATH . 'tickets/license-renewal-request.php';
        return ob_get_clean();
    }

    /**
     * Renders the title of the ticket created when a user sent a renewal request.
     *
     * If a license is about to expire, the user may send a renewal request. In this case
     * there will be created a ticket on the JIRA helpdesk.
     * This template defines the title of that ticket.
     *
     * @since    1.0.0
     * @access   public
     * @param    LimeLM_License    $license    The license that is about to expire.
     */
    public static function render_renewal_request_ticket_title($license)
    {
        ob_start();
        include self::TEMPLATE_PATH . 'tickets/license-renewal-request-title.php';
        return ob_get_clean();
    }

    /**
     * Renders the content of the email sent to the user when a license expires soon.
     *
     * If a license is about to expire, there will may be sent an email to the user. This
     * template defines the content of that email.
     *
     * @since    1.0.0
     * @access   public
     * @param    LimeLM_License    $license    The license that is about to expire.
     */
    public static function render_renewal_notification_email($license)
    {
        ob_start();
        include self::TEMPLATE_PATH . 'emails/license-expires-soon.php';
        return ob_get_clean();
    }

    /**
     * Renders the title of the email sent to the user when a license expires soon.
     *
     * If a license is about to expire, there will may be sent an email to the user. This
     * template defines the title of that email.
     *
     * @since    1.0.0
     * @access   public
     * @param    LimeLM_License    $license    The license that is about to expire.
     */
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

    public static function render_automatic_renewal_notification_email($license)
    {
        ob_start();
        include self::TEMPLATE_PATH . 'emails/license-will-renew-soon.php';
        return ob_get_clean();
    }

    public static function render_automatic_renewal_notification_email_title($license)
    {
        ob_start();
        include self::TEMPLATE_PATH . 'emails/license-will-renew-soon-title.php';
        return ob_get_clean();
    }
}
