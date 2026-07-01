<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!is_a($license, 'Vizoo_LicenseSpring_License')) {
    exit;
}

require_once 'countries.php';

?>

<form id="vizoo-licensespring-license-popup-form" action="" method="POST">
    <fieldset class="vizoo-licensespring-license-popup-fieldset">
        License-ID: <span id="vizoo-licensespring-license-renewal-id"><?= $license->get_id() ?></span><br />
        Expiration date: <span id="vizoo-licensespring-license-renewal-expiration-date"><?= $license->get_formatted_renewal_date() ?></span>
    </fieldset>
    <fieldset class="vizoo-licensespring-license-popup-fieldset">
        <label class="vizoo-licensespring-license-renewal-label">How long would you like to extend your license / maintenance?</label>
        <select id="vizoo-licensespring-license-renewal-period" class="vizoo-licensespring-license-renewal-selection" name="vizoo_licensespring_period" onchange="licenseSpring.changeExtensionPeriod(this);">
            <option value="1" selected="selected">12 Months</option>
            <option value="2">24 Months (-5% discount)</option>
            <option value="3">36 Months (-10% discount)</option>
        </select>
    </fieldset>
    <fieldset class="vizoo-licensespring-license-popup-fieldset">
        Price: <span id="vizoo_licensespring_license_renewal_price"><?= number_format($license->get_renewal_price(), 0) ?> <?= $license->get_renewal_currency() ?></span><br />
        New expiration date: <span id="vizoo-licensespring-license-renewal-new-date"><?= $license->get_renewal_date()->modify('+1 year')->format('d-M-Y') ?></span>
    </fieldset>
    <fieldset id="vizoo-licensespring-license-renewal-sendme" class="vizoo-licensespring-license-popup-fieldset">
        Please send me:<br />
        <input id="vizoo-licensespring-license-renewal-quotation" name="vizoo_licensespring_type" type="radio" value="quotation" checked="checked" onchange="licenseSpring.changeRenewalRequestType(this)" />
        <label for="vizoo-licensespring-license-renewal-quotation"> A quotation</label><br />
        <input id="vizoo-licensespring-license-renewal-invoice" name="vizoo_licensespring_type" type="radio" value="invoice" onchange="licenseSpring.changeRenewalRequestType(this)" />
        <label for="vizoo-licensespring-license-renewal-invoice"> An invoice</label>
    </fieldset>
    <fieldset class="vizoo-licensespring-license-popup-fieldset vizoo-licensespring-license-popup-contact">
        <div>
            <label for="vizoo-licensespring-license-renewal-contact-first-name">First name</label>
            <input id="vizoo-licensespring-license-renewal-contact-first-name" name="vizoo_licensespring_contact_first_name" class="needs-validation" onblur="licenseSpring.validateField(this)" />
        </div>
        <div>
            <label for="vizoo-licensespring-license-renewal-contact-last-name">Last name</label>
            <input id="vizoo-licensespring-license-renewal-contact-last-name" name="vizoo_licensespring_contact_last_name" class="needs-validation" onblur="licenseSpring.validateField(this)" />
        </div>
        <div>
            <label for="vizoo-licensespring-license-renewal-contact-mail">Email</label>
            <input id="vizoo-licensespring-license-renewal-contact-mail" name="vizoo_licensespring_contact_mail" class="needs-validation" onblur="licenseSpring.validateField(this)" />
        </div>
        <div>
            <label for="vizoo-licensespring-license-renewal-contact-company">Company Name</label>
            <input id="vizoo-licensespring-license-renewal-contact-company" name="vizoo_licensespring_contact_company" class="needs-validation" onblur="licenseSpring.validateField(this)" />
        </div>
        <div id="vizoo-licensespring-license-renewal-vat" style="display:none">
            <label for="vizoo-licensespring-license-renewal-contact-vat">VAT Number (EU only)</label>
            <input id="vizoo-licensespring-license-renewal-contact-vat" name="vizoo_licensespring_contact_vat" />
        </div>
    </fieldset>
    <fieldset class="vizoo-licensespring-license-popup-fieldset vizoo-licensespring-license-popup-contact">
        Billing address
        <div>
            <label for="vizoo-licensespring-license-renewal-billing-street">Street</label>
            <input id="vizoo-licensespring-license-renewal-billing-street" name="vizoo_licensespring_billing_street" class="needs-validation" onblur="licenseSpring.validateField(this)" />
        </div>
        <div>
            <label for="vizoo-licensespring-license-renewal-billing-zip">Zip code, City</label>
            <input id="vizoo-licensespring-license-renewal-billing-zip" name="vizoo_licensespring_billing_zip" />
            <input id="vizoo-licensespring-license-renewal-billing-city" name="vizoo_licensespring_billing_city" class="needs-validation" onblur="licenseSpring.validateField(this)" />
        </div>
        <div>
            <label for="vizoo-licensespring-license-renewal-billing-state">State</label>
            <input id="vizoo-licensespring-license-renewal-billing-state" name="vizoo_licensespring_billing_state" />
        </div>
        <div>
            <label for="vizoo-licensespring-license-renewal-billing-country">Country</label>
            <select id="vizoo-licensespring-license-renewal-billing-country" class="vizoo-licensespring-license-renewal-selection needs-validation" name="vizoo_licensespring_billing_country" onchange="licenseSpring.validateForm()">
                <option value="" selected="selected" disabled>Please select...</option>
                <?php foreach ($all_countries as $code => $name) : ?>
                    <option value="<?= $code ?>"><?= $name ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </fieldset>
    <fieldset id="vizoo-licensespring-license-renewal-po" class="po-upload-container" style="display:none">
        <div class="po-inputs">
            <div class="po-number">
                <label for="vizoo-licensespring-license-renewal-po-number">PO number (optional)</label>
                <input id="vizoo-licensespring-license-renewal-po-number" name="vizoo_licensespring_po_number" />
            </div>
            <div class="po-file">
                <label id="vizoo-licensespring-license-renewal-upload" class="button-medium" for="vizoo-licensespring-license-renewal-po-file">
                    <i class="fa fa-lg fa-upload fa-fw"></i> <span>Upload</span>
                </label>
                <input id="vizoo-licensespring-license-renewal-po-file" style="visibility:hidden;position:absolute;" class="needs-validation" name="vizoo-licensespring-license-renewal-po-file" type="file" accept=".pdf,.png,.jpg,.jpeg,.tif,.tiff" onchange="licenseSpring.updateFile(this)" />
            </div>
        </div>
        <span>
            Maximum file size: 5 MB
        </span>
    </fieldset>
    <fieldset id="vizoo-licensespring-license-popup-disclaimer-quote" class="vizoo-licensespring-license-popup-fieldset">
        <?php if (!empty($license->get_weclapp_customer_number())) : ?>
            Please note that by confirming you will receive a quote by email from <span style="font-style:italic;">service@customers.vizoo3d.com</span>.<br />
        <?php endif; ?>
    </fieldset>
