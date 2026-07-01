<?php

/**
 * The plugin's main class.
 *
 * @link          https://customers.vizoo3d
 * @since         1.0.0
 *
 * @package       Vizoo_Pipedrive
 * @subpackage    Vizoo_Pipedrive/includes
 */
class Vizoo_Weclapp
{
    public const REPORT_MAIL = 'info@vizoo3d.com';
    public const RESPONSIBLE_USER_MAPPING = [
        'Renate'    =>   '7195',
        'Martin'    =>   '7186',
        'Andrew'    =>  '33360',
    ];
    public const DEAL_TYPE_MAPPING = [
        'CSWS' => '89880',
        'CPWM' => '95906',
    ];
    public const REVERSE_CHARGE_COUNTRIES = [
        'CH',
        'IS',
        'LI',
        'NO',

        'AT',
        'BE',
        'BG',
        'CY',
        'CZ',
        'DK',
        'EE',
        'ES',
        'FI',
        'FR',
        'GR',
        'HR',
        'HU',
        'IE',
        'IT',
        'LT',
        'LU',
        'LV',
        'MT',
        'NL',
        'PL',
        'PT',
        'RO',
        'SE',
        'SI',
        'SK',
    ];

    /**
     * Runs the plugin startup.
     *
     * Initializes the plugin.
     *
     * @since     1.0.0
     * @access    public
     */
    public function run()
    {
        // Include the necessary dependencies.
        require plugin_dir_path(__FILE__) . 'class-vizoo-weclapp-api-handler.php';
    }

