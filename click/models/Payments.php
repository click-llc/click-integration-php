<?php

//  ██████╗██╗     ██╗ ██████╗██╗   ██╗    ██╗    ██╗██████████╗
// ██╔════╝██║     ██║██╔════╝██║ ██╔═╝    ██║    ██║      ██╔═╝
// ██║     ██║     ██║██║     ████╔═╝      ██║    ██║    ██╔═╝
// ██║     ██║     ██║██║     ██║ ██╗      ██║    ██║  ██══╝
// ╚██████╗███████╗██║╚██████╗██║   ██╗ ██╗█████████║██████████╗
//  ╚═════╝╚══════╝╚═╝ ╚═════╝╚═╝   ╚═╝ ╚═╝╚════════╝╚═════════╝

namespace click\models;

use click\models\Model;
use click\utils\Security;
use click\utils\Helper;
use click\utils\Configs;
use click\requests\Request;
use click\exceptions\ClickException;
use click\models\PaymentsStatus;
use click\models\BasePayments;

/**
 * @name Payments : class, extended BasePayments
 * The Payments class is the basic class to perform your transactions,
 * it capable to auto transform the model by click billing system.
 * 
 * Read more information by https://docs.click.uz/ link.
 * 
 * @example: over writing the some methods
 * 
 * class MyPayments extends Payments{
 *      public function on_invoice_creating($data){
 *          ...
 *          $response = $this->client->request('POST', 'invoice/create', [
 *              'json' => $data
 *          ]);
 *          ...
 *          return $response;
 *      }
 *      
 *      public function on_invoice_created($request, $response, $token){
 *          ...
 *          if($response->getStatusCode() == 200){
 *              $result = (array)json_decode((string) $response->getBody());
 *              ...
 *          }
 *          ...
 *          return $result;
 *      }
 * }
 */

class Payments extends BasePayments{

    /** @var client : \GuzzleHttp\Client object, it used to making the http requests */
    protected $client = null;
    /** @var provider : array-like, it has need included the some configs of click merchant system */
    protected $provider = null;
    /** @var model : \click\models\Model object, it used for connecting to database and perform same processes over the database */
    protected $model = null;
    /** @var helper : \click\utils\Helper object, included same helpfull methods */
    protected $helper = null;
    /** @var request : \clikc\request\Request object */
    protected $request = null;
    /** @var configs : \clikc\utils\Configs object */
    protected $configs = null;

    /**
     * The Payments constructor
     * @param params array-like, this params has included the db configs
     */
    public function __construct($params){
        $this->configs = new Configs();
        if($params == null){
            $params = $this->configs->get_database_configs();
        }
        $this->model = new Model($params);
        $this->helper = new Helper();
        $this->request = new Request();
    }

