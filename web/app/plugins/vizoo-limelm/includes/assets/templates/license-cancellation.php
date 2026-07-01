<?php

/**
 *
 *
 * @link       https://customers.vizoo3d.com
 * @since      1.0.0
 *
 * @package    Vizoo_LimeLM
 * @subpackage Vizoo_LimeLM/assets/templates
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!is_a($license, 'Vizoo_LimeLM_License')) {
    exit;
}

$noscript = isset($_GET['noscript']);
?>

<form id="vizoo-limelm-license-popup-form" action="" method="POST">
    <fieldset class="vizoo-limelm-license-popup-fieldset">
        License-ID: <span id="vizoo-limelm-license-cancellation-id"><?= $license->get_id() ?></span><br />
        Key:
        <pre id="vizoo-limelm-license-cancellation-key"><?= $license->get_key() ?></pre><br />
        Activations: <?= $license->get_acts() ?> (<?= $license->get_acts_used() ?> in use)<br />
        Expiration date: <span id="vizoo-limelm-license-cancellation-expiration-date"><?= $license->get_formatted_renewal_date() ?></span>
    </fieldset>
    <fieldset class="vizoo-limelm-license-popup-fieldset">
        <?php if ($license->get_license_type_code() === 'CSWS') : ?>
            If you're sure you'd like to cancel your subscription, please note the following and confirm below:
            <ul>
                <li>Your xTex license will remain active until <?= $license->get_formatted_renewal_date() ?> and you will no longer be charged.</li>
                <li>After this date, you won't be able to use xTex anymore.</li>
                <li>Come back any time!</li>
            </ul>
        <?php else : ?>
            If you're sure you'd like to cancel your software maintenance, please note the following and confirm below:
            <ul>
                <li>Your xTex license will remain active and you will no longer be charged.</li>
                <li>After the expiration date, you won't receive any updates for the xTex software.</li>
                <li>Your Customer Portal and support ticket access will be suspended.</li>
            </ul>
        <?php endif; ?>
    </fieldset>
    <fieldset class="vizoo-limelm-license-popup-fieldset">
        <label for="vizoo-limelm-license-cancellation-comment">Why do you want to cancel this license / maintenance? (optional)</label>
        <textarea id="vizoo-limelm-license-cancellation-comment" name="vizoo_limelm_comment"></textarea>
    </fieldset>
    <?php if ($noscript) : ?>
        <input type="hidden" name="vizoo_limelm_action" value="cancel_license" />
        <input type="hidden" name="vizoo_limelm_license_id" value="<?= $license->get_id() ?>" />
        <input type="hidden" name="nonce" value="<?= wp_create_nonce('vizoo_limelm_confirm_cancellation-' . $license->get_id()) ?>" />
        <button type="submit" id="vizoo-limelm-license-cancellation-submission" class="vizoo-limelm-license-button"><i class="fa fa-exclamation-triangle fa-fw"></i> Confirm cancellation</button>
        <a class="vizoo-limelm-license-popup-cancel" href="?noscript=1"><i class="fa fa-times fa-fw"></i> Back</a>
    <?php endif; ?>
</form>
<?php if (!$noscript) : ?>
    <section id="vizoo-limelm-license-popup-control">
        <button id="vizoo-limelm-license-cancellation-button-confirm" class="button-medium secondary" onclick="confirmCancellation(<?= $license->get_id() ?>, '<?= wp_create_nonce('vizoo_limelm_confirm_cancellation-' . $license->get_id()) ?>');"><i class="fa fa-exclamation-triangle fa-fw"></i> Confirm cancellation</button>
        <button id="vizoo-limelm-license-cancellation-button-cancel" class="button-medium" onclick="closeLicensePopup();"><i class="fa fa-times fa-fw"></i> Back</button>
    </section>
<?php endif; ?>