<?php

/**
 * The core plugin class.
 *
 * The class providing the main functionality of the plugin.
 *
 * @since      1.0.0
 * @package    Vizoo_LimeLM
 * @subpackage Vizoo_LimeLM/includes
 */
class Vizoo_LimeLM
{
    /**
     * Load the required dependencies for this plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {
        require_once plugin_dir_path(__FILE__) . 'class-vizoo-limelm-api-handler.php';
        require_once plugin_dir_path(__FILE__) . 'class-vizoo-limelm-ajax-handler.php';
        require_once plugin_dir_path(__FILE__) . 'class-vizoo-limelm-template-handler.php';
        require_once plugin_dir_path(__FILE__) . 'class-vizoo-limelm-license.php';
        require_once plugin_dir_path(__FILE__) . 'class-vizoo-limelm-activation.php';
    }

    /**
     * Register all of the hooks related to the functionality of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_hooks()
    {
        // Register the stylesheet and JavaScript.
        wp_register_style('vizoo_limelm_style', plugin_dir_url(__FILE__) . 'assets/css/vizoo-limelm.css');
        wp_register_script('vizoo_limelm_script', plugin_dir_url(__FILE__) . 'assets/js/vizoo-limelm.js');

        // Register the shortcode.
        add_shortcode('vizoo_limelm_overview', [$this, 'do_shortcode']);

        // Check the requested posts for the shortcode and conditionally enqueue it.
        add_filter('the_posts', [$this, 'enqueue_style']);

        // Add action hooks for AJAX functions.
        Vizoo_LimeLM_AJAX_Handler::define_actions();
    }

    /**
     * Enqueue the plugin's stylesheet.
     *
     * Conditionally enqueue the stylesheet of the plugin. Only enqueue it, if the shortcode
     * is present on the page requested. Prevents useless stylesheets in the header.
     *
     * The JavaScript can be enqueued in the actual shortcode handling, because it can be
     * rendered to the page's footer. CSS is not allowed in the footer.
     *
     * @since    1.0.0
     * @access   private
     */
    public function enqueue_style($posts)
    {
        if (empty($posts) || !is_page()) {
            return $posts;
        }
        $shortcode_found = false;
        foreach ($posts as $post) {
            if (stripos($post->post_content, '[vizoo_limelm_overview]') !== false) {
                $shortcode_found = true;
                break;
            }
        }
        if ($shortcode_found) {
            wp_enqueue_style('vizoo_limelm_style');
        }
        return $posts;
    }

