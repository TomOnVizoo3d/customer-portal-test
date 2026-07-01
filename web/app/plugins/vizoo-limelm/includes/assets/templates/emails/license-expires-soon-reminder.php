<?php

/**
 * This is the template file for reminder emails to the customer about impending
 * license expiration.
 * This is the plaintext part of the email.
 * Since there is no multipart MIME email functionality available (yet), this is the only
 * template that is going to be used.
 *
 * Please note: Since PHP discards line breaks if a PHP block is at the end of a line for
 * some reason it is required to then add the PHP_EOL to the block.
 *
 * Example:
 * ID: <?= $license->get_id() ?>
 * Key: Somethingelse
 *
 * Will be displayed "ID: 0123456789Key: Somethingelse"
 *
 * To restore the linebreak, use:
 * ID: <?= $license->get_id() . PHP_EOL ?>
 * Key: Somethingelse
 *
 * This is not required if there is any character (except a whitespace) behind the PHP
 * block.
 *
 * @link       https://customers.vizoo3d.com
 * @since      1.1.0
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

?>
Hi there,

Your xTex <?= $license->is_perpetual() ? 'maintenance plan' : 'software license' ?> is expiring in <?= $license->get_days_left() ?> days. Details of the license:
ID: <?= $license->get_id() . PHP_EOL ?>
Key: <?= $license->get_key() . PHP_EOL ?>
Licensee: <?= $license->get_licensee() . PHP_EOL ?>
Expires on: <?= $license->get_formatted_renewal_date() . PHP_EOL ?>

If you wish to renew your <?= $license->is_perpetual() ? 'maintenance plan' : 'software license' ?> please visit the Customer Portal at https://customers.vizoo3d.com/licenses.

Did you encounter any issues doing so? Please don't hesitate to contact our support at https://customers.vizo3d.com/support or write us an email to support@vizoo3d.com.

Do you have questions regarding your license? Contact your Vizoo account manager or write an email to info@vizoo3d.com. Together we'll find a solution that fits your demand.

Kind regards,
Vizoo Customer Service
(This is an automatically generated email)