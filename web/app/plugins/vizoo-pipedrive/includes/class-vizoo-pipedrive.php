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
class Vizoo_Pipedrive
{
    public const REPORT_MAIL = 'info@vizoo3d.com';
    public const DEFAULT_MANAGER = 2296356;

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
        require plugin_dir_path(__FILE__) . 'class-vizoo-pipedrive-database-handler.php';
        require plugin_dir_path(__FILE__) . 'class-vizoo-pipedrive-ajax-handler.php';
        require plugin_dir_path(__FILE__) . 'class-vizoo-pipedrive-organization.php';
        require plugin_dir_path(__FILE__) . 'class-vizoo-pipedrive-api-handler.php';
        require plugin_dir_path(__FILE__) . 'class-vizoo-pipedrive-template-handler.php';

        Vizoo_Pipedrive_AJAX_Handler::define_actions();

        // Include functions callable all throughout WordPress.
        require plugin_dir_path(__FILE__) . 'functions.php';
    }

    public static function send_error_mail($subject, $message)
    {
        $subject = '[Error report] ' . $subject . '.';
        $message = 'There was an error changing something in the Customer Portal database:' . "\n\n" . $message;
        wp_mail(self::REPORT_MAIL, $subject, $message);
    }

    public static function send_success_mail($user_email, $owner_id, $user_name, $user_organization, $wp_user_id)
    {
        $subject = '[Report] User was successfully registered at the Customer Portal.';
        $message =
            'The user \'' . $user_name . '\' was successfully registered at the Customer Portal!' . "\n\n" .
            'Details of registration:' . "\n" .
            'Name: ' . $user_name . "\n" .
            'Mail: ' . $user_email . "\n" .
            'Pipedrive-ID: ' . $owner_id . "\n" .
            'Customer-Portal-ID: ' . $wp_user_id . "\n" .
            'Organization: ' . $user_organization->name . ' (Pipedrive-ID: ' . $user_organization->id . ' / Customer-Portal-ID: ' . $user_organization->database_id . ')' . "\n" .
            'Role: ' . $user_organization->relation;

        wp_mail(self::REPORT_MAIL, $subject, $message);
    }

    public static function send_user_mail($user_name, $user_email, $user_password)
    {
        $subject = 'You have been granted access to the Vizoo Knowledge Base!';
        $message =
            'Hello ' . $user_name . ',' . "\n\n" .
            'You have just been granted access to the Vizoo Knowledge Base!' . "\n" .
            'You can now browse all the resources you need for your xTex product.' . "\n\n" .
            'Visit us today at https://customers.vizoo3d.com and log in with your credentials:' . "\n\n" .
            'Username: ' . $user_email . "\n" .
            'Password: ' . $user_password . "\n\n" .
            '(Please change this password after the first time you logged in)' . "\n\n\n" .
            'Kind regards,' . "\n" .
            'Vizoo Customer Service' . "\n\n" .
            '(This is an automatically generated e-mail)';

        $headers = [
            'Reply-To: Vizoo GmbH <info@vizoo3d.com>',
        ];

        wp_mail($user_email, $subject, $message, $headers);
    }

    public static function create_missinginfo_deal($license, $users)
    {
        $deal_title = Vizoo_Pipedrive_Template_Handler::render_missinginfo_deal_title();
        $note_content = Vizoo_Pipedrive_Template_Handler::render_missinginfo_deal($license, $users);
        $manager_id = Vizoo_Pipedrive_API_Handler::get_user_id_by_name($license->get_account_manager());
        if ($manager_id === 0) {
            $manager_id = self::DEFAULT_MANAGER;
        }
        $person_id = is_user_logged_in() ? get_user_meta(get_current_user_id(), 'vizoo_pipedrive_id', true) : null;
        $deal_id = Vizoo_Pipedrive_API_Handler::create_deal($deal_title, $license->get_renewal_price(), $license->get_renewal_currency(), $manager_id, $person_id, $license->get_company_id(), $license->get_formatted_renewal_date('Y-m-d'));
        Vizoo_Pipedrive_API_Handler::create_note($note_content, $deal_id);
        Vizoo_Pipedrive_API_Handler::create_activity('Decide how to follow up with client ' . $license->get_licensee(), 'task', new DateTime(), $manager_id, $deal_id);
    }

    public static function create_noresponse_deal($license, $users)
    {
        $deal_title = Vizoo_Pipedrive_Template_Handler::render_noresponse_deal_title($license);
        $note_content = Vizoo_Pipedrive_Template_Handler::render_noresponse_deal($license, $users);
        $manager_id = Vizoo_Pipedrive_API_Handler::get_user_id_by_name($license->get_account_manager());
        if ($manager_id === 0) {
            $manager_id = self::DEFAULT_MANAGER;
        }
        $deal_id = Vizoo_Pipedrive_API_Handler::create_deal($deal_title, $license->get_renewal_price(), $license->get_renewal_currency(), $manager_id, null, $license->get_company_id(), $license->get_formatted_renewal_date('Y-m-d'));
        Vizoo_Pipedrive_API_Handler::create_note($note_content, $deal_id);
        Vizoo_Pipedrive_API_Handler::create_activity('Decide how to follow up with client ' . $license->get_licensee(), 'task', new DateTime(), $manager_id, $deal_id);
    }

    public static function create_cancellation_request_deal($license, $comment = '')
    {
        $person_id = get_user_meta(get_current_user_id(), 'vizoo_pipedrive_id', true);
        $deal_title = Vizoo_Pipedrive_Template_Handler::render_cancellation_deal_title($license);
        $note_content = Vizoo_Pipedrive_Template_Handler::render_cancellation_deal($license, $comment);
        $manager_id = Vizoo_Pipedrive_API_Handler::get_user_id_by_name($license->get_account_manager());
        if ($manager_id === 0) {
            $manager_id = self::DEFAULT_MANAGER;
        }
        error_log("cancellation deal data:" . $person_id . $deal_title . $note_content);
        $deal_id = Vizoo_Pipedrive_API_Handler::create_deal($deal_title, $license->get_renewal_price(), $license->get_renewal_currency(), $manager_id, $person_id, $license->get_company_id(), $license->get_formatted_renewal_date('Y-m-d'));
        Vizoo_Pipedrive_API_Handler::create_note($note_content, $deal_id);
        Vizoo_Pipedrive_API_Handler::create_activity('Decide how to follow up with client ' . $license->get_licensee(), 'task', new DateTime(), $manager_id, $deal_id);
        if (is_a($license, 'Vizoo_LimeLM_License')) {
            Vizoo_Pipedrive_API_Handler::create_activity('Mark cancellation in LimeLM: Set license type to CPCM for license #' . $license->get_id(), 'task', $license->get_renewal_date(), $manager_id, $deal_id);
        }
        Vizoo_Pipedrive_API_Handler::create_activity('Remove client ' . $license->get_licensee() . ' from the Customer Portal (DB status in pipedrive)', 'task', $license->get_renewal_date(), $manager_id, $deal_id);
        return $deal_id;
    }

    public static function create_migration_request_deal($license, $license_comment)
    {
        $person_id = get_user_meta(get_current_user_id(), 'vizoo_pipedrive_id', true);
        $deal_title = "Update license #" .  $license->get_id() . " (" . $license->get_licensee() . ") to subscription";
        $note_content = "The manager of the license #" . $license->get_id() . " requested a quote for a subscription.<br />" . $license_comment;
        $manager_id = Vizoo_Pipedrive_API_Handler::get_user_id_by_name($license->get_account_manager());
        if ($manager_id === 0) {
            $manager_id = self::DEFAULT_MANAGER;
        }
        $tasks_due_date = (new DateTime())->modify('+1 day');
        $deal_id = Vizoo_Pipedrive_API_Handler::create_deal($deal_title, $license->get_renewal_price(), $license->get_renewal_currency(), $manager_id, $person_id, $license->get_company_id(), $license->get_formatted_renewal_date('Y-m-d'));
        Vizoo_Pipedrive_API_Handler::create_note($note_content, $deal_id);
        Vizoo_Pipedrive_API_Handler::create_activity('Create quotation for ' . $license->get_licensee(), 'task', $tasks_due_date, $manager_id, $deal_id);
        Vizoo_Pipedrive_API_Handler::create_activity('Update license #' . $license->get_id() . ' in LimeLM to subscription', 'task', $tasks_due_date, $manager_id, $deal_id, '<a href="https://wyday.com/limelm/pkey/' . $license->get_id() . '/">https://wyday.com/limelm/pkey/' . $license->get_id() . '/</a>');
        return $deal_id;
    }

    public static function create_invoice_request_deal($license, $price, $renewal_period, $total, $comment = '', $users = [])
    {
        $person_id = get_user_meta(get_current_user_id(), 'vizoo_pipedrive_id', true);
        $deal_title = Vizoo_Pipedrive_Template_Handler::render_invoicing_deal_title($license);
        $note_content = Vizoo_Pipedrive_Template_Handler::render_invoicing_deal($license, $price, $renewal_period, $total, $comment, $users);
        $manager_id = Vizoo_Pipedrive_API_Handler::get_user_id_by_name($license->get_account_manager());
        if (preg_match('/^((?:-?[0-9]{1,3}(,[0-9]{3})*)|(?:-?[0-9]+))\s+/', $total, $matches)) {
            $total_value = intval(str_replace(',', '', $matches[1]), 10);
            if ($total_value === 0 && $matches[1] !== '0') {
                throw new Exception('Got invalid value from license.');
            }
        } else {
            throw new Exception('Unable to match license value.');
        }
        if ($manager_id === 0) {
            $manager_id = self::DEFAULT_MANAGER;
        }
        $tasks_due_date = (new DateTime())->modify('+1 day');
        $deal_id = Vizoo_Pipedrive_API_Handler::create_deal($deal_title, $total_value, $license->get_renewal_currency(), $manager_id, $person_id, $license->get_company_id(), $license->get_formatted_renewal_date('Y-m-d'));
        Vizoo_Pipedrive_API_Handler::create_note($note_content, $deal_id);
        Vizoo_Pipedrive_API_Handler::create_activity('Create invoice for ' . $license->get_licensee(), 'task', $tasks_due_date, $manager_id, $deal_id);
        Vizoo_Pipedrive_API_Handler::create_activity('Extend expiry date for license #' . $license->get_id() . ' in LimeLM', 'task', $tasks_due_date, $manager_id, $deal_id, '<a href="https://wyday.com/limelm/pkey/' . $license->get_id() . '/">https://wyday.com/limelm/pkey/' . $license->get_id() . '/</a>');
        return $deal_id;
    }

    public static function create_quotation_request_deal($license, $price, $renewal_period, $total, $comment = '', $users = [])
    {
        $person_id = get_user_meta(get_current_user_id(), 'vizoo_pipedrive_id', true);

        $deal_title = Vizoo_Pipedrive_Template_Handler::render_quotation_deal_title($license);
        $note_content = Vizoo_Pipedrive_Template_Handler::render_quotation_deal($license, $price, $renewal_period, $total, $comment, $users);
        $manager_id = Vizoo_Pipedrive_API_Handler::get_user_id_by_name($license->get_account_manager());
        if ($manager_id === 0) {
            $manager_id = self::DEFAULT_MANAGER;
        }
        $deal_id = Vizoo_Pipedrive_API_Handler::create_deal($deal_title, $total, $license->get_renewal_currency(), $manager_id, $person_id, $license->get_company_id(), $license->get_formatted_renewal_date('Y-m-d'));
        Vizoo_Pipedrive_API_Handler::create_note($note_content, $deal_id);
        Vizoo_Pipedrive_API_Handler::create_activity('Create quotation for ' . $license->get_licensee(), 'task', (new DateTime())->modify('+1 day'), $manager_id, $deal_id);
        return $deal_id;
    }

    public static function upload_attachment($deal_id, $file)
    {
        $attachment_id = Vizoo_Pipedrive_API_Handler::add_file_to_deal($file['tmp_name'], $deal_id, $file['type'], $file['name']);
        return $attachment_id;
    }
}
