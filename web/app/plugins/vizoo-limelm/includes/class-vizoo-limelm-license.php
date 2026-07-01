<?php

/**
 * A container class for LimeLM licenses.
 *
 * Each instance of this class represents a license in LimeLM. It can contain all the
 * information from LimeLM and is easily extendable as the raw data from LimeLM gets
 * passed to the constructor. A short description on how to add a field of LimeLM is
 * described below, directly above the constructor.
 *
 * The derived functionality (e.g. if a license is perpetual) is provided by functions
 * and is therefore generated real-time. Changes can be made easily without changing the
 * whole class structure.
 *
 * The constants defining the string displayed for the software type, the perpetual
 * threshold and the notification threshold are defined in this file.
 *
 * @link       https://customers.vizoo3d
 * @since      1.0.0
 *
 * @package    Vizoo_LimeLM
 * @subpackage Vizoo_LimeLM/includes
 */

class Vizoo_LimeLM_License
{
    /**
     * The lookup array for the license type.
     *
     * LimeLM saves different software types as numbers. This array contains the numbers
     * defining the type and the related strings that should be displayed.
     *
     * @since     1.0.0
     * @access    public
     * @var       array     TYPE_CONVERSION    The lookup array for the software type ids.
     */
    public const TYPE_CONVERSION = [
        '4401' => 'Software only',
        '2226' => 'Software',
    ];

    /**
     * The lookup array for the license type codes.
     *
     * LimeLM saves different software types as numbers. This array contains the numbers
     * defining the type and the related strings that should be displayed.
     *
     * @since     1.1.0
     * @access    public
     * @var       array     LICENSE_TYPES    The lookup array for the license type short codes.
     */
    public const LICENSE_TYPES = [
        'CSWS' => 'client software w. subscription (old pricing)',
        'CPUM' => 'client perpetual unlimited maintenance',
        'CPWM' => 'client perpetual w. yearly maintenance',
        'CPCM' => 'client perpetual cancelled maintenance',
        'CRNT' => 'client rental',
        'EDU' => 'education',
        'INT' => 'internal',
        'TMP' => 'temporary (demo) licenses',
        'PAL' => 'partner licenses',
    ];

    /**
     *
     * The perpetual license threshold.
     *
     * Licenses with expiration dates beyond this year will be displayed as perpetual.
     * Renewal will be labeled 'Maintenance renewal' and is dated to the 'PaymentDue'-date
     * if available.
     *
     * @since    1.0.0
     * @access   public
     * @var      string    PERPETUAL_THRESHOLD    The perpetual license threshold.
     */
    public const PERPETUAL_THRESHOLD = 2030;

    /**
     * The notification threshold.
     *
     * Defines in which time period before the expiration date the license is considered
     * 'expiring soon'. If licenses 'expire soon' the renewal date will be displayed in
     * red, there will be a label 'Expires soon' in the license overview.
     * If there is a license manager registered at the customer portal for the affected
     * license he will be notified.
     * Also, the renewal process will be kicked off.
     *
     * @since    1.0.0
     * @access   public
     * @var      string    NOTIFICATION_THRESHOLD    The notification threshold.
     */
    public const NOTIFICATION_THRESHOLD = 31;

    /**
     * The reminder threshold.
     *
     * Defines in which time period before the expiration date the licensee gets another
     * reminder that the license expires soon. This will be skipped if the licensee
     * already requested renewal or cancelled the license.
     *
     * @since    1.1.0
     * @access   public
     * @var      string    REMINDER_THRESHOLD    The reminder threshold.
     */
    public const REMINDER_THRESHOLD = 15;
    public const NORESPONSE_THRESHOLD = 7;

    /**
     * Constants for the expiration state of the license.
     *
     * Define the expiration state of the license.
     *
     * @since    1.0.0
     * @access   public
     * @var      string    ACTIVE          Licenses that won't expire in the time period of the notification threshold.
     * @var      string    EXPIRES_SOON    Licenses that will expire in the time period of the notification threshold.
     * @var      string    EXPIRED         Licenses that are expired.
     */
    public const ACTIVE = 'active';
    public const EXPIRES_SOON = 'expires-soon';
    public const EXPIRED = 'expired';

