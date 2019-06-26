<?php

//  ██████╗██╗     ██╗ ██████╗██╗   ██╗    ██╗    ██╗██████████╗
// ██╔════╝██║     ██║██╔════╝██║ ██╔═╝    ██║    ██║      ██╔═╝
// ██║     ██║     ██║██║     ████╔═╝      ██║    ██║    ██╔═╝
// ██║     ██║     ██║██║     ██║ ██╗      ██║    ██║  ██══╝
// ╚██████╗███████╗██║╚██████╗██║   ██╗ ██╗█████████║██████████╗
//  ╚═════╝╚══════╝╚═╝ ╚═════╝╚═╝   ╚═╝ ╚═╝╚════════╝╚═════════╝

return [
    'provider' => [
        'endpoint' => 'https://api.click.uz/v2/merchant/',
        'click' => [
            'merchant_id' => 'YOUR MERCHANT ID',
            'service_id' => 'YOUR SERVICE ID',
            'user_id' => 'YOUR MERCHANT USER ID',
            'secret_key' => 'SECRET KEY'
        ]
    ],
    'db' => [
        // basic configs
        'dsn' => 'mysql:host=localhost;dbname=module',
        'username' => 'root',
        'password' => ''
    ]
];