    /**
     * Inserts plugin functionality.
     *
     * Inserts the plugin's functionality if the shortcode is present. This function
     * contains the main functionality.
     *
     * @since    1.0.0
     * @access   public
     */
    public function do_shortcode()
    {
        if (!is_user_logged_in()) {
            return;
        }

        $user_id = get_current_user_id();
        $user_role = get_user_meta($user_id, 'vizoo_customerrole', true);
        $organization_id = get_user_meta($user_id, 'vizoo_organization', true);

        if (!$user_role == 'LicenseManager') {
            echo '<h2>No License Overview Available:</h2> You currently do not have access to the license and billing information. Either you do not have any licenses or your account does not have the permission to access licenses. If you need access to the license page, please contact our <a href="mailto:support@vizoo3d.com">support</a>';
            return;
        }

        if (empty($organization_id)) {
            // TODO: Error pages
            echo 'Error: You are not registered in an organization.';
            return;
        }

        $noscript = isset($_REQUEST['noscript']);
        $license_action = $_POST['vizoo_limelm_action'] ?? 'overview';

        switch ($license_action) {
            case 'deactivate_activation':
                $license_id = $_GET['license_id'] ?? null;
                $activation_id = $_GET['activation_id'] ?? null;
                $nonce = $_GET['nonce'] ?? null;

                if (!isset($license_id) || !isset($activation_id) || !isset($nonce)) {
                    // TODO: Error pages
                    echo 'Error: Not information provided for activation deactivation.';
                    return;
                }

                if (!wp_verify_nonce($nonce, 'vizoo_limelm_confirm_deactivation-' . $activation_id)) {
                    Vizoo_LimeLM_Template_Handler::render_nonce_fail();
                    return;
                }

                Vizoo_LimeLM_API_Handler::deactivate_activation($activation_id, $license_id, $organization_id);

                break;
            case 'renewal_form':
                $license_id = $_POST['vizoo_limelm_license_id'] ?? null;
                $nonce = $_POST['nonce'] ?? null;

                if (!isset($license_id) || !isset($nonce)) {
                    // TODO: Error pages
                    echo 'Error: Not information provided for renewal form.';
                    return;
                }

                if (!wp_verify_nonce($nonce, 'vizoo_limelm_confirm_renewal_form-' . $license_id)) {
                    Vizoo_LimeLM_Template_Handler::render_nonce_fail();
                    return;
                }

                $license = Vizoo_LimeLM_API_Handler::get_license($license_id, $organization_id);

                Vizoo_LimeLM_Template_Handler::render_license_migration($license);

                break;
            case 'cancellation_form':
                $license_id = $_POST['vizoo_limelm_license_id'] ?? null;
                $nonce = $_POST['nonce'] ?? null;

                if (!isset($license_id) || !isset($nonce)) {
                    // TODO: Error pages
                    echo 'Error: Not information provided for cancellation form.';
                    return;
                }

                if (!wp_verify_nonce($nonce, 'vizoo_limelm_confirm_cancellation_form-' . $license_id)) {
                    Vizoo_LimeLM_Template_Handler::render_nonce_fail();
                    return;
                }

                $license = Vizoo_LimeLM_API_Handler::get_license($license_id, $organization_id);

                Vizoo_LimeLM_Template_Handler::render_license_cancellation($license);

                break;
            case 'migrate_license':
                $license_id = $_POST['vizoo_limelm_license_id'] ?? null;
                $comment = $_POST['vizoo_limelm_special_request'] ?? null;
                $nonce = $_POST['nonce'] ?? null;

                if (!isset($license_id) || !isset($nonce)) {
                    // TODO: Error pages
                    echo 'Error: Not enough information provided for license renewal.';
                    return;
                }

                if (!wp_verify_nonce($nonce, 'vizoo_limelm_confirm_renewal-' . $license_id)) {
                    Vizoo_LimeLM_Template_Handler::render_nonce_fail();
                }

                $license = Vizoo_LimeLM_API_Handler::get_license($license_id, $organization_id);

                $deal_id = self::send_migration_request($license, $comment);
                if ($deal_id !== -1) {
                    Vizoo_LimeLM_API_Handler::set_license_renewal_deal($license_id, $deal_id);
                }

                Vizoo_LimeLM_Template_Handler::render_success();
                break;

            case 'cancel_license':
                $license_id = $_POST['vizoo_limelm_license_id'] ?? null;
                $comment = $_POST['vizoo_limelm_comment'] ?? '';
                $nonce = $_POST['nonce'] ?? null;

                if (!isset($license_id) || !isset($nonce)) {
                    // TODO: Error pages
                    echo 'Error: Not enough information provided for license cancellation.';
                    return;
                }

                if (!wp_verify_nonce($nonce, 'vizoo_limelm_confirm_cancellation-' . $license_id)) {
                    Vizoo_LimeLM_Template_Handler::render_nonce_fail();
                    return;
                }

                $license = Vizoo_LimeLM_API_Handler::get_license($license_id, $organization_id);

                $deal_id = self::send_cancellation_request($license, $comment);

                Vizoo_LimeLM_API_Handler::set_license_renewal_deal($license_id, $deal_id);
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
                    '',
                    [trailingslashit(wp_upload_dir('2022/08')) . '2022-08-09_Software_Maintenance_and_Support_Contract_EN.pdf']
                );

                Vizoo_LimeLM_Template_Handler::render_success();

                break;
            case 'overview':
            default:
                if ($noscript) {
                    $licenses = Vizoo_LimeLM_API_Handler::get_licenses($organization_id);
                } else {
                    // The licenses are going to be loaded with AJAX.
                    $licenses = [];
                    wp_enqueue_script('vizoo_limelm_script');
                }

                return Vizoo_LimeLM_Template_Handler::get_license_overview($licenses);
        }
    }

    public static function send_migration_request($license, $license_comment)
    {
        return Vizoo_Pipedrive::create_migration_request_deal($license, $license_comment);
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