    /**
     * Constants for the renewal state of the license.
     *
     * Define the renewal state of the license.
     *
     * @since    1.0.0
     * @access   public
     * @var      string    NEEDS_RENEWAL             Licenses which need to be renewed.
     * @var      string    RENEWAL_PENDING           Licenses which the user already sent a renewal request for.
     * @var      string    CANCELLATION_REQUESTED    Licenses which the user sent a cancellation request for.
     * @var      string    NOT_RENEWABLE             Licenses that are not renewable.
     */
    public const NEEDS_RENEWAL = 'needs-renewal';
    public const RENEWAL_PENDING = 'renewal-pending';
    public const CANCELLATION_REQUESTED = 'cancellation-requested';
    public const NOT_RENEWABLE = 'not-renewable';

    /**
     * The default fields of a LimeLM license.
     *
     * @since    1.0.0
     * @access   private
     * @var      integer    $id             The license's id.
     * @var      string     $key            The license's key which is used to activate the software.
     * @var      string     $type           The license's type. Maps to the 'version_id'-field in LimeLM.
     * @var      integer    $acts           The count of activations the license allows.
     * @var      integer    $acts_used      The count of activations that are currently in use.
     * @var      boolean    $is_floating    Whether this license is a floating license.
     */
    private $id;
    private $key;
    private $type;
    private $acts;
    private $acts_used;
    private $is_floating;

    /**
     * The dates of the license.
     *
     * As for the naming of dates (used consistently throughout this plugin):
     * - Expiration date is the date the license expires. After that date the user will not
     *                   be able to use the software anymore. For perpetual licenses this
     *                   should actually be empty, but is set to a year in the distant
     *                   future.
     * - Payment due is the date the licensee has to pay in order to renew his license or
     *               maintenance plan.
     * - Renewal date is the date the user actually has to act and decide whether to renew
     *                his license or maintenance plan. This date is either the expiration
     *                date or the payment due date, depending on the license's attributes.
     *
     * @since    1.0.0
     * @access   private
     * @var      DateTime    $expiration_date              The license's expiration date.
     * @var      DateTime    $payment_due                  The license's payment due date.
     * @var      DateTime    $renewal_notification_sent    The renewal date the user already got a notification for.
     * @var      DateTime    $renewal_reminder_sent        The renewal date the user already got a reminder for.
     * @var      DateTime    $renewal_reminder_ignored     The renewal date the user has ignored any notification for.
     * @var      DateTime    $cancellation_request_sent    The renewal date the user has sent a cancellation request for.
     * @var      boolean     $has_open_renewal_deal        Whether the license has an open renewal deal.
     */
    private $expiration_date;
    private $payment_due;
    private $renewal_notification_sent;
    private $renewal_reminder_sent;
    private $renewal_reminder_ignored;
    private $cancellation_request_sent;
    private $has_open_renewal_deal;

    /**
     * Custom fields of the license.
     *
     * @since    1.0.0
     * @access   private
     * @var      integer    $renewal_price       The price the user has to pay in order to renew the license.
     * @var      string     $renewal_currency    The currency of the renewal price.
     * @var      string     $licensee            The company that acts as licensee.
     * @var      integer    $company_id          The company's pipedrive id.
     * @var      string     $migrated            Whether the license was merged with LicenseSpring or not.
     * @var      string     $license_type        The short code of the license type.
     * @var      string     $account_manager     The Vizoo employee that manages the license.
     * @var      string     $camera_type         The type of camera that is connected to the license (can be 'NULL' for software only licenses).
     */
    private $renewal_price;
    private $renewal_currency;
    private $licensee;
    private $company_id;
    private $migrated;
    private $license_type;
    private $account_manager;
    private $camera_type;
    private $weclapp_customer_number;

