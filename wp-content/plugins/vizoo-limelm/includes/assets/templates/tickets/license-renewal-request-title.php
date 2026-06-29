<?php

/**
 * This is the template file for the title of the ticket that is created on the JIRA
 * helpdesk when a user requests a license renewal.
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
License renewal (#<?= $license->get_id() ?>)