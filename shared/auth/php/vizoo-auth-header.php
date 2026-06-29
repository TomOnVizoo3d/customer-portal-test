<?php

function vizoo_get_salts()
{
    $lines = array();

    $contents = file_get_contents("../salts.txt");
    $separator = "\r\n";
    $line = strtok($contents, $separator);

    while ($line != null) {
        $line = trim($line);
        if ($line[0] !== ';') {
            $lines[] = $line;
        }

        $line = strtok($separator);
    }

    return $lines;
}

function vizoo_validate_body($body)
{
    $hashed_body = hash('sha512', $body, true);
    list($index_1, $index_2, $token) = explode(' ', $_SERVER['HTTP_X_VIZOO_AUTHENTICATION'], 3);
    $salts = vizoo_get_salts();

    return hash('sha512', hex2bin($salts[$index_1]) . $hashed_body . hex2bin($salts[$index_2])) === $token;
}

function vizoo_generate_authentication_header($body)
{
    $salts = vizoo_get_salts();
    $max_number = count($salts);
    $response_index_1 = random_int(0, $max_number);
    $response_index_2 = random_int(0, $max_number - 1);
    if ($response_index_2 >= $response_index_1) {
        $response_index_2++;
    }

    $response_token = hash('sha512', hex2bin($salts[$response_index_1]) . hash('sha512', $body, true) . hex2bin($salts[$response_index_2]));

    return $response_index_1 . ' ' . $response_index_2 . ' ' . $response_token;
}
