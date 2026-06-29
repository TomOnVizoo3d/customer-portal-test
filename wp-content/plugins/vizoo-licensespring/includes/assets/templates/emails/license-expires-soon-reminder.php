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

If you wish to renew your license please visit the Customer Portal at https://customers.vizoo3d.com/licenses.

Did you encounter any issues doing so? Please don't hesitate to contact our support at https://customers.vizo3d.com/support or write us an email to support@vizoo3d.com.

Do you have questions regarding your license? Contact your Vizoo account manager or write an email to info@vizoo3d.com. Together we'll find a solution that fits your demand.

Kind regards,
Vizoo Customer Service
(This is an automatically generated email)