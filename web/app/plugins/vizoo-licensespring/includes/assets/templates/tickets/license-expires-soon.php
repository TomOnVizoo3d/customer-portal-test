<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!is_a($license, 'Vizoo_LicenseSpring_License')) {
    exit;
}

?>
Details of the license:
ID: <a href="https://saas.licensespring.com/1770/orders/<?= $license->get_order() ?>/<?= $license->get_id() ?>">#<?= $license->get_id() ?></a>
Licensee: <?= $license->get_licensee() . PHP_EOL ?>
Expires on: <?= $license->get_formatted_renewal_date() . PHP_EOL ?>

(This is an automatically generated message)