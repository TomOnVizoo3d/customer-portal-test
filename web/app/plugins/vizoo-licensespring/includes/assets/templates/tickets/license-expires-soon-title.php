<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!is_a($license, 'Vizoo_LicenseSpring_License')) {
    exit;
}

?>
The license #<?= $license->get_id() ?> is expiring soon. (Licensee: <?= $license->get_licensee() ?>)