    /**
     * Initilaziation the provilder configs
     * @param provider array-like, this params has included the click configs
     * @example:
     *      $payments = new Payments($params);
     *      $payments->init_provider([
     *          ...
     *      ]);
     * Note: The Payments model can be able to connect to click after
     *      provider inited
     */
    public function init_provider($provider){
        $this->provider = $provider;
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => 'https://api.click.uz/v2/merchant/',
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Auth' => (
                    $this->provider['user_id'] . ':' . sha1($this->helper->timestamp . $this->provider['secret_key']) . ':' . $this->helper->timestamp
                )
            ]
        ]);
    }

    /**
     * @name create_invoice : method, create invoice
     * @param request array-like
     * @return response array-like
     * 
     * @example:
     *      $model = new Payments($params);
     *      $model->create_invoice([
     *          'token' => 'aaaa-bbbc-cccc-dddddddd',
     *          'phone_number' => '998XXYYYYYYY'
     *      ]);
     */
    public function create_invoice($request = null){
        // check the request to nan-null
        if($request == null){
            $request = $this->request->post();
        }
        // getting payment data from payments model
        $data = $this->model->find_by_token($request['token']);
        // checking the data status to possible
        if($data['status'] != PaymentsStatus::INPUT && $data['status'] != PaymentsStatus::REFUNDED){
            return [
                'error_code' => -31300,
                'error_note' => 'Payment in processing'
            ];
        }
        // prepare the data
        $json = [
            'service_id' => $this->provider['service_id'],
            'merchant_trans_id' => $this->get_merchant_trans_id($request['token']),
            'phone_number' => $request['phone_number'],
            'amount' => (float) $data['total']
        ];
        // sending data to click
        $response = $this->on_invoice_creating($json);
        // preparing the data to response
        $result = $this->on_invoice_created($json, $response, $request['token']);
        // display the result if it will not be null
        if($result != null){
            return $result;
        }
        // else return the exception
        throw new ClickException(
            $response->getReasonPhrase(),
            ClickException::ERROR_INSUFFICIENT_PRIVILEGE
        );
    }

    /**
     * @name create_card_token : method, create card token
     * @param request array-like
     * @return response array-like
     * 
     * @example:
     *      $model = new Payments($params);
     *      $model->create_card_token([
     *          'token' => 'aaaa-bbbb-cccc-dddddddd',
     *          'card_number' => 'AAAA-BBBB-CCCC-DDDD',
     *          'expire_date' => 'BBEE',
     *          'temporary' => 1
     *      ]);
     */
    public function create_card_token($request = null){
        // check the request to nan-null
        if($request == null){
            $request = $this->request->post();
        }
        // getting the payment data from payments model
        $data = $this->model->find_by_token($request['token']);
        // checking the data status to possible
        if($data['status'] != PaymentsStatus::INPUT && $data['status'] != PaymentsStatus::REFUNDED){
            return [
                'error_code' => -31300,
                'error_note' => 'Payment in processing'
            ];
        }
        // check the temporary to exists
        if(!isset($request['temporary'])){
            $request['temporary'] = 1;
        }
        // prepare the data
        $json = [
            'service_id' => $this->provider['service_id'],
            'card_number' => $request['card_number'],
            'expire_date' => $request['expire_date'],
            'temporary' => $request['temporary']
        ];
        // sending data to click
        $response = $this->on_card_token_creating($json);
        // prepare the data to response
        $result   = $this->on_card_token_created($json, $response, $request['token']);
        // display the result if it will not be null
        if($result != null){
            return $result;
        }
        // else return the exception
        throw new ClickException(
            $response->getReasonPhrase(),
            ClickException::ERROR_INSUFFICIENT_PRIVILEGE
        );
    }

    /**
     * @name verify_card_token : method, verify card token
     * @param request array-like
     * @return response array-like
     * 
     * @example:
     *      $model = new Payments($params);
     *      $model->verify_card_token([
     *          'token' => 'aaaa-bbbb-cccc-ddddddddddd',
     *          'sms_code' => '12345'
     *      ]);
     */
    public function verify_card_token($request = null){
        // check the request to nan-null
        if($request == null){
            $request = $this->request->post();
        }
        // getting payment data from payments model
        $data = $this->model->find_by_token($request['token']);
        // checking the data status to possible
        if($data['status'] != PaymentsStatus::WAITING){
            throw new ClickException(
                'Payment is not stable to perform',
                ClickException::ERROR_COULD_NOT_PERFORM
            );
        }
        // preparing data
        $json = [
            'service_id' => $this->provider['service_id'],
            'card_token' => $data['card_token'],
            'sms_code' => $request['sms_code']
        ];
        // sending data to click
        $response = $this->on_card_token_verifying($json);
        // preparing the data to response
        $result   = $this->on_card_token_verified($json, $response, $request['token']);
        // display the result if it will not be null
        if($result != null){
            return $result;
        }
        // else return the exception
        throw new ClickException(
            $response->getReasonPhrase(),
            ClickException::ERROR_INSUFFICIENT_PRIVILEGE
        );
    }

    /**
     * @name payment_with_card_token : method, payment with card token
     * @param request array-like
     * @return response array-like
     * 
     * @example:
     *      $model = new Payments($params);
     *      $model->payment_with_card_token([
     *          'token' => 'aaaa-bbbb-cccc-ddddddddd',
     *          'card_token' => 'AAAAAA-BBBB-CCCC-DDDDDDD'
     *      ]);
     */
    public function payment_with_card_token($request = null){
        // check the request to nan-null
        if($request == null){
            $request = $this->request->post();
        }
        // get payment data from payments model
        $data = $this->model->find_by_token($request['token']);
        // card_token is checking for possible to product card_tokan 
        if($data['card_token'] == $request['card_token']){
            // making the data for acception
            $json = [
                'service_id' => $this->provider['service_id'],
                'card_token' => $request['card_token'],
                'amount' => (float)$data['total'],
                'merchant_trans_id' => $this->get_merchant_trans_id($request['token'])
            ];

            // sending data to click
            $response = $this->on_card_token_paying($json);
            // preparing the data to response
            $result   = $this->on_card_token_payed($json, $response, $request['token']);
            // display the result if it will not be null
            if($result != null){
                return $result;
            }
            // else return the exception
            throw new ClickException(
                $response->getReasonPhrase(),
                ClickException::ERROR_INSUFFICIENT_PRIVILEGE
            );
            return $data;
        }
        // return the exception as incorrect card token
        throw new ClickException(
            'Incorrect card token',
            ClickException::ERROR_COULD_NOT_PERFORM
        );
    }

    /**
     * @name delete_card_token : method, delete card token
     * @param request array-like
     * @return response array-like
     * 
     * @example:
     *      $model = new Payments($params);
     *      $model->delete_card_token([
     *          'token' => 'aaaa-bbbb-cccc-dddddddd',
     *          'card_token' => 'AAAAAA-BBBB-CCCC-DDDDDDD'
     *      ]);
     */
    public function delete_card_token($request = null){
        // check the request to nan-null
        if($request == null){
            $request = $this->request->post();
        }
        // getting payment data from payments model
        $data = $this->model->find_by_token($request['token']);
        // check card_token to possible
        if($data['card_token'] == $request['card_token']){
            // prepare data
            $json = [
                'service_id' => $this->provider['service_id'],
                'card_token' => $request['card_token']
            ];
            // sending data to click
            $response = $this->on_card_token_deleting($json);
            // prepare data to response
            $result   = $this->on_card_token_deleted($json, $response, $request['token']);
            // display the result if it will not be null
            if($result != null){
                return $result;
            }
            // else return the exception
            throw new ClickException(
                $response->getReasonPhrase(),
                ClickException::ERROR_INSUFFICIENT_PRIVILEGE
            );
        }
        // return the exception as incorrect card token
        throw new ClickException(
            'Incorrect card token',
            ClickException::ERROR_COULD_NOT_PERFORM
        );
    }

    /**
     * @name check_invoice : method, check invoice
     * @param request array-like
     * @return response array-like
     * 
     * @example:
     *      $model = new Payments($params);
     *      $model->check_invoice([
     *          'token' => 'aaaa-bbbb-cccc-dddddddd',
     *          'invoice_id' => 2222
     *      ]);
     */
    public function check_invoice($request = null){
        // check request to nan-null
        if($request == null){
            $request = $this->request->post();
        }
        // getting payment data from payments model
        $data = $this->model->find_by_token($request['token']);
        // check invoice_id to possible
        if($data['invoice_id'] == $request['invoice_id']){
            // prepare data
            $json = [
                'service_id' => $this->provider['service_id'],
                'invoice_id' => $request['invoice_id']
            ];
            // send request to click
            $response = $this->on_invoice_checking($json);
            // preapre the data to response
            $result   = $this->on_invoice_checked($json, $response, $request['token']);
            // display the result if it will be nan-null
            if($result != null){
                return $result;
            }
            // else return exception
            throw new ClickException(
                $response->getReasonPhrase(),
                ClickException::ERROR_INSUFFICIENT_PRIVILEGE
            );
        }
        // return the exception as incorrect invoice id
        throw new ClickException(
            'Incorrect invoice id',
            ClickException::ERROR_COULD_NOT_PERFORM
        );
    }

    /**
     * @name check_payment : method, check payment
     * @param request array-like
     * @return response array-like
     * 
     * @example:
     *      $model = new Payments($params);
     *      $model->check_payment([
     *          'token' => 'aaaa-bbbb-cccc-ddddddd',
     *          'payment_id' => 1111
     *      ]);
     * 
     *      Note : in this example, 'payment_id' is the click payment_id,
     *      it is not payments table id (*_*)
     */
    public function check_payment($request = null){
        // check the request to nan-null
        if($request == null){
            $request = $this->request->post();
        }
        // prepare the data
        $json = [
            'service_id' => $this->provider['service_id'],
            'payment_id' => $request['payment_id']
        ];
        // send request to click
        $response = $this->on_payment_checking($json);
        // preapre data to response
        $result   = $this->on_payment_checked($json, $response, $request['token']);
        // display the result if it will be nan-null
        if($result != null){
            return $result;
        }
        // else return exception
        throw new ClickException(
            $response->getReasonPhrase(),
            ClickException::ERROR_INSUFFICIENT_PRIVILEGE
        );
    }

    /**
     * @name merchant_trans_id : method, check payment status by merchant_trans_id
     * @param request array-like
     * @return response array-like
     * 
     * @example:
     *      $model = new Payments($params);
     *      $model->merchant_trans_id([
     *          'token' => 'aaaa-bbbb-cccc-ddddddd',
     *          'merchant_trans_id' => 1111
     *      ]);
     */
    public function merchant_trans_id($request = null){
        // check the request to nan-null
        if($request == null){
            $request = $this->request->post();
        }
        // prepare the data
        $json = [
            'service_id' => $this->provider['service_id'],
            'merchant_trans_id' => $request['merchant_trans_id']
        ];
        // send the request to click
        $response = $this->on_checking_with_merchant_trans_id($json);
        // preapre the data to response
        $result = $this->on_checked_with_merchant_trans_id($json, $response, $request['token']);
        // display the result if it will be nan-null
        if($result != null){
            return $result;
        }
        // else return exception
        throw new ClickException(
            $response->getReasonPhrase(),
            ClickException::ERROR_INSUFFICIENT_PRIVILEGE
        );
    }

    /**
     * @name cancel : method, calcel the payment
     * @param request array-like
     * @return response array-like
     * 
     * @example:
     *      $model = new Payments($params);
     *      $model->cancel([
     *          'token' => 'aaaa-bbbb-cccc-dddddddd',
     *          'payment_id' => 1111
     *      ]);
     * 
     *      Note : in this example, 'payment_id' is the click payment_id,
     *      it is not payments table id (*_*)
     */
    public function cancel($request = null){
        // check request to nan-null
        if($request == null){
            $request = $this->request->post();
        }
        // prepare data
        $json = [
            'service_id' => $this->provider['service_id'],
            'payment_id' => $request['payment_id']
        ];
        // send request to click
        $response = $this->on_canceling($json);
        // prepare the data to response
        $result   = $this->on_canceled($json, $response, $request['token']);
        if($result != null){
            return $result;
        }
        // else return exception
        throw new ClickException(
            $response->getReasonPhrase(),
            ClickException::ERROR_INSUFFICIENT_PRIVILEGE
        );
    }

    /**
     * @name payment : method
     * this method capable to auto detect the payment method type from your data
     * and can be helpful to quick complete your payment project
     * 
     * @return response array-like
     * 
     * @example:
     *      $model = new Payments($params);
     *      $model->payment();
     */
    public function payment(){
        // checking the provider is inited
        if($this->provider == null){
            throw new ClickException(
                'Coult not perform the request without provider',
                ClickException::ERORR_COULD_NOT_PERFORM
            );
        }

        // used request->payment method for detect the method type and
        // getting possible params
        $check = $this->request->payment($this->provider);

        // paying with phone number
        if($check['type'] == 'phone_number'){
            return $this->create_invoice($check);
        }

        // card token creating
        if($check['type'] == 'card_number'){
            $this->create_card_token($check);
        }

        // card token verifying
        if($check['type'] == 'sms_code'){
            $this->verify_card_token($check);
        }

        // paying with card token
        if($check['type'] == 'card_token'){
            $this->payment_with_card_token($check);
        }

        // deleting the card token
        if($check['type'] == 'delete_card_token'){
            $this->delete_card_token($check);
        }

        // check invoice id
        if($check['type'] == 'check_invoice_id'){
            $this->check_invoice($check);
        }

        // check payment
        if($check['type'] == 'check_payment'){
            $this->check_payment($check);
        }

        // payment status by merchant_trans_id
        if($check['type'] == 'merchant_trans_id'){
            $this->merchant_trans_id($check);
        }

        // cancel the payment
        if($check['type'] == 'cancel'){
            $this->cancel($check);
        }

        // return the exception as method type coult not detect
        throw new ClickException(
            'Could not detect the method',
            ClickException::ERROR_INSUFFICIENT_PRIVILEGE
        );
    }
}