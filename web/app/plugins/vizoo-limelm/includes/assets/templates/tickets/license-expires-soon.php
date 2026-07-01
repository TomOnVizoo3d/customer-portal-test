<?php

/**
 * This is the template file for the content of the ticket that is created on the JIRA
 * helpdesk when a license is expiring soon.
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

if (!is_a($license, 'Vizoo_LimeLM_License')) {
    exit;
}

?>
Details of the license:
ID: <?= $license->get_id() . PHP_EOL ?>
Key: <?= $license->get_key() . PHP_EOL ?>
Licensee: <?= $license->get_licensee() . PHP_EOL ?>
Expires on: <?= $license->get_formatted_renewal_date() . PHP_EOL ?>

(This is an automatically generated message)