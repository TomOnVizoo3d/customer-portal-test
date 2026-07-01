<?php

if (!defined('ABSPATH')) {
    exit;
}

$noscript = isset($_GET['noscript']);

?>
<form id="vizoo-limelm-license-renewal-popup-content" action="" method="POST">
    <fieldset class="vizoo-limelm-license-renewal-popup-fieldset">
        We're sorry, but your request could not be processed. Please try again later.<br />
        If this issue persists, please contact our support
        <?php if (!empty($error_msg)) : ?>
            and pass the following error message to them:<br />
            <pre><?= $error_msg ?></pre>
        <?php else : ?>
            .<br />
        <?php endif; ?>
    </fieldset>
    <?php if ($noscript) : ?>
        <a class="vizoo-limelm-license-renewal-cancel" href="?noscript=1"><i class="fa fa-times fa-fw"></i> Back</a>
    <?php endif; ?>
</form>
<?php if (!$noscript) : ?>
    <section id="vizoo-limelm-license-popup-control">
        <button id="vizoo-limelm-license-renewal-button-cancel" class="vizoo-limelm-license-renewal-button" onclick="closeLicensePopup();"><i class="fa fa-check fa-fw"></i> Back</button>
    </section>
<?php endif; ?>