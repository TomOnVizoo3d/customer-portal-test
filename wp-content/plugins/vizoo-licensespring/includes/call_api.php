<?php

if (!defined('ABSPATH')) {
    exit;
}

function call_api($url, $headers, $body, $method = null)
{
    $channel = curl_init();
    curl_setopt($channel, CURLOPT_URL, $url);
    if (!empty($headers)) {
        curl_setopt($channel, CURLOPT_HTTPHEADER, $headers);
    }
    curl_setopt($channel, CURLOPT_RETURNTRANSFER, 1);
    if ($method === null) {
        $method = $_SERVER['REQUEST_METHOD'];
    }
    curl_setopt($channel, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($channel, CURLOPT_POSTFIELDS, $body);

    $response = curl_exec($channel);

    $httpcode = curl_getinfo($channel, CURLINFO_RESPONSE_CODE);
    curl_close($channel);

    return [(int) $httpcode, $response];
}
