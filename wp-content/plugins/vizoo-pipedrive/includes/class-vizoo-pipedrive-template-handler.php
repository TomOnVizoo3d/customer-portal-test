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
 * @package    Vizoo_Pipedrive
 * @subpackage Vizoo_Pipedrive/includes
 */
class Vizoo_Pipedrive_Template_Handler
{
    /**
     * The base path of the templates.
     *
     * @since    1.0.0
     * @access   public
     * @var      string    TEMPLATE_PATH    The path where the templates are located.
     */
    public const TEMPLATE_PATH = __DIR__ . '/assets/templates/';

    public static function render_cancellation_deal($license, $comment)
    {
        ob_start();
        include self::TEMPLATE_PATH . 'deals/pd-cancellation.php';
        return ob_get_clean();
    }

    public static function render_cancellation_deal_title($license)
    {
        if (is_a($license, 'Vizoo_LimeLM_License')) {
            return sprintf('[Maintenance:Sales] %s requested a maintenance or license cancellation.', $license->get_licensee());
        } elseif (is_a($license, 'Vizoo_LicenseSpring_License')) {
            return sprintf('[Maintenance:Sales] %s requested a subscription cancellation.', $license->get_licensee());
        }
    }

    public static function render_invoicing_deal($license, $price, $renewal_period, $total, $comment, $users = [])
    {
        ob_start();
        include self::TEMPLATE_PATH . 'deals/pd-invoicing.php';
        return ob_get_clean();
    }

    public static function render_invoicing_deal_title($license)
    {
        if (is_a($license, 'Vizoo_LimeLM_License')) {
            return sprintf('[Maintenance:Sales] %s requested an invoice for a maintenance or license renewal.', $license->get_licensee());
        } elseif (is_a($license, 'Vizoo_LicenseSpring_License')) {
            return sprintf('[Maintenance:Sales] %s requested an invoice for a subscription renewal.', $license->get_licensee());
        }
    }

    public static function render_missinginfo_deal($license, $users)
    {
        ob_start();
        include self::TEMPLATE_PATH . 'deals/pd-missinginfo.php';
        return ob_get_clean();
    }

    public static function render_missinginfo_deal_title()
    {
        return '[Maintenance:Sales] License with missing info is expiring soon.';
    }

    public static function render_noresponse_deal($license, $users)
    {
        ob_start();
        include self::TEMPLATE_PATH . 'deals/pd-noresponse.php';
        return ob_get_clean();
    }

    public static function render_noresponse_deal_title($license)
    {
        if (is_a($license, 'Vizoo_LimeLM_License')) {
            return sprintf('[Maintenance:Sales] %s is not responding to the impending maintenance or license expiration.', $license->get_licensee());
        } elseif (is_a($license, 'Vizoo_LicenseSpring_License')) {
            return sprintf('[Maintenance:Sales] %s is not responding to the impending subscription expiration.', $license->get_licensee());
        }
    }

    public static function render_quotation_deal($license, $price, $renewal_period, $total, $comment, $users = [])
    {
        ob_start();
        include self::TEMPLATE_PATH . 'deals/pd-quotation.php';
        return ob_get_clean();
    }

    public static function render_quotation_deal_title($license)
    {
        if (is_a($license, 'Vizoo_LimeLM_License')) {
            return sprintf('[Maintenance:Sales] %s requested a quotation for a maintenance or license renewal.', $license->get_licensee());
        } elseif (is_a($license, 'Vizoo_LicenseSpring_License')) {
            return sprintf('[Maintenance:Sales] %s requested a quotation for a subscription renewal.', $license->get_licensee());
        }
    }
}
