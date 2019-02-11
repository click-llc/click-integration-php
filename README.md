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
$model = new MyPayments();
```
#### SHOP Api methods
Prepare
```php
$model->prepare([
    'click_trans_id' => 1111,
    'service_id' => 2222,
    'click_paydoc_id' => 3333,
    'merchant_trans_id' =>  '11111',
    'amount' => 1000.0,
    'action' => 0,
    'error' => 0,
    'error_note' => 'Success',
    'sign_time' => 'YYYY-MM-DD HH:mm:ss',
    'sign_string' => 'AAAAAAAAAAAAAAAAAAAAAAAAAA'
]);
```
Complete
```php
$model->complete([
    'click_trans_id' => 1111,
    'service_id' => 2222,
    'click_paydoc_id' => 3333,
    'merchant_trans_id' =>  '11111',
    'merchant_prepare_id' => 11111,
    'amount' => 1000.0,
    'action' => 1,
    'error' => 0,
    'error_note' => 'Success',
    'sign_time' => 'YYYY-MM-DD HH:mm:ss',
    'sign_string' => 'AAAAAAAAAAAAAAAAAAAAAAAAAA'
]);
```

#### Merchant Api methods
Note : All of the merchant api methods return the CLICK-MERCHANT-API response as array-like

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

#### 2) Overwrite the some methods over the Payments
```php
use click\models\Payments;
class MyPayments extends Payments{
    /**
     * @param data array-like
     * @return response \GuzzleHttp\Client
     */
    public function on_invoice_creating($data){
        ...
        $response = $this->client->request('POST', 'invoice/create', [
            ...
        ]);
        ...
        return $response;
    }
    /**
     * @param request array-like
     * @param response \GuzzleHttp\Client object
     * @param token string
     * @return response array-like|null
     */
    public function on_invoice_created($request, $response, $token){
        ...
        if($response->getStatusCode() == 200){
            $result = (array)json_decode((string) $response->getBody());
            ...
            $this->model->update_by_token($token, [
                ...
            ]);
            ...
        }
        ...
        return $result;
    }
}
```
List of the Payments methods
1) `on_invoice_creating` and `on_invoice_created` for create invoice
2) `on_invoice_checking` and `on_invoice_checked` for check invoice
3) `on_canceling` and `on_canceled` for cancel payment
4) `on_card_token_creating` and `on_card_token_created` for create card token
5) `on_card_token_verifying` and `on_card_token_verified` for verify card token
6) `on_card_token_paying` and `on_card_token_payed` for payment via card token
7) `on_card_token_deleting` and `on_card_token_deleted` for delete card token
8) `on_payment_checking` and `on_payment_checked` for check payment status by merchant_id
9) `on_checking_with_merchant_trans_id` and `on_checked_with_merchant_trans_id` for check payment status by merchant_trans_id

If you want check the payment user to exists, complete
this method

```php
use click\models\Payments;
class MyPayments extends Payments{
    /**
     * @name on_user_is_exists method
     * @param payment array-like
     * @return response boolean|null
     */
    protected function on_user_is_exists($payment){
        ...
    }
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
