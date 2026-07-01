<?php

if (!defined('ABSPATH')) {
    exit;
}

if (empty($license) || !is_a($license, "Vizoo_LicenseSpring_License")) {
    exit;
}

?>

<section id="vizoo-licensespring-license-<?= $license->get_id() ?>" class="vizoo-licensespring-license-wrapper">
    <div class="license-wrapper">
        <div class="license-details-container">
            <div class="vizoo-licensespring-license-details">
                <div class="license-header">
                    <h2 class="vizoo-licensespring-license-title">
                        xTex Software
                    </h2>
                    <span>ID: <?= $license->get_id() ?></span>
                </div>
                <div class="license-div-row">
                    <div class="vizoo-licensespring-license-info-label">Status:</div>
                    <div>
                        <?php if ($license->get_expiration_state() == Vizoo_LicenseSpring_License::ACTIVE) : ?>
                            <span class="vizoo-licensespring-license-warning"><i class="fa fa-check-circle" style="color: #00bfbf;"></i><span class="expiry-text">Active</span></span>
                        <?php elseif ($license->get_expiration_state() == Vizoo_LicenseSpring_License::EXPIRES_SOON) : ?>
                            <span class="vizoo-licensespring-license-warning"><i class="fa fa-exclamation-diviangle" style="color: #dfe323"></i></i><span class="expiry-text">Expiring soon</span></span>
                        <?php elseif ($license->get_expiration_state() == Vizoo_LicenseSpring_License::EXPIRED) : ?>
                            <span class="vizoo-licensespring-license-warning"><i class="fa fa-times-circle" style="color: #b62e2e"></i><span class="expiry-text">Expired</span></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="license-div-row">
                    <div class="vizoo-licensespring-liceHnse-info-label">Type:</div>
                    <div><?= $license->is_design_license() ? 'Design' : 'Production' ?></div>
                </div>
                <div class="license-div-row">
                    <div class="vizoo-licensespring-license-info-label">Licensee:</div>
                    <div><?= $license->get_licensee() ?></div>
                </div>
                <div class="license-div-row">
                    <div class="vizoo-licensespring-license-info-label">Renewal date:</div>
                    <div>
                        <?php if ($license->get_expiration_state() == Vizoo_LicenseSpring_License::EXPIRES_SOON || $license->get_expiration_state() == Vizoo_LicenseSpring_License::EXPIRED) : ?>
                            <span class="vizoo-licensespring-license-expires-soon"><?= $license->get_formatted_renewal_date() ?></span>
                        <?php else : ?>
                            <?= $license->get_formatted_renewal_date() ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="license-div-row">
                    <div class="vizoo-licensespring-license-info-label">License seats:</div>
                    <div><?= $license->get_seats() ?></div>
                </div>
                <?php
                $userslist = $license->get_users(); ?>
                <div class="license-div-row">
                    <div class="vizoo-licensespring-license-info-label">Users:</div>
                    <?php if (count($userslist) > 1): ?>
                        <a onclick="licenseSpring.toggleShowUsers('<?= $license->get_id() ?>')">See users</a>
                        <?php else:
                            foreach ($userslist as $user): ?>
                            <span>
                                <?= $user ?>
                            </span>
                    <?php endforeach;
                        endif ?>
                </div>
            </div>
        </div>
        <div id="license-users-<?= $license->get_id() ?>" class="license-users">
            <button onclick="licenseSpring.toggleShowUsers('<?= $license->get_id() ?>')" class="back-btn">
                <i class="fa-solid fa-arrow-left" style="color: #002b2b;"></i>
            </button>
            <ul class="vizoo-licensespring-license-users-list">
                <?php foreach ($license->get_users() as $user) : ?>
                    <li class="single-user-list-item">
                        <?= $user ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="license-info-container">
        <?php if ($license->get_renewal_state() == Vizoo_LicenseSpring_License::RENEWAL_PENDING || $license->get_renewal_state() == Vizoo_LicenseSpring_License::CANCELLATION_REQUESTED) : ?>
            <div id="vizoo-licensespring-license-renewal-notice_<?= $license->get_id() ?>" class="vizoo-licensespring-license-renewal-notice">
                <?php if ($license->get_renewal_state() == Vizoo_LicenseSpring_License::RENEWAL_PENDING) : ?>
                    <i class="fa fa-info-circle fa-fw"></i> Your renewal request is being processed.
                <?php elseif ($license->get_renewal_state() == Vizoo_LicenseSpring_License::CANCELLATION_REQUESTED) : ?>
                    <i class="fa fa-info-circle fa-fw"></i> Your cancellation request is being processed.
                <?php endif; ?>
            </div>
        <?php else : ?>
            <div id="vizoo-licensespring-license-actions_<?= $license->get_id() ?>" class="license-actions">
                <button id="renew-link-<?= $license->get_id() ?>" class="renew-link" onclick="licenseSpring.renewLicense( <?= $license->get_id() ?>, '<?= wp_create_nonce('vizoo_licensespring_get_renewal_form') ?>' );">
                    Renew
                </button>
                <button id="cancel-link-<?= $license->get_id() ?>" class="cancel-link" onclick="licenseSpring.cancelLicense( <?= $license->get_id() ?>, '<?= wp_create_nonce('vizoo_licensespring_get_cancellation_form') ?>');">
                    Cancel
                </button>
            </div>
        <?php endif; ?>
    </div>
    </div>


</section>