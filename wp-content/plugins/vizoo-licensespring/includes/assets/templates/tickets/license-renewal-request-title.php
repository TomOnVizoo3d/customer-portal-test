<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!is_a($license, 'Vizoo_LicenseSpring_License')) {
    exit;
}

?>
License renewal (#<?= $license->get_id() ?>)