<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!is_a($license, 'Vizoo_LicenseSpring_License')) {
    exit;
}

?>
Hi there,

Your xTex license is expiring in <?= $license->get_days_left() ?> days. Details of the license:
ID: <?= $license->get_id() . PHP_EOL ?>
Licensee: <?= $license->get_licensee() . PHP_EOL ?>
Expires on: <?= $license->get_formatted_renewal_date() . PHP_EOL ?>

If you wish to renew your license please visit the customer portal at https://customers.vizoo3d.com/licenses.

Your license guarantees access to the xTex software, direct support by phone and email as well as access to the Customer Portal, including documentation, video tutorials and a support ticket system.

Kind regards,
Vizoo Customer Service
(This is an automatically generated email)