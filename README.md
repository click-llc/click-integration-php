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
```

## Installation and dump autoload via composer
```
composer install
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

## Quick Start
### 1) Create Model
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
### Create the application for rest api
```php
use click\applications\Application;
use click\models\Payments;

$model = new Payments();
$application = new Application([
    'model' => $model
]);
```

### Create the application with application session for authtorization via token
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
