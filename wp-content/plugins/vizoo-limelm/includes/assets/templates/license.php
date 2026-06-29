<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing displayment of a LimeLM license.
 *
 * @link       https://customers.vizoo3d.com
 * @since      1.0.0
 *
 * @package    Vizoo_LimeLM
 * @subpackage Vizoo_LimeLM/public/partials
 */

if (!defined('ABSPATH')) {
    exit;
}

if (empty($license) || !is_a($license, "Vizoo_LimeLM_License")) {
    exit;
}

$noscript = isset($_REQUEST['noscript']);
?>

<section id="vizoo-limelm-license-<?= $license->get_id() ?>" class="vizoo-limelm-license-wrapper">
    <h2 class="vizoo-limelm-license-title">
        License #<?= $license->get_id() ?>
        <?php if ($license->get_expiration_state() == Vizoo_LimeLM_License::EXPIRES_SOON) : ?>
            <span class="vizoo-limelm-license-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Expires soon</span>
        <?php elseif ($license->get_expiration_state() == Vizoo_LimeLM_License::EXPIRED) : ?>
            <span class="vizoo-limelm-license-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Expired</span>
        <?php endif; ?>
    </h2>

    <table class="vizoo-limelm-license-details">
        <tr>
            <td class="vizoo-limelm-license-info-label">Key</td>
            <td>
                <pre class="vizoo-limelm-license-key" <?= $noscript ? '' : ' onclick="selectText(this);"' ?>><?= $license->get_key() ?></pre>
            </td>
        </tr>
        <tr>
            <td class="vizoo-limelm-license-info-label">Type</td>
            <td><?= $license->get_type() ?> (<?= $license->is_floating() ? 'Floating' : 'Single-user' ?>)<?= $license->is_perpetual() ? ', perpetual' : '' ?></td>
        </tr>
        <tr>
            <td class="vizoo-limelm-license-info-label">xTex Scan Interface</td>
            <td><?= $license->is_software_only() ? 'No' : 'Yes' ?></td>
        </tr>
        <tr>
            <td class="vizoo-limelm-license-info-label">Licensee</td>
            <td><?= $license->get_licensee() ?></td>
        </tr>
        <tr>
            <td class="vizoo-limelm-license-info-label">Activations</td>
            <td><?= $license->get_acts() ?> (<?= $license->get_acts_used() ?> in use)</td>
        </tr>
        <?php if (!empty($license->get_renewal_label())) : ?>
            <tr>
                <td class="vizoo-limelm-license-info-label"><?= $license->get_renewal_label() ?></td>
                <td>
                    <?php if ($license->get_expiration_state() == Vizoo_LimeLM_License::EXPIRES_SOON || $license->get_expiration_state() == Vizoo_LimeLM_License::EXPIRED) : ?>
                        <span class="vizoo-limelm-license-expires-soon"><?= $license->get_formatted_renewal_date() ?></span>
                    <?php else : ?>
                        <?= $license->get_formatted_renewal_date() ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endif; ?>
    </table>
    <div id="vizoo-limelm-license-renewal-notice_<?= $license->get_id() ?>" class="vizoo-limelm-license-renewal-notice">
        <?php if ($license->get_renewal_state() == Vizoo_LimeLM_License::RENEWAL_PENDING) : ?>
            <i class="fa fa-info-circle fa-fw"></i> Your renewal request is being processed.
        <?php elseif ($license->get_renewal_state() == Vizoo_LimeLM_License::CANCELLATION_REQUESTED) : ?>
            <i class="fa fa-info-circle fa-fw"></i> Your cancellation request is being processed.
        <?php endif; ?>
    </div>
    <div id="vizoo-limelm-license-actions_<?= $license->get_id() ?>" class="vizoo-limelm-license-actions">
        <?php if ($license->can_request_migration()) : ?>
            <button class="button-medium" onclick="renewLicense( <?= $license->get_id() ?>, '<?= wp_create_nonce('vizoo_limelm_get_renewal_form') ?>' );">
                <i class="fa-solid fa-repeat"></i> <span>Migration options</span>
            </button>
            <button class="button-medium secondary" onclick="cancelLicense( <?= $license->get_id() ?>, '<?= wp_create_nonce('vizoo_limelm_get_cancellation_form') ?>', '<?= $license->get_license_type_code() ?>' );">
                <i class="fa-solid fa-ban"></i> <span>Cancel maintenance</span>
            </button>
        <?php endif; ?>
    </div>

    <h3>Activations</h3>
    <?php if (!empty($license->get_activations())) : ?>
        <table class="vizoo-limelm-activations">
            <tr>
                <th class="vizoo-limelm-activation-index">#</th>
                <th class="vizoo-limelm-activation-platform"></th>
                <th class="vizoo-limelm-activation-address">IP address</th>
                <th class="vizoo-limelm-activation-date">Activation date</th>
                <th class="vizoo-limelm-activation-deactivate">Deactivate</th>
            </tr>
            <?php foreach ($license->get_activations() as $index => $activation) : ?>
                <tr class="vizoo-limelm-hoverable">
                    <td class="vizoo-limelm-activation-index"><?= $index + 1 ?></td>
                    <td>
                        <i class="fa fa-lg fa-<?= $activation->get_platform() ?> fa-fw" title="<?= $activation->get_platform_title() ?>"></i>
                    </td>
                    <td>
                        <pre><?= $activation->get_ip() ?></pre>
                    </td>
                    <td><?= $activation->get_formatted_date() ?></td>
                    <?php if ($activation->is_deactivatable()) : ?>
                        <?php if ($noscript) : ?>
                            <td class="deactivation-cell">
                                <a href="?noscript=1&activation_id=<?= $activation->get_id() ?>&license_id=<?= $license->get_id() ?>&nonce=<?= wp_create_nonce('vizoo_limelm_conirm_deactivation-' . $activation->get_id()) ?>"><i class="fa fa-lg fa-times fa-fw delete-cross" title="Deactivate this activation"></i></a>
                            </td>
                        <?php else : ?>
                            <td id="deactivation-cell-<?= $activation->get_id() ?>" class="deactivation-cell" data-actid="<?= $activation->get_id() ?>">
                                <i class="fa fa-lg fa-times fa-fw delete-cross" onclick="deactivateLicense( <?= $activation->get_id() ?>, 0, <?= $license->get_id() ?>,'<?= wp_create_nonce('vizoo_limelm_deactivate_activation-' . $activation->get_id()) ?>' );" title="Deactivate this activation"></i>
                            </td>
                        <?php endif; ?>
                    <?php else : ?>
                        <td class="deactivation-cell">
                            <i class="fa fa-lg fa-info fa-fw" title="This license was either activated offline or activated with an older version of xTex. Please use the license deactivator to free your license."></i>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else : ?>
        No activations in use.
    <?php endif; ?>
</section>