<?php

/**
 * This is the template file for the note on pipedrive if a customer decides to cancel a
 * maintenance or license.
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

$requestor_email = wp_get_current_user()->user_email;

if (is_a($license, 'Vizoo_LimeLM_License')) :
    ?>
    <p><?= $requestor_email ?> (<?= $license->get_licensee(); ?>) requested a cancellation of the following maintenance or license:</p>
    <strong>ID</strong>: <?= $license->get_id(); ?><br />
    <strong>Key</strong>: <?= $license->get_key(); ?><br />
    <strong>License type</strong>: <?= $license->get_license_type(); ?><br />
    <strong>Licensee</strong>: <?= $license->get_licensee(); ?><br />
    <strong>Expires on</strong>: <?= $license->get_formatted_renewal_date(); ?><br />
    <?php if (!empty($comment)) : ?>
        The licensee has added the following note:<br />
        <?= $comment ?>
    <?php endif; ?>
<?php elseif (is_a($license, 'Vizoo_LicenseSpring_License')) :
        ?>
    <p><?= $requestor_email ?> (<?= $license->get_licensee(); ?>) requested a cancellation of the following subscription:</p>
    <strong>ID</strong>: <?= $license->get_id(); ?><br />
    <strong>Users</strong>: <?= implode(', ', $license->get_users()); ?><br />
    <strong>License type</strong>: <?= $license->is_design_license() ? 'Design' : 'Production' ?><br />
    <strong>Licensee</strong>: <?= $license->get_licensee(); ?><br />
    <strong>Expires on</strong>: <?= $license->get_formatted_renewal_date(); ?><br />
    <?php if (!empty($comment)) : ?>
        The licensee has added the following note:<br />
        <?= $comment ?>
    <?php endif; ?>
<?php
endif;
?>