    public static function create_quotation($license, $address, $email_address, $years)
    {
        $account_manager = $license->get_account_manager();
        $responsible_user = self::RESPONSIBLE_USER_MAPPING[$account_manager];

        $from_date = DateTimeImmutable::createFromMutable($license->get_renewal_date());
        $to_date = $from_date->add(new DateInterval(sprintf('P%dY', $years)));

        if (is_a($license, 'Vizoo_LimeLM_License')) {
            $quotation_id = Vizoo_Weclapp_API_Handler::create_quotation(
                self::DEAL_TYPE_MAPPING[$license->get_license_type_code()],
                $license->get_key(),
                (int)$from_date->format('Uv'),
                (int)$to_date->format('Uv'),
                $license->get_weclapp_customer_number(),
                self::get_article_nummer($license->get_license_type_code(), $license->is_floating(), $license->is_software_only()),
                (string)self::get_discount($years),
                (string)$years,
                self::get_tax_class($address['countryCode']),
                $license->get_renewal_price(),
                $address,
                $email_address,
                $license->get_renewal_currency(),
                $responsible_user,
                $quotation_number
            );
        } elseif (is_a($license, 'Vizoo_LicenseSpring_License')) {
            $quotation_id = Vizoo_Weclapp_API_Handler::create_subscription_quotation(
                (string)$license->get_id(),
                (int)$from_date->format('Uv'),
                (int)$to_date->format('Uv'),
                $license->get_weclapp_customer_number(),
                $license->is_design_license() ? 'SW-200' : 'SW-210',
                (string)self::get_discount($years),
                (string)$years,
                self::get_tax_class($address['countryCode']),
                $license->get_renewal_price(),
                $address,
                $email_address,
                $license->get_renewal_currency(),
                $responsible_user,
                $quotation_number
            );
        } else {
            exit;
        }

        $pdf_content = Vizoo_Weclapp_API_Handler::create_quotation_pdf($quotation_id);

        require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
        require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
        require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';

        $mailer = new PHPMailer\PHPMailer\PHPMailer(true);
        $success = false;
        try {
            $mailer->isSMTP();
            $mailer->Host = getenv('VIZOO_MAIL_SMTP');
            $mailer->SMTPAuth = (bool) getenv('VIZOO_MAIL_AUTH');
            $mailer->Username = getenv('VIZOO_MAIL_USERNAME');
            $mailer->Password = getenv('VIZOO_MAIL_PASSWORD');
            $mailer->Port = (int) getenv('VIZOO_MAIL_PORT');
            $mailer->setFrom(getenv('VIZOO_MAIL_FROM'), $from_title);

            $mailer->addAddress($email_address);
            $mailer->Subject = "Quote $quotation_number - Vizoo GmbH";
            $mailer->Body = "Hi there,

Thank you for your request. Attached is our offer.

If our offer meets your expectations, please proceed to place your order via the Customer Portal (https://customers.vizoo3d.com/licenses/) or by replying to this email.

Please do not hesitate to contact us if you have further questions.


Kind regards,

Thomas

Vizoo Customer Service";
            $mailer->addReplyTo('info@vizoo3d.com', 'Vizoo GmbH');
            $mailer->addStringAttachment($pdf_content, sprintf('Quote%s.pdf', $quotation_id));

            if ($mailer->send()) {
                $success = true;
            }
        } catch (PHPMailer\PHPMailer\Exception $e) {
        }

        $license_ref = is_a($license, 'Vizoo_LimeLM_License') ? $license->get_key() : $license->get_id();
        $note_content = sprintf('A license manager requested a quote for license %s [<a href="%s">view quotation</a>]', $license_ref, "https://vizoo3d.weclapp.com/webapp/view/offer/OfferDetail.page?entityId=$quotation_id");
        if (!$success) {
            $note_content .= ' -- sending mail to customer failed.';
        }

        $person_id = get_user_meta(get_current_user_id(), 'vizoo_pipedrive_id', true);

        $manager_id = Vizoo_Pipedrive_API_Handler::get_user_id_by_name($license->get_account_manager());
        if ($manager_id === 0) {
            $manager_id = Vizoo_Pipedrive::DEFAULT_MANAGER;
        }
        $final_price = ((100 - self::get_discount($years)) / 100) * $years * $license->get_renewal_price();
        $deal_id = Vizoo_Pipedrive_API_Handler::create_deal(sprintf('[Maintenance:Sales] %s requested a quotation for the license %s', $license->get_licensee(), $license_ref), $final_price, $license->get_renewal_currency(), $manager_id, $person_id, $license->get_company_id(), (new DateTime())->modify('+1 day')->format('Y-m-d'));
        Vizoo_Pipedrive_API_Handler::create_note($note_content, $deal_id, 'deal');
    }

    private static function get_discount(int $years)
    {
        switch ($years) {
            case 2:
                return 5;
                break;
            case 3:
                return 10;
                break;
            default:
                return 0;
                break;
        }
    }

    private static function get_tax_class(string $country_code)
    {
        if ($country_code === "DE") {
            return '2178';
        }
        if (in_array($country_code, self::REVERSE_CHARGE_COUNTRIES, true)) {
            return '50956';
        }
        return '56344';
    }

    private static function get_article_nummer(string $license_type, bool $is_floating, bool $is_design_license)
    {
        if ($license_type === 'CPWM' && $is_design_license && !$is_floating) {
            return 'SWM-100';
        }
        if ($license_type === 'CPWM' && $is_design_license && $is_floating) {
            return 'SWM-110';
        }
        if ($license_type === 'CPWM' && !$is_design_license && !$is_floating) {
            return 'SWM-120';
        }
        if ($license_type === 'CPWM' && !$is_design_license && $is_floating) {
            return 'SWM-130';
        }
        if ($license_type === 'CSWS' && $is_design_license && !$is_floating) {
            return 'SW-140';
        }
        if ($license_type === 'CSWS' && $is_design_license && $is_floating) {
            return 'SW-160';
        }
        if ($license_type === 'CSWS' && !$is_design_license && !$is_floating) {
            return 'SW-150';
        }
        if ($license_type === 'CSWS' && !$is_design_license && $is_floating) {
            return 'SW-170';
        }
        throw new RuntimeException("No matching article number found.");
    }
}
