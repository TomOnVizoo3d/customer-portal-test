<?php

/**
 * This is the template file for the title of the reminder emails to the customer
 * about impending license expiration.
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

?>
Reminder: Your xTex <?= $license->is_perpetual() ? 'maintenance plan' : 'software license' ?> is expiring soon.