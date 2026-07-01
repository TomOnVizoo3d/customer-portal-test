<?php

if (!defined('ABSPATH')) {
    exit;
}

$user = wp_get_current_user();
$userrole = get_user_meta($user->ID, 'vizoo_customerrole', true);
$userCompanyId = intval(get_user_meta($user->ID, "vizoo_organization", true));
$organizationDetails = Vizoo_Pipedrive_API_Handler::get_organization_details($userCompanyId);
$manager = Vizoo_Pipedrive_API_Handler::get_user_by_id($organizationDetails["owner_id"]);
?>

<section id="vizoo-licensespring-wrapper" class="licensespring-wrapper">
    <?php if ($userrole != "LicenseManager") : ?>
        <div class="no-overview-container">
        <h2>No License Overview Available:</h2>
        <span>You currently do not have access to the license and billing information. Either you do not have any licenses or your account does not have the permission to access licenses. If you need access to the license page, please contact our <a href="mailto:support@vizoo3d.com">support</a></span>
        </div>
    <?php else : ?>
    <div class="access-denied"></div>
    <div class="sales-contact-wrapper">
        <div class="pic-and-name">
            <img src="<?php echo Vizoo_LicenseSpring_Template_Handler::get_contact_img_path($manager["name"]) ?>" alt="Picture of your sales contact.">
        </div>
        <div class="contact-details">
            <h2>Your Vizoo Contact</h2>
            <h3><?php echo $manager["name"]; ?></h3>
            <a href="mailto:<?php echo $manager["email"] ?>"><?php echo $manager["email"] ?></a>
            <span>Contact me for help with renewal, adding software seats, or questions about our solutions.</span>
        </div>
    </div>
    <h2 class="license-heading">Subscriptions</h2>
    <div id="vizoo-licensespring-overview-loader" class="vizoo-licensespring-loader" style="display: none;">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" class="lds-eclipse">
            <path ng-attr-d="{{config.pathCmd}}" ng-attr-fill="{{config.color}}" stroke="none" d="M20 50A30 30 0 0 0 80 50A30 32 0 0 1 20 50" fill="#00bfbf">
                <animateTransform attributeName="transform" type="rotate" calcMode="linear" values="0 50 51;360 50 51" keyTimes="0;1" dur="1s" begin="0s" repeatCount="indefinite"></animateTransform>
            </path>
        </svg>
    </div>
    <section id="vizoo-licensespring-licenses-container" class="licenses-container">
        <?php foreach ($licenses as $license) : ?>
            <?php self::render_license($license); ?>
        <?php endforeach; ?>
    </section>
    <input type="hidden" id="ajax_url" value="<?= admin_url('admin-ajax.php'); ?>" />
    <input type="hidden" id="vizoo_licensespring_get_licenses" value="<?= wp_create_nonce('vizoo_licensespring_get_licenses'); ?>" />

    <div id="vizoo-licensespring-license-popup-wrapper" style="display:none;">
        <div id="vizoo-licensespring-license-popup-container">
            <div id="vizoo-licensespring-license-popup-title-bar">
                <h3 id="vizoo-licensespring-license-popup-title">Loading</h3>
                <button id="vizoo-licensespring-license-popup-button-close" class="vizoo-licensespring-license-button" onclick="licenseSpring.closeLicensePopup();">
                    <i class="fa fa-times fa-fw"></i>
                </button>
            </div>
            <div id="vizoo-licensespring-license-popup-loader" class="vizoo-licensespring-loader" style="display: none;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" class="lds-eclipse">
                    <path ng-attr-d="{{config.pathCmd}}" ng-attr-fill="{{config.color}}" stroke="none" d="M20 50A30 30 0 0 0 80 50A30 32 0 0 1 20 50" fill="#00bfbf">
                        <animateTransform attributeName="transform" type="rotate" calcMode="linear" values="0 50 51;360 50 51" keyTimes="0;1" dur="1s" begin="0s" repeatCount="indefinite"></animateTransform>
                    </path>
                </svg>
            </div>
            <div id="vizoo-licensespring-license-popup-content"></div>
        </div>
    </div>
    <div class="renewal-guidelines-container">
        <h2>
            License Renewal Guidelines
        </h2>
        <span class="renewal-guide-description">
            Instructions for requesting a subscription quote or invoice.
        </span>
        <a href="<?php echo wp_get_attachment_url(5083) ?>" class="button-medium" target="_blank" rel="noopener">
            <i class="fa-solid fa-download"></i>
            <span class="download-btn-text">Download PDF</span>
        </a>
    </div>
    <?php endif ?>
</section>