<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!is_a($license, 'Vizoo_LicenseSpring_License') || !isset($license_period) || !isset($license_comment) || !isset($license_price)) {
    exit;
}

?>
Hello, please renew my license:

ID: <a href="https://saas.licensespring.com/1770/orders/<?= $license->get_order() ?>/<?= $license->get_id() ?>">#<?= $license->get_id() ?></a>
Expiration date: <?= $license->get_formatted_renewal_date() . PHP_EOL ?>

Length: <?= ($license_period * 12) ?> Months

Price: <?= $license_price . PHP_EOL ?>
New expiration date: <?= (clone $license->get_renewal_date())->modify('+' . $license_period . ' years')->format('d-M-Y') . PHP_EOL ?>
<?php if ($license_comment != '') : ?>
    <?= $license_comment ?>
<?php endif; ?>