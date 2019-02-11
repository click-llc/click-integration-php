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
                'merchant_id' => 3233,
                'service_id' => 12713,
                'user_id' => 11154,
                'secret_key' => '6ZRZmAt8OG'
            ]
        ]
    ]);
    $application->run();
});