    /**
     * Activations of the license.
     *
     * @since    1.0.0
     * @access   private
     * @var      array      $activations    The active activations of the license.
     */
    private $activations;

    /**
     * Constructs a Vizoo_LimeLM_License object.
     *
     * Takes the predefined attributes, features and activations and constructs a
     * Vizoo_LimeLM_License object out of that.
     *
     * The attributes, features and activations are directly provided by LimeLM and contain
     * all field data stored there. So if you'd like to add a custom field 'Comment' procede
     * as following:
     * 1. Add the field in LimeLM.
     * 2. Add the declaration above.
     * 3. Initialize the field in this constructor using '$features['Comment']'.
     * 4. Add a getter method below.
     * 5. Insert the getter method in a template found in '/includes/assets/templates/'.
     *
     * @since    1.0.0
     * @access   public
     * @param    array      $attributes     The license's attributes as provided by LimeLM.
     * @param    array      $features       The license's features as provided by LimeLM.
     * @param    array      $activations    The license's activations as provided by LimeLM.
     */
    public function __construct($attributes, $features, $activations, $open_renewal_deals = [])
    {
        // Default fields.
        $this->id = $attributes['id'];
        $this->key = $attributes['key'];
        $this->type = $attributes['version_id'];
        $this->acts = $attributes['acts'];
        $this->acts_used = $attributes['acts_used'];
        $this->is_floating = $attributes['for_tfs'] == 'true';

        // Dates.
        $this->expiration_date = (!empty($features['ExpirationDate']) ? new DateTime($features['ExpirationDate']) : null);
        $this->payment_due = (!empty($features['PaymentDue']) ? new DateTime($features['PaymentDue']) : null);
        $this->has_open_renewal_deal = in_array($features['RenewalDealId'], $open_renewal_deals);
        $this->renewal_notification_sent = (!empty($features['ExpirationNotificationSent']) ? new DateTime($features['ExpirationNotificationSent']) : null);
        $this->renewal_reminder_sent = (!empty($features['ExpirationReminderSent']) ? new DateTime($features['ExpirationReminderSent']) : null);
        $this->renewal_reminder_ignored = (!empty($features['ExpirationReminderIgnored']) ? new DateTime($features['ExpirationReminderIgnored']) : null);
        $this->cancellation_request_sent = (!empty($features['CancellationRequestSent']) ? new DateTime($features['CancellationRequestSent']) : null);

        // Custom fields.
        $this->renewal_price = $features['RenewalPrice'];
        $this->renewal_currency = $features['RenewalCurrency'];
        $this->licensee = $features['Company'];
        $this->company_id = $features['CompanyId'];
        $this->migrated = !empty($features['Migrated']) && $features['Migrated'] !== 'DNM';
        $this->license_type = $features['LicenseType'];
        $this->account_manager = $features['AccountManager'];
        $this->camera_type = $features['cameratype'];
        $this->weclapp_customer_number = $features['WeclappCustomerNumber'] === "0" || !empty($features['WeclappCustomerNumber']) ? $features['WeclappCustomerNumber'] : null;

        // Activations.
        $this->activations = $activations;
    }

    /**
     * Gets the id of the license.
     *
     * @since     1.0.0
     * @access    public
     * @return    string    The id of the license.
     */
    public function get_id()
    {
        return $this->id;
    }

    /**
     * Gets the key of the license.
     *
     * @since     1.0.0
     * @access    public
     * @return    string    The key of the license.
     */
    public function get_key()
    {
        return $this->key;
    }

    /**
     * Gets the type of the license.
     *
     * Uses the TYPE_CONVERSION lookup array constant defined above.
     *
     * @since     1.0.0
     * @access    public
     * @return    mixed     The string representation of the license type. Null if the id is not known.
     */
    public function get_type()
    {
        if ($this->type == null || !in_array($this->type, array_keys(self::TYPE_CONVERSION))) {
            return null;
        }
        return self::TYPE_CONVERSION[$this->type];
    }

