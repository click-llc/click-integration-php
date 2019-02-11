<?php

namespace click\requests;

use click\utils\Helper;
use click\exceptions\ClickException;

/**
 * @name Request class
 * 
 * @example
 *      $request = new Request();
 *      $post = $request->post();
 */
class Request{
    /** @var request array-like */
    private $request;
    /** @var heper \click\utils\Helper objectc */
    private $helper;

    /**
     * Payments constructor
     */
    public function __construct(){
        // set helper
        $this->helper = new Helper();
        // set request
        $this->request = $_POST;
        if(count($this->request) == 0){
            // setting rest api request body
            $request_body = file_get_contents('php://input');
            // parsing the request body
            $this->request = json_decode($request_body, true);
            // check request to possible
            if(!$this->request){
                throw new ClickException(
                    'Incorrect JSON-RPC object',
                    ClickException::ERROR_INVALID_JSON_RPC_OBJECT
                );
            }
        }
    }

    /**
     * @name payment method, this can detect the method type and can prepare data to process
     * @param provider array-like
     * @return response array-like
     * 
     * @example
     *      $request = new Request();
     *      $request->payment([
     *          ... 
     *      ]);
     */
    public function payment($provider){
        // check data to prepare and complete method
        if(isset($this->request['action']) && $this->request['action'] != null){
            // check action to prepare
            if((int)$this->request['action'] == 0){
                $this->request['type'] = 'prepare'; 
            }
            // check action to complete
            else{
                $this->request['type'] = 'complete';
            }
            // return respons e array-like
            return $this->request;
        }
        // check method type to invoice create
        elseif(isset($this->request['phone_number']) && $this->request['phone_number'] != null){
            // get phone number from request data
            $phone_number = $this->helper->check_phone_number($this->request['phone_number']);
            if($phone_number != null){
                if(isset($this->request['token']) && $this->request['token'] != null){
                    // get token from request
                    $token = (int) $this->request['token'];
                    return [
                        'type' => 'phone_number',
                        'token' => $token,
                        'phone_number' => $phone_number
                    ];
                }
                // return exception
                throw new ClickException(
                    'Could not make a payment without payment_id or token',
                    ClickException::ERROR_COULD_NOT_PERFORM
                );
            }
            // return exception
            throw new ClickException(
                'Incorrect phone number',
                ClickException::ERROR_COULD_NOT_PERFORM
            );
        }
        // check type to create card token
        else if(isset($this->request['card_number']) && $this->request['card_number'] != null){
            // get card number
            $card_number = $this->helper->check_card_number($this->request['card_number']);
            if($card_number != null){
                if(isset($this->request['token']) && $this->request['token'] != null){
                    // get token
                    $token = (int) $this->request['token'];
                    // get temporary
                    $temporary = 1;
                    if(isset($this->request['temporary'])){
                        $temporary = $this->request['temporary'];
                    }
                    // return response array-like
                    return [
                        'type' => 'card_number',
                        'token' => $token,
                        'card_number' => $card_number,
                        'expire_date' => $this->request['expire_date'],
                        'temporary' => $temporary
                    ];
                }
                // return exception
                throw new ClickException(
                    'Could not make a payment without token',
                    ClickException::ERROR_COULD_NOT_PERFORM
                );
            }
            // return exception
            throw new ClickException(
                'Incorrect card number',
                ClickException::ERROR_COULD_NOT_PERFORM
            );
        }
        // check method type to verify card token
        else if(isset($this->request['sms_code']) && $this->request['sms_code'] != null){
            $sms_code = (string) $this->request['sms_code'];
            if(isset($this->request['token']) && $this->request['token'] != null){
                // get token from request data
                $token = (int) $this->request['token'];
                // return response array-like
                return [
                    'type' => 'sms_code',
                    'token' => $token,
                    'sms_code' => $sms_code
                ];
            }
            // return response array-like
            throw new ClickException(
                'Could not make a payment without payment_id or token',
                ClickException::ERROR_COULD_NOT_PERFORM
            );
        }
        // check method type to payment via card token
        else if(isset($this->request['card_token']) && $this->request['card_token'] != null){
            // get card token
            $card_token = $this->request['card_token'];
            if(isset($this->request['token']) && $this->request['token'] != null){
                // get token from request data
                $token = (int) $this->request['token'];
                // return response
                return [
                    'type' => 'card_token',
                    'token' => $token,
                    'card_token' => $card_token
                ];
            }
            // return exception
            throw new ClickException(
                'Could not make a payment without payment_id or token',
                ClickException::ERROR_COULD_NOT_PERFORM
            );
        }
        // check method type to delete card token
        else if(isset($this->request['delete_card_token']) && $this->request['delete_card_token'] != null){
            // get card token
            $card_token = $this->request['delete_card_token'];
            if(isset($this->request['token']) && $this->request['token'] != null){
                // get token
                $token = (int) $this->request['token'];
                // return response as array-like
                return [
                    'type' => 'delete_card_token',
                    'token' => $token,
                    'card_token' => $card_token
                ];
            }
            // return exception
            throw new ClickException(
                'Could not make a payment without payment_id or token',
                ClickException::ERROR_COULD_NOT_PERFORM
            );
        }
        // check method type to check invoice id
        else if(isset($this->request['check_invoice_id']) && $this->request['check_invoice_id'] != null){
            // get invoice id from request data
            $invoice_id = $this->request['check_invoice_id'];
            if(isset($this->request['token']) && $this->request['token'] != null){
                // get token id from request data
                $token = (int) $this->request['token'];
                // return response array-like
                return [
                    'type' => 'check_invoice_id',
                    'token' => $token,
                    'invoice_id' => $invoice_id
                ];
            }
            // return exception
            throw new ClickException(
                'Could not make a payment without payment_id or token',
                ClickException::ERROR_COULD_NOT_PERFORM
            );
        }
        // check method type to check payment status
        else if(isset($this->request['payment_id']) && $this->request['payment_id'] != null){
            // return response array-like
            return [
                'type' => 'check_payment',
                'payment_id' => (int)$this->request['payment_id']
            ];
        }
        // check method type to check payment status via merchant trans id
        else if(isset($this->request['merchant_trans_id']) && $this->request['merchant_trans_id'] != null){
            // get merchant trans id from request data
            $merchant_trans_id = (int)$this->request['merchant_trans_id'];
            if(isset($this->request['token']) && $this->request['token'] != null){
                // token from request
                $token = (int)$this->request['token'];
                // return response
                return [
                    'type' => 'merchant_trans_id',
                    'token' => $token,
                    'merchant_trans_id' => $merchant_trans_id
                ];
            }
            // return en exception
            throw new ClickException(
                'Could not make a payment without payment_id or token',
                ClickException::ERROR_COULD_NOT_PERFORM
            );
        }
        // checl method type to payment cancel
        else if(isset($this->request['cancel_payment_id']) && $this->request['cancel_payment_id'] != null){
            // return response array-like
            return [
                'type' => 'cancel',
                'payment_id' => $this->request['cancel_payment_id']
            ];
        }
    }
    /**
     * @name post method
     */
    public function post(){
        // return the request data array-like
        return $this->request;
    }
}