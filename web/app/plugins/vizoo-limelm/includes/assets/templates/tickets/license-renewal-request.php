<?php

/**
 * This is the template file for the content of the ticket that is created on the JIRA
 * helpdesk when a user requests a license renewal.
 *
 * Please note: Since PHP discards line breaks if a PHP block is at the end of a line for
 * some reason it is required to then add the PHP_EOL to the block.
 *
 * Example:
 * ID: <?= $license->get_id() ...
 * Key: Somethingelse
 *
 * Will be displayed "ID: 0123456789Key: Somethingelse"
 *
 * To restore the linebreak, use:
 * ID: <?= $license->get_id() . PHP_EOL ...
 * Key: Somethingelse
 *
 * This is not required if there is any character (except a whitespace) behind the PHP
 * block.
 *
 * @link       https://customers.vizoo3d.com
 * @since      1.0.0
 *
 * @package    Vizoo_LimeLM
 * @subpackage Vizoo_LimeLM/assets/templates/tickets
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!is_a($license, 'Vizoo_LimeLM_License') || !isset($license_period) || !isset($license_comment) || !isset($license_price)) {
    exit;
}

?>
Hello, please renew my license (or maintenance):

ID: #<?= $license->get_id() . PHP_EOL ?>
Key: <?= $license->get_key() . PHP_EOL ?>
Expiration date: <?= $license->get_formatted_renewal_date() . PHP_EOL ?>

Length: <?= ($license_period * 12) ?> Months

Price: <?= $license_price . PHP_EOL ?>
New expiration date: <?= (clone $license->get_renewal_date())->modify('+' . $license_period . ' years')->format('d-M-Y') . PHP_EOL ?>
<?php if ($license_comment != '') : ?>

    <?= $license_comment ?>
<?php endif; ?>