    /**
     * Gets the number of maximum activations of the license.
     *
     * @since     1.0.0
     * @access    public
     * @return    integer    The maximum amount of activations of the license.
     */
    public function get_acts()
    {
        return $this->acts;
    }

    /**
     * Gets the number of used activations of the license.
     *
     * @since     1.0.0
     * @access    public
     * @return    integer    The amount of used activations of the license.
     */
    public function get_acts_used()
    {
        return $this->acts_used;
    }

    /**
     * Gets whether the license is a floating license.
     *
     * @since     1.0.0
     * @access    public
     * @return    boolean    True if the license is a floating license, false otherwise.
     */
    public function is_floating()
    {
        return $this->is_floating;
    }

    /**
     * Gets the licensee of the license.
     *
     * The licensee is the organization that uses the license. This allows for resellers
     * managing their customers' licenses to see which organization the license belongs to.
     *
     * @since     1.0.0
     * @access    public
     * @return    string    The name of the licensee.
     */
    public function get_licensee()
    {
        return $this->licensee;
    }

    /**
     * Gets the company id.
     *
     * This is the id of the organization managing the licenses. In case of a
     * reseller-customer-relation, this should (probably) be the reseller.
     *
     * @since     1.0.0
     * @access    public
     * @return    integer    The id of the company managing the license.
     */
    public function get_company_id()
    {
        return $this->company_id;
    }

    /**
     * Returns the license type abbreviation.
     *
     * @since     1.1.0
     * @access    public
     * @return    string    The short code for the license type.
     */
    public function get_license_type_code()
    {
        return $this->license_type;
    }

    /**
     * Returns the license type.
     *
     * Uses the LICENSE_TYPES lookup array constant defined above.
     *
     * @since     1.1.0
     * @access    public
     * @return    string    The license type.
     */
    public function get_license_type()
    {
        if ($this->license_type == null || !in_array($this->license_type, array_keys(self::LICENSE_TYPES))) {
            return null;
        }
        return self::LICENSE_TYPES[$this->license_type];
    }

    /**
     * Returns the account manager.
     *
     * @since     1.1.0
     * @access    public
     * @return    string    The account manager.
     */
    public function get_account_manager()
    {
        return $this->account_manager;
    }

    /**
     * Returns whether the license is software only.
     *
     * @since     1.2.0
     * @access    public
     * @return    boolean    Whether the license is software only.
     */
    public function is_software_only()
    {
        return empty($this->camera_type) || $this->camera_type === 'NULL';
    }

    public function get_weclapp_customer_number()
    {
        return $this->weclapp_customer_number;
    }

    public function is_migrated()
    {
        return $this->migrated;
    }