</form>
<section id="vizoo-licensespring-license-popup-control">
    <div class="control-content">
        <span id="vizoo-licensespring-license-popup-disclaimer-invoice" class="invoice-disclaimer">
            Please note that by confirming, a request will be sent to our licensing department.<br />
        </span>
        <div class="btn-container">
            <button id="vizoo-licensespring-license-renewal-button-confirm" class="button-medium" disabled="disabled" onclick="licenseSpring.confirmRenew(<?= $license->get_id() ?>, '<?= wp_create_nonce('vizoo_licensespring_confirm_renewal-' . $license->get_id()) ?>');"><i class="fa fa-check fa-fw"></i> <span id="vizoo-licensespring-license-popup-main-action-label" style="min-width: 91%;">Get quote</span></button>
            <button id="vizoo-licensespring-license-renewal-button-cancel" class="button-medium secondary" onclick="licenseSpring.closeLicensePopup();"><i class="fa fa-times fa-fw"></i> Cancel</button>
        </div>
    </div>
</section>
<input type="hidden" id="vizoo-licensespring-license-info-expiration-date" value="<?= $license->get_formatted_renewal_date('c') ?>" />
<input type="hidden" id="vizoo-licensespring-license-info-renewal-price" value="<?= $license->get_renewal_price() ?>" />
<input type="hidden" id="vizoo-licensespring-license-info-renewal-currency" value="<?= $license->get_renewal_currency() ?>" />