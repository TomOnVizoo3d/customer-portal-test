<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!is_a($license, 'Vizoo_LicenseSpring_License')) {
    exit;
}

?>

<form id="vizoo-licensespring-license-popup-form" action="" method="POST">
    <fieldset class="vizoo-licensespring-license-popup-fieldset">
        License-ID: <span id="vizoo-licensespring-license-cancellation-id"><?= $license->get_id() ?></span><br />
        Expiration date: <span id="vizoo-licensespring-license-cancellation-expiration-date"><?= $license->get_formatted_renewal_date() ?></span>
    </fieldset>
    <fieldset class="vizoo-licensespring-license-popup-fieldset">
        If you're sure you'd like to cancel your subscription, please note the following and confirm below:
        <ul>
            <li>Your xTex subscription will remain active until <?= $license->get_formatted_renewal_date() ?> and you will no longer be charged.</li>
            <li>After this date, you won't be able to use xTex anymore.</li>
            <li>Come back any time!</li>
        </ul>
    </fieldset>
    <fieldset class="vizoo-licensespring-license-popup-fieldset">
        <label for="vizoo-licensespring-license-cancellation-comment">Why do you want to cancel your subscription?</label>
        <textarea id="vizoo-licensespring-license-cancellation-comment" name="vizoo_licensespring_comment"></textarea>
    </fieldset>
</form>
<section id="vizoo-licensespring-license-popup-control">
    <button id="vizoo-licensespring-license-cancellation-button-confirm" class="button-medium secondary" onclick="licenseSpring.confirmCancellation(<?= $license->get_id() ?>, '<?= wp_create_nonce('vizoo_licensespring_confirm_cancellation-' . $license->get_id()) ?>');"><i class="fa fa-exclamation-triangle fa-fw"></i> Confirm cancellation</button>
    <button id="vizoo-licensespring-license-cancellation-button-cancel" class="button-medium" onclick="licenseSpring.closeLicensePopup();"><i class="fa fa-times fa-fw"></i> Back</button>
</section>