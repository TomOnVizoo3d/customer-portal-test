<?php

/**
 * This is the template file for the title of the ticket that is created on the JIRA
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
The license #<?= $license->get_id() ?> or license maintenance is expiring soon. (Licensee: <?= $license->get_licensee() ?>)