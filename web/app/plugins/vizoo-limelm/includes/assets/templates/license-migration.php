<?php

/**
 * The license renewal window, displayed when the customer requests to renew a license.
 *
 * It shows basic information about the license (such as id, key, expiration date) and
 * let's the user decide how many activations and how long he would like to renew. The
 * price is calculated dynamically (if JavaScript is enabled). The user can also choose
 * whether he would like an invoice or a quotation and also add an additional comment.
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
        <b>License-ID</b>: <span id="vizoo-limelm-license-renewal-id"><?= $license->get_id() ?></span><br />
        <b>Key</b>:
        <pre id="vizoo-limelm-license-renewal-key"><?= $license->get_key() ?></pre><br />
        <b>Maintenance expiration date</b>: <span id="vizoo-limelm-license-renewal-expiration-date"><?= $license->get_formatted_renewal_date() ?></span>
    </fieldset>
    <fieldset class="vizoo-limelm-license-popup-fieldset">
        <p>
            Please note that starting 2024, xTex has moved to a new licensing system.
            This means, your perpetual license will no longer be supported with
            software updates. To use xTex 2.8 and later, you will need a subscription
            plan.
        </p>
        <p>
            Connect with our sales team to get your <b>special subscription offer by
                clicking "Get quote" below</b>.
        </p>
    </fieldset>
    <fieldset class="vizoo-limelm-license-popup-fieldset vizoo-limelm-license-popup-special-request">
        <label for="vizoo-limelm-license-renewal-special-request">Special request</label>
        <textarea id="vizoo-limelm-license-renewal-special-request" name="vizoo_limelm_special_request"></textarea>
    </fieldset>
    <?php if ($noscript) : ?>
        <input type="hidden" name="vizoo_limelm_action" value="migrate_license" />
        <input type="hidden" name="vizoo_limelm_license_id" value="<?= $license->get_id() ?>" />
        <input type="hidden" name="nonce" value="<?= wp_create_nonce('vizoo_limelm_confirm_renewal-' . $license->get_id()) ?>" />
        <button type="submit" id="vizoo-limelm-license-renewal-submission" class="vizoo-limelm-license-renewal-button"><i class="fa fa-check fa-fw"></i> Send</button>
        <a class="vizoo-limelm-license-renewal-cancel secondary" href="?noscript=1"><i class="fa fa-times fa-fw"></i> Cancel</a>
    <?php endif; ?>
</form>
<?php if (!$noscript) : ?>
    <section id="vizoo-limelm-license-popup-control">
        <button id="vizoo-limelm-license-renewal-button-confirm" class="button-medium" onclick="confirmMigration(<?= $license->get_id() ?>, '<?= wp_create_nonce('vizoo_limelm_confirm_renewal-' . $license->get_id()) ?>');"><i class="fa fa-check fa-fw"></i> <span id="vizoo-limelm-license-popup-main-action-label">Get quote</span></button>
        <button id="vizoo-limelm-license-renewal-button-cancel" class="button-medium secondary" onclick="closeLicensePopup();"><i class="fa fa-times fa-fw"></i> Cancel</button>
    </section>
    <input type="hidden" id="vizoo-limelm-license-info-expiration-date" value="<?= $license->get_formatted_renewal_date('c') ?>" />
<?php endif; ?>