# CLICK Integration PHP
This is a capable to `"CLICK"` integration library, written in `PHP`.
This library is an easy to use, powerful and rapid php integration library.

## Installation via Composer
```
cd click-integration-php
composer create-project click/integration-module
```

## Installation via Git
```
git clone https://github.com/click-llc/click-integration-php.git
cd click-integration-php
composer install
```

And you can use the our library after require `\vendor\autoload.php` file
```php
require(__DIR__ . '\vendor\autoload.php');
```

## Documentation contents
### Configuration
- Click configuration
- Database configuration
### Quick start
- Create model
- Create application
    - Application session
    - Some basic methods to easy use
    - Some spiecial methods
### Advanced
- Rewrite the methods over the Model

## Documentation
### Configuration
Your can set your configurations via `click/configs.php` file.
#### Click configuration
```php
return [
    ...
    'provider' => [
        'endpoint' => 'https://api.click.uz/v2/merchant/',
        'click' => [
            'merchant_id' => 1111,
            'service_id' => 2222,
            'user_id' => 3333,
            'secret_key' => 'AAAAAAAA'
        ]
    ]
    ...
]
```

#### Database configuration
```php
return [
    ...
    'db' => [
        'dsn' => 'mysql:host=localhost;dbname=<your_db_name>',
        'username' => 'root',
        'password' => ''
    ]
    ...
]
```

### Quick Start
#### 1) Create Model
You can use the `\cick\models\Payments` model
```php
use click\models\Payments;
$model = new Payments();
```
Or can create yourself payments model via `\click\models\Payments` class
```php
use click\models\Payments;
class MyPayments extends Payments{
    ...
}
```
#### 2) Create the application for rest api
```php
use click\applications\Application;
use click\models\Payments;

$model = new Payments();
$application = new Application([
    'model' => $model
]);
```

#### Methods of Payments
Create invoice
```php
$model->create_invoice([
    'token' => 'aaaa-bbbb-cccc-ddddddd',
    'phone_number' => '998112222222'
]);
```
Check invoice status
```php
$model->check_invoice([
    'token' => 'aaaa-bbbb-cccc-ddddddd',
    'invoice_id' => 2222
]);
```
Create card token
```php
$model->create_card_token([
    'token' => 'aaaa-bbbb-cccc-ddddddd',
    'card_number' => 'AAAA-BBBB-CCCC-DDDD',
    'expire_date' => 'BBEE',
    'temporary' => 1
]);
```
Verify card token
```php
$model->verify_card_token([
    'token' => 'aaaa-bbbb-cccc-ddddddd',
    'sms_code' => '12345'
]);
```
Payment with card token
```php
$model->payment_with_card_token([
    'token' => 'aaaa-bbbb-cccc-ddddddd',
    'card_token' => 'AAAAAA-BBBB-CCCC-DDDDDDD'
]);
```
Delete card token
```php
$model->delete_card_token([
    'token' => 'aaaa-bbbb-cccc-ddddddd',
    'card_token' => 'AAAAAA-BBBB-CCCC-DDDDDDD'
]);
```
Check payment status by `payment_id`
```php
$model->check_payment([
    'token' => 'aaaa-bbbb-cccc-ddddddd',
    'payment_id' => 1111
]);
```
Check payment status by `merchant_trans_id`
```php
$model->merchant_trans_id([
    'token' => 'aaaa-bbbb-cccc-ddddddd',
    'merchant_trans_id' => 1111
]);
```
Cancel payment (reversal)
```php
$model->cancel([
    'token' => 'aaaa-bbbb-cccc-dddddddd',
    'payment_id' => 1111
]);
```

#### 3) Create the application with application session for authtorization via token
```php
use click\applications\Application;
use click\models\Payments;

Application::session('<YOUR_AUTH_TOKEN>', ['/prepare', '/complete'], function(){
    $model = new Payments();
    $application = new Application([
        'model' => $model
    ]);
    $application->run();
});
```