    /**
     * Fetches whether the license is a perpetual license.
     *
     * A perpetual license does not expire. In LimeLM such licenses' expiration date is set
     * to a year in the distant future.
     * Perpetual licenses may however have a payment due date. In this case it's the new
     * licensing plan with maintenance renewal.
     *
     * @since     1.0.0
     * @access    public
     * @return    boolean    True if the license is a perpetual license, false otherwise.
     */
    public function is_perpetual()
    {
        if (!is_a($this->get_renewal_date(), 'DateTime')) {
            return;
        }

        if ($this->expiration_date->format('Y') >= self::PERPETUAL_THRESHOLD) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Retrieves the expiration state of the license.
     *
     * Licenses may be 'active', 'expiring soon' or 'expired'. 'Active' licenses do not have
     * to be actually activated, but their renewal date is in a safe distance in the future.
     *
     * @since     1.0.0
     * @access    public
     * @return    string    The expiration state of the license.
     */
    public function get_expiration_state()
    {
        $renewal_date = $this->get_renewal_date();

        if (!is_a($renewal_date, 'DateTime')) {
            return self::ACTIVE;
        }

        $now_date = new DateTime();
        $difference = $now_date->diff($renewal_date);

        if ($difference->invert == 1) {
            return self::EXPIRED;
        } elseif ($difference->days <= self::NOTIFICATION_THRESHOLD) {
            return self::EXPIRES_SOON;
        }

        return self::ACTIVE;
    }

    /**
     * Fetches whether the license has a payment due date.
     *
     * Licenses without a payment due date are trial, free, educational or freelancer
     * licenses. These licenses should not be renewable through the Customer Portal and
     * should not receive any expiration notification.
     * The implementation of the correct return of the status NOT_RENEWABLE is in the
     * function get_renewal_state() below.
     *
     * @since     1.0.0
     * @access    public
     * @return    boolean    True if the license has a payment due date, false otherwise.
     */
    public function has_payment_due()
    {
        return is_a($this->payment_due, 'DateTime');
    }

    /**
     * Retrieves the renewal state of the license.
     *
     * Licenses may be 'not renewable', may 'need renewal' or there may be a
     * 'renewal pending' for this license.
     *
     * @since     1.0.0
     * @access    public
     * @return    string    The renewal state of the license.
     */
    public function get_renewal_state()
    {
        // If there is no payment due date, the license shall not be renewable.
        if (!$this->has_payment_due()) {
            return self::NOT_RENEWABLE;
        }
        if ($this->migrated) {
            return self::NOT_RENEWABLE;
        }

        if ($this->cancellation_request_sent()) {
            return self::CANCELLATION_REQUESTED;
        }

        if ($this->has_open_renewal_deal) {
            return self::RENEWAL_PENDING;
        }

        switch ($this->get_expiration_state()) {
            case (self::EXPIRES_SOON):
            case (self::EXPIRED):
                return self::NEEDS_RENEWAL;
            default:
                return self::NOT_RENEWABLE;
        }
    }

    public function can_request_migration()
    {
        $migrateEligible = ["CPWM", "CPCM", "CSWS", "CPUM", "CRNT", "EDU", "PAL"];
        if ($this->migrated) {
            return false;
        }
        if ($this->cancellation_request_sent()) {
            return false;
        }
        if (!in_array($this->get_license_type_code(), $migrateEligible)) {
            return false;
        }
        return !$this->has_open_renewal_deal;
    }

    /**
     * Returns whether there should be a reminder sent to the customer.
     *
     * @since     1.1.0
     * @access    public
     * @return    boolean    Whether a reminder should be sent.
     */
    public function should_send_reminder()
    {
        if (!$this->renewal_reminder_sent() && $this->get_renewal_state() === self::NEEDS_RENEWAL && !$this->migrated) {
            $renewal_date = $this->get_renewal_date();

            if (is_a($renewal_date, 'DateTime')) {
                $now_date = new DateTime();
                $difference = $now_date->diff($renewal_date);

                if ($difference->invert !== 1 && $difference->days <= self::REMINDER_THRESHOLD) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Returns whether there should added a no response deal.
     *
     * @since     1.1.0
     * @access    public
     * @return    boolean    Whether a no response deal should be added.
     */
    public function should_create_no_response_deal()
    {
        if (!$this->renewal_reminder_ignored() && $this->get_renewal_state() === self::NEEDS_RENEWAL && !$this->migrated) {
            $renewal_date = $this->get_renewal_date();

            if (is_a($renewal_date, 'DateTime')) {
                $now_date = new DateTime();
                $difference = $now_date->diff($renewal_date);

                if ($difference->invert !== 1 && $difference->days <= self::NORESPONSE_THRESHOLD) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Retrieves whether there was a renewal notification sent.
     *
     * @since     1.0.0
     * @access    public
     * @return    boolean    True if there was already sent a renewal notification, false otherwise.
     */
    public function renewal_notification_sent()
    {
        if (is_a($this->get_renewal_date(), 'DateTime')) {
            if (is_a($this->renewal_notification_sent, 'DateTime')) {
                if ($this->renewal_notification_sent->diff($this->get_renewal_date())->days == 0) {
                    return true;
                }
                return false;
            }
            return false;
        }
        return true;
    }

    /**
     * Retrieves whether there was a renewal reminder sent.
     *
     * @since     1.1.0
     * @access    public
     * @return    boolean    True if there was already sent a renewal reminder, false otherwise.
     */
    public function renewal_reminder_sent()
    {
        if (is_a($this->get_renewal_date(), 'DateTime')) {
            if (is_a($this->renewal_reminder_sent, 'DateTime')) {
                if ($this->renewal_reminder_sent->diff($this->get_renewal_date())->days == 0) {
                    return true;
                }
                return false;
            }
            return false;
        }
        return true;
    }

    /**
     * Retrieves whether the licensee requested a license cancellation.
     *
     * @since     1.1.0
     * @access    public
     * @return    boolean    True if the user sent a cancellation request.
     */
    public function cancellation_request_sent()
    {
        if (is_a($this->get_renewal_date(), 'DateTime')) {
            if (is_a($this->cancellation_request_sent, 'DateTime')) {
                if ($this->cancellation_request_sent->diff($this->get_renewal_date())->days == 0) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Retrieves whether the licensee ignored any renewal notifications.
     *
     * @since     1.1.0
     * @access    public
     * @return    boolean    True if the user ignored any notifications.
     */
    public function renewal_reminder_ignored()
    {
        if (is_a($this->get_renewal_date(), 'DateTime')) {
            if (is_a($this->renewal_reminder_ignored, 'DateTime')) {
                if ($this->renewal_reminder_ignored->diff($this->get_renewal_date())->days == 0) {
                    return true;
                }
                return false;
            }
            return false;
        }
        return true;
    }

    /**
     * Gets the renewal price.
     *
     * @since     1.0.0
     * @access    public
     * @return    integer    The renewal price of the license.
     */
    public function get_renewal_price()
    {
        return $this->renewal_price;
    }

    /**
     * Gets the renewal currency.
     *
     * @since     1.0.0
     * @access    public
     * @return    string    The renewal currency of the license.
     */
    public function get_renewal_currency()
    {
        return $this->renewal_currency;
    }

    /**
     * Gets the activations.
     *
     * @since     1.0.0
     * @access    public
     * @return    array     The activations of the license.
     */
    public function get_activations()
    {
        return $this->activations;
    }

    /**
     * Gets renewal date.
     *
     * Returns the payment due date if available, the expiration date otherwise. May be null
     * if both values are empty in LimeLM.
     *
     * @since     1.0.0
     * @access    public
     * @return    DateTime    The renewal date of the license.
     */
    public function get_renewal_date()
    {
        return $this->payment_due ?: $this->expiration_date;
    }

    /**
     * Gets the days left before the license expires.
     *
     * @since     1.0.0
     * @access    public
     * @return    integer    The days left before the license expires.
     */
    public function get_days_left()
    {
        // If there is no renewal date, there are infinite days left.
        $renewal_date = $this->get_renewal_date();
        if (!is_a($renewal_date, 'DateTime')) {
            return -1;
        }

        // Caluculate the difference between now and the renewal date.
        $now_date = new DateTime();
        $difference = $now_date->diff($renewal_date);

        if ($difference->invert != 1) {
            return $difference->days;
        }

        // If the renewal date is in the past, there are no days left.
        return 0;
    }

    /**
     * Gets the formatted renewal date.
     *
     * @since     1.0.0
     * @access    public
     * @param     string     $format    Optional. The format in which the renewal date should be returned.
     * @return    integer               The formatted renewal date of the license.
     */
    public function get_formatted_renewal_date($format = 'd-M-Y')
    {
        if (!is_a($this->get_renewal_date(), 'DateTime')) {
            return;
        }
        return $this->get_renewal_date()->format($format);
    }

    /**
     * Gets the renewal label.
     *
     * @since     1.0.0
     * @access    public
     * @return    integer    The renewal label of the license.
     */
    public function get_renewal_label()
    {
        if (!is_a($this->get_renewal_date(), 'DateTime')) {
            return;
        }

        if ($this->cancellation_request_sent() || $this->migrated) {
            return 'Expires on';
        } elseif ($this->is_perpetual()) {
            return 'Maintenance renewal';
        }
        return 'License renewal';
    }
}
