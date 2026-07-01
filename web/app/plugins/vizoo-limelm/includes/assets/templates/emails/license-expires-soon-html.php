<?php

/**
 * This is the template file for notification emails to the customer about impending
 * license expiration.
 * As there isn't implemented any function of sending multipart MIME emails yet, this
 * file is only a placeholder. For compatability reasons we are better of sending
 * plaintext emails for now.
 * If there is a function implemented for multipart MIME emails later, use this file to
 * construct the HTML part of the email.
 *
 * @link       https://customers.vizoo3d.com
 * @since      1.0.0
 *
 * @package    Vizoo_LimeLM
 * @subpackage Vizoo_LimeLM/assets/templates/emails
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!is_a($license, 'Vizoo_LimeLM_License')) {
    exit;
}
