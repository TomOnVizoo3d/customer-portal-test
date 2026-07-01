<?php

/**
 * This is the template file for the note on pipedrive if a customer doesn't respond to
 * the notifications sent to him regarding maintenance or license expiration.
 *
 * @link       https://customers.vizoo3d.com
 * @since      1.0.0
 *
 * @package    Vizoo_Pipedrive
 * @subpackage Vizoo_Pipedrive/assets/templates/deals
 */

if (!defined('ABSPATH')) {
    exit;
}

if (is_a($license, 'Vizoo_LimeLM_License')) :
    ?>
    <p>The maintenance or license of <?= $license->get_licensee(); ?> is expiring in <?= $license->get_days_left() ?> days, but there was no response regarding the renewal of the license. Details of the license:</p>
    <?php if (!empty($users)) : ?>
        <p>Contact(s) registered as license admins in the Customer Portal:</p>
        <?php foreach ($users as $user) : ?>
            <strong><?= $user->display_name ?></strong>: <?= $user->user_email ?><br />
        <?php endforeach; ?>
    <?php else : ?>
        <p>There are no contacts registered as license admins in the Customer Portal. Please refer to the contact person in LimeLM.</p>
    <?php endif; ?>
    <strong>ID</strong>: <?= $license->get_id(); ?><br />
    <strong>Key</strong>: <?= $license->get_key(); ?><br />
    <strong>License type</strong>: <?= $license->get_license_type(); ?><br />
    <strong>Licensee</strong>: <?= $license->get_licensee(); ?><br />
    <strong>Expires on</strong>: <?= $license->get_formatted_renewal_date(); ?><br />
    <strong>Price (per year)</strong>: <?= $license->get_renewal_price(); ?><br />
<?php elseif (is_a($license, 'Vizoo_LicenseSpring_License')) :
    ?>
    <p>The subscription of <?= $license->get_licensee(); ?> is expiring in <?= $license->get_days_left() ?> days, but there was no response regarding the renewal of the subscription. Details of the license:</p>
    <?php if (!empty($users)) : ?>
        <p>Contact(s) registered as license admins in the Customer Portal:</p>
        <?php foreach ($users as $user) : ?>
            <strong><?= $user->display_name ?></strong>: <?= $user->user_email ?><br />
        <?php endforeach; ?>
    <?php else : ?>
        <p>There are no contacts registered as license admins in the Customer Portal. Please refer to the contact person in LicenseSpring.</p>
    <?php endif; ?>
    <strong>ID</strong>: <?= $license->get_id(); ?><br />
    <strong>Users</strong>: <?= implode(', ', $license->get_users()); ?><br />
    <strong>License type</strong>: <?= $license->is_design_license() ? 'Design' : 'Production' ?><br />
    <strong>Licensee</strong>: <?= $license->get_licensee(); ?><br />
    <strong>Expires on</strong>: <?= $license->get_formatted_renewal_date(); ?><br />
    <strong>Price (per year)</strong>: <?= $license->get_renewal_price(); ?><br />
<?php
    endif;
?>