<?php

return [
    'provider' => [
        'endpoint' => 'https://api.click.uz/v2/merchant/',
        'click' => [
            'merchant_id' => 1111,
            'service_id' => 2222,
            'user_id' => 33333,
            'secret_key' => 'AAAAAA'
        ]
    ],
    'db' => [
        // basic configs
        'dsn' => 'mysql:host=localhost;dbname=module',
        'username' => 'root',
        'password' => ''
    ]
];