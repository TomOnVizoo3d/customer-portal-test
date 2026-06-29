<?php

if (!defined('ABSPATH')) {
    exit;
}

class Vizoo_LicenseSpring_License
{
    public const NOTIFICATION_THRESHOLD = 31; // days
    public const REMINDER_THRESHOLD = 15; // days
    public const NORESPONSE_THRESHOLD = 7; // days

    public const ACTIVE = 'active';
    public const EXPIRES_SOON = 'expires-soon';
    public const EXPIRED = 'expired';

    public const NEEDS_RENEWAL = 'needs-renewal';
    public const RENEWAL_PENDING = 'renewal-pending';
    public const CANCELLATION_REQUESTED = 'cancellation-requested';
    public const NOT_RENEWABLE = 'not-renewable';

    private $id;
    private $order;
    private $renewal_date;
    private $features;
    private $contact_email;
    private $raw_metadata;
    private $users;
    private $seats;

    private $renewal_notification_sent;
    private $renewal_reminder_sent;
    private $renewal_reminder_ignored;
    private $cancellation_request_sent;
    private $has_open_renewal_deal;

    private $renewal_price;
    private $renewal_currency;
    private $licensee;
    private $company_id;
    private $account_manager;
    private $weclapp_customer_number;
    private $tier;

    public function __construct($raw_license, $open_renewal_deals = [])
    {
        $this->id = $raw_license['id'];
        $this->contact_email = $raw_license['customer']['email'];
        $this->order = $raw_license['order'];
        $this->seats = $raw_license['floating_users'];

        $this->renewal_date = new DateTime($raw_license['validity_period']);

        $metadata = $raw_license['metadata'];
        $this->renewal_notification_sent = (!empty(@$metadata['web']['renewal']['expirationNotificationSent']) ? new DateTime($metadata['web']['renewal']['expirationNotificationSent']) : null);
        $this->renewal_reminder_sent = (!empty(@$metadata['web']['renewal']['expirationReminderSent']) ? new DateTime($metadata['web']['renewal']['expirationReminderSent']) : null);
        $this->renewal_reminder_ignored = (!empty(@$metadata['web']['renewal']['expirationReminderIgnored']) ? new DateTime($metadata['web']['renewal']['expirationReminderIgnored']) : null);
        $this->cancellation_request_sent = (!empty(@$metadata['web']['renewal']['cancellationRequestSent']) ? new DateTime($metadata['web']['renewal']['cancellationRequestSent']) : null);

        $this->renewal_price = @$metadata['web']['renewalAmount'];
        $this->renewal_currency = @$metadata['web']['renewalCurrency'];
        $this->licensee = @$metadata['web']['company'];
        $this->company_id = @$metadata['web']['pipedriveOrganizationId'];
        $this->account_manager = @$metadata['web']['manager'];
        $this->weclapp_customer_number = @$metadata['web']['weclappCustomerNumber'];
        $this->tier = @$metadata['web']['tier'];

        $this->features = array_map(fn ($item) => $item['product_feature']['code'], $raw_license['product_features']);
        $this->users = array_map(fn ($item) => $item['true_email'], $raw_license['license_users']);

        $this->has_open_renewal_deal = !empty($open_renewal_deals) && !empty(@$metadata['web']['renewal']['dealId']) && in_array($metadata['web']['renewal']['dealId'], $open_renewal_deals, true);
        $this->raw_metadata = $metadata;
    }

    public function get_id()
    {
        return $this->id;
    }

    public function get_contact_email()
    {
        return $this->contact_email;
    }

    public function get_users()
    {
        return $this->users;
    }

    public function get_seats()
    {
        return $this->seats;
    }

    public function get_order()
    {
        return $this->order;
    }

    public function get_licensee()
    {
        return $this->licensee;
    }

    public function get_raw_metadata()
    {
        return $this->raw_metadata;
    }

    public function set_raw_metadata($metadata)
    {
        $this->raw_metadata = $metadata;
    }

    public function get_company_id()
    {
        return $this->company_id;
    }

    public function get_account_manager()
    {
        return $this->account_manager;
    }

    public function is_design_license()
    {
        return !in_array('xtexswcapture', $this->features, true);
    }

    public function get_weclapp_customer_number()
    {
        return $this->weclapp_customer_number;
    }

    public function get_tier()
    {
        return $this->tier;
    }

    public function get_expiration_state()
    {
        $now_date = new DateTime();
        $difference = $now_date->diff($this->renewal_date);

        if ($difference->invert === 1) {
            return self::EXPIRED;
        }
        if ($difference->days <= self::NOTIFICATION_THRESHOLD) {
            return self::EXPIRES_SOON;
        }

        return self::ACTIVE;
    }

    public function get_renewal_state()
    {
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

    public function is_user_renewable()
    {
        if ($this->cancellation_request_sent()) {
            return false;
        }
        if (empty($this->renewal_currency) || empty($this->renewal_price)) {
            return false;
        }
        return !$this->has_open_renewal_deal;
    }

    public function set_renewal_deal($value)
    {
        $this->has_open_renewal_deal = $value;
    }

    public function should_send_reminder()
    {
        if ($this->renewal_reminder_sent() || $this->get_renewal_state() !== self::NEEDS_RENEWAL) {
            return false;
        }
        $now_date = new DateTime();
        $difference = $now_date->diff($this->renewal_date);

        if ($difference->invert === 1 || $difference->days > self::REMINDER_THRESHOLD) {
            return false;
        }

        return true;
    }

    public function should_create_no_response_deal()
    {
        if ($this->renewal_reminder_ignored() || $this->get_renewal_state() !== self::NEEDS_RENEWAL) {
            return false;
        }

        $now_date = new DateTime();
        $difference = $now_date->diff($this->renewal_date);

        if ($difference->invert === 1 || $difference->days > self::NORESPONSE_THRESHOLD) {
            return false;
        }

        return true;
    }

    public function renewal_notification_sent()
    {
        return is_a($this->renewal_notification_sent, 'DateTime') && $this->renewal_notification_sent->diff($this->renewal_date)->days === 0;
    }

    public function renewal_reminder_sent()
    {
        return is_a($this->renewal_reminder_sent, 'DateTime') && $this->renewal_reminder_sent->diff($this->renewal_date)->days === 0;
    }

    public function cancellation_request_sent()
    {
        return is_a($this->cancellation_request_sent, 'DateTime') && $this->cancellation_request_sent->diff($this->renewal_date)->days === 0;
    }

    public function renewal_reminder_ignored()
    {
        return is_a($this->renewal_reminder_ignored, 'DateTime') && $this->renewal_reminder_ignored->diff($this->renewal_date)->days === 0;
    }

    public function get_renewal_price()
    {
        return $this->renewal_price;
    }

    public function get_renewal_currency()
    {
        return $this->renewal_currency;
    }

    public function get_days_left()
    {
        $now_date = new DateTime();
        $difference = $now_date->diff($this->renewal_date);

        if ($difference->invert === 1) {
            // If the renewal date is in the past, there are no days left.
            return 0;
        }

        return $difference->days;
    }

    public function get_renewal_date()
    {
        return clone $this->renewal_date;
    }

    public function get_formatted_renewal_date($format = 'd-M-Y')
    {
        return $this->renewal_date->format($format);
    }
}
