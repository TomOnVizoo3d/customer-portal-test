<?php

/**
 * Placeholder
 *
 * Placeholder
 *
 * @link       https://customers.vizoo3d.com
 * @since      1.0.0
 *
 * @package    Vizoo_LimeLM
 * @subpackage Vizoo_LimeLM/includes/assets/templates
 */

if (!defined('ABSPATH')) {
    exit;
}

$noscript = $_GET['noscript'] == '1';

$user = wp_get_current_user();
$userrole = get_user_meta($user->ID, 'vizoo_customerrole', true);
?>
<section id="vizoo-limelm-wrapper">
    <?php if (!$noscript) : ?>
        <noscript>
            It seems that you don't have JavaScript enabled or installed. Please proceed <a href="?noscript=1">here</a>.
        </noscript>
    <?php endif; ?>
    <h2 class="license-heading">Legacy Licenses</h2>
    <div id="vizoo-limelm-overview-loader" class="vizoo-limelm-loader" style="display: none;">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" class="lds-eclipse">
            <path ng-attr-d="{{config.pathCmd}}" ng-attr-fill="{{config.color}}" stroke="none" d="M20 50A30 30 0 0 0 80 50A30 32 0 0 1 20 50" fill="#00bfbf">
                <animateTransform attributeName="transform" type="rotate" calcMode="linear" values="0 50 51;360 50 51" keyTimes="0;1" dur="1s" begin="0s" repeatCount="indefinite"></animateTransform>
            </path>
        </svg>
    </div>
    <?php if ($userrole === "LicenseManager"): ?>
        <section id="vizoo-limelm-licenses-container" class="vizoo-limelm-licenses-container">
            <?php if (empty($licenses) && $noscript == 1) : ?>
                There are no licenses registered for your company. Please contact our support <a href="<?= get_permalink(get_page_by_path('support')) ?>">here</a>.
            <?php else : ?>
                <?php foreach ($licenses as $license) : ?>
                    <?php self::render_license($license); ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    <?php endif; ?>
    <input type="hidden" id="ajax_url" value="<?= admin_url('admin-ajax.php'); ?>" />
    <input type="hidden" id="vizoo_limelm_get_licenses" value="<?= wp_create_nonce('vizoo_limelm_get_licenses'); ?>" />

    <?php if (!$noscript) : ?>
        <div id="vizoo-limelm-license-popup-wrapper" style="display:none;">
            <div id="vizoo-limelm-license-popup-container">
                <div id="vizoo-limelm-license-popup-title-bar">
                    <h3 id="vizoo-limelm-license-popup-title">Loading</h3>
                    <button id="vizoo-limelm-license-popup-button-close" onclick="closeLicensePopup();">
                        <i class="fa fa-times fa-fw"></i>
                    </button>
                </div>
                <div id="vizoo-limelm-license-popup-loader" class="vizoo-limelm-loader" style="display: none;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" class="lds-eclipse">
                        <path ng-attr-d="{{config.pathCmd}}" ng-attr-fill="{{config.color}}" stroke="none" d="M20 50A30 30 0 0 0 80 50A30 32 0 0 1 20 50" fill="#00bfbf">
                            <animateTransform attributeName="transform" type="rotate" calcMode="linear" values="0 50 51;360 50 51" keyTimes="0;1" dur="1s" begin="0s" repeatCount="indefinite"></animateTransform>
                        </path>
                    </svg>
                </div>
                <div id="vizoo-limelm-license-popup-content"></div>
            </div>
        </div>
    <?php endif; ?>
</section>