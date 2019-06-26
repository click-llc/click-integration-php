<?php
require(__DIR__ . '\vendor\autoload.php');

use click\applications\Application;
use click\models\Payments;

Application::session('JKhkjANmjHAJjbnKAhA', ['/prepare', '/complete'], function(){
    $payments = new Payments([
        'db' => [
            'dsn' => 'mysql:host=localhost;dbname=module',
            'username' => 'root',
            'password' => ''
        ]
    ]);
    $application = new Application([
        'type' => 'json',
        'model' => $payments,
        'configs' => [
            'click' => [
                'merchant_id' => 'YOUR MERCHANT ID',
                'service_id' => 'YOUR SERVICE ID',
                'user_id' => 'YOUR MERCHANT USER ID',
                'secret_key' => 'SECRET KEY'
            ]
        ]
    ]);
    $application->run();
});
